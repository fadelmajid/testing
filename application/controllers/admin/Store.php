<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Store extends MY_Admin
{
    function __construct()
    {
        parent::__construct();
    }
    
    public function index()
    {
        show_404();
    }

    //=== START STORE
    public function store()
    {
        // validasi menu dan assign title
        $submenu_code   = 'store';
        $permits        = $this->_check_menu_access($submenu_code, 'view');

        $this->_set_title('Stores');
        $this->load->library('pagination');
        $this->load->model('storedb');
        $this->load->model('productdb');
        $this->load->model('admindb');
        
        //set variable
        $current_url        = ADMIN_URL.'store/store';
        $page               = $this->input->get('page');
        $search             = set_var($this->input->get('search'), '');
        $open_time          = set_var($this->input->get('open'), '00:00');
        $close_time         = set_var($this->input->get('close'), '23:59');
        $sort_col           = set_var($this->input->get('sc'), 'name');
        $sort_by            = set_var($this->input->get('sb'), 'ASC');//ASC or DESC
        $xtra_var['search'] = $search;

        //set sortable col
        $allow_sort = [
            'id'                    => 'st_id',
            'code'                  => 'st_code',
            'name'                  => 'st_name',
            'type'                  => 'st_type',
            'status'                => 'st_status',
            'phone'                 => 'st_phone',
            'link'                  => 'st_dllink',
            'address'               => 'st_address',
            'open_time'             => 'st_open',
            'open_delivery_time'    => 'st_delivery_open',
            'is_visibility'         => 'is_visibility',
            'courier'               => 'st_courier',
            'desc'                  => 'st_desc',
            'concept'               => 'st_concept'
        ];
        
        //start query
        $url_query      = "search={$search}&sc={$sort_col}&sb={$sort_by}";
        $search_where   = " AND (st_id LIKE ? OR st_code LIKE ? OR st_name LIKE ? OR st_type LIKE ? OR st_status LIKE ? OR st_phone LIKE ? OR st_dllink LIKE ? OR st_address LIKE ?  OR is_visibility LIKE ? OR st_courier LIKE ? OR st_desc LIKE ? OR st_concept LIKE ?) AND st_open >= ? AND st_close <= ? ";
        $search_data    = [$search.'%', "%{$search}%", "%{$search}%", "%{$search}%", "%{$search}%", "%{$search}%", "%{$search}%", "%{$search}%", "%{$search}%", "%{$search}%", "%{$search}%", "%{$search}%", $open_time, $close_time];
        $search_order   = sort_table_order($allow_sort, $sort_col, $sort_by);
        $all_data       = $this->storedb->getpaging(
            $search_where,
            $search_data,
            $search_order,
            $page
        );
        
        // start pagination setting
        $config = [
            'base_url'      => $current_url.($url_query != '' ? '?'.$url_query : ''),
            'total_rows'    => $all_data['total_row'],
            'per_page'      => $all_data['per_page']
        ];

        $this->pagination->initialize($config);
        // end pagination setting

        // >>show store without product<<
        $arr_store          = [];
        $arr_store_product  = [];
        $list_store_name    = [];
        $search_groupby     = " GROUP BY st_id";
        $list_store         = $this->storedb->getall();
        $list_store_product = $this->productdb->getall_store_product($search_groupby);

        //tampung st_id dari store
        foreach($list_store as $store){
            $arr_store[] = $store->st_id;
        }

        //tampung st_id dari store_product
        foreach($list_store_product as $store_product){
            $arr_store_product[] = $store_product->st_id;
        }
        
        //tampung st_id dari store yang tidak ada di store_product
        $list_store_id  = array_diff($arr_store, $arr_store_product);

        $where          = "AND st_id IN ? ";
        $data           = [$list_store_id];
        
        if(!empty($list_store_id)){
            $list_store     = $this->storedb->getall($where, $data);

            //get nama store yg st_idnya tidak ada di store_product
            foreach($list_store as $store){
                $list_store_name[] = $store->st_name;
            }
        }
        // >>end show store without product<<

        // >>show store without barista<<
        $arr_store_barista      = [];
        $list_store_name_br     = [];
        $where_br               = " AND role_id = ? ".$search_groupby;
        $data_role              = $this->config->item('setup_admin')['role']['barista'];
        $data_br                = [$data_role];
        $list_store_barista     = $this->admindb->getall_store_barista($where_br, $data_br);

        //tampung st_id dari setup_barista
        foreach($list_store_barista as $store_barista){
            $arr_store_barista[] = $store_barista->st_id;
        }
        
        //tampung st_id dari store yang tidak ada di setup_barista
        $list_store_id_br      = array_diff($arr_store, $arr_store_barista);
        if(!empty($list_store_id_br)){
            $where_br          = " AND is_visibility = ? ".$where;
            $data_visible      = $this->config->item('store')['is_visible']['show'];
            $data_br           = [$data_visible, $list_store_id_br];
            $list_store_br     = $this->storedb->getall($where_br, $data_br);

            //get nama store yg st_idnya tidak ada di setup_barista
            foreach($list_store_br as $store){
                $list_store_name_br[] = $store->st_name;
            }
        }        

        // >>end show store without barista<<

        // select data & assign variable $data
        $data = [
            'current_url'   => $current_url,
            'form_url'      => $config['base_url'],
            'page_url'      => str_replace($url_query, '', $config['base_url']),
            'xtra_var'      => $xtra_var,
            'search'        => $search,
            'open_time'     => $this->input->get('open'),
            'close_time'    => $this->input->get('close'),
            'permits'       => $permits,
            'cst_status'    => $this->config->item('store')['status'],
            'cst_store'     => $this->config->item('store')['is_visible'],
            'data'          => $all_data['data'],
            'st_without_pd' => $list_store_name,
            'st_without_br' => $list_store_name_br,
            'pagination'    => $this->pagination->create_links()
        ];
        $this->_render('store/store', $data);
    }
    
    public function store_add($id = 0)
    {
        $this->load->model('storedb');
        $this->load->model('productdb');

        // validate user roles
        $submenu_code = 'store';
        if ($id > 0) {
            $permits = $this->_check_menu_access($submenu_code, 'edit');
            $this->_set_title('Edit Store');
        } else {
            $permits = $this->_check_menu_access($submenu_code, 'add');
            $this->_set_title('Add Store');
        }
        $form_permit = $this->_get_form_permit($id, $permits);
        
        // validate if product exists
        if ($id > 0) {
            $store = $this->storedb->get($id);
            if (empty($store)) {
                redirect(ADMIN_URL);
            }
        }
        
        $this->load->library('form_validation');
        $this->form_validation->set_error_delimiters(PREFIX_ERROR_DELIMITER, SUFFIX_ERROR_DELIMITER);
        $this->form_validation->set_rules('st_code', 'Store Code', 'strip_tags|trim|required|callback__is_unique_store_code');
        $this->form_validation->set_rules('st_name', 'Store Name', 'strip_tags|trim|required|callback__is_unique_store_name');
        $this->form_validation->set_rules('st_type', 'Type', 'strip_tags|trim|required');
        $this->form_validation->set_rules('st_status', 'Store Status', 'strip_tags|trim|required');
        $this->form_validation->set_rules('st_phone', 'Phone', 'strip_tags|trim|required|callback__is_integer|min_length[10]|max_length[15]');
        $this->form_validation->set_rules('st_dllink', 'Download Link', 'strip_tags|trim|required');
        $this->form_validation->set_rules('st_address', 'Address', 'strip_tags|trim|required');
        $this->form_validation->set_rules('st_lat', 'Latitude', 'strip_tags|trim|required');
        $this->form_validation->set_rules('st_long', 'Longitude', 'strip_tags|trim|required');
        $this->form_validation->set_rules('st_open', 'Open Pickup Time', 'strip_tags|trim|required');
        $this->form_validation->set_rules('st_close', 'Close Pickup Time', 'strip_tags|trim|required|callback__close_later_than_open');
        $this->form_validation->set_rules('st_delivery_open', 'Open Delivery Time', 'strip_tags|trim|required');
        $this->form_validation->set_rules('st_delivery_close', 'Close Delivery Time', 'strip_tags|trim|required|callback__close_later_than_open');
        $this->form_validation->set_rules('courier_code', 'Courier Code', 'strip_tags|trim|required');
        $this->form_validation->set_rules('is_visibility', 'Is Visibility', 'strip_tags|trim|required');
        $this->form_validation->set_rules('st_desc', 'Store Description', 'strip_tags|trim');
        $this->form_validation->set_rules('st_concept', 'Store Concept', 'strip_tags|trim|required');
        
        $err_msg = [];
        if ($this->form_validation->run()) {
            if(empty($err_msg)){
                $store          = $this->storedb->get($id);
                $courier_code   = $this->input->post('courier_code');
                $barista        = $this->input->post('barista') == 1 ? true : false;
                
                $rules  = [
                    'courier_code'      => $courier_code,
                    'barista'           => $barista
                ];

                $params      = [
                    'st_name'           => $this->input->post('st_name'),
                    'st_code'           => $this->input->post('st_code'),
                    'st_type'           => $this->input->post('st_type'),
                    'st_status'         => $this->input->post('st_status'),
                    'st_address'        => $this->input->post('st_address'),
                    'st_phone'          => $this->input->post('st_phone'),
                    'st_dllink'         => $this->input->post('st_dllink'),
                    'st_lat'            => $this->input->post('st_lat'),
                    'st_long'           => $this->input->post('st_long'),
                    'st_open'           => $this->input->post('st_open'),
                    'st_close'          => $this->input->post('st_close'),
                    'st_delivery_open'  => $this->input->post('st_delivery_open'),
                    'st_delivery_close' => $this->input->post('st_delivery_close'),
                    'is_visibility'     => $this->input->post('is_visibility'),
                    'st_courier'        => json_encode($rules),
                    'st_desc'           => $this->input->post('st_desc'),
                    'st_concept'        => $this->input->post('st_concept')
                ];

                if ($id > 0 && in_array('edit', $permits)) {
                    // If the administrator set/modify the Store Type permanently
                    $st_default_type = $this->input->post('st_default_type')  == 1 ? true : false;
                    if($st_default_type) 
                    {
                        $params['st_default_type']   = $this->input->post('st_type');
                    }

                    // If the administrator set/modify the Store Status permanently
                    $st_default_status = $this->input->post('st_default_status')  == 1 ? true : false;
                    if($st_default_status) 
                    {
                        $params['st_default_status']   = $this->input->post('st_status');
                    }

                    $params['updated_by']   = $this->_get_user_id();
                    $params['updated_date'] = date('Y-m-d H:i:s');
                    if ($this->storedb->update($id, $params)) {
                        $err_msg = [
                            'msg'   => 'Edit Store Success',
                            'type'  => 'success'
                        ];
                    } else {
                        $err_msg = [
                            'msg'   => 'Edit Store Failed!',
                            'type'  => 'danger'
                        ];
                    }

                } else if ($id <= 0 && in_array('add', $permits)) {
                    // If Add Store, set st_default_type = st_type & set st_default_status = st_status
                    $params['st_default_type']   = $this->input->post('st_type');
                    $params['st_default_status']   = $this->input->post('st_status');

                    $params['created_by']   = $this->_get_user_id();
                    $params['created_date'] = date('Y-m-d H:i:s');

                    $err_msg = [
                        'msg'   => 'Add Store Failed!',
                        'type'  => 'danger'
                    ];

                    if ($this->storedb->insert($params)) {
                        $search_where   = "AND created_date >= ? AND st_status = ? ";
                        $data           = [$params['created_date'], $params["st_status"]];
                        $orderby        = " st_id DESC ";
                        $max_store      = $this->storedb->getall($search_where, $data, $orderby, $limit = 1);
                        $st_id[]        = $max_store[0]->st_id;

                        if ($this->productdb->import_store_product($st_id, $this->_get_user_id())) {                
                            $err_msg = [
                                'msg'   => 'Add Store Success.'.js_clearform(),
                                'type'  => 'success'
                            ];
                        }
                    }
                } else {
                    $err_msg = [
                        'msg'   => 'Access denied - You are not authorized to access this page.',
                        'type'  => 'danger'
                    ];
                }
            }
        }
        $store          = $this->storedb->get($id);
        $st_courier     = isset($store->st_courier) ? json_decode($store->st_courier, true) : [];

        $data = [
            'current_url'   => ADMIN_URL.'store/store_add',
            'msg'           => set_form_msg($err_msg),
            'permits'       => $permits,
            'show_form'     => $form_permit['show_form'],
            'title_form'    => $form_permit['title_form'],
            'cst_store'     => $this->config->item('store'),
            'arr_code'      => $this->storedb->get_courier(),
            'store'         => $store,
            'st_courier'    => $st_courier
        ];
        $this->_render('store/store_add', $data);
    }

    public function _close_later_than_open($close_time)
    {
        $open_time = $this->input->post('st_open');
        
        // if base price is not 0, validate final price must be less than base price
        if ($close_time <= $open_time) {
            $this->form_validation->set_message('_close_later_than_open', 'The Close Time must be later than Open Time.');
            return false;       
      }
      return true;
    }

    public function _is_unique_store_name($store_name)
    {
        $this->load->model('storedb');
        $id     = $this->input->post('st_id');
        // if duplicate name found and not belong to selected ID, return false
        $store  = $this->storedb->get_by_name($store_name);
        if ($store && $store->st_id !== $id) {
            $this->form_validation->set_message('_is_unique_store_name', 'The Store Name field must contain a unique value.');
            return false;
        }
        return true;
    }

    public function _is_unique_store_code($store_code)
    {
        $this->load->model('storedb');
        $id     = $this->input->post('st_id');
        // if duplicate name found and not belong to selected ID, return false
        $store  = $this->storedb->get_by_code($store_code);
        if ($store && $store->st_id !== $id) {
            $this->form_validation->set_message('_is_unique_store_code', 'The Store Code field must contain a unique value.');
            return false;
        }
        return true;
    }

    public function _is_integer($st_phone)
    {
        $st_phone = $this->input->post('st_phone');
        if(!preg_match('/^([0-9])+$/i', $st_phone) || (substr($st_phone,0,2) == "02")){
            $this->form_validation->set_message('_is_integer', 'The %s field may only contain integer and not start with 02');
            return FALSE;
        } else {
            return TRUE;
        }
        
    }

    //=== END STORE

    //=== START STORE OPERATIONAL
    public function store_operational()
    {
        $this->load->model('storedb');
        // validasi menu dan assign title
        $submenu_code = 'store_operational';
        $permits = $this->_check_menu_access($submenu_code, 'view');

        $this->_set_title('Store Operational');
        $this->load->library('pagination');
        
        //set variable
        $current_url        = ADMIN_URL.'store/store_operational';
        $page               = $this->input->get('page');
        $search             = set_var($this->input->get('search'), '');
        $sort_col           = set_var($this->input->get('sc'), '');
        $sort_by            = set_var($this->input->get('sb'), 'ASC');//ASC or DESC
        $xtra_var['search'] = $search;

        //set sortable col
        $allow_sort = [
            'st_name'       => 'st_name',
            'monday'        => 'monday',
            'tuesday'       => 'tuesday',
            'wednesday'     => 'wednesday',
            'thursday'      => 'thursday',
            'friday'        => 'friday',
            'saturday'      => 'saturday',
            'sunday'        => 'sunday',
            'start_date'    => 'start_date',
            'created'       => 'created_date',
        ];
        
        //start query
        $url_query      = "search={$search}&sc={$sort_col}&sb={$sort_by}";
        $search_where   = " AND (sto_id LIKE ? OR store.st_name LIKE ? OR monday LIKE ? OR tuesday LIKE ? OR wednesday LIKE ? OR thursday LIKE ? OR friday LIKE ? OR saturday LIKE ? OR sunday LIKE ?  OR start_date LIKE ? OR end_date LIKE ? OR sto_status LIKE ? OR store_opt.created_date LIKE ? OR store_opt.updated_date LIKE ? ) ";
        $search_data    = [$search.'%', "%{$search}%", "%{$search}%", "%{$search}%", "%{$search}%", "%{$search}%", "%{$search}%", "%{$search}%", "%{$search}%", "%{$search}%", "%{$search}%", "%{$search}%", "%{$search}%", "%{$search}%"];
        $search_order   = sort_table_order($allow_sort, $sort_col, $sort_by);
        $all_data       = $this->storedb->getpaging_store_opt(
            $search_where,
            $search_data,
            $search_order,
            $page
        );
        
        // start pagination setting
        $config = [
            'base_url' => $current_url.($url_query != '' ? '?'.$url_query : ''),
            'total_rows' => $all_data['total_row'],
            'per_page' => $all_data['per_page']
        ];

        $this->pagination->initialize($config);
        // end pagination setting

        $arr_admin          = $this->admindb->getarr_admin();

        // select data & assign variable $data
        $data = [
            'current_url'   => $current_url,
            'form_url'      => $config['base_url'],
            'page_url'      => str_replace($url_query, '', $config['base_url']),
            'xtra_var'      => $xtra_var,
            'search'        => $search,
            'permits'       => $permits,
            'arr_admin'     => $arr_admin,
            'cst_status'    => $this->config->item('store_operational')['stopt_status'],
            'data'          => $all_data['data'],
            'pagination'    => $this->pagination->create_links()
        ];
        $this->_render('store/store_operational', $data);
    }
    
    public function store_operational_add($id = 0)
    {

        $this->load->model('storedb');
        // validate user roles
        $submenu_code = 'store_operational';
        if ($id > 0) {
            $permits = $this->_check_menu_access($submenu_code, 'edit');
            $this->_set_title('Edit Store Operational');
        } else {
            $permits = $this->_check_menu_access($submenu_code, 'add');
            $this->_set_title('Add Store Operational');
        }
        $form_permit = $this->_get_form_permit($id, $permits);

        // validate if product exists
        if ($id > 0) {
            $store_opt = $this->storedb->get_store_opt($id);

            if (empty($store_opt)) {
                redirect(ADMIN_URL);
            }
        }

        $cst_day    = $this->config->item('store_operational')['day_name'];
        $cst_status = $this->config->item('store_operational')['store_opt_status'];
        $cst_type   = $this->config->item('store_operational')['store_type'];

        $this->load->library('form_validation');
        $this->form_validation->set_error_delimiters(PREFIX_ERROR_DELIMITER, SUFFIX_ERROR_DELIMITER);
        $this->form_validation->set_rules('st_id', 'Store Name', 'strip_tags|trim|required');
        $this->form_validation->set_rules('start_date', 'Start Date', 'strip_tags|trim|required');
        $this->form_validation->set_rules('end_date', 'End Date', 'strip_tags|trim|required|callback__end_date_later_than_start_date');
        $this->form_validation->set_rules('store_opt_list[]', 'Status', 'strip_tags|trim|required');

        foreach($cst_day as $day)
        {   
            $store_type = $this->input->post('store_opt_list['.$day.'][st_type]');
                        
            //conditional for store type validation
            if($store_type == 'all'){
                $this->form_validation->set_rules('store_opt_list['.$day.'][pickup]['.$cst_status['open'].']', 'Open Pickup Time', 'strip_tags|trim|required');
                $this->form_validation->set_rules('store_opt_list['.$day.'][pickup]['.$cst_status['close'].']', 'Close Pickup Time', 'strip_tags|trim|required|callback__pickup_close_later_than_open['.$day.']');
                $this->form_validation->set_rules('store_opt_list['.$day.'][delivery]['.$cst_status['open'].']', 'Open Delivery Time', 'strip_tags|trim|required');
                $this->form_validation->set_rules('store_opt_list['.$day.'][delivery]['.$cst_status['close'].']', 'Close Delivery Time', 'strip_tags|trim|required|callback__delivery_close_later_than_open['.$day.']');
            }
            else if($store_type == 'pickup_only')
            {
                $this->form_validation->set_rules('store_opt_list['.$day.'][pickup]['.$cst_status['open'].']', 'Open Pickup Time', 'strip_tags|trim|required');
                $this->form_validation->set_rules('store_opt_list['.$day.'][pickup]['.$cst_status['close'].']', 'Close Pickup Time', 'strip_tags|trim|required|callback__pickup_close_later_than_open['.$day.']');
            }
            else if($store_type == 'delivery_only')
            {
                $this->form_validation->set_rules('store_opt_list['.$day.'][delivery]['.$cst_status['open'].']', 'Open Delivery Time', 'strip_tags|trim|required');
                $this->form_validation->set_rules('store_opt_list['.$day.'][delivery]['.$cst_status['close'].']', 'Close Delivery Time', 'strip_tags|trim|required|callback__delivery_close_later_than_open['.$day.']');               
            }
        }

        $err_msg = [];
        if ($this->form_validation->run()) {
            if(empty($err_msg)){

                //set value ke database
                foreach($cst_day as $day)
                {
                    $post           = $this->input->post("store_opt_list"); 
                    $status         = isset($post[$day]['status']) ? $post[$day]['status'] : '' ;
                    $st_type        = isset($post[$day]['st_type']) ? $post[$day]['st_type'] : '';
                    $open_pickup    = isset($post[$day]['pickup']['open']) ? $post[$day]['pickup']['open'] : '';
                    $close_pickup   = isset($post[$day]['pickup']['close']) ? $post[$day]['pickup']['close'] : '';
                    $open_delivery  = isset($post[$day]['delivery']['open']) ? $post[$day]['delivery']['open'] : '';
                    $close_delivery = isset($post[$day]['delivery']['close']) ? $post[$day]['delivery']['close'] : '';
                    $rules[]        = [
                        'status'    => $status,
                        'st_type'   => $st_type,
                        'pickup'    => [
                            'open'      => $open_pickup,
                            'close'     => $close_pickup
                        ],
                        'delivery'  => [
                            'open'      => $open_delivery,
                            'close'     => $close_delivery
                        ]
                    ]; 
                }

                $params = [
                    'st_id'         => $this->input->post('st_id'),
                    'start_date'    => $this->input->post('start_date'),
                    'end_date'      => $this->input->post('end_date'),
                    'monday'        => json_encode($rules[0]),
                    'tuesday'       => json_encode($rules[1]),
                    'wednesday'     => json_encode($rules[2]),
                    'thursday'      => json_encode($rules[3]),
                    'friday'        => json_encode($rules[4]),
                    'saturday'      => json_encode($rules[5]),
                    'sunday'        => json_encode($rules[6]),
                ];

                if ($id > 0 && in_array('edit', $permits)) {
                    $params['updated_by']   = $this->_get_user_id();
                    $params['updated_date'] = date('Y-m-d H:i:s');
                    $params['sto_status']   = $store_opt->sto_status;

                    if ($this->storedb->update_store_opt($id, $params)) {
                        $err_msg = [
                            'msg'   => 'Edit Store Operational Success',
                            'type'  => 'success'
                        ];
                    } else {
                        $err_msg = [
                            'msg'   => 'Edit Store Operational Failed!',
                            'type'  => 'danger'
                        ];
                    }

                } else if ($id <= 0 && in_array('add', $permits)) {
                    $params['created_by']   = $this->_get_user_id();
                    $params['created_date'] = date('Y-m-d H:i:s');
                    $params['sto_status']   = $this->config->item('store_operational')['stopt_status']['active'];

                    if ($this->storedb->insert_store_opt($params)) {
                        $err_msg = [
                            'msg'   => 'Add Store Operational Success.'.js_clearform(),
                            'type'  => 'success'
                        ];
                    } else {
                        $err_msg = [
                            'msg' => 'Add Store Operational Failed!',
                            'type' => 'danger'
                        ];
                    }
                } else {
                    $err_msg = [
                        'msg' => 'Access denied - You are not authorized to access this page.',
                        'type' => 'danger'
                    ];
                }
            }
        }

        $monday         = isset($store_opt->monday) ? json_decode($store_opt->monday, true) : [];
        $tuesday        = isset($store_opt->tuesday) ? json_decode($store_opt->tuesday, true) : [];
        $wednesday      = isset($store_opt->wednesday) ? json_decode($store_opt->wednesday, true) : [];
        $thursday       = isset($store_opt->thursday) ? json_decode($store_opt->thursday, true) : [];
        $friday         = isset($store_opt->friday) ? json_decode($store_opt->friday, true) : [];
        $saturday       = isset($store_opt->saturday) ? json_decode($store_opt->saturday, true) : [];
        $sunday         = isset($store_opt->sunday) ? json_decode($store_opt->sunday, true) : [];

        $data = [
            'current_url'   => ADMIN_URL.'store/store_operational_add',
            'msg'           => set_form_msg($err_msg),
            'permits'       => $permits,
            'show_form'     => $form_permit['show_form'],
            'title_form'    => $form_permit['title_form'],
            'cst_status'    => $this->config->item('store_operational')['store_opt_status'],
            'cst_type'      => $this->config->item('store_operational')['store_type'],
            'monday'        => $monday,
            'tuesday'       => $tuesday,
            'wednesday'     => $wednesday,
            'thursday'      => $thursday,
            'friday'        => $friday,
            'saturday'      => $saturday,
            'sunday'        => $sunday,
            'store_opt'     => $this->storedb->get_store_opt($id),
            'store'         => $this->storedb->getall()      
        ];

        $this->_render('store/store_operational_add', $data);
    }

    //check close pickup time later than open pickup time or not
    public function _pickup_close_later_than_open($close_time, $day_param)
    {   
        $pickup     = $this->input->post('store_opt_list');
        $cst_day    = $this->config->item('store_operational')['day_name'];
        $open_time  = "";

        foreach($cst_day as $day)
        { 
            if(preg_match('/^(['.$day.'])+$/i', $day_param)){
                $open_time = isset($pickup[$day]['pickup']['open']) ? $pickup[$day]['pickup']['open'] : '';
                if ($close_time <= $open_time) {
                    $this->form_validation->set_message('_pickup_close_later_than_open', 'The Close Time must be later than Open Time.');
                    return false;       
                }
            }
        }
        return true; 
    } 

    //check delivery pickup time later than open delivery time or not
    public function _delivery_close_later_than_open($close_time, $day_param)
    {   
        $delivery   = $this->input->post('store_opt_list');
        $cst_day    = $this->config->item('store_operational')['day_name'];
        $open_time  = "";

        foreach($cst_day as $day)
        { 
            if(preg_match('/^(['.$day.'])+$/i', $day_param)){
                $open_time = isset($delivery[$day]['delivery']['open']) ? $delivery[$day]['delivery']['open'] : '';
                if ($close_time <= $open_time) {
                    $this->form_validation->set_message('_delivery_close_later_than_open', 'The Close Time must be later than Open Time.');
                    return false;       
                }
            }
        }
        return true; 
    }

    //check end date later than start date
    public function _end_date_later_than_start_date($end_date)
    {   
        $start_date   = $this->input->post('start_date');

        if ($end_date <= $start_date) {
            $this->form_validation->set_message('_end_date_later_than_start_date', 'The End Date must be later than Start Date.');
            return false;       
        }
        return true; 
    }
    
    //=== END STORE OPERATIONAL

    //>>START STORE IMAGE<<
    public function store_image()
    {
        // validasi menu dan assign title
        $submenu_code   = 'store_image';
        $permits        = $this->_check_menu_access($submenu_code, 'view');
        $this->_set_title('Store Image');
        $this->load->library('pagination');
        $this->load->model('storedb');

        //set variable
        $current_path       = 'store/store_image';
        $current_url        = ADMIN_URL.$current_path;
        $page               = $this->input->get('page');
        $search             = set_var($this->input->get('search'), '');
        $sort_col           = set_var($this->input->get('sc'), '');
        $sort_by            = set_var($this->input->get('sb'), 'ASC');//ASC or DESC
        $xtra_var['search'] = $search;

        //set sortable col
        $allow_sort = [
            'id'            => 'sti_id',
            'name'          => 'st.st_name',
            'order'         => 'sti_order',
            'status'        => 'sti_status',
            'created'       => 'created_date',
            'updated'       => 'updated_date'
        ];
        
        //start query
        $url_query      = "search={$search}&sc={$sort_col}&sb={$sort_by}";
        $search_where   = " AND (sti_id LIKE ? OR st.st_name LIKE ? OR sti_order LIKE ? OR sti_status LIKE ? ) ";
        $search_data    = [$search.'%', "%{$search}%", "%{$search}%", "%{$search}%"];
        $search_order   = sort_table_order($allow_sort, $sort_col, $sort_by);
        $all_data       = $this->storedb->getpaging_store_img(
            $search_where,
            $search_data,
            $search_order,
            $page
        );
        
        // start pagination setting
        $config = [
            'base_url'      => $current_url.($url_query != '' ? '?'.$url_query : ''),
            'total_rows'    => $all_data['total_row'],
            'per_page'      => $all_data['per_page']
        ];

        $this->pagination->initialize($config);
        // end pagination setting

        $arr_admin          = $this->admindb->getarr_admin();
        // select data & assign variable $data
        $data = [
            'current_url'   => $current_url,
            'form_url'      => $config['base_url'],
            'page_url'      => str_replace($url_query, '', $config['base_url']),
            'xtra_var'      => $xtra_var,
            'search'        => $search,
            'permits'       => $permits,
            'arr_admin'     => $arr_admin,
            'all_data'      => $all_data['data'],
            'cst_status'    => $this->config->item('store_image')['status'],
            'pagination'    => $this->pagination->create_links()
        ];
        $this->_render('store/store_image', $data);
    }

    public function store_image_add($id = 0)
    {
        //load
        $this->load->model('storedb');

        // validate user roles
        $submenu_code = 'store_image';
        if ($id > 0) {
            $permits = $this->_check_menu_access($submenu_code, 'edit');
            $this->_set_title('Edit Store Image');
        } else {
            $permits = $this->_check_menu_access($submenu_code, 'add');
            $this->_set_title('Add Store Image');
        }
        $form_permit = $this->_get_form_permit($id, $permits);
        
        // validate if store_image exists
        if ($id > 0) {
            $store_image = $this->storedb->get_store_img($id);
            if (empty($store_image)) {
                redirect(ADMIN_URL);
            }
        }
        
        $this->load->library('form_validation');
        $this->form_validation->set_error_delimiters(PREFIX_ERROR_DELIMITER, SUFFIX_ERROR_DELIMITER);
        $this->form_validation->set_rules('st_id', 'Store', 'strip_tags|trim|required');
        $this->form_validation->set_rules('st_name', 'Store', 'strip_tags|trim|required');
        $this->form_validation->set_rules('sti_img[]', 'Store Image', 'callback__check_upload_image_st_img');
        $this->form_validation->set_rules('sti_order', 'Store Order', 'strip_tags|trim|integer');

        $err_msg = [];
        if ($this->form_validation->run()) {
            //BEGIN UPLOAD IMAGE
            $image_name = '';
            $image = [];

            foreach($_FILES['sti_img']['tmp_name'] as $key_image => $val_image) {
                if (is_uploaded_file($val_image)) {
    
                    $this->load->library("google_cloud_bucket");
                    $image_name = str_replace(UPLOAD_PATH, '', STORE_IMG_IMAGE_PATH).$_FILES['sti_img']['name'][$key_image];
    
                    if(!@fopen(UPLOAD_URL.$image_name, 'r')){
                        $data = [
                            "source" => $val_image,
                            "name"   => $image_name
                        ];
                        
                        $this->google_cloud_bucket->upload_image($data);
                    }
                    $image[] = $image_name;
                }
                //END UPLOAD IMAGE
            }

            if(empty($err_msg)){
                $params = [
                    'st_id'         => $this->input->post('st_id'),
                    'sti_order'     => set_var($this->input->post('sti_order'), 1)
                ];
    
                if ($id > 0 && in_array('edit', $permits)) {
                    if($image_name != ''){
                        $params['sti_img']  = $image_name;
                    }
                    $params['updated_by']   = $this->_get_user_id();
                    $params['updated_date'] = date('Y-m-d H:i:s');
    
                    if ($this->storedb->update_store_img($id, $params)) {
                        $this->storedb->sort_store_img_order($id, $params['sti_order']);
                        $err_msg = [
                            'msg'   => 'Edit Store Image Success',
                            'type'  => 'success'
                        ];
                    } else {
                        $err_msg = [
                            'msg'   => 'Edit Store Image Failed!',
                            'type'  => 'danger'
                        ];
                    }
                } else if ($id <= 0 && in_array('add', $permits)) {
                    $params['created_by']   = $this->_get_user_id();
                    $params['created_date'] = date('Y-m-d H:i:s');
                    
                    if(count($image) > 0) {
                        foreach($image as $key => $value) {
                            $params['sti_img'] = $value;
                            $store_img = $this->storedb->insert_store_img($params);
                            if ($store_img) {
                                $this->storedb->sort_store_img_order($store_img, $params['sti_order']);
                                $err_msg = [
                                    'msg'   => 'Add Store Image  Success.'.js_clearform(),
                                    'type'  => 'success'
                                ];
                            } else {
                                $err_msg = [
                                    'msg'   => 'Add Store Image  Failed!',
                                    'type'  => 'danger'
                                ];
                            }
                        }
                    } else{
                        $store_img = $this->storedb->insert_store_img($params);
                        if ($store_img) {
                            $this->storedb->sort_store_img_order($store_img, $params['sti_order']);
                            $err_msg = [
                                'msg'   => 'Add Store Image  Success.'.js_clearform(),
                                'type'  => 'success'
                            ];
                        } else {
                            $err_msg = [
                                'msg'   => 'Add Store Image  Failed!',
                                'type'  => 'danger'
                            ];
                        }
                    }
                } else {
                    $err_msg = [
                        'msg'   => 'Access denied - You are not authorized to access this page.',
                        'type'  => 'danger'
                    ];
                }
            }
        }
        
        $store_img  = $this->storedb->getall_store_img(' AND sti.sti_id = ? ', [$id]);
        $data       = [
            'current_url'   => ADMIN_URL.'store/store_image_add',
            'msg'           => set_form_msg($err_msg),
            'permits'       => $permits,
            'show_form'     => $form_permit['show_form'],
            'title_form'    => $form_permit['title_form'],
            'store_img'     => isset($store_img[0]) ? $store_img[0] : $store_img
        ];
        $this->_render('store/store_image_add', $data);
    }
    
    public function _check_upload_image_st_img()
    {
        $id = $this->input->post('sti_id');

        $allowed_mime_type_arr  = array('image/gif','image/jpeg','image/pjpeg','image/png','image/x-png');
        
        if(!empty($_FILES['sti_img']['name'])){
            foreach($_FILES['sti_img']['name'] as $key_image => $val_image){
                
                if($_FILES['sti_img']['size'][$key_image] > 500000) {
                    $this->form_validation->set_message('_check_upload_image_st_img', 'Max file size is 500kb.');
                    return false;
                }

                $mime                   = get_mime_by_extension($val_image);
                if(in_array($mime, $allowed_mime_type_arr)){
                    return true;
                }else{
                    $this->form_validation->set_message('_check_upload_image_st_img', 'Please select only gif/jpg/png file.');
                    return false;
                }
            }
        }else if($id <= 0){
            $this->form_validation->set_message('_check_upload_image_st_img', 'Please select a file.');
            return false;
        }    
        return true;
    }

    //>>END STORE IMAGE<<

    //=== START STORE CONSTANT ===
    public function store_constant($id = 1)
    {
        // validasi menu dan assign title
        $submenu_code = 'store_constant';
        $permits = $this->_check_menu_access($submenu_code, 'edit');

        $this->_set_title('Store Constant');
        $this->load->model('storedb');
        
        $form_permit = $this->_get_form_permit($id, $permits);

        $this->load->library('form_validation');
        $this->form_validation->set_error_delimiters(PREFIX_ERROR_DELIMITER, SUFFIX_ERROR_DELIMITER);
        $this->form_validation->set_rules('stct_min_cup', 'Minimal Cup', 'required|numeric');
        $this->form_validation->set_rules('stct_max_cup', 'Maximal Cup', 'required|numeric');
        $this->form_validation->set_rules('stct_min_order', 'Minimal Order', 'required|numeric');
        $this->form_validation->set_rules('stct_max_order', 'Maximal Order', 'required|numeric');
        $this->form_validation->set_rules('stct_range_data', 'Range Data', 'required|numeric');
        
        $err_msg = [];
        
        if ($this->form_validation->run()) {
            if(empty($err_msg)) {
                $params      = [
                    'stct_min_cup' => $this->input->post('stct_min_cup'),
                    'stct_max_cup' => $this->input->post('stct_max_cup'),
                    'stct_min_order' => $this->input->post('stct_min_order'),
                    'stct_max_order' => $this->input->post('stct_max_order'),
                    'stct_range_data' => $this->input->post('stct_range_data'),
                    'updated_by' => $this->_get_user_id(),
                    'updated_date' => date('Y-m-d H:i:s')
                ];

                if ($this->storedb->update_store_constant($id, $params)) {
                    $err_msg = [
                        'msg'   => 'Edit Store Constant Success',
                        'type'  => 'success'
                    ];
                } else {
                    $err_msg = [
                        'msg'   => 'Edit Store Constant Failed!',
                        'type'  => 'danger'
                    ];
                }
            }
        }

        $store_constant = $this->storedb->get_store_constant();
        
        // select data & assign variable $data
        $data = [
            'current_url'   => ADMIN_URL.'store/store_constant',
            'msg'           => set_form_msg($err_msg),
            'show_form'     => $form_permit['show_form'],
            'title_form'    => $form_permit['title_form'],
            'store_constant' => $store_constant
        ];

        $this->_render('store/store_constant', $data);
    }
    //=== END STORE CONSTANT ===
    
    //=== START STORE CONFIG ===
    public function store_config()
    {
        // validasi menu dan assign title
        $submenu_code   = 'store_config';
        $permits        = $this->_check_menu_access($submenu_code, 'view');

        $this->_set_title('Stores');
        $this->load->library('pagination');
        $this->load->model('storedb');

        //set variable
        $current_url        = ADMIN_URL.'store/store_config';
        $page               = $this->input->get('page');
        $search             = set_var($this->input->get('search'), '');
        $sort_col           = set_var($this->input->get('sc'), 'id');
        $sort_by            = set_var($this->input->get('sb'), 'ASC');//ASC or DESC
        $xtra_var['search'] = $search;

        //set sortable col
        $allow_sort = [
            'id'         => 'stcf_id',
            'name'       => 'st_name',
            'min_cup'    => 'stcf_min_cup',
            'max_cup'    => 'stcf_max_cup',
            'min_order'  => 'stcf_min_order',
            'max_order'  => 'stcf_max_order',
            'range_data' => 'stcf_range_data'
        ];

        //start query
        $url_query      = "search={$search}&sc={$sort_col}&sb={$sort_by}";
        $search_where   = " AND st.st_name LIKE ? ";
        $search_data    = ["%{$search}%"];
        $search_order   = sort_table_order($allow_sort, $sort_col, $sort_by);
        $all_data       = $this->storedb->getpaging_store_config(
            $search_where,
            $search_data,
            $search_order,
            $page
        );

        // start pagination setting
        $config = [
            'base_url' => $current_url.($url_query != '' ? '?'.$url_query : ''),
            'total_rows' => $all_data['total_row'],
            'per_page' => $all_data['per_page']
        ];

        $this->pagination->initialize($config);
        // end pagination setting

        // select data & assign variable $data
        $data = [
            'current_url'   => $current_url,
            'form_url'      => $config['base_url'],
            'page_url'      => str_replace($url_query, '', $config['base_url']),
            'xtra_var'      => $xtra_var,
            'search'        => $search,
            'permits'       => $permits,
            'data'          => $all_data['data'],
            'pagination'    => $this->pagination->create_links()
        ];
        $this->_render('store/store_config', $data);
    }

    public function store_config_add($id = 0)
    {
        // $hello = '';
        $this->load->model('storedb');
        
        // validate user roles
        $submenu_code = 'store_config';
        if ($id > 0) {
            $permits = $this->_check_menu_access($submenu_code, 'edit');
            $this->_set_title('Edit Store Config');
        } else {
            $permits = $this->_check_menu_access($submenu_code, 'add');
            $this->_set_title('Add Store Config');
        }
        $form_permit = $this->_get_form_permit($id, $permits);

        // validate if the store config exists
        $store_config = [];
        if ($id > 0) {
            $store_config = $this->storedb->get_store_config($id);
            if (empty($store_config)) {
                redirect(ADMIN_URL);
            }
        }

        $this->load->library('form_validation');
        $this->form_validation->set_error_delimiters(PREFIX_ERROR_DELIMITER, SUFFIX_ERROR_DELIMITER);
        $this->form_validation->set_rules('st_id', 'Store', 'required|numeric|callback__is_duplicate_store_id');
        $this->form_validation->set_rules('stcf_min_cup', 'Minimal Cup', 'required|numeric');
        $this->form_validation->set_rules('stcf_max_cup', 'Maximal Cup', 'required|numeric');
        $this->form_validation->set_rules('stcf_min_order', 'Minimal Order', 'required|numeric');
        $this->form_validation->set_rules('stcf_max_order', 'Maximal Order', 'required|numeric');
        $this->form_validation->set_rules('stcf_range_data', 'Range Data', 'required|numeric');

        $err_msg = [];
        
        if ($this->form_validation->run()) {
            if(empty($err_msg)) {
                $params      = [
                    'stcf_min_cup' => $this->input->post('stcf_min_cup'),
                    'stcf_max_cup' => $this->input->post('stcf_max_cup'),
                    'stcf_min_order' => $this->input->post('stcf_min_order'),
                    'stcf_max_order' => $this->input->post('stcf_max_order'),
                    'stcf_range_data' => $this->input->post('stcf_range_data')
                ];

                if ($id > 0 && in_array('edit', $permits)) {
                    $params['updated_by']   = $this->_get_user_id();
                    $params['updated_date'] = date('Y-m-d H:i:s');
                    
                    if ($this->storedb->update_store_config($id, $params)) {
                        $err_msg = [
                            'msg'   => 'Edit Store Config Success',
                            'type'  => 'success'
                        ];
                    } else {
                        $err_msg = [
                            'msg'   => 'Edit Store Config Failed!',
                            'type'  => 'danger'
                        ];
                    }

                } else if ($id <= 0 && in_array('add', $permits)) {
                    $params['st_id']        = $this->input->post('st_id');
                    $params['created_by']   = $this->_get_user_id();
                    $params['created_date'] = date('Y-m-d H:i:s');
                    
                    if ($this->storedb->insert_store_config($params)) {
                        $err_msg = [
                            'msg'   => 'Add Store Config Success.'.js_clearform(),
                            'type'  => 'success'
                        ];
                    } else {
                        $err_msg = [
                            'msg' => 'Add Store Config Failed!',
                            'type' => 'danger'
                        ];
                    }
                } else {
                    $err_msg = [
                        'msg' => 'Access denied - You are not authorized to access this page.',
                        'type' => 'danger'
                    ];
                }
            }
        }

        $data = [];
        $where = "";
        $order = "";
        // If in Edit State, get only one store data
        if ($id > 0) {
            
            $data[] = $store_config->st_id;
            $where = " AND st_id = ?";
        } 
        // If in Add State, get all store
        else {
            $order = " st_name ASC";
        }
        $store = $this->storedb->getall($where, $data, $order);
        
        $data = [
            'current_url'   => ADMIN_URL.'store/store_config_add',
            'msg'           => set_form_msg($err_msg),
            'permits'       => $permits,
            'show_form'     => $form_permit['show_form'],
            'title_form'    => $form_permit['title_form'],
            'store_config'  => $store_config,
            'store'         => $store
        ];
        $this->_render('store/store_config_add', $data);
    }
    
    public function _is_duplicate_store_id($store_id)
    {
        $this->load->model('storedb');

        $stcf_id = $this->input->post('stcf_id');
        
        $store_config = $this->storedb->check_duplicate_store_config($stcf_id, $store_id);
        
        if ($store_config) {
            $this->form_validation->set_message('_is_duplicate_store_id', 'The Store already have a config data.');
            return false;
        }
        return true;
    }
    //=== END STORE CONFIG ===
}

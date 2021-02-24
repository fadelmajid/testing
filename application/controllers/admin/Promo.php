<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Promo extends MY_Admin
{
    function __construct()
    {
        parent::__construct();

        $this->load->model('promodb');
        $this->load->model('productdb');
    }


    // START PROMO
    public function index()
    {
        // validasi menu dan assign title
        $submenu_code = 'promo';
        $permits = $this->_check_menu_access($submenu_code, 'view');

        $this->_set_title('Promo');
        $this->load->library('pagination');

        //set variable
        $page = $this->input->get('page');
        $search = set_var($this->input->get('search'), '');
        $sort_col = set_var($this->input->get('sc'), 'id');
        $sort_by = set_var($this->input->get('sb'), 'DESC');//ASC or DESC
        $xtra_var['search'] = $search;

        //set sortable col
        $allow_sort = [
            'id' => 'prm_id',
            'name' => 'prm_name',
            'code' => 'prm_custom_code',
            'start' => 'prm_start',
            'type' => 'prm_type',
            'status' => 'prm_status',
            'visible' => 'prm_visible',
            'by' => 'created_by',
            'date' => 'created_date'
        ];

        $arr_admin      = $this->admindb->getarr_admin();

        //start query
        $url_query = "search={$search}&sc={$sort_col}&sb={$sort_by}";
        $search_where = " AND (prm_id LIKE ? OR prm_name LIKE ? OR prm_custom_code LIKE ? OR prm_start LIKE ? OR prm_type LIKE ? OR prm_status LIKE ? OR prm_visible LIKE ? OR created_by LIKE ? OR created_date LIKE ?) ";
        $search_data = [$search.'%', "%{$search}%", "%{$search}%", "%{$search}%", "%{$search}%", "%{$search}%", "%{$search}%", "%{$search}%", "%{$search}%"];
        $search_order = sort_table_order($allow_sort, $sort_col, $sort_by);
        $all_data = $this->promodb->getpaging_promo(
            $search_where,
            $search_data,
            $search_order,
            $page
        );

        // start pagination setting
        $config = [
            'base_url' => ADMIN_URL.'promo'.($url_query != '' ? '?'.$url_query : ''),
            'total_rows' => $all_data['total_row'],
            'per_page' => $all_data['per_page']
        ];

        $this->pagination->initialize($config);
        // end pagination setting

        // select data & assign variable $data
        $data = [
            'form_url' => $config['base_url'],
            'page_url' => str_replace($url_query, '', $config['base_url']),
            'xtra_var' => $xtra_var,
            'search' => $search,
            'permits' => $permits,
            'cst_status' => $this->config->item('promo')['status'],
            'all_data' => $all_data['data'],
            'arr_admin' => $arr_admin,
            'pagination' => $this->pagination->create_links()
        ];
        $this->_render('promo/promo', $data);
    }

    public function promo_add($id = 0)
    {
        // validate user roles
        $submenu_code = 'promo';
        if ($id > 0) {
            $permits = $this->_check_menu_access($submenu_code, 'edit');
            $this->_set_title('Edit Promo');
        } else {
            $permits = $this->_check_menu_access($submenu_code, 'add');
            $this->_set_title('Add Promo');
        }
        $form_permit = $this->_get_form_permit($id, $permits);
        $product    = $this->productdb->getall_product();
        $promo_code = $this->config->item('promo')['item_type'];

        // validate if promo exists
        $promo = [];
        if ($id > 0) {
            $promo = $this->promodb->get_promo($id);
            if (empty($promo)) {
                redirect(ADMIN_URL);
            }
        }

        $this->load->library('form_validation');
        $this->form_validation->set_error_delimiters(PREFIX_ERROR_DELIMITER, SUFFIX_ERROR_DELIMITER);
        $this->form_validation->set_rules('prm_name', 'Promo Name', 'strip_tags|trim|required');
        $this->form_validation->set_rules('prm_start', 'Promo Start', 'required');
        $this->form_validation->set_rules('prm_end', 'Promo End', 'required|callback__validate_date');
        $this->form_validation->set_rules('prm_img', 'Promo Image', 'callback__check_upload_image');
        $this->form_validation->set_rules('disc_nominal', 'Discount Nominal', 'required|integer');
        $this->form_validation->set_rules('disc_max', 'Discount Maximal', 'integer');
        $this->form_validation->set_rules('min_order', 'Minimal Order', 'integer');
        $this->form_validation->set_rules('item_type', 'Item Type', 'required');
        $this->form_validation->set_rules('item_list[]', 'Item List', 'required|callback__check_item_list');
        $this->form_validation->set_rules('delivery_type', 'Delivery Type', 'required');
        $this->form_validation->set_rules('prm_visible', 'Visible', 'required');
        if(!empty($promo) && $promo->prm_status == 'pending'){
            $this->form_validation->set_rules('prm_type', 'Promo Type', 'required');
            $this->form_validation->set_rules('prm_custom_code', 'Promo Code', 'strip_tags|trim|required|callback__is_unique_promo_code');
            $this->form_validation->set_rules('limit_usage', 'Limit Usage', 'integer');
            $this->form_validation->set_rules('disc_type', 'Discount Type', 'required');
            $this->form_validation->set_rules('limit_per_user', 'Limit Per User', 'integer');
        }

        $err_msg = [];
        if ($this->form_validation->run()) {

            //BEGIN UPLOAD IMAGE
            $image_name = '';
            if (is_uploaded_file($_FILES['prm_img']['tmp_name'])) {

                $this->load->library("google_cloud_bucket");
                $image_name = str_replace(UPLOAD_PATH, '', PROMO_IMAGE_PATH).$_FILES['prm_img']['name'];

                if(!@fopen(UPLOAD_URL.$image_name, 'r')){
                    $data = [
                        "source" => $_FILES['prm_img']['tmp_name'],
                        "name"   => $image_name
                    ];

                    $this->google_cloud_bucket->upload_image($data);
                }
            }
            //END UPLOAD IMAGE
            if(empty($err_msg)){
                $item_type = $this->input->post('item_type');
                $item_list = $this->input->post('item_list');
                $promo = $this->promodb->get_promo($id);
                $limit_usage = intval($this->input->post('limit_usage'));
                $disc_type = $this->input->post('disc_type');
                $prm_type = $this->input->post('prm_type');
                $prm_custom_code = $this->input->post('prm_custom_code');
                $prm_status = $this->config->item('promo')['status']['pending'];
                $disc_nominal = intval($this->input->post('disc_nominal'));
                $disc_max = intval($this->input->post('disc_max'));
                $min_order = intval($this->input->post('min_order'));
                $custom_function = $this->input->post('custom_function');
                $free_delivery = $this->input->post('free_delivery') == 1 ? true : false;
                $delivery_included = $this->input->post('delivery_included') == 1 ? true : false;
                $delivery_type = $this->input->post('delivery_type');
                $limit_per_user = intval($this->input->post('limit_per_user'));

                if(!empty($promo) && $promo->prm_status != 'pending'){
                    $prm_rules = json_decode($promo->prm_rules, true);
                    $limit_usage = $prm_rules['limit_usage'];
                    $disc_type = $prm_rules['disc_type'];
                    $prm_type = $promo->prm_type;
                    $prm_custom_code = $promo->prm_custom_code;
                }

                $rules = [
                    'limit_usage' => $limit_usage,
                    'limit_per_user' => $limit_per_user,
                    'custom_function' => $custom_function,
                    'disc_type' =>  $disc_type,
                    'disc_nominal' => $disc_nominal,
                    'disc_max' => $disc_max,
                    'min_order' => $min_order,
                    'delivery_included' => $delivery_included,
                    'free_delivery' => $free_delivery,
                    'item_type' => $item_type,
                    'delivery_type' => $delivery_type,
                    'item_list' => (in_array("0", $item_list)) ? [] : $item_list
                ];

                $params = [
                    'prm_name' => $this->input->post('prm_name'),
                    'prm_custom_code' => $prm_custom_code,
                    'prm_start' => $this->input->post('prm_start'),
                    'prm_end' => $this->input->post('prm_end'),
                    'prm_type' => $prm_type,
                    'prm_visible' => $this->input->post('prm_visible'),
                    'prm_rules' => json_encode($rules)
                ];


                if($image_name != ''){
                    $params['prm_img']  = $image_name;
                }

                if ($id > 0 && in_array('edit', $permits)) {
                    $params['updated_by'] = $this->_get_user_id();
                    $params['updated_date'] = date('Y-m-d H:i:s');

                    if ($this->promodb->update_promo($id, $params)) {
                        $err_msg = [
                            'msg' => 'Edit Promo Success',
                            'type' => 'success'
                        ];
                    } else {
                        $err_msg = [
                            'msg' => 'Edit Promo Failed!',
                            'type' => 'danger'
                        ];
                    }

                } else if ($id <= 0 && in_array('add', $permits)) {
                    $params['created_by'] = $this->_get_user_id();
                    $params['created_date'] = date('Y-m-d H:i:s');
                    $params['prm_status'] = $prm_status;

                    if ($this->promodb->insert_promo($params)) {
                        $err_msg = [
                            'msg' => 'Add Promo Success.'.js_clearform(),
                            'type' => 'success'
                        ];
                    } else {
                        $err_msg = [
                            'msg' => 'Add Promo Failed!',
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
        $promo      = $this->promodb->get_promo($id);
        $prm_rules  = isset($promo->prm_rules) ? json_decode($promo->prm_rules, true) : [];

        $data = [
            'current_url' => ADMIN_URL.'promo/promo_add',
            'msg' => set_form_msg($err_msg),
            'permits' => $permits,
            'show_form' => $form_permit['show_form'],
            'title_form' => $form_permit['title_form'],
            'cst_promo' => $this->config->item('promo'),
            'promo' => $promo,
            "product" => $product,
            'prm_rules' => $prm_rules,
            'disabled' => !empty($promo) && $promo->prm_status != 'pending' ? 'disabled' : '',
            'prm_rules_item_list' => isset($prm_rules['item_list']) ? $prm_rules['item_list'] : []
        ];

        $this->_render('promo/promo_add', $data);
    }

    public function _is_unique_promo_code($prm_custom_code)
    {
        $id = $this->input->post('prm_id');
        // if duplicate name found and not belong to selected ID, return false
        $promo = $this->promodb->get_promo_custom_filter(' AND prm_id != ? AND prm_custom_code = ?', [$id, $prm_custom_code]);
        if ($promo) {
            $this->form_validation->set_message('_is_unique_promo_code', 'The Promo Code field must contain a unique value.');
            return false;
        }
        return true;
    }

    public function _check_upload_image()
    {
        $id = $this->input->post('prm_id');
        $allowed_mime_type_arr = array('image/gif','image/jpeg','image/pjpeg','image/png','image/x-png');
        $mime = get_mime_by_extension($_FILES['prm_img']['name']);
        if(!empty($_FILES['prm_img']['name'])){
            if(in_array($mime, $allowed_mime_type_arr)){
                return true;
            }else{
                $this->form_validation->set_message('_check_upload_image', 'Please select only gif/jpg/png file.');
                return false;
            }
        }else if($id <= 0){
            $this->form_validation->set_message('_check_upload_image', 'Please select a file.');
            return false;
        }
        return TRUE;
    }

    public function _validate_date($end_time)
    {
        $prm_start = $this->input->post('prm_start');

        // if base price is not 0, validate final price must be less than base price
        if ($end_time <= $prm_start) {
            $this->form_validation->set_message('_validate_date', 'The End Date must be later than Start Date.');
            return false;
      }
      return true;
    }

    public function _max_qty_voucher($qty) {

        if($qty > 5 ) {
            $this->form_validation->set_message('_max_qty_voucher', 'The %s max is 5.');
            return FALSE;
        }

        return TRUE;
    }
    // END PROMO

    //=== START VOUCHER
    public function voucher()
    {
        // validasi menu dan assign title
        $submenu_code = 'voucher';
        $permits = $this->_check_menu_access($submenu_code, 'view');

        $this->_set_title('Voucher');
        $this->load->library('pagination');

        //set variable
        $page = $this->input->get('page');
        $search = set_var($this->input->get('search'), '');
        $sort_col = set_var($this->input->get('sc'), 'id');
        $sort_by = set_var($this->input->get('sb'), 'DESC');//ASC or DESC
        $xtra_var['search'] = $search;

        //set sortable col
        $allow_sort = [
            'id' => 'vc_id',
            'customer' => 'user_name',
            'promo' => 'prm_name',
            'code' => 'vc_code',
            'status' => 'vc_status',
            'created' => 'vc.created_date',
            'updated' => 'vc.updated_date'
        ];

        //start query
        $url_query = "search={$search}&sc={$sort_col}&sb={$sort_by}";
        $search_where = " AND (vc.vc_id LIKE ? OR prm.prm_name LIKE ? OR user.user_name LIKE ?  OR user.user_phone LIKE ? OR user.user_email LIKE ? OR vc.vc_code LIKE ? OR vc.vc_status LIKE ? OR vc.created_date LIKE ? OR vc.updated_date LIKE ?) ";
        $search_data = ["%{$search}%", "%{$search}%", "%{$search}%", "%{$search}%", "%{$search}%", "%{$search}%","%{$search}%","%{$search}%", "%{$search}%"];
        $search_order = sort_table_order($allow_sort, $sort_col, $sort_by);
        $all_data = $this->promodb->getpaging_voucher(
            $search_where,
            $search_data,
            $search_order,
            $page
        );

        // start pagination setting
        $config = [
            'base_url' => ADMIN_URL.'promo/voucher'.($url_query != '' ? '?'.$url_query : ''),
            'total_rows' => $all_data['total_row'],
            'per_page' => $all_data['per_page']
        ];

        $this->pagination->initialize($config);
        // end pagination setting

        // select data & assign variable $data
        $data = [
            'form_url' => $config['base_url'],
            'page_url' => str_replace($url_query, '', $config['base_url']),
            'xtra_var' => $xtra_var,
            'search' => $search,
            'permits' => $permits,
            'cst_status' => $this->config->item('voucher')['status'],
            'all_data' => $all_data['data'],
            'pagination' => $this->pagination->create_links()
        ];
        $this->_render('promo/voucher', $data);
    }

    public function voucher_add()
    {
        // validate user roles
        $submenu_code = 'voucher';
        $permits = $this->_check_menu_access($submenu_code, 'add');
        $this->_set_title('Add Voucher');

        $id = 0;
        $form_permit = $this->_get_form_permit($id, $permits);
        $this->load->library('form_validation');
        $this->load->model('userdb');
        $this->form_validation->set_error_delimiters(PREFIX_ERROR_DELIMITER, SUFFIX_ERROR_DELIMITER);
        $this->form_validation->set_rules('user_id', 'Email/Phone Number', 'strip_tags|trim|required');
        $this->form_validation->set_rules('qty', 'Quantity', 'required|callback__max_qty_voucher');
        $data = $this->config->item('user')['status'];
        $promo_code = $this->config->item('promo');
        $discount_type = $this->config->item('promo')['discount_type'];
        $user_id = $this->input->post('user_id');
        $qty = (int) $this->input->post('qty');
        $err_msg = [];

        if ($this->form_validation->run()) {

            // END UPLOAD IMAGE
            if(empty($err_msg)){
                $promo_name = 'Free Cup';
                $promo_custom_code =  $promo_code['promo_code']['free'].date('ymd');

                $prm_rules = [
                    "limit_usage"=> 0,
                    "custom_function"=> null,
                    "disc_type"=> $discount_type['freecup'],
                    "disc_nominal"=> 1,
                    "disc_max"=> 0,
                    "min_order"=> 0,
                    "delivery_included"=> false,
                    "free_delivery"=> true,
                    'item_type' => $promo_code['item_type']['blacklist'],
                    'item_list' => []
                ];
                $get_promo = $this->promodb->find_create_free_cup_promo($promo_name, $promo_custom_code, $prm_rules, $promo_code['promo_code']['free']);

                for($start_qty = 0; $qty > $start_qty; $start_qty++ ) {
                    $vs_code = $this->promodb->generate_voucher_code($promo_custom_code);

                    $data = [
                        "prm_id" => $get_promo->prm_id,
                        "user_id"=> $user_id,
                        "vc_code"=> $vs_code,
                        "vc_status"=> "active",
                        "created_date"=> date('Y-m-d H:i:s')
                    ];

                    $this->promodb->insert_voucher($data);
                }

                $err_msg = [
                    'msg' => 'Create Voucher  Success.'.js_clearform(),
                    'type' => 'success'
                ];
            } else {
                $err_msg = [
                    'msg' => 'Access denied - You are not authorized to access this page.',
                    'type' => 'danger'
                ];
            }
        }

        $data = [
            'current_url' => ADMIN_URL.'promo/voucher_add',
            'msg' => set_form_msg($err_msg),
            'permits' => $permits,
            'show_form' => $form_permit['show_form'],
            'title_form' => $form_permit['title_form'],
            'cst_type' => $this->config->item('promo'),
            'user' => $this->userdb->get($user_id),
        ];

        $this->_render('promo/voucher_add', $data);
    }
    //END VOUCHER

    // START VOUCHER HISTORY
    public function voucher_his()
    {
        // validasi menu dan assign title
        $submenu_code = 'voucher_his';
        $permits = $this->_check_menu_access($submenu_code, 'view');

        $this->_set_title('Voucher history');
        $this->load->library('pagination');

        //set variable
        $page = $this->input->get('page');
        $start_date = set_var($this->input->get('start'), date('Y-m-d', strtotime('-7 days')));
        $end_date = set_var($this->input->get('end'), date('Y-m-d'));
        $search = set_var($this->input->get('search'), '');
        $sort_col = set_var($this->input->get('sc'), 'id');
        $sort_by = set_var($this->input->get('sb'), 'DESC');//ASC or DESC
        $xtra_var['search'] = $search;
        $xtra_var['start'] = $start_date;
        $xtra_var['end'] = $end_date;

        //set sortable col
        $allow_sort = [
            'id' => 'vchis_id',
            'prm' => 'prm_name',
            'order' => 'uor_code',
            'nominal' => 'vchis_nominal',
            'status' => 'vchis_status',
            'created' => 'created_date',
        ];

        //start query
        $url_query = "search={$search}&sc={$sort_col}&sb={$sort_by}&start={$start_date}&end={$end_date}";
        $search_where = " AND (vchis_id LIKE ? OR vc_code LIKE ? OR prm_name LIKE ? OR uor_code LIKE ? OR vchis_nominal LIKE ? OR vchis_status LIKE ?) AND vchis.created_date >= ? AND vchis.created_date <= ?";
        $search_data = ["%{$search}%", "%{$search}%", "%{$search}%", "%{$search}%","%{$search}%","%{$search}%", $start_date, $end_date.' 23:59:59'];
        $search_order = sort_table_order($allow_sort, $sort_col, $sort_by);
        $all_data = $this->promodb->getpaging_voucher_his(
            $search_where,
            $search_data,
            $search_order,
            $page
        );

        // start pagination setting
        $config = [
            'base_url' => ADMIN_URL.'promo/voucher_his'.($url_query != '' ? '?'.$url_query : ''),
            'total_rows' => $all_data['total_row'],
            'per_page' => $all_data['per_page']
        ];

        $this->pagination->initialize($config);
        // end pagination setting

        // select data & assign variable $data
        $data = [
            'order_url' => ADMIN_URL.'transaction/order_detail',
            'form_url' => $config['base_url'],
            'page_url' => str_replace($url_query, '', $config['base_url']),
            'xtra_var' => $xtra_var,
            'search' => $search,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'permits' => $permits,
            'cst_status' => $this->config->item('voucher')['status'],
            'all_data' => $all_data['data'],
            'pagination' => $this->pagination->create_links()
        ];
        $this->_render('promo/voucher_his', $data);
    }
    // END

    public function _check_item_list($type){
        $cst_type   = $this->config->item('promo')['item_type'];
        $type       = $this->input->post('item_type');
        $item_list  = $this->input->post('item_list');

        if($type == $cst_type['whitelist']) {
            if(in_array("0", $item_list)) {
                $this->form_validation->set_message('_check_item_list', 'The %s can not to set No Product if you choose Whitelist type.');
                return false;
            }
        }

        return true;
    }

    // START VOUCHER UNASSIGNED
    public function voucher_unassigned()
    {
        // validasi menu dan assign title
        $submenu_code   = 'voucher_unassigned';
        $permits        = $this->_check_menu_access($submenu_code, 'view');

        $this->_set_title('Voucher Unassigned');
        $this->load->library('pagination');

        //set variable
        $page               = $this->input->get('page');
        $start_date         = set_var($this->input->get('start'), date('Y-m-d', strtotime('-7 days')));
        $end_date           = set_var($this->input->get('end'), date('Y-m-d'));
        $search             = set_var($this->input->get('search'), '');
        $sort_col           = set_var($this->input->get('sc'), 'id');
        $sort_by            = set_var($this->input->get('sb'), 'ASC');//ASC or DESC
        $xtra_var['search'] = $search;
        $xtra_var['start']  = $start_date;
        $xtra_var['end']    = $end_date;

        //set sortable col
        $allow_sort = [
            'id'        => 'vcu_id',
            'prm'       => 'prm_name',
            'user'      => 'user_id',
            'code'      => 'vcu_code',
            'status'    => 'vcu_status',
            'expired'   => 'expired_date',
            'created'   => 'created_date',
            'updated'   => 'updated_date',
        ];

        //start query
        $url_query      = "search={$search}&sc={$sort_col}&sb={$sort_by}&start={$start_date}&end={$end_date}";
        $search_where   = " AND (vcu_id LIKE ? OR prm.prm_name LIKE ? OR user_id LIKE ? OR vcu_code LIKE ? OR vcu_status LIKE ? ) AND vcu.created_date >= ? AND vcu.created_date <= ?";
        $search_data    = ["%{$search}%", "%{$search}%", "%{$search}%", "%{$search}%","%{$search}%", $start_date, $end_date.' 23:59:59'];
        $search_order   = sort_table_order($allow_sort, $sort_col, $sort_by);

        if($this->input->get('export') == 'xls'){
            $permits    = $this->_check_menu_access($submenu_code, 'export');
            //mulai dari set header dan filename
            $filename   = 'voucher_unassigned.xls';
            $this->set_header_xls($filename);

            //select data dari database
            $all_data = $this->promodb->getall_voucher_unassigned($search_where, $search_data, $search_order);

            //taro datanya di parameter untuk di baca di view
            $data ['all_data']  = $all_data;

            //load view table yang mau di export
            $this->_render('promo/voucher_unassigned_xls', $data);

        }else{
            //start query
            $all_data = $this->promodb->getpaging_voucher_unassigned(
                $search_where,
                $search_data,
                $search_order,
                $page
            );
            // start pagination setting
            $config = [
                'base_url'      => ADMIN_URL.'promo/voucher_unassigned'.($url_query != '' ? '?'.$url_query : ''),
                'total_rows'    => $all_data['total_row'],
                'per_page'      => $all_data['per_page']
            ];

            $this->pagination->initialize($config);
            // end pagination setting

            // select data & assign variable $data
            $data = [
                'user_url'      => ADMIN_URL.'user/user_detail',
                'form_url'      => $config['base_url'],
                'page_url'      => str_replace($url_query, '', $config['base_url']),
                'xtra_var'      => $xtra_var,
                'search'        => $search,
                'start_date'    => $start_date,
                'end_date'      => $end_date,
                'permits'       => $permits,
                'cst_status'    => $this->config->item('voucher')['status'],
                'all_data'      => $all_data['data'],
                'pagination'    => $this->pagination->create_links()
            ];

            $this->_render('promo/voucher_unassigned', $data);
        }
    }
    // END VOUCHER UNASSIGNED

    //VOUCHER DEFAULT
    public function voucher_default(){
        // validasi menu dan assign title
        $submenu_code = 'voucher_default';
        $permits = $this->_check_menu_access($submenu_code, 'view');

        $this->_set_title('Voucher Default');
        $this->load->library('pagination');

        //set variable
        $page = $this->input->get('page');
        $search = set_var($this->input->get('search'), '');
        $sort_col = set_var($this->input->get('sc'), 'id');
        $sort_by = set_var($this->input->get('sb'), 'DESC');//ASC or DESC
        $xtra_var['search'] = $search;

        //set sortable col
        $allow_sort = [
            'id'    => 'vcdef_id',
            'code'  => 'vcdef_code',
            'type'  => 'vcdef_type',
            'list'  => 'vcdef_list',
            'date'  => 'created_date',
            'update'=> 'updated_date',
        ];

        $arr_admin = $this->admindb->getarr_admin();

        //start query
        $url_query = "search={$search}&sc={$sort_col}&sb={$sort_by}";
        $search_where = " AND (vcdef_id = ? OR vcdef_code LIKE ? OR vcdef_type LIKE ? OR vcdef_list LIKE ? OR created_by LIKE ? OR created_date LIKE ?) ";
        $search_data = [$search, "%{$search}%", "%{$search}%", "%{$search}%", "%{$search}%", "%{$search}%"];
        $search_order = sort_table_order($allow_sort, $sort_col, $sort_by);
        $all_data = $this->promodb->getpaging_voucher_default(
            $search_where,
            $search_data,
            $search_order,
            $page
        );

        // start pagination setting
        $config = [
            'base_url' => ADMIN_URL.'promo/voucher_default'.($url_query != '' ? '?'.$url_query : ''),
            'total_rows' => $all_data['total_row'],
            'per_page' => $all_data['per_page']
        ];

        $this->pagination->initialize($config);
        // end pagination setting

        // select data & assign variable $data
        $data = [
            'current_url' => ADMIN_URL.'promo/voucher_default',
            'form_url' => $config['base_url'],
            'page_url' => str_replace($url_query, '', $config['base_url']),
            'xtra_var' => $xtra_var,
            'search' => $search,
            'permits' => $permits,
            'all_data' => $all_data['data'],
            'arr_admin' => $arr_admin,
            'pagination' => $this->pagination->create_links()
        ];
        $this->_render('promo/voucher_default', $data);

    }

    public function voucher_default_add($id = 0)
    {
        // validate user roles
        $submenu_code = 'voucher_default';
        if ($id > 0) {
            $permits = $this->_check_menu_access($submenu_code, 'edit');
            $this->_set_title('Edit Voucher Default');
        } else {
            $permits = $this->_check_menu_access($submenu_code, 'add');
            $this->_set_title('Add Voucher Default');
        }
        $form_permit = $this->_get_form_permit($id, $permits);
        $product    = $this->productdb->getall_product();

        // validate if voucdef exists
        $vcdef = [];
        if ($id > 0) {
            $vcdef = $this->promodb->get_promo($id);
            if (empty($vcdef)) {
                redirect(ADMIN_URL);
            }
        }

        $this->load->library('form_validation');
        $this->form_validation->set_error_delimiters(PREFIX_ERROR_DELIMITER, SUFFIX_ERROR_DELIMITER);
        $this->form_validation->set_rules('vcdef_code', 'Voucher Default Code', 'strip_tags|trim|required');
        $this->form_validation->set_rules('item_type', 'Voucher Default Type', 'required');
        $this->form_validation->set_rules('item_list[]', 'Item List', 'required|callback__check_item_list');

        $err_msg = [];
        if ($this->form_validation->run()) {
            $vcdef_type = $this->input->post('item_type');
            $vcdef_code = $this->input->post('vcdef_code');
            $item_list  = (in_array("0", $this->input->post('item_list')) ? [] : $this->input->post('item_list'));

            $params = [
                'vcdef_code' => $vcdef_code,
                'vcdef_type' => $vcdef_type,
                'vcdef_list' => json_encode($item_list, JSON_NUMERIC_CHECK)
            ];

            if ($id > 0 && in_array('edit', $permits)) {
                $params['updated_by'] = $this->_get_user_id();
                $params['updated_date'] = date('Y-m-d H:i:s');
                if ($this->promodb->update_voucher_default($id, $params)) {
                    $err_msg = [
                        'msg' => 'Edit Voucher Default Success',
                        'type' => 'success'
                    ];
                } else {
                    $err_msg = [
                        'msg' => 'Edit Voucher Default Failed!',
                        'type' => 'danger'
                    ];
                }

            } else if ($id <= 0 && in_array('add', $permits)) {
                $params['created_by'] = $this->_get_user_id();
                $params['created_date'] = date('Y-m-d H:i:s');

                if ($this->promodb->insert_voucher_default($params)) {
                    $err_msg = [
                        'msg' => 'Add Voucher Default Success.'.js_clearform(),
                        'type' => 'success'
                    ];
                } else {
                    $err_msg = [
                        'msg' => 'Add Voucher Default Failed!',
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

        $voucher_default = $this->promodb->get_voucher_default($id);

        $data = [
            'current_url'   => ADMIN_URL.'promo/voucher_default_add',
            'msg'           => set_form_msg($err_msg),
            'cst_type'      => $this->config->item('promo'),
            'permits'       => $permits,
            'vcdef'         => $voucher_default,
            'show_form'     => $form_permit['show_form'],
            'title_form'    => $form_permit['title_form'],
            "product"       => $product
        ];

        $this->_render('promo/voucher_default_add', $data);

    }
    //END DEFAULT

    //>>START VOUCHER EMPLOYEE<<
    public function voucher_employee(){
        // validasi menu dan assign title
        $submenu_code   = 'voucher_employee';
        $permits        = $this->_check_menu_access($submenu_code, 'view');

        $this->_set_title('Voucher Employee');
        $this->load->library('pagination');

        //set variable
        $page               = $this->input->get('page');
        $search             = set_var($this->input->get('search'), '');
        $sort_col           = set_var($this->input->get('sc'), 'id');
        $sort_by            = set_var($this->input->get('sb'), 'ASC');//ASC or DESC
        $xtra_var['search'] = $search;

        //set sortable col
        $allow_sort = [
            'id'                     => 'vce_id',
            'user'                   => 'user_id',
            'name'                   => 'vce_name',
            'email'                  => 'vce_email',
            'phone'                  => 'vce_phone',
            'company'                => 'vce_organize_name',
            'position'               => 'vce_position',
            'created'                => 'created_date',
            'updated'                => 'updated_date'
        ];

        $arr_admin      = $this->admindb->getarr_admin();

        //start query
        $url_query      = "search={$search}&sc={$sort_col}&sb={$sort_by}";
        $search_where   = " AND (vce_id = ? OR user_id LIKE ? OR vce_name LIKE ? OR vce_email LIKE ? OR vce_phone LIKE ? OR vce_position LIKE ? OR vce_organize_name LIKE ? ) ";
        $search_data    = [$search, "%{$search}%","%{$search}%","%{$search}%", "%{$search}%", "%{$search}%", "%{$search}%"];
        $search_order   = sort_table_order($allow_sort, $sort_col, $sort_by);
        $all_data       = $this->promodb->getpaging_voucher_employee(
            $search_where,
            $search_data,
            $search_order,
            $page
        );

        // start pagination setting
        $config = [
            'base_url'      => ADMIN_URL.'promo/voucher_employee'.($url_query != '' ? '?'.$url_query : ''),
            'total_rows'    => $all_data['total_row'],
            'per_page'      => $all_data['per_page']
        ];

        $this->pagination->initialize($config);
        // end pagination setting

        // select data & assign variable $data
        $data = [
            'current_url'   => ADMIN_URL.'promo/voucher_employee',
            'form_url'      => $config['base_url'],
            'page_url'      => str_replace($url_query, '', $config['base_url']),
            'xtra_var'      => $xtra_var,
            'search'        => $search,
            'permits'       => $permits,
            'all_data'      => $all_data['data'],
            'arr_admin'     => $arr_admin,
            'pagination'    => $this->pagination->create_links()
        ];
        $this->_render('promo/voucher_employee', $data);

    }

    public function voucher_employee_add($id = 0)
    {
        //load model
        $this->load->model('userdb');

        // validate user roles
        $submenu_code = 'voucher_employee';
        if ($id > 0) {
            $permits = $this->_check_menu_access($submenu_code, 'edit');
            $this->_set_title('Edit Voucher Employee');
        } else {
            $permits = $this->_check_menu_access($submenu_code, 'add');
            $this->_set_title('Add Voucher Employee');
        }
        $form_permit = $this->_get_form_permit($id, $permits);

        // validate if voucdef exists
        $voucher_employee = '';
        if ($id > 0) {
            $voucher_employee = $this->promodb->get_voucher_employee($id);
            if (empty($voucher_employee)) {
                redirect(ADMIN_URL);
            }
        }

        $this->load->library('form_validation');
        $this->form_validation->set_error_delimiters(PREFIX_ERROR_DELIMITER, SUFFIX_ERROR_DELIMITER);
        $this->form_validation->set_rules('user_id', 'User Name', 'strip_tags|trim|required');
        $this->form_validation->set_rules('vce_organize_name', 'Organize Name', 'strip_tags|trim|required');
        $this->form_validation->set_rules('vce_position', 'Position Name', 'strip_tags|trim|required');

        $err_msg = [];
        if ($this->form_validation->run()) {

            $params = [
                'user_id'           => $this->input->post('user_id'),
                'vce_name'          => $this->input->post('vce_name'),
                'vce_email'         => $this->input->post('vce_email'),
                'vce_phone'         => $this->input->post('vce_phone'),
                'vce_position'      => $this->input->post('vce_position'),
                'vce_organize_name' => $this->input->post('vce_organize_name')
            ];

            if ($id > 0 && in_array('edit', $permits)) {
                $params['updated_by'] = $this->_get_user_id();
                $params['updated_date'] = date('Y-m-d H:i:s');
                if ($this->promodb->update_voucher_employee($id, $params)) {
                    $err_msg = [
                        'msg' => 'Edit Voucher Employee Success',
                        'type' => 'success'
                    ];
                } else {
                    $err_msg = [
                        'msg' => 'Edit Voucher Employee Failed!',
                        'type' => 'danger'
                    ];
                }

            } else if ($id <= 0 && in_array('add', $permits)) {
                $params['created_by'] = $this->_get_user_id();
                $params['created_date'] = date('Y-m-d H:i:s');

                if ($this->promodb->insert_voucher_employee($params)) {
                    $err_msg = [
                        'msg' => 'Add Voucher Employee Success.'.js_clearform(),
                        'type' => 'success'
                    ];
                } else {
                    $err_msg = [
                        'msg' => 'Add Voucher Employee Failed!',
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

        $data = [
            'current_url'   => ADMIN_URL.'promo/voucher_employee_add',
            'msg'           => set_form_msg($err_msg),
            'permits'       => $permits,
            'show_form'     => $form_permit['show_form'],
            'title_form'    => $form_permit['title_form'],
            'user'          => $this->userdb->get($this->input->post('user_id')),
            'vce'           => $voucher_employee,
            'cst_vce'       => [
                'organize'  => $this->config->item('voucher_employee')['organize'],
                'position'  => $this->config->item('voucher_employee')['position']
            ]
        ];

        $this->_render('promo/voucher_employee_add', $data);

    }
    //>>END VOUCHER EMPLOYEE<<

    //START VOUCHER REVIEW ADD
    public function voucher_review_add()
    {
        // validate user roles
        $submenu_code = 'voucher_review';
        $permits = $this->_check_menu_access($submenu_code, 'add');
        $this->_set_title('Add Voucher Review');

        $id = 0;
        $form_permit = $this->_get_form_permit($id, $permits);

        $this->load->library('form_validation');
        $this->load->model('userdb');

        $this->form_validation->set_error_delimiters(PREFIX_ERROR_DELIMITER, SUFFIX_ERROR_DELIMITER);
        $this->form_validation->set_rules('user_id', 'Email/Phone Number', 'strip_tags|trim|required');

        $data           = $this->config->item('user')['status'];
        $promo_code     = $this->config->item('promo');
        $discount_type  = $this->config->item('promo')['discount_type'];
        $user_id        = $this->input->post('user_id');
        $qty            = 10;
        $err_msg        = [];

        if ($this->form_validation->run()) {

            if(empty($err_msg)){
                $promo_name = 'FOREview Voucher';
                $promo_custom_code =  $promo_code['promo_code']['review'].date('ymd');

                $prm_rules = [
                    "limit_usage"       => 0,
                    "custom_function"   => null,
                    "disc_type"         => $discount_type['freecup'],
                    "disc_nominal"      => 1,
                    "disc_max"          => 0,
                    "min_order"         => 0,
                    "delivery_included" => false,
                    "free_delivery"     => true,
                    'item_type'         => $promo_code['item_type']['blacklist'],
                    'item_list'         => []
                ];
                $get_promo = $this->promodb->find_create_free_cup_promo($promo_name, $promo_custom_code, $prm_rules, $promo_code['promo_code']['free']);

                for($start_qty = 0; $qty > $start_qty; $start_qty++ ) {
                    $vs_code = $this->promodb->generate_voucher_code($promo_custom_code);

                    $data = [
                        "prm_id"        => $get_promo->prm_id,
                        "user_id"       => $user_id,
                        "vc_code"       => $vs_code,
                        "vc_status"     => "active",
                        "created_date"  => date('Y-m-d H:i:s')
                    ];

                    $this->promodb->insert_voucher($data);
                }

                $err_msg = [
                    'msg'   => 'Create Voucher Review Success.'.js_clearform(),
                    'type'  => 'success'
                ];
            } else {
                $err_msg = [
                    'msg'   => 'Access denied - You are not authorized to access this page.',
                    'type'  => 'danger'
                ];
            }
        }

        $data = [
            'current_url'           => ADMIN_URL.'promo/voucher_review_add',
            'msg'                   => set_form_msg($err_msg),
            'permits'               => $permits,
            'show_form'             => $form_permit['show_form'],
            'title_form'            => $form_permit['title_form'],
            'cst_type'              => $this->config->item('promo'),
            'user'                  => $this->userdb->get($user_id),
        ];

        $this->_render('promo/voucher_review_add', $data);
    }
    //END VOUCHER REVIEW ADD

    //START VOUCHER GIFT ADD
    public function voucher_gift_add()
    {
        // validate user roles
        $submenu_code = 'voucher_gift';
        $permits = $this->_check_menu_access($submenu_code, 'add');
        $this->_set_title('Add Voucher Gift');

        $id = 0;
        $form_permit = $this->_get_form_permit($id, $permits);

        $this->load->library('form_validation');
        $this->load->model('userdb');

        $this->form_validation->set_error_delimiters(PREFIX_ERROR_DELIMITER, SUFFIX_ERROR_DELIMITER);
        $this->form_validation->set_rules('user_id', 'Email/Phone Number', 'strip_tags|trim|required');
        $this->form_validation->set_rules('qty', 'Quantity', 'required|callback__max_qty_voucher_gift');
        $this->form_validation->set_rules('exp_date', 'Expired Date', 'strip_tags|trim|required');

        $data           = $this->config->item('user')['status'];
        $promo_code     = $this->config->item('promo');
        $discount_type  = $this->config->item('promo')['discount_type'];
        $user_id        = $this->input->post('user_id');
        $exp_date       = $this->input->post('exp_date');
        $qty            = (int) $this->input->post('qty');
        $err_msg        = [];

        if ($this->form_validation->run()) {

            if(empty($err_msg)){
                $promo_name = 'Gift Voucher';
                $promo_custom_code =  $promo_code['promo_code']['gift'].date('ymd');

                $prm_rules = [
                    "limit_usage"       => 0,
                    "custom_function"   => null,
                    "disc_type"         => $discount_type['freecup'],
                    "disc_nominal"      => 1,
                    "disc_max"          => 0,
                    "min_order"         => 0,
                    "delivery_included" => false,
                    "free_delivery"     => true,
                    'item_type'         => $promo_code['item_type']['blacklist'],
                    'item_list'         => []
                ];
                $get_promo = $this->promodb->find_create_free_cup_promo($promo_name, $promo_custom_code, $prm_rules, $promo_code['promo_code']['free'], $exp_date);

                for($start_qty = 0; $qty > $start_qty; $start_qty++ ) {
                    $vs_code = $this->promodb->generate_voucher_code($promo_custom_code);

                    $data = [
                        "prm_id"        => $get_promo->prm_id,
                        "user_id"       => $user_id,
                        "vc_code"       => $vs_code,
                        "vc_status"     => "active",
                        "created_date"  => date('Y-m-d H:i:s')
                    ];

                    $this->promodb->insert_voucher($data);
                }

                $err_msg = [
                    'msg'   => 'Create Voucher Gift Success.'.js_clearform(),
                    'type'  => 'success'
                ];
            } else {
                $err_msg = [
                    'msg'   => 'Access denied - You are not authorized to access this page.',
                    'type'  => 'danger'
                ];
            }
        }

        $data = [
            'current_url'           => ADMIN_URL.'promo/voucher_gift_add',
            'msg'                   => set_form_msg($err_msg),
            'permits'               => $permits,
            'show_form'             => $form_permit['show_form'],
            'title_form'            => $form_permit['title_form'],
            'cst_type'              => $this->config->item('promo'),
            'user'                  => $this->userdb->get($user_id),
        ];

        $this->_render('promo/voucher_gift_add', $data);
    }

    public function _max_qty_voucher_gift($qty) {

        if($qty > 500 ) {
            $this->form_validation->set_message('_max_qty_voucher_gift', 'The %s max is 500.');
            return FALSE;
        }

        return TRUE;
    }
    //END VOUCHER GIFT ADD
}

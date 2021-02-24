<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Transaction extends MY_Admin
{
    function __construct()
    {
        parent::__construct();

        $this->load->model('orderdb');
        $this->load->model('courierdb');
    }

    public function index()
    {
        show_404();
    }

    public function order()
    {
        // validasi menu dan assign title
        $submenu_code = 'order';
        $permits = $this->_check_menu_access($submenu_code, 'view');

        $this->_set_title('Orders');
        $this->load->library('pagination');

        //set variable
        $current_path = 'transaction/order';
        $current_url = ADMIN_URL.$current_path;
        $page = $this->input->get('page');
        $search = set_var($this->input->get('search'), '');
        $start_date = set_var($this->input->get('start'), date('Y-m-d', strtotime('-7 hours')));// -7 jam untuk jaga2 store yang buka 24 jam
        $end_date = set_var($this->input->get('end'), date('Y-m-d'));
        $sort_col = set_var($this->input->get('sc'), '');
        $sort_by = set_var($this->input->get('sb'), 'ASC');//ASC or DESC
        $xtra_var['search'] = $search;
        $xtra_var['start'] = $start_date;
        $xtra_var['end'] = $end_date;
        $cst_status = $this->config->item('order')['status'];

        //set sortable col
        $allow_sort = [
            'id' => 'uor_id',
            'code' => 'uor_code',
            'name' => 'user_name',
            'date' => 'uor_date',
            'total' => 'uor_total',
            'type' => 'uor_delivery_type',
            'status' => 'uor_status',
            'pymtd' => 'pymtd.pymtd_name',
            'updated_date' => 'updated_date'
        ];

        //start query
        $url_query      = "search={$search}&sc={$sort_col}&sb={$sort_by}&start={$start_date}&end={$end_date}";
        $search_where   = " AND uor_date >= ? AND uor_date <= ? ";
        $search_data    = [$start_date, $end_date.' 23:59:59'];
        $search_order   = sort_table_order($allow_sort, $sort_col, $sort_by);

        //set default where & data
        $additional_where = " AND (uoradd.uadd_person LIKE ? OR uoradd.uadd_phone LIKE ? OR uor.uor_id = ? OR uor_code LIKE ? OR st.st_name LIKE ? OR user_name LIKE ? OR user_phone LIKE ? OR user_email LIKE ? OR uor_delivery_type LIKE ? OR uor_status LIKE ?) ";
        $additional_data =  ["%{$search}%", "%{$search}%", $search, $search.'%', "%{$search}%", "%{$search}%","%{$search}%","%{$search}%","%{$search}%","%{$search}%"];

        //search by gk
        $prefix_booking = substr(strtolower($search), 0, 3); 
        if($prefix_booking == 'gk-'){
            $found_courier = $this->courierdb->get_order_courier_booking($search);
            if($found_courier){
                //set where & data base on courier
                $additional_where = " AND uor.uor_id = ? ";
                $additional_data = [$found_courier->uor_id];
            }
        }

        //gabungin search keyword dan search GK
        $search_where .= $additional_where;
        $search_data   = array_merge($search_data, $additional_data);

        $all_data = $this->orderdb->getpaging_order(
            $search_where,
            $search_data,
            $search_order,
            $page
        );

        // set uor_id to arr_data
        $arr_data = array();
        $arr_data_voucher = array();
        $status_trk = $this->config->item('order')['status'];
        $type_trk = $this->config->item('order')['delivery_type'];

        // looping for set data to array
        foreach($all_data['data'] as $key => $value ) {
            $arr_data_voucher[] = $value->uor_id;
            // to get order delivery and status is not cancelled
            if($value->uor_status !== $status_trk['cancelled'] && $value->uor_delivery_type == $type_trk['delivery']){
                $arr_data[] = $value->uor_id;
            }
        }

        // call function to get latest order track
        $order_trk = $this->orderdb->getall_latest_track($arr_data);

        //call function to get latest driver
        $courier = $this->courierdb->getall_latest_courier($arr_data);

        //call function to get all user order voucher
        $voucher = $this->orderdb->getall_user_order_voucher($arr_data_voucher);

        // start pagination setting
        $config = [
            'base_url' => $current_url.($url_query != '' ? '?'.$url_query : ''),
            'total_rows' => $all_data['total_row'],
            'per_page' => $all_data['perpage']
        ];
        $this->pagination->initialize($config);
        // end pagination setting

        $arr_admin      = $this->admindb->getarr_admin();

        // select data & assign variable $data
        $data = [
            'current_url'       => $current_url,
            'form_url'          => $config['base_url'],
            'page_url'          => str_replace($url_query, '', $config['base_url']),
            'xtra_var'          => $xtra_var,
            'search'            => $search,
            'start_date'        => $start_date,
            'end_date'          => $end_date,
            'permits'           => $permits,
            'arr_admin'         => $arr_admin,
            'cst_status'        => $cst_status,
            'cst_status_name'   => $this->config->item('order')['status_name'],
            'cst_next_status'   => $this->config->item('order')['next_status'],
            'cst_delivery_type' => $this->config->item('order')['delivery_type'],
            'gosend_status'     => $this->config->item('gosend')['status'],
            'all_data'          => $all_data['data'],
            'order_trk'         => $order_trk,
            'courier'           => $courier,
            'voucher'           => $voucher,
            'pagination'        => $this->pagination->create_links()
        ];

        $this->_render('transaction/order', $data);
    }

    public function order_detail($id = 0)
    {
        $this->load->model('admindb');
        $submenu_code = 'ongoing';
        $permits = $this->_check_menu_access($submenu_code, 'view');
        $this->_set_title('Order Detail');
        $form_permit = $this->_get_form_permit($id, $permits);

        // validate order
        $order = $this->orderdb->get_order($id);
        if (!$order) {
            redirect(ADMIN_URL);
        }

        $arr_admin      = $this->admindb->getarr_admin();

        // get order product list
        $where = " AND uor_id = ?";
        $order_product = $this->orderdb->getall_order_product($where, [$id]);

        // get order track list
        $where = " AND uor_id = ?";
        $order_track = $this->orderdb->getall_order_track($where, [$id]);

        //get order courier
        $order_courier  = $this->orderdb->get_order_courier($id);

        //call function to get user order voucher
        $select = "uov.vc_code";
        $where = " AND uor_id = ? ";
        $order_voucher = $this->orderdb->getall_order_voucher($select, $where, [$id]);
        foreach($order_voucher as $key => $value) {
            $order_voucher = [];
            $order_voucher[] = $value->vc_code;
        }
        $current_path = 'transaction/order_detail';
        $data = [
            'current_url'       => ADMIN_URL.$current_path,
            'permits'           => $permits,
            'show_form'         => $form_permit['show_form'],
            'title_form'        => $form_permit['title_form'],
            'data' => [
                'order'     => $order,
                'courier'   => $order_courier,
                'product'   => $order_product,
                'track'     => $order_track,
                'voucher'   => $order_voucher
            ],
            'arr_admin'         => $arr_admin,
            'cst_status'        => $this->config->item('order')['status'],
            'cst_status_name'   => $this->config->item('order')['status_name'],
            'cst_delivery_type' => $this->config->item('order')['delivery_type'],
        ];
        $this->_render('transaction/order_detail', $data);
    }

    public function ongoing()
    {
        // validasi menu dan assign title
        $submenu_code = 'ongoing';
        $permits = $this->_check_menu_access($submenu_code, 'view');

        $this->_set_title('Ongoing');
        $this->load->library('pagination');
        $this->load->model('storedb');
        //set variable
        $detail_path = ADMIN_URL.'transaction/order';
        $current_url = ADMIN_URL.'transaction/ongoing';
        $page = $this->input->get('page');
        $search = set_var($this->input->get('search'), '');
        $start_date = set_var($this->input->get('start'), date('Y-m-d', strtotime('-7 hours')));// -7 jam untuk jaga2 store yang buka 24 jam
        $end_date = set_var($this->input->get('end'), date('Y-m-d'));
        $sort_col = set_var($this->input->get('sc'), '');
        $sort_by = set_var($this->input->get('sb'), 'ASC');//ASC or DESC
        $xtra_var['search'] = $search;
        $xtra_var['start'] = $start_date;
        $xtra_var['end'] = $end_date;
        $cst_status = $this->config->item('order')['status'];
        $py_status = $this->config->item('payment')['status'];
        $allow_pickup_completed = $this->config->item('order')['allow_pickup_completed'];

        //set sortable col
        $allow_sort = [
            'id' => 'uor_id',
            'code' => 'uor_code',
            'name' => 'user_name',
            'date' => 'uor_date',
            'total' => 'uor_total',
            'type' => 'uor_delivery_type',
            'status' => 'uor_status',
            'updated_by' => 'updated_by',
            'updated_date' => 'updated_date'
        ];

        //start query
        $url_query      = "search={$search}&sc={$sort_col}&sb={$sort_by}&start={$start_date}&end={$end_date}";
        $search_where   = " AND uor_date >= ? AND uor_date <= ? ";
        $search_data    = [$start_date, $end_date.' 23:59:59'];
        $search_order   = sort_table_order($allow_sort, $sort_col, $sort_by);

        //get all order with filter store permits
        $user = $this->admindb->get_admin($this->_get_user_id());
        $store_permits = 0;
        if(!empty($user) && $user->st_id > 0) {
            $store_permits = $user->st_id;
            $search_where .=  " AND st.st_id = ? ";
            array_push($search_data, $user->st_id);
        }

        //set default where & data
        $additional_where = " AND (uoradd.uadd_person LIKE ? OR uoradd.uadd_phone LIKE ? OR uor.uor_id = ? OR uor_code LIKE ? OR st.st_name LIKE ? OR user_name LIKE ? OR user_phone LIKE ? OR user_email LIKE ? OR uor_delivery_type LIKE ? OR uor_status LIKE ?) ";
        $additional_data =  ["%{$search}%", "%{$search}%", $search, $search.'%', "%{$search}%", "%{$search}%","%{$search}%","%{$search}%","%{$search}%","%{$search}%"];

        //search by gk
        $prefix_booking = substr(strtolower($search), 0, 3);
        if(in_array($prefix_booking, ['gk-', 'in-'])){
            $found_courier = $this->courierdb->get_order_courier_booking($search);
            if($found_courier){
                //set where & data base on courier
                $additional_where = " AND uor.uor_id = ? ";
                $additional_data = [$found_courier->uor_id];
            }
        }

        if(empty($search)){
            //set where & data base on courier
            $additional_where = " AND uor.uor_status NOT IN(?, ?) ";
            $additional_data = [$cst_status['completed'], $cst_status['cancelled']];
        }

        //gabungin search keyword dan search GK
        $search_where .= $additional_where;
        $search_data   = array_merge($search_data, $additional_data);

        $all_data = $this->orderdb->getpaging_order(
            $search_where,
            $search_data,
            $search_order,
            $page
        );
        
        // set uor_id to arr_data
        $arr_data = array();
        $arr_data_voucher = array();
        $status_trk = $this->config->item('order')['status'];
        $type_trk = $this->config->item('order')['delivery_type'];

        // looping for set data to array
        foreach($all_data['data'] as $key => $value ) {
            $arr_data_voucher[] = $value->uor_id;
            // to get order delivery and status is not cancelled
            if($value->uor_status !== $status_trk['cancelled'] && $value->uor_delivery_type == $type_trk['delivery']){
                $arr_data[] = $value->uor_id;
            }
        }

        // call function to get latest order track
        $order_trk = $this->orderdb->getall_latest_track($arr_data);

        //call function to get latest driver
        $courier = $this->courierdb->getall_latest_courier($arr_data);

        //call function to get all user order voucher
        $voucher = $this->orderdb->getall_user_order_voucher($arr_data_voucher);
        
        // looping and get order product list
        foreach($all_data['data'] as $key_order => $order_data) {
            $where = " AND uor_id = ?";
            $order_product = $this->orderdb->getall_order_product($where, [$order_data->uor_id]);
            $all_data['data'][$key_order]->product_list = $order_product;
        }

        // start pagination setting
        $config = [
            'base_url' => $current_url.($url_query != '' ? '?'.$url_query : ''),
            'total_rows' => $all_data['total_row'],
            'per_page' => $all_data['perpage']
        ];
        $this->pagination->initialize($config);
        // end pagination setting

        $arr_admin      = $this->admindb->getarr_admin();
        $all_store      = $this->storedb->getall();

        //if show brewing == true berarti datanya cuma 1 & pickup & bukan complete / cancel & harus ada pencarian
        $data_brewing = array();
        $show_brewing = false;
        if(count($all_data['data']) == 1){
            $data_brewing = $all_data['data'][0];
            if(in_array($data_brewing->uor_status, $allow_pickup_completed)
                && $data_brewing->uor_delivery_type == $type_trk['pickup']
                && $search){
                $show_brewing = true;
            }
        }

        // select data & assign variable $data
        $data = [
            'current_url'       => $current_url,
            'form_url'          => $config['base_url'],
            'page_url'          => str_replace($url_query, '', $config['base_url']),
            'xtra_var'          => $xtra_var,
            'search'            => $search,
            'start_date'        => $start_date,
            'end_date'          => $end_date,
            'permits'           => $permits,
            'arr_admin'         => $arr_admin,
            'cst_status'        => $cst_status,
            'cst_courier_code'  => $this->config->item('courier')['courier_code'],
            'cst_status_name'   => $this->config->item('order')['status_name'],
            'cst_next_status'   => $this->config->item('order')['next_status'],
            'cst_delivery_type' => $this->config->item('order')['delivery_type'],
            'gosend_status'     => $this->config->item('gosend')['status'],
            'all_data'          => $all_data['data'],
            'order_trk'         => $order_trk,
            'courier'           => $courier,
            'detail_path'       => $detail_path,
            'store_permits'     => $store_permits,
            'store_data'        => $all_store,
            'voucher'           => $voucher,
            'show_brewing'      => $show_brewing,
            'data_brewing'      => $data_brewing,
            'pagination'        => $this->pagination->create_links()
        ];

        $this->_render('transaction/ongoing', $data);
    }

    public function gosend_logs() {
        $this->load->model('gosenddb');

        // VALIDASI MENU AKSES DAN ASSIGN TITLE
        $submenu_code = 'gosend_logs';
        $permits = $this->_check_menu_access( $submenu_code, 'view');//kalau tidak ada permission untuk view langsung redirect & return permits
        $this->_set_title('Gosend Logs');
        $this->load->library('pagination');

        //set variable
        $page       = $this->input->get('page');
        $socol      = set_var($this->input->get('sc'), 'order');
        $soby       = set_var($this->input->get('sb'), 'ASC');//ASC or DESC

        //additional filter
        $search                 = set_var($this->input->get('search'), '');
        $from                   = set_var($this->input->get('from'), date('Y-m-d', strtotime('-1 day')));
        $to                     = set_var($this->input->get('to'), date('Y-m-d'));
        $xtravar['search']      = $search;
        $xtravar['from']        = $from;
        $xtravar['to']          = $to;

        //set sortable col
        $allow_sort['id']           = 'gosend_id';
        $allow_sort['type']         = 'gosend_type';
        $allow_sort['date']         = 'created_date';
        $allow_sort['endpoint']     = 'gosend_endpoint';
        $allow_sort['header']       = 'gosend_header';
        $allow_sort['request']      = 'gosend_request';
        $allow_sort['response']     = 'gosend_response';


        //start query
        $url_query    = 'sc='.$socol.'&sb='.$soby;
        $url_query   .= '&from='.$from.'&to='.$to;
        $url_query   .= '&search='.$search;

        $search_where = "AND created_date >= ? AND created_date <= ? AND ( gosend_endpoint LIKE ? OR gosend_header LIKE ? OR gosend_request LIKE ? OR gosend_response LIKE ? OR gosend_type LIKE ? )";
        $search_data  = array($from, $to.' 23:59:59','%'.$search.'%', '%'.$search.'%', '%'.$search.'%', '%'.$search.'%', '%'.$search.'%');
        $search_order = sort_table_order($allow_sort, $socol, $soby);

        $this->load->model('storedb');

        //start query
        $all_data = $this->gosenddb->getpaging_logs($search_where, $search_data, $search_order, $page);

        //start pagination setting
        $config['base_url']             = ADMIN_URL.'transaction/gosend_logs'.($url_query != '' ? '?'.$url_query : '');
        $config['total_rows']           = $all_data['total_row'];
        $config['per_page']             = $all_data['perpage'];
        $this->pagination->initialize($config);
        //end pagination setting


        // SELECT DATA & ASSIGN VARIABLE $DATA
        $data['form_url']       = $config['base_url'];
        $data['page_url']       = str_replace($url_query, '', $config['base_url']);
        $data['xtravar']        = $xtravar;
        $data['permits']        = $permits;
        $data['all_data']       = $all_data['data'];
        $data['pagination']     = $this->pagination->create_links();

        $data['from']   	    = $from;
        $data['to']     	    = $to;
        $data['search']   	    = $search;

        $this->_render('transaction/gosend_logs', $data);
    }

    public function payment_logs() {

        // VALIDASI MENU AKSES DAN ASSIGN TITLE
        $submenu_code   = 'payment_logs';
        $permits        = $this->_check_menu_access( $submenu_code, 'view');//kalau tidak ada permission untuk view langsung redirect & return permits
        $this->_set_title('Payment Logs');
        $this->load->library('pagination');

        //set variable
        $page       = $this->input->get('page');
        $socol      = set_var($this->input->get('sc'), 'order');
        $soby       = set_var($this->input->get('sb'), 'ASC');//ASC or DESC

        //additional filter
        $search                 = set_var($this->input->get('search'), '');
        $from                   = set_var($this->input->get('from'), date('Y-m-d', strtotime('-1 day')));
        $to                     = set_var($this->input->get('to'), date('Y-m-d'));
        $xtravar['search']      = $search;
        $xtravar['from']        = $from;
        $xtravar['to']          = $to;

        //set sortable col
        $allow_sort['id']           = 'pylog_id';
        $allow_sort['type']         = 'pylog_type';
        $allow_sort['date']         = 'created_date';
        $allow_sort['endpoint']     = 'pylog_endpoint';
        $allow_sort['header']       = 'pylog_header';
        $allow_sort['request']      = 'pylog_request';
        $allow_sort['response']     = 'pylog_response';


        //start query
        $url_query    = 'sc='.$socol.'&sb='.$soby;
        $url_query   .= '&from='.$from.'&to='.$to;
        $url_query   .= '&search='.$search;

        $search_where = "AND created_date >= ? AND created_date <= ? AND ( pylog_endpoint LIKE ? OR pylog_header LIKE ? OR pylog_request LIKE ? OR pylog_response LIKE ? OR pylog_type LIKE ? )";
        $search_data  = array($from, $to.' 23:59:59','%'.$search.'%', '%'.$search.'%', '%'.$search.'%', '%'.$search.'%', '%'.$search.'%');
        $search_order = sort_table_order($allow_sort, $socol, $soby);

        $this->load->model('orderdb');

        //start query
        $all_data = $this->orderdb->getpaging_payment_logs($search_where, $search_data, $search_order, $page);

        //start pagination setting
        $config['base_url']             = ADMIN_URL.'transaction/payment_logs'.($url_query != '' ? '?'.$url_query : '');
        $config['total_rows']           = $all_data['total_row'];
        $config['per_page']             = $all_data['perpage'];
        $this->pagination->initialize($config);
        //end pagination setting


        // SELECT DATA & ASSIGN VARIABLE $DATA
        $data['form_url']       = $config['base_url'];
        $data['page_url']       = str_replace($url_query, '', $config['base_url']);
        $data['xtravar']        = $xtravar;
        $data['permits']        = $permits;
        $data['all_data']       = $all_data['data'];
        $data['pagination']     = $this->pagination->create_links();

        $data['from']   	    = $from;
        $data['to']     	    = $to;
        $data['search']   	    = $search;

        $this->_render('transaction/payment_logs', $data);
    }

    public function print_struck($id = 0) {

        $submenu_code = 'ongoing';
        $permits = $this->_check_menu_access($submenu_code, 'edit');

        $order = $this->orderdb->get_order($id);
        if (!$order) {
            redirect(ADMIN_URL);
        }

        //load library qrcode and generate
        $this->load->library('sicepatapi');
        $barcode = $this->sicepatapi->generate_barcode($order->uor_code);

        $order_product = $this->orderdb->get_order_item_detail($order->uor_id);

        $data['order_data']     = $order;
        $data['order_product']  = $order_product;
        $data['image']          = $barcode;

        $this->load->view('print/sicepat', $data);
    }


    public function bulk_print_struck() {

        $submenu_code = 'ongoing';
        $permits = $this->_check_menu_access($submenu_code, 'edit');
        //load library qrcode and generate
        $this->load->library('sicepatapi');
        $id = $this->input->get("id");
        $decode = json_decode(base64_decode($id), true);
        $order = [];
        $barcode = [];
        $order_product = [];

        foreach($decode as $key_order => $val_order) {
            $order[$key_order] = $this->orderdb->get_order($val_order);

            if (!$order[$key_order]) {
                unset($order[$key_order]);
            } else {
                $barcode[$key_order] = $this->sicepatapi->generate_barcode($order[$key_order]->uor_code);
    
                $order_product[$key_order] = $this->orderdb->get_order_item_detail($order[$key_order]->uor_id);
            }
        }
        
        $data['order_data']     = $order;
        $data['order_product']  = $order_product;
        $data['image']          = $barcode;

        $this->load->view('print/bulk_sicepat', $data);
    }

    public function grab_logs() {
        $this->load->model('grabdb');

        // VALIDASI MENU AKSES DAN ASSIGN TITLE
        $submenu_code = 'grab_logs';
        $permits = $this->_check_menu_access( $submenu_code, 'view');//kalau tidak ada permission untuk view langsung redirect & return permits
        $this->_set_title('grab Logs');
        $this->load->library('pagination');

        //set variable
        $page       = $this->input->get('page');
        $socol      = set_var($this->input->get('sc'), 'order');
        $soby       = set_var($this->input->get('sb'), 'ASC');//ASC or DESC

        //additional filter
        $search                 = set_var($this->input->get('search'), '');
        $from                   = set_var($this->input->get('from'), date('Y-m-d', strtotime('-1 day')));
        $to                     = set_var($this->input->get('to'), date('Y-m-d'));
        $xtravar['search']      = $search;
        $xtravar['from']        = $from;
        $xtravar['to']          = $to;

        //set sortable col
        $allow_sort['id']           = 'grab_id';
        $allow_sort['type']         = 'grab_type';
        $allow_sort['date']         = 'created_date';
        $allow_sort['endpoint']     = 'grab_endpoint';
        $allow_sort['header']       = 'grab_header';
        $allow_sort['request']      = 'grab_request';
        $allow_sort['response']     = 'grab_response';


        //start query
        $url_query    = 'sc='.$socol.'&sb='.$soby;
        $url_query   .= '&from='.$from.'&to='.$to;
        $url_query   .= '&search='.$search;

        $search_where = "AND created_date >= ? AND created_date <= ? AND ( grab_endpoint LIKE ? OR grab_header LIKE ? OR grab_request LIKE ? OR grab_response LIKE ? OR grab_type LIKE ? )";
        $search_data  = array($from, $to.' 23:59:59','%'.$search.'%', '%'.$search.'%', '%'.$search.'%', '%'.$search.'%', '%'.$search.'%');
        $search_order = sort_table_order($allow_sort, $socol, $soby);

        $this->load->model('storedb');

        //start query
        $all_data = $this->grabdb->getpaging_logs($search_where, $search_data, $search_order, $page);

        //start pagination setting
        $config['base_url']             = ADMIN_URL.'transaction/grab_logs'.($url_query != '' ? '?'.$url_query : '');
        $config['total_rows']           = $all_data['total_row'];
        $config['per_page']             = $all_data['perpage'];
        $this->pagination->initialize($config);
        //end pagination setting


        // SELECT DATA & ASSIGN VARIABLE $DATA
        $data['form_url']       = $config['base_url'];
        $data['page_url']       = str_replace($url_query, '', $config['base_url']);
        $data['xtravar']        = $xtravar;
        $data['permits']        = $permits;
        $data['all_data']       = $all_data['data'];
        $data['pagination']     = $this->pagination->create_links();

        $data['from']   	    = $from;
        $data['to']     	    = $to;
        $data['search']   	    = $search;

        $this->_render('transaction/grab_logs', $data);
    }

    public function sicepat_logs() {
        $this->load->model('sicepatdb');

        // VALIDASI MENU AKSES DAN ASSIGN TITLE
        $submenu_code = 'sicepat_logs';
        $permits = $this->_check_menu_access( $submenu_code, 'view');//kalau tidak ada permission untuk view langsung redirect & return permits
        $this->_set_title('Sicepat Logs');
        $this->load->library('pagination');

        //set variable
        $page       = $this->input->get('page');
        $socol      = set_var($this->input->get('sc'), 'order');
        $soby       = set_var($this->input->get('sb'), 'ASC');//ASC or DESC

        //additional filter
        $search                 = set_var($this->input->get('search'), '');
        $from                   = set_var($this->input->get('from'), date('Y-m-d', strtotime('-1 day')));
        $to                     = set_var($this->input->get('to'), date('Y-m-d'));
        $xtravar['search']      = $search;
        $xtravar['from']        = $from;
        $xtravar['to']          = $to;

        //set sortable col
        $allow_sort['id']           = 'sicepat_id';
        $allow_sort['type']         = 'sicepat_type';
        $allow_sort['date']         = 'created_date';
        $allow_sort['endpoint']     = 'sicepat_endpoint';
        $allow_sort['header']       = 'sicepat_header';
        $allow_sort['request']      = 'sicepat_request';
        $allow_sort['response']     = 'sicepat_response';


        //start query
        $url_query    = 'sc='.$socol.'&sb='.$soby;
        $url_query   .= '&from='.$from.'&to='.$to;
        $url_query   .= '&search='.$search;

        $search_where = "AND created_date >= ? AND created_date <= ? AND ( sicepat_endpoint LIKE ? OR sicepat_header LIKE ? OR sicepat_request LIKE ? OR sicepat_response LIKE ? OR sicepat_type LIKE ? )";
        $search_data  = array($from, $to.' 23:59:59','%'.$search.'%', '%'.$search.'%', '%'.$search.'%', '%'.$search.'%', '%'.$search.'%');
        $search_order = sort_table_order($allow_sort, $socol, $soby);

        $this->load->model('storedb');

        //start query
        $all_data = $this->sicepatdb->getpaging_logs($search_where, $search_data, $search_order, $page);

        //start pagination setting
        $config['base_url']             = ADMIN_URL.'transaction/sicepat_logs'.($url_query != '' ? '?'.$url_query : '');
        $config['total_rows']           = $all_data['total_row'];
        $config['per_page']             = $all_data['perpage'];
        $this->pagination->initialize($config);
        //end pagination setting


        // SELECT DATA & ASSIGN VARIABLE $DATA
        $data['form_url']       = $config['base_url'];
        $data['page_url']       = str_replace($url_query, '', $config['base_url']);
        $data['xtravar']        = $xtravar;
        $data['permits']        = $permits;
        $data['all_data']       = $all_data['data'];
        $data['pagination']     = $this->pagination->create_links();

        $data['from']   	    = $from;
        $data['to']     	    = $to;
        $data['search']   	    = $search;

        $this->_render('transaction/sicepat_logs', $data);
    }

    public function triplogic_logs() {
        $this->load->model('triplogicdb');

        // VALIDASI MENU AKSES DAN ASSIGN TITLE
        $submenu_code = 'triplogic_logs';
        $permits = $this->_check_menu_access( $submenu_code, 'view');//kalau tidak ada permission untuk view langsung redirect & return permits
        $this->_set_title('Triplogic Logs');
        $this->load->library('pagination');

        //set variable
        $page       = $this->input->get('page');
        $socol      = set_var($this->input->get('sc'), 'order');
        $soby       = set_var($this->input->get('sb'), 'ASC');//ASC or DESC

        //additional filter
        $search                 = set_var($this->input->get('search'), '');
        $from                   = set_var($this->input->get('from'), date('Y-m-d', strtotime('-1 day')));
        $to                     = set_var($this->input->get('to'), date('Y-m-d'));
        $xtravar['search']      = $search;
        $xtravar['from']        = $from;
        $xtravar['to']          = $to;

        //set sortable col
        $allow_sort['id']           = 'triplogic_id';
        $allow_sort['type']         = 'triplogic_type';
        $allow_sort['date']         = 'created_date';
        $allow_sort['endpoint']     = 'triplogic_endpoint';
        $allow_sort['header']       = 'triplogic_header';
        $allow_sort['request']      = 'triplogic_request';
        $allow_sort['response']     = 'triplogic_response';


        //start query
        $url_query    = 'sc='.$socol.'&sb='.$soby;
        $url_query   .= '&from='.$from.'&to='.$to;
        $url_query   .= '&search='.$search;

        $search_where = "AND created_date >= ? AND created_date <= ? AND ( triplogic_endpoint LIKE ? OR triplogic_header LIKE ? OR triplogic_request LIKE ? OR triplogic_response LIKE ? OR triplogic_type LIKE ? )";
        $search_data  = array($from, $to.' 23:59:59','%'.$search.'%', '%'.$search.'%', '%'.$search.'%', '%'.$search.'%', '%'.$search.'%');
        $search_order = sort_table_order($allow_sort, $socol, $soby);

        $this->load->model('storedb');

        //start query
        $all_data = $this->triplogicdb->getpaging_logs($search_where, $search_data, $search_order, $page);

        //start pagination setting
        $config['base_url']             = ADMIN_URL.'transaction/triplogic_logs'.($url_query != '' ? '?'.$url_query : '');
        $config['total_rows']           = $all_data['total_row'];
        $config['per_page']             = $all_data['perpage'];
        $this->pagination->initialize($config);
        //end pagination setting


        // SELECT DATA & ASSIGN VARIABLE $DATA
        $data['form_url']       = $config['base_url'];
        $data['page_url']       = str_replace($url_query, '', $config['base_url']);
        $data['xtravar']        = $xtravar;
        $data['permits']        = $permits;
        $data['all_data']       = $all_data['data'];
        $data['pagination']     = $this->pagination->create_links();

        $data['from']   	    = $from;
        $data['to']     	    = $to;
        $data['search']   	    = $search;

        $this->_render('transaction/triplogic_logs', $data);
    }

    public function dana_logs() {
        $this->load->model('danadb');

        // VALIDASI MENU AKSES DAN ASSIGN TITLE
        $submenu_code = 'dana_logs';
        $permits = $this->_check_menu_access( $submenu_code, 'view');//kalau tidak ada permission untuk view langsung redirect & return permits
        $this->_set_title('Dana Logs');
        $this->load->library('pagination');

        //set variable
        $page       = $this->input->get('page');
        $socol      = set_var($this->input->get('sc'), 'order');
        $soby       = set_var($this->input->get('sb'), 'ASC');//ASC or DESC

        //additional filter
        $search                 = set_var($this->input->get('search'), '');
        $from                   = set_var($this->input->get('from'), date('Y-m-d', strtotime('-1 day')));
        $to                     = set_var($this->input->get('to'), date('Y-m-d'));
        $xtravar['search']      = $search;
        $xtravar['from']        = $from;
        $xtravar['to']          = $to;

        //set sortable col
        $allow_sort['id']           = 'dnlog_id';
        $allow_sort['user_id']      = 'user_id';
        $allow_sort['type']         = 'dnlog_type';
        $allow_sort['date']         = 'created_date';
        $allow_sort['endpoint']     = 'dnlog_endpoint';
        $allow_sort['header']       = 'dnlog_header';
        $allow_sort['request']      = 'dnlog_request';
        $allow_sort['response']     = 'dnlog_response';


        //start query
        $url_query    = 'sc='.$socol.'&sb='.$soby;
        $url_query   .= '&from='.$from.'&to='.$to;
        $url_query   .= '&search='.$search;

        $search_where = "AND created_date >= ? AND created_date <= ? AND ( dnlog_endpoint LIKE ? OR dnlog_header LIKE ? OR dnlog_request LIKE ? OR dnlog_response LIKE ? OR dnlog_type LIKE ? OR user_id LIKE ? )";
        $search_data  = array($from, $to.' 23:59:59','%'.$search.'%', '%'.$search.'%', '%'.$search.'%', '%'.$search.'%', '%'.$search.'%', '%'.$search.'%');
        $search_order = sort_table_order($allow_sort, $socol, $soby);

        $this->load->model('storedb');

        //start query
        $all_data = $this->danadb->getpaging_logs($search_where, $search_data, $search_order, $page);

        //start pagination setting
        $config['base_url']             = ADMIN_URL.'transaction/dana_logs'.($url_query != '' ? '?'.$url_query : '');
        $config['total_rows']           = $all_data['total_row'];
        $config['per_page']             = $all_data['perpage'];
        $this->pagination->initialize($config);
        //end pagination setting


        // SELECT DATA & ASSIGN VARIABLE DATA
        $data['current_url']    = ADMIN_URL.'user/user_detail/';
        $data['form_url']       = $config['base_url'];
        $data['page_url']       = str_replace($url_query, '', $config['base_url']);
        $data['xtravar']        = $xtravar;
        $data['permits']        = $permits;
        $data['all_data']       = $all_data['data'];
        $data['pagination']     = $this->pagination->create_links();

        $data['from']   	    = $from;
        $data['to']     	    = $to;
        $data['search']   	    = $search;

        $this->_render('transaction/dana_logs', $data);
    }
}
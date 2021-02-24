<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Report extends MY_Admin {

    function __construct()
	{
		parent::__construct();

        $this->load->model('reportdb');
	}

    public function index()
	{
		show_404();
    }

    public function sales_item_old()
    {
        // VALIDASI MENU AKSES DAN ASSIGN TITLE
        $submenu_code = 'sales_item';
        $permits = $this->_check_menu_access( $submenu_code, 'view');//kalau tidak ada permission untuk view langsung redirect & return permits
        $this->_set_title('Sales per item');

        $this->load->library('pagination');

        //set variable
        $page       = $this->input->get('page');
        $socol      = set_var($this->input->get('sc'), 'order');
        $soby       = set_var($this->input->get('sb'), 'ASC');//ASC or DESC
        $delivery_type = $this->config->item('order')['delivery_type'];

        //additional filter
        $search                 = set_var($this->input->get('search'), '');
        $from                   = set_var($this->input->get('from'), date('Y-m-d', strtotime('-1 day')));
        $to                     = set_var($this->input->get('to'), date('Y-m-d'));
        $st_id                  = intval($this->input->get('st_id'));
        $delivery               = set_var($this->input->get('delivery_type'), 'all');
        $xtravar['search']      = $search;
        $xtravar['from']        = $from;
        $xtravar['to']          = $to;
        $xtravar['st_id']       = $st_id;
        $xtravar['delivery_type'] = $delivery;

        //set sortable col
        $allow_sort['date']         = 'uor.uor_date';
        $allow_sort['name']         = 'uor.user_id';
        $allow_sort['order']        = 'uor.uor_code';
        $allow_sort['type']         = 'uor.uor_delivery_type';
        $allow_sort['status']       = 'uor.uor_status';
        $allow_sort['product']      = 'uorpd.uorpd_name';
        $allow_sort['price']        = 'uorpd.uorpd_final_price';
        $allow_sort['disc']         = 'discount';
        $allow_sort['qty']          = 'uorpd.uorpd_qty';
        $allow_sort['total_price']  = 'total_price';
        $allow_sort['total_disc']   = 'total_disc';
        $allow_sort['grand_total']  = 'total';
        $allow_sort['pymtd']        = 'pymtd.pymtd_name';


        //start query
        $url_query    = 'sc='.$socol.'&sb='.$soby;
        $url_query   .= '&from='.$from.'&to='.$to;
        $url_query   .= '&st_id='.$st_id.'&delivery_type='.$delivery;
        $url_query   .= '&search='.$search;

        $search_where = " AND uor.uor_status LIKE ? AND uor.uor_date >= ? AND uor.uor_date <= ? AND ( uorpd.uorpd_name LIKE ? OR uor.uor_code LIKE ? )";
        $search_data  = array($this->config->item('order')['status']['completed'], $from, $to.' 23:59:59', '%'.$search.'%', '%'.$search.'%');
        //update by igo 22 dec 2018, jika store ID di passing maka munculkan data berdasarkan store tersebut, jika tidak munculkan semua data di semua store

        //update by purnomo 22 feb 2018, semua total yang ada di sales per order di implements juga ke sales per item
        $search_where_order = " AND uor.uor_status LIKE ? AND uor.uor_date >= ? AND uor.uor_date <= ? AND ( uor.uor_code LIKE ? )";
        $search_data_order  = array($this->config->item('order')['status']['completed'], $from, $to.' 23:59:59', '%'.$search.'%');

        $user = $this->admindb->get_admin($this->_get_user_id());
        $store_permits = 0;
        if($user->st_id > 0) {
            $store_permits = $user->st_id;
            $search_where .= " AND uoradd.st_id = ? ";
            $search_where_order .= " AND uoradd.st_id = ? ";
            array_push($search_data, $user->st_id);
            array_push($search_data_order, $user->st_id);
        }else {
            if($st_id > 0) {
                $search_where .= " AND uoradd.st_id = ? ";
                $search_where_order .= " AND uoradd.st_id = ? ";
                array_push($search_data, $st_id);
                array_push($search_data_order, $st_id);
            }
        }

        if($delivery != 'all'){
            $search_where .= " AND uor.uor_delivery_type = ? ";
            $search_where_order .= " AND uor.uor_delivery_type = ? ";
            $search_data[] = $delivery;
            $search_data_order[] = $delivery;
        }

        $search_order = sort_table_order($allow_sort, $socol, $soby);
        $find_total = $this->reportdb->get_total_sales_item($search_where, $search_data, $search_order);
        $find_total = $this->reportdb->get_total_sales_order($search_where_order, $search_data_order, $search_order);

        if($this->input->get('export') == 'xls'){
            //mulai dari set header dan filename
            $filename = 'sales-item.xls';
            $this->set_header_xls($filename);

            //select data dari database
            $all_data = $this->reportdb->getall_sales_item($search_where, $search_data, $search_order);

            //taro datanya di parameter untuk di baca di view
            $data['all_data'] = $all_data;
            $data['total_report'] = $find_total;

            //load view table yang mau di export
            $this->_render('report/sales_item_xls', $data);

        }else{

            $this->load->model('storedb');

            //start query
            $all_data = $this->reportdb->getpaging_sales_item($search_where, $search_data, $search_order, $page);
            $all_store = $this->storedb->getall();

            //start pagination setting
            $config['base_url']             = ADMIN_URL.'report/sales_item'.($url_query != '' ? '?'.$url_query : '');
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
            $data['store_data']     = $all_store;
            $data['delivery_type']  = $delivery_type;
            $data['curr_delivery']  = !empty($delivery) ? $delivery : 'all';

            $data['from']   	    = $from;
            $data['to']     	    = $to;
            $data['search']   	    = $search;
            $data['st_id']          = $st_id;
            $data['store_permits']  = $store_permits;
            $data['total_report']   = $find_total;

            $this->_render('report/sales_item', $data);

        }
    }


    public function sales_item()
    {
        // VALIDASI MENU AKSES DAN ASSIGN TITLE
        $submenu_code = 'sales_item';
        $permits = $this->_check_menu_access( $submenu_code, 'view');//kalau tidak ada permission untuk view langsung redirect & return permits
        $this->_set_title('Sales per item');

        $this->load->library('pagination');
        $this->load->model('orderdb');

        //set variable
        $page       = $this->input->get('page');
        $socol      = set_var($this->input->get('sc'), 'order');
        $soby       = set_var($this->input->get('sb'), 'ASC');//ASC or DESC
        $delivery_type = $this->config->item('order')['delivery_type'];

        //additional filter
        $search                 = set_var($this->input->get('search'), '');
        $from                   = set_var($this->input->get('from'), date('Y-m-d'));
        $to                     = set_var($this->input->get('to'), date('Y-m-d'));
        $st_id                  = intval($this->input->get('st_id'));
        $delivery               = set_var($this->input->get('delivery_type'), 'all');
        $xtravar['search']      = $search;
        $xtravar['from']        = $from;
        $xtravar['to']          = $to;
        $xtravar['st_id']       = $st_id;
        $xtravar['delivery_type'] = $delivery;

        //set sortable col
        $allow_sort['date']         = 'uor.uor_date';
        $allow_sort['name']         = 'uor.user_id';
        $allow_sort['order']        = 'uor.uor_code';
        $allow_sort['type']         = 'uor.uor_delivery_type';
        $allow_sort['status']       = 'uor.uor_status';
        $allow_sort['product']      = 'uorpd.uorpd_name';
        $allow_sort['price']        = 'uorpd.uorpd_final_price';
        $allow_sort['qty']          = 'uorpd.uorpd_qty';
        $allow_sort['pymtd']        = 'pymtd.pymtd_name';


        //start query
        $url_query    = 'sc='.$socol.'&sb='.$soby;
        $url_query   .= '&from='.$from.'&to='.$to;
        $url_query   .= '&st_id='.$st_id.'&delivery_type='.$delivery;
        $url_query   .= '&search='.$search;

        $search_where = " AND uor.uor_status LIKE ? AND uor.uor_date >= ? AND uor.uor_date <= ? ";
        $search_data  = array($this->config->item('order')['status']['completed'], $from, $to.' 23:59:59');
        //update by igo 22 dec 2018, jika store ID di passing maka munculkan data berdasarkan store tersebut, jika tidak munculkan semua data di semua store

        //kalau ada yang di search baru include query where seperti ini, jika tidak jangan di passing
        if($search != ""){
            $search_where .= " AND (uor.uor_code LIKE ? ) ";
            $search_data[] = '%'.$search.'%';
        }

        $admin_detail = $this->admindb->get_admin($this->_get_user_id());
        $store_permits = 0;
        if($admin_detail->st_id > 0) {
            $store_permits = $admin_detail->st_id;
            $search_where .= " AND uoradd.st_id = ? ";
            array_push($search_data, $admin_detail->st_id);
        }else {
            if($st_id > 0) {
                $search_where .= " AND uoradd.st_id = ? ";
                array_push($search_data, $st_id);
            }
        }

        if($delivery != 'all'){
            $search_where .= " AND uor.uor_delivery_type = ? ";
            $search_data[] = $delivery;
        }

        $search_order = sort_table_order($allow_sort, $socol, $soby);
        $find_total = $this->reportdb->get_total_sales($search_where, $search_data);

        if($this->input->get('export') == 'xls'){
            //mulai dari set header dan filename
            $filename = 'sales-item.xls';
            $this->set_header_xls($filename);

            //select data dari database
            $all_data = $this->reportdb->getall_sales_item($search_where, $search_data, $search_order);

            // looping for set data to array
            $where = "";
            $arr_data_product = [];
            foreach($all_data as $key => $value ) {
                $arr_data_product[] = $value->pd_id;
                $where .= '?,';
            }

            //get cogs price
            $where_cogs = " AND cogs.cogs_date >= ? AND cogs.cogs_date <= ? ";
            //call function to get all user order voucher
            if(!empty($arr_data_product) && count($arr_data_product) > 0){
                $where = substr($where, 0, -1);
                $search_data_cogs = $arr_data_product;
                array_unshift($search_data_cogs, $from, $to);
                $where_cogs .= " AND cogs.pd_id IN ({$where}) ";
            } else {
                $search_data_cogs = [$from, $to];
            }
            $data_cogs = $this->reportdb->get_all_product_cogs_by_date($where_cogs, $search_data_cogs);

            //taro datanya di parameter untuk di baca di view
            $data['all_data']       = $all_data;
            $data['total_report']   = $find_total;
            $data['cogs']           = $data_cogs;

            //load view table yang mau di export
            $this->_render('report/sales_item_xls', $data);

        }else{
            $this->load->model('storedb');

            //start query
            $all_data = $this->reportdb->getpaging_sales_item($search_where, $search_data, $search_order, $page);
            // get all store
            $all_store = $this->storedb->getall();

            // looping for set data to array
            $where = "";
            $arr_data_product = [];
            foreach($all_data['data'] as $key => $value ) {
                $arr_data_product[] = $value->pd_id;

                $where .= '?,';
            }

            //get cogs price
            $where_cogs = " AND cogs.cogs_date >= ? AND cogs.cogs_date <= ? ";
            if(!empty($arr_data_product) && count($arr_data_product) > 0){
                $where = substr($where, 0, -1);
                $search_data_cogs = $arr_data_product;
                array_unshift($search_data_cogs, $from, $to);
                $where_cogs .= " AND cogs.pd_id IN ({$where}) ";
            } else {
                $search_data_cogs = [$from, $to];
            }

            $data_cogs = $this->reportdb->get_all_product_cogs_by_date($where_cogs, $search_data_cogs);

            //start pagination setting
            $config['base_url']             = ADMIN_URL.'report/sales_item'.($url_query != '' ? '?'.$url_query : '');
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
            $data['store_data']     = $all_store;
            $data['delivery_type']  = $delivery_type;
            $data['curr_delivery']  = !empty($delivery) ? $delivery : 'all';
            $data['cogs']           = $data_cogs;

            $data['from']   	    = $from;
            $data['to']     	    = $to;
            $data['search']   	    = $search;
            $data['st_id']          = $st_id;
            $data['store_permits']  = $store_permits;
            $data['total_report']   = $find_total;

            $this->_render('report/sales_item', $data);

        }
    }

    public function sales_order_old()
    {
        // VALIDASI MENU AKSES DAN ASSIGN TITLE
        $submenu_code = 'sales_order';
        $permits = $this->_check_menu_access( $submenu_code, 'view');//kalau tidak ada permission untuk view langsung redirect & return permits
        $this->_set_title('Sales per order');

        $this->load->library('pagination');
        $this->load->model('orderdb');

        //set variable
        $page       = $this->input->get('page');
        $socol      = set_var($this->input->get('sc'), 'order');
        $soby       = set_var($this->input->get('sb'), 'ASC');//ASC or DESC
        $delivery_type = $this->config->item('order')['delivery_type'];

        //additional filter
        $search                 = set_var($this->input->get('search'), '');
        $from                   = set_var($this->input->get('from'), date('Y-m-d', strtotime('-1 day')));
        $to                     = set_var($this->input->get('to'), date('Y-m-d'));
        $st_id                  = intval($this->input->get('st_id'));
        $delivery               = set_var($this->input->get('delivery_type'), 'all');
        $xtravar['search']      = $search;
        $xtravar['from']        = $from;
        $xtravar['to']          = $to;
        $xtravar['st_id']       = $st_id;
        $xtravar['delivery_type'] = $delivery;

        //set sortable col
        $allow_sort['date']                 = 'uor.uor_date';
        $allow_sort['name']                 = 'usr.user_name';
        $allow_sort['order']                = 'uor.uor_code';
        $allow_sort['type']                 = 'uor.uor_delivery_type';
        $allow_sort['status']               = 'uor.uor_status';
        $allow_sort['total']                = 'uor.uor_subtotal';
        $allow_sort['sub_total']            = 'uor.uor_subtotal + uorpd.total_disc';
        $allow_sort['qty']                  = 'uorpd.total_cups';
        $allow_sort['discount']             = 'uorpd.total_disc';
        $allow_sort['delivery_fee']         = 'uor.uor_actual_delivery_fee';
        $allow_sort['disc_delivery_fee']    = 'disc_delivery_fee';
        $allow_sort['grand_total']          = 'uor.uor_total';
        $allow_sort['pymtd']                = 'pymtd.pymtd_name';


        //start query
        $url_query    = 'sc='.$socol.'&sb='.$soby;
        $url_query   .= '&from='.$from.'&to='.$to;
        $url_query   .= '&st_id='.$st_id.'&delivery_type='.$delivery;
        $url_query   .= '&search='.$search;

        $search_where = " AND uor.uor_status LIKE ? AND uor.uor_date >= ? AND uor.uor_date <= ? AND ( usr.user_name LIKE ? OR usr.user_email LIKE ? OR usr.user_phone LIKE ? OR uor.uor_code LIKE ? )";
        $search_data  = array($this->config->item('order')['status']['completed'], $from, $to.' 23:59:59', '%'.$search.'%', '%'.$search.'%', '%'.$search.'%', '%'.$search.'%');
        //update by igo 22 dec 2018, jika store ID di passing maka munculkan data berdasarkan store tersebut, jika tidak munculkan semua data di semua store

        $user = $this->admindb->get_admin($this->_get_user_id());
        $store_permits = 0;
        if($user->st_id > 0) {
            $store_permits = $user->st_id;
            $search_where .= " AND uoradd.st_id = ? ";
            array_push($search_data, $user->st_id);
        }else {
            if($st_id > 0) {
                $search_where .= " AND uoradd.st_id = ? ";
                array_push($search_data, $st_id);
            }
        }

        if($delivery != 'all'){
            $search_where .= " AND uor.uor_delivery_type = ? ";
            $search_data[] = $delivery;
        }

        $search_order = sort_table_order($allow_sort, $socol, $soby);

        $find_total = $this->reportdb->get_total_sales_order($search_where, $search_data, $search_order);

        if($this->input->get('export') == 'xls'){
            //mulai dari set header dan filename
            $filename = 'sales-order.xls';
            $this->set_header_xls($filename);

            //select data dari database
            $all_data = $this->reportdb->getall_sales_order($search_where, $search_data, $search_order);

            //taro datanya di parameter untuk di baca di view
            $data['all_data']       = $all_data;
            $data['total_report']   = $find_total;

            //load view table yang mau di export
            $this->_render('report/sales_order_xls', $data);

        }else{
            $this->load->model('storedb');

            //start query
            $all_data = $this->reportdb->getpaging_sales_order($search_where, $search_data, $search_order, $page);
            $all_store = $this->storedb->getall();

            //start pagination setting
            $config['base_url']             = ADMIN_URL.'report/sales_order'.($url_query != '' ? '?'.$url_query : '');
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
            $data['store_data']     = $all_store;
            $data['total_report']   = $find_total;
            $data['delivery_type']  = $delivery_type;
            $data['curr_delivery']  = !empty($delivery) ? $delivery : 'all';

            $data['from']   	= $from;
            $data['to']     	= $to;
            $data['search']   	= $search;
            $data['st_id']          = $st_id;
            $data['store_permits']  = $store_permits;

            $this->_render('report/sales_order', $data);

        }
    }

    public function sales_order()
    {
        // VALIDASI MENU AKSES DAN ASSIGN TITLE
        $submenu_code = 'sales_order';
        $permits = $this->_check_menu_access( $submenu_code, 'view');//kalau tidak ada permission untuk view langsung redirect & return permits
        $this->_set_title('Sales per order');

        $this->load->library('pagination');
        $this->load->model('orderdb');

        //set variable
        $page       = $this->input->get('page');
        $socol      = set_var($this->input->get('sc'), 'order');
        $soby       = set_var($this->input->get('sb'), 'ASC');//ASC or DESC
        $delivery_type = $this->config->item('order')['delivery_type'];

        //additional filter
        $search                 = set_var($this->input->get('search'), '');
        $from                   = set_var($this->input->get('from'), date('Y-m-d'));
        $to                     = set_var($this->input->get('to'), date('Y-m-d'));
        $st_id                  = intval($this->input->get('st_id'));
        $delivery               = set_var($this->input->get('delivery_type'), 'all');
        $xtravar['search']      = $search;
        $xtravar['from']        = $from;
        $xtravar['to']          = $to;
        $xtravar['st_id']       = $st_id;
        $xtravar['delivery_type'] = $delivery;

        //set sortable col
        $allow_sort['date']                 = 'uor.uor_date';
        $allow_sort['name']                 = 'uor.user_id';
        $allow_sort['order']                = 'uor.uor_code';
        $allow_sort['type']                 = 'uor.uor_delivery_type';
        $allow_sort['status']               = 'uor.uor_status';
        $allow_sort['total']                = 'uor.uor_subtotal';
        $allow_sort['sub_total']            = 'uor.uor_subtotal + uorpd.total_disc';
        $allow_sort['qty']                  = 'uorpd.total_cups';
        $allow_sort['discount']             = 'uorpd.total_disc';
        $allow_sort['delivery_fee']         = 'uor.uor_actual_delivery_fee';
        $allow_sort['disc_delivery_fee']    = 'disc_delivery_fee';
        $allow_sort['grand_total']          = 'uor.uor_total';
        $allow_sort['pymtd']                = 'pymtd.pymtd_name';


        //start query
        $url_query    = 'sc='.$socol.'&sb='.$soby;
        $url_query   .= '&from='.$from.'&to='.$to;
        $url_query   .= '&st_id='.$st_id.'&delivery_type='.$delivery;
        $url_query   .= '&search='.$search;

        $search_where = " AND uor.uor_status LIKE ? AND uor.uor_date >= ? AND uor.uor_date <= ? ";
        $search_data  = array($this->config->item('order')['status']['completed'], $from, $to.' 23:59:59');
        //update by igo 22 dec 2018, jika store ID di passing maka munculkan data berdasarkan store tersebut, jika tidak munculkan semua data di semua store

        //kalau ada yang di search baru include query where seperti ini, jika tidak jangan di passing
        if($search != ""){
            $search_where .= " AND (uor.uor_code LIKE ? ) ";
            $search_data[] = '%'.$search.'%';
        }

        $user = $this->admindb->get_admin($this->_get_user_id());
        $store_permits = 0;
        if($user->st_id > 0) {
            $store_permits = $user->st_id;
            $search_where .= " AND uoradd.st_id = ? ";
            array_push($search_data, $user->st_id);
        }else { 
            if($st_id > 0) {
                $search_where .= " AND uoradd.st_id = ? ";
                array_push($search_data, $st_id);
            }
        }

        if($delivery != 'all'){
            $search_where .= " AND uor.uor_delivery_type = ? ";
            $search_data[] = $delivery;
        }
        $search_order = sort_table_order($allow_sort, $socol, $soby);

        $find_total = $this->reportdb->get_total_sales($search_where, $search_data);

        if($this->input->get('export') == 'xls'){
            //mulai dari set header dan filename
            $filename = 'sales-order.xls';
            $this->set_header_xls($filename);

            //select data dari database
            $all_data = $this->reportdb->getall_sales_order($search_where, $search_data, $search_order);

            //taro datanya di parameter untuk di baca di view
            $data['all_data'] = $all_data;
            $data['total_report'] = $find_total;

            //load view table yang mau di export
            $this->_render('report/sales_order_xls', $data);

        }else{
            $this->load->model('storedb');

            //start query
            $all_data = $this->reportdb->getpaging_sales_order($search_where, $search_data, $search_order, $page);
            $all_store = $this->storedb->getall();

            //start pagination setting
            $config['base_url']             = ADMIN_URL.'report/sales_order'.($url_query != '' ? '?'.$url_query : '');
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
            $data['store_data']     = $all_store;
            $data['total_report']   = $find_total;
            $data['delivery_type']  = $delivery_type;
            $data['curr_delivery']  = !empty($delivery) ? $delivery : 'all';

            $data['from']   	= $from;
            $data['to']     	= $to;
            $data['search']   	= $search;
            $data['st_id']          = $st_id;
            $data['store_permits']  = $store_permits;

            $this->_render('report/sales_order', $data);

        }
    }

    public function balance_user()
    {
        // VALIDASI MENU AKSES DAN ASSIGN TITLE
        $submenu_code = 'balance_user';
        $permits = $this->_check_menu_access( $submenu_code, 'view');//kalau tidak ada permission untuk view langsung redirect & return permits
        $this->_set_title('Balance per user');

        $this->load->library('pagination');

        //set variable
        $page       = $this->input->get('page');
        $socol      = set_var($this->input->get('sc'), 'name');
        $soby       = set_var($this->input->get('sb'), 'ASC');//ASC or DESC

        //additional filter
        $search                 = set_var($this->input->get('search'), '');
        $from                   = set_var($this->input->get('from'), date('Y-m-d', strtotime('-1 day')));
        $to                     = set_var($this->input->get('to'), date('Y-m-d'));
        $xtravar['search']      = $search;
        $xtravar['from']        = $from;
        $xtravar['to']          = $to;

        //set sortable col
        $allow_sort['id']           = 'user_id';
        $allow_sort['name']         = 'user_name';
        $allow_sort['email']        = 'user_email';
        $allow_sort['phone']        = 'user_phone';
        $allow_sort['opening']      = 'total_opening_balace';
        $allow_sort['topup']        = 'total_topup';
        $allow_sort['cashback']     = 'total_cashback';
        $allow_sort['transaction']  = 'total_transaction';
        $allow_sort['refund']       = 'total_refund';
        $allow_sort['withdraw']     = 'total_withdraw';
        $allow_sort['closing']      = 'total_closing_balance';

        //start query
        $url_query    = 'sc='.$socol.'&sb='.$soby;
        $url_query   .= '&from='.$from.'&to='.$to;
        $url_query   .= '&search='.$search;

        $search_order = sort_table_order($allow_sort, $socol, $soby);

        if($this->input->get('export') == 'xls'){
            //mulai dari set header dan filename
            $filename = 'balance-per-user.xls';
            $this->set_header_xls($filename);

            //select data dari database
            $all_data = $this->reportdb->getall_balance_per_user($search, $from, $to.' 23:59:59', $search_order);

            //taro datanya di parameter untuk di baca di view
            $data['all_data'] = $all_data;

            //load view table yang mau di export
            $this->_render('report/balance_user_xls', $data);

        }else{
            //start query
            $all_data = $this->reportdb->getpaging_balance_per_user($search, $from, $to.' 23:59:59', $search_order, $page);


            //start pagination setting
            $config['base_url']             = ADMIN_URL.'report/balance_user'.($url_query != '' ? '?'.$url_query : '');
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

            $data['from']   	= $from;
            $data['to']     	= $to;
            $data['search']   	= $search;

            $this->_render('report/balance_user', $data);

        }
    }

    public function monthly()
    {
        // VALIDASI MENU AKSES DAN ASSIGN TITLE
        $submenu_code = 'monthly';
        $permits      = $this->_check_menu_access( $submenu_code, 'view');//kalau tidak ada permission untuk view langsung redirect & return permits
        $this->_set_title('Monthly');

        //SET VARIABLE
        $from               = set_var($this->input->get('from'), date('Y-m-d', strtotime('-1 day')));
        $to                 = set_var($this->input->get('to'), date('Y-m-d'));

        $cst_status             = $this->config->item('order')['status']['completed'];
        $cst_delivery_type      = $this->config->item('order')['delivery_type'];
        $ios                    = $this->config->item('user_download')['usrd_type']['ios'];
        $android                = $this->config->item('user_download')['usrd_type']['android'];

        $url_query              = '&from='.$from.'&to='.$to;

        //ADDITIONAL FILTER

        $arr_data                           = ["from_date" => $from, "to_date" => $to.' 23:59:59', "negation" => true, "status" => $cst_status];
        $arr_data_usrd_android              = $arr_data;
        $arr_data_usrd_ios                  = $arr_data;
        $arr_data_usrd_android['usrd_type'] = $android;
        $arr_data_usrd_ios['usrd_type']     = $ios;

        $arr_data_negation                  = $arr_data;
        $arr_data_pickup                    = $arr_data;
        $arr_data_delivery                  = $arr_data;
        $arr_data_negation["negation"]      = false;
        $arr_data_pickup['delivery_type']   = $cst_delivery_type['pickup'];
        $arr_data_delivery['delivery_type'] = $cst_delivery_type['delivery'];


        $total_android                  = $this->reportdb->get_total_download_apps($arr_data_usrd_android);
        $total_ios                      = $this->reportdb->get_total_download_apps($arr_data_usrd_ios);
        $total_download                 = (int) $total_android->total + (int) $total_ios->total;

        //--------------------------------------------------------------------------------------------------------------------------
        $data_total_order     = $this->reportdb->get_total_complete($arr_data);
        $data_total_pickup    = $this->reportdb->get_total_complete($arr_data_pickup);
        $data_total_delivery  = $this->reportdb->get_total_complete($arr_data_delivery);
        //-------------------------------------------------------------------------------------------------------------------------------------------------
        $user                           = $this->reportdb->get_total_user($arr_data);
        $user_not_order                 = $this->reportdb->get_total_user_not_order($arr_data);
        $user_topup_in_same_month       = $this->reportdb->get_total_user_topup_month($arr_data);
        $user_topup_not_in_same_month   = $this->reportdb->get_total_user_topup_month($arr_data_negation);
        $user_have_balance              = $this->reportdb->get_total_user_have_balance_in_end_month($arr_data);
        //-------------------------------------------------------------------------------------------------------------------------------------------------
        $user_reff                      = $this->reportdb->get_total_user_referral($arr_data);
        $user_reff_not_claim            = $this->reportdb->get_total_user_referral_claim($arr_data_negation);
        $user_reff_claim                = $this->reportdb->get_total_user_referral_claim($arr_data);
        $user_reff_claim_not_repeat     = $this->reportdb->get_total_user_referral_claim_repeat($arr_data_negation);
        $user_reff_claim_repeat         = $this->reportdb->get_total_user_referral_claim_repeat($arr_data);
        $user_reff_not_free             = $this->reportdb->get_total_user_referral_not_free($arr_data);
        //--------------------------------------------------------------------------------------------------------------------------
        $claim_free                     = $this->reportdb->get_total_claim_free($arr_data);
        $claim_free_repeat              = $this->reportdb->get_total_claim_free_repeat($arr_data);
        $claim_free_not_order           = $this->reportdb->get_total_claim_free_repeat($arr_data_negation);
        $not_claim_free                 = $this->reportdb->get_not_claim_free($arr_data);
        //--------------------------------------------------------------------------------------------------------------------------
        $user_topup                     = $this->reportdb->get_total_user_topup($arr_data);

        // SELECT DATA & ASSIGN VARIABLE $DATA
        $config['base_url']                     = ADMIN_URL.'report/monthly'.($url_query != '' ? '?'.$url_query : '');
        $data['form_url']                       = $config['base_url'];
        $data['page_url']                       = str_replace($url_query, '', $config['base_url']);
        $data['permits']                        = $permits;
        $data['from']                           = $from;
        $data['to']                             = $to;
        //-----------------------------------------------------------------------
        $data['total_order']                    = $data_total_order->total;
        $data['total_pickup']                   = $data_total_pickup->total;
        $data['total_delivery']                 = $data_total_delivery->total;
        //-----------------------------------------------------------------------
        $data['user']                           = $user;
        $data['user_not_order']                 = $user_not_order;
        $data['user_topup_in_same_month']       = $user_topup_in_same_month;
        $data['user_topup_not_in_same_month']   = $user_topup_not_in_same_month;
        $data['user_have_balance']              = $user_have_balance;
        //----------------------------------------------------------------------
        $data['user_reff']                      = $user_reff;
        $data['reff_not_claim']                 = $user_reff_not_claim;
        $data['reff_claim']                     = $user_reff_claim;
        $data['reff_claim_not_repeat']          = $user_reff_claim_not_repeat;
        $data['reff_claim_repeat']              = $user_reff_claim_repeat;
        $data['reff_not_free']                  = $user_reff_not_free;
        //----------------------------------------------------------------------
        $data['claim_free']                     = $claim_free;
        $data['claim_free_repeat']              = $claim_free_repeat;
        $data['claim_free_not_order']           = $claim_free_not_order;
        $data['not_claim_free']                 = $not_claim_free;
        //----------------------------------------------------------------------
        $data['user_topup']                     = $user_topup;
        $data['total_download']                 = $total_download;
        $data['total_android']                  = $total_android->total;
        $data['total_ios']                      = $total_ios->total;

        if($this->input->get('export') == 'xls'){
            $permits = $this->_check_menu_access( $submenu_code, 'export');
            //mulai dari set header dan filename
            $filename = 'monthly.xls';
            $this->set_header_xls($filename);

            $this->_render('report/monthly_xls', $data);

        }else{
            $this->_render('report/monthly', $data);
        }
    }

    public function cohort(){
        // VALIDASI MENU AKSES DAN ASSIGN TITLE
        $submenu_code = 'cohort';
        $permits = $this->_check_menu_access( $submenu_code, 'export');
        $this->_set_title('Cohort');

        //SET VARIABLE
        $to                     = set_var($this->input->get('to'), date('Y-m-d'));
        $cup_status             = $this->config->item('cohort')['cups_status'];

        $url_query              = '&to='.$to;

        $arr_data                       = ["to_date" => $to.' 23:59:59'];
        $arr_data_paid                  = $arr_data;
        $arr_data_free                  = $arr_data;
        $arr_data_paid["uorpd_is_free"] = $cup_status['paid'];
        $arr_data_free["uorpd_is_free"] = $cup_status['free'];


        // SELECT DATA & ASSIGN VARIABLE DATA
        $config['base_url']     = ADMIN_URL.'report/cohort'.($url_query != '' ? '?'.$url_query : '');
        $data['form_url']       = $config['base_url'];
        $data['page_url']       = str_replace($url_query, '', $config['base_url']);
        $data['permits']        = $permits;
        $data['to']             = $to;

        if($this->input->get('export') == 'total_transaction_xls'){
            $filename = 'total_transaction.xls';
            $this->set_header_xls($filename);

            $all_data = $this->reportdb->get_total_transaction($arr_data);

            $data['all_data'] = $all_data;

            $this->_render('report/total_transaction_xls', $data);

        }else if($this->input->get('export') == 'data_xls'){
            //mulai dari set header dan filename
            $filename = 'cohort_data.xls';
            $this->set_header_xls($filename);

            //select data dari database
            $all_data = $this->reportdb->get_cohort_data($arr_data);

            //taro datanya di parameter untuk di baca di view
            $data['all_data'] = $all_data;

            //load view table yang mau di export
            $this->_render('report/cohort_data_xls', $data);

        }else if($this->input->get('export') == 'topup_user_xls'){
            $filename = 'cohort_topup_user.xls';
            $this->set_header_xls($filename);

            $all_data = $this->reportdb->get_cohort_topup_user($arr_data);

            $data['all_data'] = $all_data;

            $this->_render('report/cohort_topup_user_xls', $data);

        }else if($this->input->get('export') == 'referral_xls'){
            $filename = 'cohort_referral.xls';
            $this->set_header_xls($filename);

            $all_data = $this->reportdb->get_cohort_referral($arr_data);

            $data['all_data'] = $all_data;

            $this->_render('report/cohort_referral_xls', $data);

        }else if($this->input->get('export') == 'paid_cups_xls'){
            $filename = 'cohort_paid_cups.xls';
            $this->set_header_xls($filename);

            $all_data = $this->reportdb->get_cohort_cups($arr_data_paid);

            $data['all_data'] = $all_data;

            $this->_render('report/cohort_paid_cups_xls', $data);

        }else if($this->input->get('export') == 'free_cups_xls'){
            $filename = 'cohort_free_cups.xls';
            $this->set_header_xls($filename);

            $all_data = $this->reportdb->get_cohort_cups($arr_data_free);

            $data['all_data'] = $all_data;

            $this->_render('report/cohort_free_cups_xls', $data);

        }else if($this->input->get('export') == 'voucher_complimentary_xls'){
            $filename = 'cohort_voucher_complimentary.xls';
            $this->set_header_xls($filename);

            $all_data = $this->reportdb->get_cohort_voucher_complimentary($arr_data);

            $data['all_data'] = $all_data;

            $this->_render('report/cohort_voucher_complimentary_xls', $data);

        }else{
            $this->_render('report/cohort', $data);
        }

    }

    // START REPORT SUBSCRIPTION
    public function summary()
    {
        // VALIDASI MENU AKSES DAN ASSIGN TITLE
        $submenu_code = 'summary';
        $permits = $this->_check_menu_access( $submenu_code, 'view');//kalau tidak ada permission untuk view langsung redirect & return permits
        $this->_set_title('Summary');

        $this->load->library('pagination');
        $this->load->model('reportdb');
        $this->load->model('subscriptiondb');

        //set variable
        $order_status = $this->config->item('order')['status']['completed'];
        $subs_status  = $this->config->item('subs_order')['status']['paid'];
        $vc_status    = $this->config->item('voucher')['status'];
        $prm_status   = $this->config->item('promo')['status'];

        //additional filter
        $from                   = set_var($this->input->get('from'), date('Y-m-d'));
        $to                     = set_var($this->input->get('to'), date('Y-m-d'));
        $xtravar['from']        = $from;
        $xtravar['to']          = $to;

        //start query
        $url_query   = '&from='.$from.'&to='.$to;

        $search_where       = " AND uor.uor_date >= ? AND uor.uor_date <= ? AND uor.uor_status LIKE ? ";
        $search_subs        = " AND suo.subsorder_date >= ? AND suo.subsorder_date <= ? ";
        $search_voucher     = " AND prm.prm_start >= ? AND prm.prm_end <= ? ";
        $search_voucher_exp = $search_voucher;
        $search_voucher_exp.= " AND prm.prm_status = ? ";
        
        $search_data        = array("from" => $from, "today" => $to.date(' H:m:s'), "to" => $to.' 23:59:59', "subs_status" => $subs_status);
        $search_data_subs   = $search_data;
        $search_data_order  = array($from, $to.' 23:59:59');
        $search_data_order[]= $order_status;

        $data_subsplan = $this->subscriptiondb->getall_subs_plan();

        foreach($data_subsplan as $key => $subsplan)
        {         
            $search_data_subs["subsplan_id"]    = $subsplan->subsplan_id;
            $search_data_subs["prm_code"]       = $subsplan->subsplan_code.'%'; 

            $find_total_subs[$key]              = $this->reportdb->get_total_report_summary($search_subs, $search_data_subs);  
                        
            $subscriber                         = $this->reportdb->get_total_subscriber($search_subs, $search_data_subs);
            $find_total_subs[$key]->total_subscriber = $subscriber->total_subs;

            // menghitung total voucher yang sudah digunakan
            $search_data_subs["status"]         = $vc_status['used']; 
            $search_data_subs["vc_status"]      = $vc_status['used'];
            $voucher                            = $this->reportdb->get_total_voucher($search_voucher, $search_data_subs); 
            $find_total_subs[$key]->total_voucher_used = $voucher->total_voucher;   

            // menghitung total voucher yang sudah expired
            $search_data_subs["status"]         = $vc_status['expired'];
            $search_data_subs["vc_status"]      = $vc_status['active'];
            $search_data_subs["prm_status"]     = $prm_status['expired'];
            $voucher                            = $this->reportdb->get_total_voucher($search_voucher_exp, $search_data_subs);
            $find_total_subs[$key]->total_voucher_expired = $voucher->total_voucher;    

            // menghitung total voucher yang belum digunakan
            $search_data_subs["status"]         = $vc_status['active'];
            $search_data_subs["vc_status"]      = $vc_status['active'];
            $search_data_subs["prm_status"]     = $prm_status['active'];
            $voucher                            = $this->reportdb->get_total_voucher($search_voucher_exp, $search_data_subs); 
            $find_total_subs[$key]->total_voucher_unused = $voucher->total_voucher;
            
        }
        
        $find_total = $this->reportdb->get_total_sales($search_where, $search_data_order);

        if($this->input->get('export') == 'xls'){
            //mulai dari set header dan filename
            $filename = 'summary '.$from.' until '.$to.'.xls';
            $this->set_header_xls($filename);

            //taro datanya di parameter untuk di baca di view
            $data['subsplan'] = $data_subsplan;
            $data['all_data'] = $find_total_subs;
            $data['total_report'] = $find_total;

            //load view table yang mau di export
            $this->_render('report/summary_xls', $data);

        }else{
            $config['base_url']     = ADMIN_URL.'report/summary'.($url_query != '' ? '?'.$url_query : '');

            // SELECT DATA & ASSIGN VARIABLE $DATA
            $data['form_url']       = $config['base_url'];
            $data['page_url']       = str_replace($url_query, '', $config['base_url']);
            $data['xtravar']        = $xtravar;
            $data['permits']        = $permits;
            $data['all_data']       = $find_total_subs;
            $data['pagination']     = $this->pagination->create_links();
            $data['total_report']   = $find_total;

            $data['from']   	= $from;
            $data['to']     	= $to;

            $this->_render('report/summary', $data);

        }
    }
    // END REPORT SUBSCRIPTION
}
?>
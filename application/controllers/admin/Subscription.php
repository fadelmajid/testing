<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Subscription extends MY_Admin
{
    function __construct()
    {
        parent::__construct();
        $this->load->model('subscriptiondb');
    }
    
    public function index()
    {
        show_404();
    }

    //>>START SUBSCRIPTION ORDER<<
    function subs_order(){
        // validasi menu dan assign title
        $submenu_code   = 'subs_order';
        $permits        = $this->_check_menu_access($submenu_code, 'view');

        $this->_set_title('subs_order');
        $this->load->library('pagination');
        
        //set variable
        $current_url        = ADMIN_URL.'subscription/subs_order';
        $page               = $this->input->get('page');
        $search             = set_var($this->input->get('search'), '');
        $start_date         = set_var($this->input->get('start'), date('Y-m-d', strtotime('-7 day')));
        $end_date           = set_var($this->input->get('end'), date('Y-m-d'));
        $sort_col           = set_var($this->input->get('sc'), 'id');
        $sort_by            = set_var($this->input->get('sb'), 'ASC');
        $xtra_var['search'] = $search;
        $xtra_var['start']  = $start_date;
        $xtra_var['end']    = $end_date;
        $arr_admin          = $this->admindb->getarr_admin();

        //set sortable col
        $allow_sort = [
            'id'                    => 'subsorder_id',
            'user'                  => 'user_name',
            'subsorder_code'        => 'subsorder_code',
            'subsorder_date'        => 'subsorder_date',
            'subsorder_subtotal'    => 'subsorder_subtotal',
            'subsorder_discount'    => 'subsorder_discount',
            'subsorder_total'       => 'subsorder_total',
            'subsorder_status'      => 'subsorder_status',
            'subsorder_remarks'     => 'subsorder_remarks',
            'created'               => 'created_date',
            'updated'               => 'updated_date'
        ];
        
        //start query
        $url_query      = "search={$search}&sc={$sort_col}&sb={$sort_by}&start={$start_date}&end={$end_date}";
        $search_where   = " AND (subsorder.subsorder_id LIKE ? OR subsplan.subsplan_name LIKE ? OR subsorder.user_id LIKE ? OR usr.user_name LIKE ? OR usr.user_email LIKE ? 
                            OR usr.user_phone LIKE ? OR pymtd.pymtd_name LIKE ? OR subsorder_code LIKE ? OR subsorder_subtotal LIKE ? OR subsorder_discount LIKE ? 
                            OR subsorder_total LIKE ? OR subsorder_status LIKE ? OR subsorder_remarks LIKE ?)AND subsorder.created_date >= ? AND subsorder.created_date <= ?";  
        $search_data    = [$search.'%', "%{$search}%", "%{$search}%", "%{$search}%", "%{$search}%","%{$search}%", "%{$search}%", "%{$search}%","%{$search}%","%{$search}%","%{$search}%", "%{$search}%","%{$search}%",$start_date, $end_date.' 23:59:59'];
        $search_order   = sort_table_order($allow_sort, $sort_col, $sort_by);
        $subs_order      = $this->subscriptiondb->getpaging_subs_order(
            $search_where,
            $search_data,
            $search_order,
            $page
        );
        
        // start pagination setting
        $config = [
            'base_url'   => $current_url.($url_query != '' ? '?'.$url_query : ''),
            'total_rows' => $subs_order['total_row'],
            'per_page'   => $subs_order['per_page']
        ];
        $this->pagination->initialize($config);
        // end pagination setting

        // select data & assign variable $data
        $data = [
            'current_url'           => $current_url,
            'form_url'              => $config['base_url'],
            'page_url'              => str_replace($url_query, '', $config['base_url']),
            'xtra_var'              => $xtra_var,
            'search'                => $search,
            'start_date'            => $start_date,
            'end_date'              => $end_date,
            'permits'               => $permits,
            'data'                  => $subs_order['data'],
            'subsorder_status_name' => $this->config->item('subs_order')['status_name'],
            'subsorder_status'      => $this->config->item('subs_order')['status'],
            'arr_admin'             => $arr_admin,
            'pagination'            => $this->pagination->create_links()
        ];
        $this->_render('subscription/subs_order', $data);
    }

    public function subs_order_detail($id = 0)
    {
        //load model
        $this->load->model('productdb');

        $submenu_code   = 'subs_order';
        $permits        = $this->_check_menu_access($submenu_code, 'view');
        $this->_set_title('Subs Order Detail');
        $form_permit    = $this->_get_form_permit($id, $permits);

        // validate order
        $subs_order     = $this->subscriptiondb->get_subs_order($id);
        if (!$subs_order) {
            redirect(ADMIN_URL);
        }

        // get subs order, subs order detail list
        $where              = " AND sod.subsorder_id = ?";
        $subs_order_detail  = $this->subscriptiondb->getall_subs_order_detail($where, [$id]);

        $current_path       = 'subscription/subs_order_detail';
        $data = [
            'current_url'       => ADMIN_URL.$current_path,
            'permits'           => $permits,
            'show_form'         => $form_permit['show_form'],
            'title_form'        => $form_permit['title_form'],
            'data' => [
                'subs_order'        => $subs_order,
                'subs_order_detail' => $subs_order_detail
            ]
        ];

        $this->_render('subscription/subs_order_detail', $data);
    }
    //>>END SUBSCRIPTION ORDER<<

    //>>START SUBSCRIPTION COUNTER<<
    function subs_counter(){
        // validasi menu dan assign title
        $submenu_code   = 'subs_counter';

        $permits        = $this->_check_menu_access($submenu_code, 'view');
        $this->_set_title('Subs Counter');
        $this->load->library('pagination');

        //set variable
        $current_url        = ADMIN_URL.'subscription/subs_counter';
        $page               = $this->input->get('page');
        $start_date         = set_var($this->input->get('start'), date('Y-m-d', strtotime('-7 day')));
        $search             = set_var($this->input->get('search'), '');
        $end_date           = set_var($this->input->get('end'), date('Y-m-d'));
        $sort_col           = set_var($this->input->get('sc'), 'id');
        $sort_by            = set_var($this->input->get('sb'), '');
        $xtra_var['search'] = $search;
        $xtra_var['start']  = $start_date;
        $xtra_var['end']    = $end_date;
        $arr_admin          = $this->admindb->getarr_admin();

        //set sortable col
        $allow_sort = [
            'id'                    => 'sc_id',
            'user'                  => 'user_id',
            'subsdetail_id'         => 'subsdetail_id',
            'subsorder_id'          => 'subsorder_id',
            'subsplan_name'         => 'subsplan_name',
            'subsorder_qty'         => 'subsorder_qty',
            'sc_status'             => 'sc_status',
            'sc_total_counter'      => 'sc_total_counter',
            'sc_counter'            => 'sc_counter',
            'last_generate'         => 'last_generate',
            'created_date'          => 'created_date',
            'updated_date'          => 'updated_date'
        ];
        
        //start query
        $url_query      = "search={$search}&sc={$sort_col}&sb={$sort_by}&start={$start_date}&end={$end_date}";
        $search_where   = " AND (sc.sc_id LIKE ? OR sc.user_id LIKE ? OR sc.subsdetail_id LIKE ? OR sc.subsorder_id LIKE ? OR sp.subsplan_name LIKE ? OR sc.subsorder_qty LIKE ? 
                            OR sc.subsplan_promo LIKE ? OR sc.sc_status LIKE ? OR sc.sc_total_counter LIKE ? OR sc.sc_counter LIKE ? )AND sc.created_date >= ? AND sc.created_date <= ?";  
        $search_data    = [$search.'%', "%{$search}%", "%{$search}%", "%{$search}%", "%{$search}%","%{$search}%","%{$search}%", "%{$search}%", "%{$search}%","%{$search}%",$start_date, $end_date.' 23:59:59'];
        $search_order   = sort_table_order($allow_sort, $sort_col, $sort_by);
        $subs_counter   = $this->subscriptiondb->getpaging_subs_counter(
            $search_where,
            $search_data,
            $search_order,
            $page
        ); 

        $config = [
        // start pagination setting
            'base_url'   => $current_url.($url_query != '' ? '?'.$url_query : ''),
            'total_rows' => $subs_counter['total_row'],
            'per_page'   => $subs_counter['per_page']
        ];
        $this->pagination->initialize($config);
        // end pagination setting

        // select data & assign variable $data
        $data = [
            'current_url'             => $current_url,
            'user_url'                => ADMIN_URL.'user/user',
            'page_url'                => str_replace($url_query, '', $config['base_url']),
            'form_url'                => $config['base_url'],
            'xtra_var'                => $xtra_var,
            'search'                  => $search,
            'start_date'              => $start_date,
            'end_date'                => $end_date,
            'data'                    => $subs_counter['data'],
            'permits'                 => $permits,
            'subscounter_status_name' => $this->config->item('subs_counter')['status_name'],
            'subscounter_status'      => $this->config->item('subs_counter')['status'],
            'pagination'              => $this->pagination->create_links(),
            'arr_admin'               => $arr_admin
        ];

        $this->_render('subscription/subs_counter', $data);
    }
    //>>END SUBSCRIPTION COUNTER<<

    // START SUBCRIPTION PAYMENT LOGS
    public function subs_payment_logs() {

        // VALIDASI MENU AKSES DAN ASSIGN TITLE
        $submenu_code   = 'subs_payment_logs';
        $permits        = $this->_check_menu_access( $submenu_code, 'view');//kalau tidak ada permission untuk view langsung redirect & return permits
        $this->_set_title('Subscription Payment Logs');
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
        $allow_sort['id']           = 'splog_id';
        $allow_sort['type']         = 'splog_type';
        $allow_sort['date']         = 'created_date';
        $allow_sort['endpoint']     = 'splog_endpoint';
        $allow_sort['header']       = 'splog_header';
        $allow_sort['request']      = 'splog_request';
        $allow_sort['response']     = 'splog_response';


        //start query
        $url_query    = 'sc='.$socol.'&sb='.$soby;
        $url_query   .= '&from='.$from.'&to='.$to;
        $url_query   .= '&search='.$search;

        $search_where = "AND created_date >= ? AND created_date <= ? AND ( splog_endpoint LIKE ? OR splog_header LIKE ? OR splog_request LIKE ? OR splog_response LIKE ? OR splog_type LIKE ? )";
        $search_data  = array($from, $to.' 23:59:59','%'.$search.'%', '%'.$search.'%', '%'.$search.'%', '%'.$search.'%', '%'.$search.'%');
        $search_order = sort_table_order($allow_sort, $socol, $soby);

        $this->load->model('subscriptiondb');

        //start query
        $all_data = $this->subscriptiondb->getpaging_subs_payment_logs($search_where, $search_data, $search_order, $page);

        //start pagination setting
        $config['base_url']             = ADMIN_URL.'subscription/subs_payment_logs'.($url_query != '' ? '?'.$url_query : '');
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

        $this->_render('subscription/subs_payment_logs', $data);
    }
    // END SUBSCRIPTION PAYMENT LOGS
}
?>
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User extends MY_Admin
{
    function __construct()
    {
        parent::__construct();

        $this->load->model('userdb');
        $this->load->model('walletdb');
    }

    public function index()
    {
        show_404();
    }

    // START USER
    public function user()
    {
        // validasi menu dan assign title
        $submenu_code = 'user';
        $permits = $this->_check_menu_access($submenu_code, 'view');

        $this->_set_title('Users');
        $this->load->library('pagination');

        //set variable
        $current_url = ADMIN_URL.'user/user';
        $page = $this->input->get('page');
        $search = set_var($this->input->get('search'), '');
        $start_date = set_var($this->input->get('start'), '');
        $end_date = set_var($this->input->get('end'), date('Y-m-d'));
        $sort_col = set_var($this->input->get('sc'), 'name');
        $sort_by = set_var($this->input->get('sb'), 'ASC');
        $xtra_var['search'] = $search;
        $xtra_var['start'] = $start_date;
        $xtra_var['end'] = $end_date;

        //set sortable col
        $allow_sort = [
            'id' => 'user_id',
            'name' => 'user_name',
            'code' => 'user_code',
            'last_login' => 'last_login',
            'last_activity' => 'last_activity',
            'balance' => 'uwal_balance',
            'status' => 'user_status'
        ];

        //start query
        $url_query = "search={$search}&sc={$sort_col}&sb={$sort_by}&start={$start_date}&end={$end_date}";
        $search_where = " AND (user.user_id LIKE ? OR user.user_name LIKE ? OR user.user_phone LIKE ? OR user.user_email LIKE ? OR user.user_code LIKE ? OR user.user_status LIKE ? OR uwal.uwal_balance LIKE ?) AND user.created_date > ?  AND user.created_date < ?";
        $search_data = [$search.'%', "%{$search}%", "%{$search}%", "%{$search}%", "%{$search}%", "%{$search}%", "%{$search}%", $start_date, $end_date.' 23:59:59'];
        $search_order = sort_table_order($allow_sort, $sort_col, $sort_by);

        if($this->input->get('export') == 'xls'){
            //check permission export
            $permits = $this->_check_menu_access($submenu_code, 'export');
            //mulai dari set header dan filename
            $filename = 'user.xls';
            $this->set_header_xls($filename);

            //select data dari database
            $all_data = $this->userdb->getall_user($search_where, $search_data, $search_order);

            //taro datanya di parameter untuk di baca di view
            $data['all_data'] = $all_data;

            //load view table yang mau di export
            $this->_render('user/user_xls', $data);

        } else {

            $user = $this->userdb->getpaging(
                $search_where,
                $search_data,
                $search_order,
                $page
            );

            // start pagination setting
            $config = [
                'base_url' => $current_url.($url_query != '' ? '?'.$url_query : ''),
                'total_rows' => $user['total_row'],
                'per_page' => $user['per_page']
            ];
            $this->pagination->initialize($config);
            // end pagination setting

            // select data & assign variable $data
            $data = [
                'current_url' => $current_url,
                'form_url' => $config['base_url'],
                'page_url' => str_replace($url_query, '', $config['base_url']),
                'xtra_var' => $xtra_var,
                'search' => $search,
                'start_date' => $start_date,
                'end_date' => $end_date,
                'permits' => $permits,
                'data' => $user['data'],
                'pagination' => $this->pagination->create_links(),
                'cst_status' => $this->config->item('user')['status'],
            ];
            $this->_render('user/user', $data);
        }
    }

    public function user_detail($id = 0)
    {
        $submenu_code = 'user';
        $permits = $this->_check_menu_access($submenu_code, 'view');
        $this->_set_title('User Detail');
        $form_permit = $this->_get_form_permit($id, $permits);

        // validate user
        $user = $this->userdb->get($id);
        if (!$user) {
            redirect(ADMIN_URL);
        }

        // get user address list
        $where = " AND user_id = ?";
        $address = $this->userdb->getall_address($where, [$id]);

        // get user wallet and wallet history list
        $wallet = $this->walletdb->get_wallet($id);
        $wallet_history = $this->walletdb->getall_history($wallet->uwal_id);

        $current_path = 'user/user_detail';
        $data = [
            'current_url' => ADMIN_URL.$current_path,
            'permits' => $permits,
            'show_form' => $form_permit['show_form'],
            'title_form' => $form_permit['title_form'],
            'data' => [
                'user' => $user,
                'address' => $address,
                'wallet' => $wallet,
                'wallet_history' => $wallet_history
            ],
            'cst_user_status' => $this->config->item('user')['status'],
            'cst_wallet_history_type' => $this->config->item('wallet')['history_type'],
        ];
        $this->_render($current_path, $data);
    }

    public function user_referral()
    {
        // validasi menu dan assign title
        $submenu_code = 'user_referral';
        $permits = $this->_check_menu_access($submenu_code, 'view');

        $this->_set_title('User Referral');
        $this->load->library('pagination');

        //set variable
        $current_url = ADMIN_URL.'user/user_referral';
        $page = $this->input->get('page');
        $start_date = set_var($this->input->get('start'), '');
        $end_date = set_var($this->input->get('end'), date('Y-m-d'));
        $search = set_var($this->input->get('search'), '');
        $sort_col = set_var($this->input->get('sc'), 'id');
        $sort_by = set_var($this->input->get('sb'), 'DESC');
        $xtra_var['search'] = $search;
        $xtra_var['start'] = $start_date;
        $xtra_var['end'] = $end_date;

        //set sortable col
        $allow_sort = [
            'id'        => 'uref_id',
            'from'      => 'from_name',
            'to'        => 'to_name',
            'purch'     => 'uref_to_purchased',
            'created'   => 'uref.created_date',
            'updated'   => 'uref.updated_date'
        ];

        //start query
        $url_query = "search={$search}&sc={$sort_col}&sb={$sort_by}&start={$start_date}&end={$end_date}";
        $search_where = " AND (uref_id LIKE ? OR from_data.user_name LIKE ? OR from_data.user_phone LIKE ? OR from_data.user_email LIKE ? OR to_data.user_name LIKE ? OR to_data.user_phone LIKE ? OR to_data.user_email LIKE ?) AND uref.created_date >= ? AND uref.created_date <= ? ";
        $search_data = [$search.'%', "%{$search}%", "%{$search}%", "%{$search}%", "%{$search}%", "%{$search}%", "%{$search}%", $start_date, $end_date.' 23:59:59'];
        $search_order = sort_table_order($allow_sort, $sort_col, $sort_by);
        $user = $this->userdb->getpaging_referral(
            $search_where,
            $search_data,
            $search_order,
            $page
        );

        // start pagination setting
        $config = [
            'base_url' => $current_url.($url_query != '' ? '?'.$url_query : ''),
            'total_rows' => $user['total_row'],
            'per_page' => $user['per_page']
        ];
        $this->pagination->initialize($config);
        // end pagination setting

        // select data & assign variable $data
        $data = [
            'current_url' => $current_url,
            'form_url' => $config['base_url'],
            'page_url' => str_replace($url_query, '', $config['base_url']),
            'xtra_var' => $xtra_var,
            'search' => $search,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'permits' => $permits,
            'data' => $user['data'],
            'pagination' => $this->pagination->create_links(),
            'cst_status' => $this->config->item('user')['status'],
        ];
        $this->_render('user/user_referral', $data);
    }

    public function virtual_account() {

        // VALIDASI MENU AKSES DAN ASSIGN TITLE
        $submenu_code = 'va';
        $permits = $this->_check_menu_access( $submenu_code, 'view');//kalau tidak ada permission untuk view langsung redirect & return permits
        $this->_set_title('Virtual Account');
        $this->load->library('pagination');
        $this->load->model('userdb');

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
        $allow_sort['id']           = 'va.uva_id';
        $allow_sort['user']         = 'user.user_name';
        $allow_sort['bank']         = 'bank.bank_code';
        $allow_sort['provider']     = 'va.uva_provider';
        $allow_sort['acount_name']  = 'va.uva_account_name';
        $allow_sort['account_num']  = 'va.uva_account_number';
        $allow_sort['response']     = 'va.uva_response';
        $allow_sort['created']      = 'va.created_date';


        //start query
        $url_query    = 'sc='.$socol.'&sb='.$soby;
        $url_query   .= '&from='.$from.'&to='.$to;
        $url_query   .= '&search='.$search;

        $search_where = "AND va.created_date >= ? AND va.created_date <= ? AND ( user.user_name LIKE ? OR user.user_email LIKE ? OR user.user_phone LIKE ? OR bank.bank_code LIKE ? OR uva_provider LIKE ? OR uva_account_name LIKE ? OR uva_account_number LIKE ? OR uva_response LIKE ? OR uva_status LIKE ? )";
        $search_data  = array($from, $to.' 23:59:59','%'.$search.'%', '%'.$search.'%', '%'.$search.'%', '%'.$search.'%', '%'.$search.'%', '%'.$search.'%', '%'.$search.'%', '%'.$search.'%', '%'.$search.'%');
        $search_order = sort_table_order($allow_sort, $socol, $soby);

        //start query
        $all_data = $this->userdb->getpaging_va($search_where, $search_data, $search_order, $page);

        //start pagination setting
        $config['base_url']             = ADMIN_URL.'user/virtual_account'.($url_query != '' ? '?'.$url_query : '');
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

        $this->_render('user/virtual_account', $data);
    }

    // END USER

    public function emoney() {

        // VALIDASI MENU AKSES DAN ASSIGN TITLE
        $submenu_code = 'emoney';
        $permits = $this->_check_menu_access( $submenu_code, 'view');//kalau tidak ada permission untuk view langsung redirect & return permits
        $this->_set_title('E-money');
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
        $allow_sort['id']           = 'emy_id';
        $allow_sort['user']         = 'user_name';
        $allow_sort['method']       = 'pymtd_name';
        $allow_sort['number']       = 'emy_number';
        $allow_sort['created']      = 'created_date';
        $allow_sort['updated']      = 'updated_date';


        //start query
        $url_query    = 'sc='.$socol.'&sb='.$soby;
        $url_query   .= '&from='.$from.'&to='.$to;
        $url_query   .= '&search='.$search;

        $search_where = "AND emy.created_date >= ? AND emy.created_date <= ? AND ( emy.emy_id LIKE ? OR user.user_name LIKE ? OR pymtd.pymtd_name LIKE ? OR emy.emy_number LIKE ? )";
        $search_data  = array($from, $to.' 23:59:59','%'.$search.'%','%'.$search.'%','%'.$search.'%','%'.$search.'%');
        $search_order = sort_table_order($allow_sort, $socol, $soby);

        $this->load->model('userdb');

        //start query
        $all_data = $this->userdb->getpaging_emoney($search_where, $search_data, $search_order, $page);

        //start pagination setting
        $config['base_url']             = ADMIN_URL.'user/emoney'.($url_query != '' ? '?'.$url_query : '');
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

        $this->_render('user/emoney', $data);
    }

    //>>Start User Email Token<<
    function user_email_token(){
        // validasi menu dan assign title
        $submenu_code   = 'user_email_token';
        $permits        = $this->_check_menu_access($submenu_code, 'view');

        $this->_set_title('user_email_token');
        $this->load->library('pagination');
        $this->load->model('userdb');

        //set variable
        $current_url        = ADMIN_URL.'user/user_email_token';
        $page               = $this->input->get('page');
        $search             = set_var($this->input->get('search'), '');
        $start_date         = set_var($this->input->get('start'), date('Y-m-d', strtotime('-7 day')));
        $end_date           = set_var($this->input->get('end'), date('Y-m-d'));
        $sort_col           = set_var($this->input->get('sc'), 'id');
        $sort_by            = set_var($this->input->get('sb'), 'DESC');
        $xtra_var['search'] = $search;
        $xtra_var['start']  = $start_date;
        $xtra_var['end']    = $end_date;
        $arr_admin          = $this->admindb->getarr_admin();

        //set sortable col
        $allow_sort = [
            'id'            => 'uet_id',
            'user'          => 'user_id',
            'user_email'    => 'user_email',
            'uet_token'     => 'uet_token',
            'uet_status'    => 'uet_status',
            'expired_date'  => 'expired_date',
            'created_date'  => 'created_date',
            'updated_date'  => 'updated_date'
        ];

        //start query
        $url_query      = "search={$search}&sc={$sort_col}&sb={$sort_by}&start={$start_date}&end={$end_date}";
        $search_where   = " AND (uet.uet_id LIKE ? OR uet.user_id LIKE ? OR uet.user_email LIKE ? OR uet.uet_token LIKE ? OR uet.uet_status LIKE ? OR
                            uet.expired_date LIKE ? OR uet.updated_date LIKE ?)AND uet.created_date >= ? AND uet.created_date <= ?";
        $search_data    = [$search.'%', "%{$search}%", "%{$search}%", "%{$search}%", "%{$search}%","%{$search}%","%{$search}%",$start_date, $end_date.' 23:59:59'];
        $search_order   = sort_table_order($allow_sort, $sort_col, $sort_by);
        $user           = $this->userdb->getpaging_email_token(
            $search_where,
            $search_data,
            $search_order,
            $page
        );


        // start pagination setting
        $config = [
            'base_url'   => $current_url.($url_query != '' ? '?'.$url_query : ''),
            'total_rows' => $user['total_row'],
            'per_page'   => $user['per_page']
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
            'start_date'    => $start_date,
            'end_date'      => $end_date,
            'permits'       => $permits,
            'data'          => $user['data'],
            'arr_admin'     => $arr_admin,
            'pagination'    => $this->pagination->create_links(),
            'cst_status'    => $this->config->item('user')['status'],
        ];
        $this->_render('user/user_email_token', $data);
    }
    //>>End User Email Token<<
}
?>
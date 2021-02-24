<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends MY_Admin
{
    function __construct()
    {
        parent::__construct();

        $this->load->model('authdb');
    }

    public function index()
    {
        show_404();
    }

    //=== START AUTH
    public function auth()
    {
        // validasi menu dan assign title
        $submenu_code = 'auth_code';
        $permits = $this->_check_menu_access($submenu_code, 'view');

        $this->_set_title('Auth Code');
        $this->load->library('pagination');
        
        //set variable
        $current_path = 'auth/auth';
        $current_url = ADMIN_URL.$current_path;
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
            'id' => 'acode_id',
            'phone' => 'acode_phone',
            'secret' => 'acode_secret',
            'status' => 'acode_status',
            'exp' => 'expired_date',
            'created' => 'created_date',
            'updated' => 'updated_date'
        ];
        
        //start query
        $url_query = "search={$search}&sc={$sort_col}&sb={$sort_by}&start={$start_date}&end={$end_date}";
        $search_where = " AND (acode_id LIKE ? OR acode_phone LIKE ? OR acode_secret LIKE ? OR acode_status LIKE ?) AND created_date >= ? AND created_date <= ? ";
        $search_data = [$search.'%', "%{$search}%", "%{$search}%", "%{$search}%", $start_date, $end_date.' 23:59:59'];
        $search_order = sort_table_order($allow_sort, $sort_col, $sort_by);
        $all_data = $this->authdb->getpaging_auth_code(
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
            'current_url' => $current_url,
            'form_url' => $config['base_url'],
            'page_url' => str_replace($url_query, '', $config['base_url']),
            'xtra_var' => $xtra_var,
            'search' => $search,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'permits' => $permits,
            'all_data' => $all_data['data'],
            'pagination' => $this->pagination->create_links()
        ];
        $this->_render($current_path, $data);
    }

    public function auth_sms()
    {
        // validasi menu dan assign title
        $submenu_code = 'auth_sms';
        $permits = $this->_check_menu_access($submenu_code, 'view');

        $this->_set_title('SMS History');
        $this->load->library('pagination');
        
        //set variable
        $current_path = 'auth/auth_sms';
        $current_url = ADMIN_URL.$current_path;
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
            'id' => 'acsent_id',
            'phone' => 'acsent_phone',
            'text' => 'acsent_text',
            'status' => 'acsent_status',
            'res' => 'acsent_response',
            'created' => 'created_date',
            'updated' => 'updated_date'
        ];
        
        //start query
        $url_query = "search={$search}&sc={$sort_col}&sb={$sort_by}&start={$start_date}&end={$end_date}";
        $search_where = " AND (acsent_id LIKE ? OR acsent_phone LIKE ? OR acsent_text LIKE ? OR acsent_status LIKE ? OR acsent_response LIKE ?) AND created_date >= ? AND created_date <= ? ";
        $search_data = [$search.'%', "%{$search}%", "%{$search}%", "%{$search}%", "%{$search}%", $start_date, $end_date.' 23:59:59'];
        $search_order = sort_table_order($allow_sort, $sort_col, $sort_by);
        $all_data = $this->authdb->getpaging_auth_sms(
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
            'current_url' => $current_url,
            'form_url' => $config['base_url'],
            'page_url' => str_replace($url_query, '', $config['base_url']),
            'xtra_var' => $xtra_var,
            'search' => $search,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'permits' => $permits,
            'all_data' => $all_data['data'],
            'pagination' => $this->pagination->create_links()
        ];
        $this->_render($current_path, $data);
    }

    public function auth_token()
    {
        // validasi menu dan assign title
        $submenu_code = 'auth_token';
        $permits = $this->_check_menu_access($submenu_code, 'view');

        $this->_set_title('Token List');
        $this->load->library('pagination');
        
        //set variable
        $current_path = 'auth/auth_token';
        $current_url = ADMIN_URL.$current_path;
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
            'id' => 'atoken_id',
            'name' => 'user_name',
            'device' => 'atoken_device',
            'platform' => 'atoken_platform',
            'access' => 'atoken_access',
            'status' => 'atoken_status',
            'exp' => 'atoken.expired_date',
            'created' => 'atoken.created_date',
            'updated' => 'atoken.updated_date'
        ];
        
        //start query
        $url_query = "search={$search}&sc={$sort_col}&sb={$sort_by}&start={$start_date}&end={$end_date}";
        $search_where = " AND (atoken_id LIKE ? OR user.user_name LIKE ? OR user.user_phone LIKE ? OR user.user_email LIKE ? OR atoken_device LIKE ? OR atoken_platform LIKE ? OR atoken_access LIKE ? OR atoken_refresh LIKE ? OR atoken_status LIKE ? ) AND atoken.created_date >= ? AND atoken.created_date <= ? ";
        $search_data = [$search.'%', "%{$search}%", "%{$search}%", "%{$search}%", "%{$search}%", "%{$search}%", "%{$search}%", "%{$search}%", "%{$search}%", $start_date, $end_date.' 23:59:59'];
        $search_order = sort_table_order($allow_sort, $sort_col, $sort_by);
        $all_data = $this->authdb->getpaging_auth_token(
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
            'current_url' => $current_url,
            'form_url' => $config['base_url'],
            'page_url' => str_replace($url_query, '', $config['base_url']),
            'xtra_var' => $xtra_var,
            'search' => $search,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'permits' => $permits,
            'all_data' => $all_data['data'],
            'pagination' => $this->pagination->create_links()
        ];
        $this->_render($current_path, $data);
    }
}    
?>
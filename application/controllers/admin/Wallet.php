<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Wallet extends MY_Admin
{
    function __construct()
    {
        parent::__construct();

        $this->load->model('userdb');
        $this->load->model('walletdb');
        $this->load->model('authdb');
        $this->load->library('pushnotification');
    }
    
    public function index()
    {
        show_404();
    }

    public function user_topup()
    {
        // validasi menu dan assign title
        $submenu_code = 'user_topup';
        $permits = $this->_check_menu_access($submenu_code, 'view');
        

        $this->_set_title('Topup');
        $this->load->library('pagination');
        
        //set variable
        $current_url        = ADMIN_URL.'wallet/user_topup';
        $page               = $this->input->get('page');
        $search             = set_var($this->input->get('search'), '');
        $start_date         = set_var($this->input->get('start'), '');
        $end_date           = set_var($this->input->get('end'), date('Y-m-d'));
        $sort_col           = set_var($this->input->get('sc'), 'id');
        $sort_by            = set_var($this->input->get('sb'), 'DESC');
        $xtra_var['search'] = $search;
        $xtra_var['start']  = $start_date;
        $xtra_var['end']    = $end_date;
        $arr_admin          = $this->admindb->getarr_admin();

        //set sortable col
        $allow_sort = [
            'id' => 'utop_id',
            'name' => 'user_name',
            'nominal' => 'utop_nominal',
            'type' => 'utop_payment',
            'status' => 'utop_status',
            'date' => 'utop_date',
            'created' => 'created_date'
        ];
        
        //start query
        $url_query = "search={$search}&sc={$sort_col}&sb={$sort_by}&start={$start_date}&end={$end_date}";
        $search_where = " AND (utop_id LIKE ? OR user.user_name LIKE ? OR user.user_phone LIKE ? OR user.user_email LIKE ? OR utop_nominal LIKE ? OR utop_payment LIKE ? OR utop_status LIKE ?) AND utop_date >= ? AND utop_date <= ? ";  
        $search_data = [$search.'%', "%{$search}%", "%{$search}%", "%{$search}%", "%{$search}%", "%{$search}%", "%{$search}%", $start_date, $end_date.' 23:59:59'];
        $search_order = sort_table_order($allow_sort, $sort_col, $sort_by);
        $user = $this->userdb->getpaging_topup(
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
        $this->_render('user/user_topup', $data);
    }

    public function user_topup_add($id = 0)
    {
        // validasi menu dan assign title
        $submenu_code = 'user_topup';
        $permits = $this->_check_menu_access($submenu_code, 'add');
        $form_permit = $this->_get_form_permit(0, $permits);
        $cst_user = $this->config->item('user_topup');
        $current_url = ADMIN_URL.'wallet/user_topup_add';
        $logthis = $this->log4php('user_topup_add', APPLOG);
        $info  = "";
        
        // validate if app_version exists
        $detail_user = '';
        if ($id > 0) {
            $detail_user = $this->userdb->get($id);
            if (empty($detail_user)) {
                redirect(ADMIN_URL);
            }  
            $current_url = ADMIN_URL.'wallet/user_topup_add/'.$id;
        }

        //set form validation
        $this->load->library('form_validation');
        $this->form_validation->set_error_delimiters(PREFIX_ERROR_DELIMITER, SUFFIX_ERROR_DELIMITER);
        $this->form_validation->set_rules('user_id', 'Email/Phone Number', 'strip_tags|trim|required');
        $this->form_validation->set_rules('utop_nominal', 'Nominal', 'strip_tags|trim|required');
        $this->form_validation->set_rules('user_email', 'Email', 'strip_tags|trim|required|callback__is_user_exist');
        $this->form_validation->set_rules('uwal_balance', 'Balance', 'strip_tags');
        $this->form_validation->set_rules('user_name', 'Username', 'strip_tags');
        $this->form_validation->set_rules('user_phone', 'Phone Number', 'strip_tags');

        //run form validation
        $err_msg = [];
        if ($this->form_validation->run()) {
            $params = [
                'user_id'       => $this->input->post('user_id'),
                'utop_date'     => date('Y-m-d H:i:s'),
                'utop_payment'  => $cst_user['payment']['cash'],
                'utop_nominal'  => $this->input->post('utop_nominal'),
                'utop_status'   => $cst_user['status']['accepted'],
            ];

            //check limit max wallet 1000000
            $user = $this->userdb->get($params['user_id']);
            $valid_balance = $user->uwal_balance + $params['utop_nominal'];

            if($valid_balance >= $cst_user['limit']['max']) {
                $err_msg = [
                    'msg'   => 'Topup Failed - Max Balance is Rp. 1.000.000',
                    'type'  => 'danger'
                ];
            } else if (in_array('add', $permits)) {
                $params['created_by']   = $this->_get_user_id();
                $params['created_date'] = date('Y-m-d H:i:s');
                $params['utop_info']    = json_encode(['source'=> 'adminpanel', 'remarks' => $params]);
                if ($this->userdb->insert_user_topups($params)) {
                    $err_msg = [
                        'msg'   => 'Add Topup Success.'.js_clearform(),
                        'type'  => 'success'
                    ];

                    $token_push = $this->authdb->getall_atokenpush_notif_by_user_id($params['user_id']);
                    //reget data user for get total wallet
                    $user_data = $this->userdb->get($params['user_id']);
                    //check token_push if exist
                    if(!empty($token_push)) {
                        //looping push notification
                        foreach($token_push as $push_token) {
                            $user_token = isset($push_token->atoken_pushnotif) ? $push_token->atoken_pushnotif : ''; 
    
                            if(!empty($user_token)) {
                                //push notif cancel data
                                $notif_data = [
                                    "push_token" => $user_token,
                                    "text"       => 'You have recieved Rp '. number_format($params['utop_nominal'], 0, ',', '.') .' in\'s wallet, now your balance is Rp '.number_format($user_data->uwal_balance, 0, ',', '.'),
                                ];
                                
                                $response_push = $this->pushnotification->send_pushnotification($notif_data);
                                $info = 'push_notification - Topup Wallet '.$response_push;
                                $logthis->info($info);
                            }
                        }
                    }
                    $detail_user = "";
                } else {
                    $err_msg = [
                        'msg'   => 'Add Topup Failed!',
                        'type'  => 'danger'
                    ];
                }
            } else {
                $err_msg = [
                    'msg'   => 'Access denied - You are not authorized to access this page.',
                    'type'  => 'danger'
                ];
            }
        }
        
        $data = [
            'current_url'   => $current_url,
            'msg'           => set_form_msg($err_msg),
            'permits'       => $permits,
            'cst_user'      => $this->config->item('user_topup'),
            'show_form'     => $form_permit['show_form'],
            'title_form'    => $form_permit['title_form'],
            'user'          => $detail_user
        ];
        
        $logthis->info($info);
        $this->_render('user/user_topup_add', $data);
    }

    public function user_withdraw()
    {
        // validasi menu dan assign title
        $submenu_code = 'user_withdraw';
        $permits = $this->_check_menu_access($submenu_code, 'view');

        $this->_set_title('Withdraw');
        $this->load->model('admindb');
        $this->load->library('pagination');
        
        //set variable
        $page = $this->input->get('page');
        $search = set_var($this->input->get('search'), '');
        $start_date = set_var($this->input->get('start'), '');
        $end_date = set_var($this->input->get('end'), date('Y-m-d'));
        $sort_col = set_var($this->input->get('sc'), 'id');
        $sort_by = set_var($this->input->get('sb'), 'DESC');
        $xtra_var['search'] = $search;
        $xtra_var['start'] = $start_date;
        $xtra_var['end'] = $end_date;
        $arr_admin      = $this->admindb->getarr_admin();

        //set sortable col
        $allow_sort = [
            'id'        => 'uwd_id',
            'name'      => 'user.user_name',
            'bank'      => 'bank.bank_code',
            'date'      => 'uwd_date',
            'nominal'   => 'uwd_nominal',
            'status'    => 'uwd_status',
            'created'   => 'uwd.created_date',
            'by'        => 'uwd.created_by',
        ];
        
        //start query
        $url_query = "search={$search}&sc={$sort_col}&sb={$sort_by}&start={$start_date}&end={$end_date}";
        $search_where = " AND (uwd_id LIKE ? OR user.user_name LIKE ? OR user.user_phone LIKE ? OR user.user_email LIKE ? OR uwd_nominal LIKE ? OR uwd_status LIKE ?) AND uwd_date >= ? AND uwd_date <= ? ";  
        $search_data = [$search.'%', "%{$search}%", "%{$search}%", "%{$search}%", "%{$search}%", "%{$search}%", $start_date, $end_date.' 23:59:59'];
        $search_order = sort_table_order($allow_sort, $sort_col, $sort_by);
        $user = $this->userdb->getpaging_withdraw(
            $search_where,
            $search_data,
            $search_order,
            $page
        );
        
        // start pagination setting
        $config = [
            'base_url' => ADMIN_URL.'wallet/user_withdraw'.($url_query != '' ? '?'.$url_query : ''),
            'total_rows' => $user['total_row'],
            'per_page' => $user['per_page']
        ];
        $this->pagination->initialize($config);
        // end pagination setting
        
        // select data & assign variable $data
        $data = [
            'current_url' => ADMIN_URL.'wallet/user_withdraw',
            'form_url' => $config['base_url'],
            'page_url' => str_replace($url_query, '', $config['base_url']),
            'xtra_var' => $xtra_var,
            'search' => $search,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'permits' => $permits,
            'data' => $user['data'],
            'arr_admin' => $arr_admin,
            'pagination' => $this->pagination->create_links()
        ];
        $this->_render('user/user_withdraw', $data);
    }

    public function user_withdraw_add($id = 0)
    {
        // validasi menu dan assign title
        $submenu_code = 'user_withdraw';
        $permits = $this->_check_menu_access($submenu_code, 'add');

        $this->_set_title('Withdraw');
        $form_permit = $this->_get_form_permit(0, $permits);
        $cst_user = $this->config->item('user_topup');
        $current_url = ADMIN_URL.'wallet/user_withdraw_add';
        $logthis = $this->log4php('user_topup_add', APPLOG);
        $info  = "";
        
        // validate if app_version exists
        $detail_user = "";
        if ($id > 0) {
            $detail_user = $this->userdb->get($id);
            if (empty($detail_user)) {
                redirect(ADMIN_URL);
            }
            $current_url = ADMIN_URL.'wallet/user_withdraw_add/'.$id;
        }
        //set form validation
        $this->load->library('form_validation');
        $this->form_validation->set_error_delimiters(PREFIX_ERROR_DELIMITER, SUFFIX_ERROR_DELIMITER);
        $this->form_validation->set_rules('user_id', 'Email/Phone Number', 'strip_tags|trim|required');
        $this->form_validation->set_rules('uwd_nominal', 'Nominal', 'strip_tags|trim|required');
        $this->form_validation->set_rules('user_email', 'Email', 'strip_tags|required|callback__is_user_exist');
        $this->form_validation->set_rules('uwal_balance', 'Balance', 'strip_tags');
        $this->form_validation->set_rules('user_name', 'Username', 'strip_tags');
        $this->form_validation->set_rules('user_phone', 'Phone Number', 'strip_tags');

       //run form validation
       $err_msg = [];
       if ($this->form_validation->run()) {
           $params = [
               'user_id'        => $this->input->post('user_id'),
               'uwd_date'       => date('Y-m-d H:i:s'),
               'ubank_id'       => 0,
               'uwd_nominal'    => $this->input->post('uwd_nominal'),
               'uwd_status'     => $cst_user['status']['accepted'],
           ];

           if (in_array('add', $permits)) {
               $params['created_by']    = $this->_get_user_id();
               $params['created_date']  = date('Y-m-d H:i:s');

               $detail_user     = $this->userdb->get($params['user_id']);
               $min_nominal     = $detail_user->uwal_balance - $params['uwd_nominal'];
               if($min_nominal >= 0) {
                    if ($this->userdb->update_user_withdraw($params)) {
                        $err_msg = [
                            'msg' => 'Withdraw Success.'.js_clearform(),
                            'type' => 'success'
                        ];

                            $token_push = $this->authdb->getall_atokenpush_notif_by_user_id($params['user_id']);
                            //reget data user for get total wallet
                            $user_data = $this->userdb->get($params['user_id']);
                            //check token_push if exist
                            if(!empty($token_push)) {
                                //looping push notification
                                foreach($token_push as $push_token) {
                                    $user_token = isset($push_token->atoken_pushnotif) ? $push_token->atoken_pushnotif : ''; 
            
                                    if(!empty($user_token)) {
                                        //push notif cancel data
                                        $notif_data = [
                                            "push_token" => $user_token,
                                            "text"       => 'You have withdraw Rp '. number_format($params['uwd_nominal'], 0, ',', '.') .' in\'s wallet, now your balance is Rp '.number_format($user_data->uwal_balance, 0, ',', '.'),
                                        ];
                                        
                                        $response_push = $this->pushnotification->send_pushnotification($notif_data);
                                        $info = 'push_notification - withdraw wallet '.$response_push;
                                        $logthis->info($info);
                                    }
                                }
                            }
                    } else {
                        $err_msg = [
                            'msg' => 'Withdraw Failed!',
                            'type' => 'danger'
                        ];
                    }
                } else {
                    $err_msg = [
                        'msg' => 'You don\'t have enough balance!',
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

        // select data & assign variable $data
        $data = [
            'current_url'   => $current_url,
            'msg'           => set_form_msg($err_msg),
            'permits'       => $permits,
            'cst_user'      => $this->config->item('user_topup'),
            'show_form'     => $form_permit['show_form'],
            'title_form'    => $form_permit['title_form'],
            'user'          => $detail_user
        ];
        $this->_render('user/user_withdraw_add', $data);
    }
    // END USER

    public function _is_user_exist($id){
        $id = $this->input->post('user_id');
        // if duplicate name found and not belong to selected ver_code, return false
        $detail_user = $this->userdb->get($id);
        if (empty($detail_user)) {
            $this->form_validation->set_message('user_id', 'User is not valid.');
            return false;
        } else if ($detail_user->user_status != 'active') {
            $this->form_validation->set_message('user_id', 'User is not active.');
            return false;
        }
        return true;
    }
}

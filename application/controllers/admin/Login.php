<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Login extends MY_Admin {

    function __construct()
	{
		parent::__construct();
        
        
        
        $this->tplmain = $this->tplpath.'template/blank_template';
	}
    
	public function index()
	{
        if($this->admindb->get_invalidlogin_bloked( $this->input->ip_address() )){
            //kalau di blocked, langsung show empty screen
            echo 'Your IP address has been blocked';
            exit();
        }
        $this->load->library('form_validation');
        $this->form_validation->set_error_delimiters(PREFIX_ERROR_DELIMITER, SUFFIX_ERROR_DELIMITER);
        $this->form_validation->set_rules('username', 'Username', 'required');
        $this->form_validation->set_rules('password', 'Password', 'required|callback__login_check');
        
        
        
        if ($this->form_validation->run() == TRUE){
            
            $this->admindb->generate_menu( $this->_get_user_id() );
            $this->_redirect_from_login_page();
        }
        
        // SELECT DATA & ASSIGN VARIABLE $DATA
        $data = array();
        
		$this->_render('home/login', $data);
	}
    
    public function _login_check($password)
    {
        $username = $this->input->post('username');
        $passwd   = $this->_salt_passwd($password);
        // print_r($passwd);
        // die();
        $detail   = $this->admindb->get_admin_login($username, $passwd);
        if(empty($detail)){
            unset($insparams);
            $insparams['log_username'] = $username;
            $insparams['log_password'] = base64_encode($password);
            $insparams['log_ipaddress']= $this->input->ip_address();
            $insparams['log_time']     = date('Y-m-d H:i:s');

            $this->admindb->insert_invalidlogin($insparams);
            
            $this->form_validation->set_message('_login_check', 'Invalid Username / Password');
            return FALSE;
        }else if($detail->admin_allowlogin == '1'){
            
        
            //set semua session yang dibutuhkan.
            $this->_set_session('adm_id', $detail->admin_id);
            $this->_set_session('adm_name', $detail->admin_fullname);
            $this->_set_session('adm_email', $detail->admin_email);
            $this->_set_session('adm_expire', time() + $this->session_expire);
            
            $this->admindb->update_last_login($detail->admin_id);
            
            return TRUE;
            
        }else{
            $this->form_validation->set_message('_login_check', 'The user is not allowed to log on at this time');
            return FALSE;
        }
    }
    
    
    public function clean()
    {
        $this->_clean_session();
        $this->_redirect_from_login_page();
    }
}

<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Profile extends MY_Admin {

    function __construct()
	{
		parent::__construct();
        
	}
    
    public function index()
    {
        $this->_set_title('Profile');
        
        $this->load->library('form_validation');
        $this->form_validation->set_error_delimiters(PREFIX_ERROR_DELIMITER, SUFFIX_ERROR_DELIMITER);
        $this->form_validation->set_rules('admin_username', 'Username', 'strip_tags|trim|required|callback__username_check');
        $this->form_validation->set_rules('admin_fullname', 'Fullname', 'strip_tags|trim|required');
        $this->form_validation->set_rules('admin_email', 'Email', 'strip_tags|trim|required|valid_email|callback__email_check');
        
        $admin_id = $this->_get_user_id();
        
        $all_roles      = $this->admindb->getall_role();
        $arr_admin      = $this->admindb->getarr_admin();
        
        $err_msg = array();
        if ($this->form_validation->run() == TRUE){
            
            unset($updata);
            $updata['admin_username']   = $this->input->post('admin_username');
            $updata['admin_fullname']   = $this->input->post('admin_fullname');
            $updata['admin_email']      = $this->input->post('admin_email');
            $ret = $this->admindb->update_admin($admin_id, $updata);
            if($ret){
                $err_msg['msg']  = 'Update Success';
                $err_msg['type'] = 'success';//success, info, warning, danger
            }else{
                $err_msg['msg']  = 'Update Failed!';
                $err_msg['type'] = 'danger';//success, info, warning, danger
            }
        }
        
        // SELECT DATA & ASSIGN VARIABLE $DATA
        $data['all_roles']      = $all_roles;
        $data['arr_admin']      = $arr_admin;
        $data['detail_admin']   = $this->admindb->get_admin($admin_id);
        $data['msg']            = set_form_msg($err_msg);
        
        $this->_render('profile/profile', $data);
    }
    
    
    public function password()
    {
        $this->_set_title('Change Password');

        $this->load->library('form_validation');
        $this->form_validation->set_error_delimiters(PREFIX_ERROR_DELIMITER, SUFFIX_ERROR_DELIMITER);
        $this->form_validation->set_rules('current_password', 'Current Password', 'trim|required|callback__password_check');
        $this->form_validation->set_rules('new_password', 'New Password', 'trim|required|min_length[4]|max_length[32]');
        $this->form_validation->set_rules('confirm_password', 'Confirm New Password', 'trim|required|min_length[4]|max_length[32]|matches[new_password]');
        
        $admin_id       = $this->_get_user_id();
        
        $err_msg = array();
        if ($this->form_validation->run() == TRUE){
            
            unset($updata);
            $updata['admin_password']   = $this->_salt_passwd($this->input->post('new_password'));
            $ret = $this->admindb->update_admin($admin_id, $updata);
            if($ret){
                $err_msg['msg']  = 'Change Password Success';
                $err_msg['type'] = 'success';//success, info, warning, danger
            }else{
                $err_msg['msg']  = 'Change Password Failed!';
                $err_msg['type'] = 'danger';//success, info, warning, danger
            }
        }
        
        // SELECT DATA & ASSIGN VARIABLE $DATA
        $data['msg'] = set_form_msg($err_msg);
        
        $this->_render('profile/password', $data);
    }
    
    
    public function _username_check($username)
    {
        $admin_id = $this->_get_user_id();
        
        $detail   = $this->admindb->get_duplicate_username($username, $admin_id);
        if(empty($detail)){
            return TRUE;
        }else{
            $this->form_validation->set_message('_username_check', 'Sorry, that username is already taken');
            return FALSE;
        }
    }
    
    public function _email_check($email)
    {
        $admin_id = $this->_get_user_id();
        
        $detail   = $this->admindb->get_duplicate_email($email, $admin_id);
        if(empty($detail)){
            return TRUE;
        }else{
            $this->form_validation->set_message('_email_check', 'Sorry, that email is already exist');
            return FALSE;
        }
    }
    
    public function _password_check($old_password)
    {
        $admin_id = $this->_get_user_id();
        $detail_admin = $this->admindb->get_admin($admin_id);
        
        if($this->_salt_passwd($old_password) != $detail_admin->admin_password){
            $this->form_validation->set_message('_password_check', '%s does not match our records');
            return FALSE;
        }else{
            return TRUE;
        }
    
    }
}

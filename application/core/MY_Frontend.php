<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Class MY_Frontend
|--------------------------------------------------------------------------
|
| This class for frontend purpose
|
*/
class MY_Frontend extends MY_Controller {
	function __construct()
	{
		parent::__construct();
        $this->output->enable_profiler(ENABLE_PROFILER);
       
        //RUNNING GLOBAL FUNCTION
        $this->session_expire = 1800;//setengah jam.
        
        $this->_set_return_url();
        
        $this->_check_login_required();
        
        $this->tplpath = 'client/';
        $this->tplmain = $this->tplpath.'main/main_template';
        
	}
    
    /// BEGIN USER DATA SECTION
    public function _get_user_id()
    {
        return intval($this->_get_session("ses_member_id"),0);
    }

    public function _get_user_fullname()
    {
        $firstname  = $this->_get_session("ses_member_firstname");
        $lastname   = $this->_get_session("ses_member_lastname");
        $email      = $this->_get_session("ses_member_email");
        return ( ( trim($firstname) =='') ? $email : trim($firstname.' '.$lastname)) ;
    }

    public function _get_user_email ( )
    {
        return $this->_get_session("ses_member_email") ;
    }
    /// END USER DATA SECTION
    
    // BEGIN SESSION RELATED
    protected function _clean_session()
    {
        //unset session yang di bikin di welcome/login
        
        $this->_unset_session('ses_member_id');
        $this->_unset_session('ses_member_firstname');
        $this->_unset_session('ses_member_lastname');
        $this->_unset_session('ses_member_email');
        $this->_unset_session('ses_expire');
    }
    protected function _check_session_expired()
    {
        //function ini dibuat supaya gak edit php.ini, dan bisa lebih dinamis.
        if( $this->_get_session('ses_expire') > time() ){
            //bila session expire dibawah waktu sekarang, update sess_expire
            $this->_set_session('ses_expire', time() + $this->session_expire );
        }else{
            $this->_clean_session();
            $this->_redirect_to_last_visited();
        }
    }
    // END SESSION RELATED
    
    
    /// BEGIN LOGIN RELATED
    protected function _to_login_page( )
    {
        redirect ( BASE_URL. 'signin' ) ;
    }
    
    protected function _redirect_to_last_visited()
    {
        if ( !$this->_is_session_set("ses_return_url") )
            redirect ( BASE_URL ) ;
        else
            redirect ( $this->_get_session("ses_return_url") ) ;
    }
    
    public function _is_logged_in()
    {
        return $this->_is_session_set("ses_member_id");
    }
    
    protected function _set_return_url()
    {
        // SET RETURN URL KALAU HALAMAN INI BUKAN LOGIN PAGE
        $no_return_method   = array('');
        $no_return_class    = array('ajax', 'welcome', 'auth', 'uploads', 'assets'); // sama seperti http://domain.com/class/*
        $cur_method         = $this->router->class.'/'.$this->router->method;
        
        if(! in_array($cur_method, $no_return_method) &&
           ! in_array($this->router->class, $no_return_class) )
        {
            if($this->uri->uri_string == '/')
                $this->_set_session('ses_return_url', BASE_URL);
            else
                $this->_set_session('ses_return_url', BASE_URL.$this->uri->uri_string);
        }
        
    }
    
    protected function _check_login_required()
    {
        $login_method   = array('class_example/need_login');
        $login_class    = array('myaccount'); // sama seperti http://domain.com/class/*
        $cur_method     = $this->router->class.'/'.$this->router->method;
        $dont_redirect  = array('ajax');//class jika tidak ingin di redirect pas login expired
        
        if(! $this->_is_logged_in() ){
            if( in_array($cur_method, $login_method) ||
                in_array($this->router->class, $login_class) )
            {
                //note : kalau pas session expired sewaktu jalanin ajax. munculin pesan error tapi jangan redirect.
                if(! in_array($this->router->class, $dont_redirect) )
                {
                    $this->_to_login_page();
                }else{
                    echo 'Please Re-Login!';
                    exit();
                }
            }
        }else{
            $this->_check_session_expired();

            //kalau sudah pernah login, dan user buka halaman ini, langsung diredirect ke halaman terakhir yang di kunjungi
            if( in_array($cur_method, array('auth/index', 'auth/signup' )) ){
                $this->_redirect_to_last_visited();
            }
        }
    }
    
    protected function _do_login($detail_member)
    {
        return FALSE;
        
        //update db
        /*
        unset($updata);
        $updata['usr_last_login'] = date('Y-m-d H:i:s');
        $ret = $this->userdb->update_user($detail_member->usr_id, $updata);
        
        if($ret){
            $this->_set_session('ses_member_id', $detail_member->usr_id);
            $this->_set_session('ses_member_firstname', $detail_member->usr_firstname);
            $this->_set_session('ses_member_lastname', $detail_member->usr_lastname);
            $this->_set_session('ses_member_email', $detail_member->usr_email);
            $this->_set_session('ses_expire', time() + $this->session_expire );
            return TRUE;
        }else{
            return FALSE;
        }
        */
    }
    
    /// END LOGIN RELATED
}
?>
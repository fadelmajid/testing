<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Class MY_Admin
|--------------------------------------------------------------------------
|
| This class for backend purpose
|
*/
class MY_Admin extends MY_Controller {
	function __construct()
	{
		parent::__construct();
        $this->output->enable_profiler(ENABLE_PROFILER);

        $this->load->library('session');

        $this->tplpath = 'admin/';
        $this->tplmain = $this->tplpath.'template/main_template';
        $this->session_expire = 86400;//session expire 1 hari

        $this->load->model('admindb');

        //RUNNING GLOBAL FUNCTION
        $this->_set_session('submenu_id', 0);
        $this->_set_return_url();
        $this->_check_login_required();

	}

    /// BEGIN USER DATA SECTION
    public function _get_user_id()
    {
        return intval($this->_get_session("adm_id"),0);
    }

    public function _get_user_fullname()
    {
        return $this->_get_session("adm_name") ;
    }

    public function _get_user_email ( )
    {
        return $this->_get_session("adm_email") ;
    }
    /// END USER DATA SECTION

    // BEGIN SESSION RELATED
    protected function _clean_session()
    {
        //unset session yang di bikin di member/login
        $this->_unset_session('adm_id');
        $this->_unset_session('adm_name');
        $this->_unset_session('adm_email');
        $this->_unset_session('adm_expire');
    }
    protected function _check_session_expired()
    {
        //function ini dibuat supaya gak edit php.ini, dan bisa lebih dinamis.
        if( $this->_get_session('adm_expire') > time() ){
            //bila session expire dibawah waktu sekarang, update sess_expire
            $this->_set_session( 'adm_expire', time() + $this->session_expire );
        }else{
            $this->_clean_session();
            $this->_redirect_from_login_page();
        }
    }
    // END SESSION RELATED


    /// BEGIN LOGIN RELATED
    protected function _to_login_page( )
    {
        redirect ( ADMIN_URL. 'login' ) ;
    }

    protected function _redirect_from_login_page( )
    {
        if ( !$this->_is_session_set("adm_return_url") )
            redirect ( ADMIN_URL ) ;
        else
            redirect ( $this->_get_session("adm_return_url") ) ;
    }

    protected function _is_logged_in( )
    {
        return $this->_is_session_set("adm_id");
    }

    protected function _set_return_url()
    {
        // SET RETURN URL KALAU HALAMAN INI BUKAN LOGIN PAGE
        $no_return_method   = array('home/index');
        $no_return_class    = array('ajax', 'login', 'cronjob'); // sama seperti http://domain.com/class/*
        $cur_method         = $this->router->class.'/'.$this->router->method;

        if(! in_array($cur_method, $no_return_method) &&
           ! in_array($this->router->class, $no_return_class) )
        {
            if($this->uri->uri_string == '/')
                $this->_set_session('adm_return_url', base_url());
            else
                $this->_set_session('adm_return_url', base_url().$this->uri->uri_string);
        }

    }

    protected function _check_login_required()
    {
        $nologin_method            = array();
        $nologin_class             = array('login', 'cronjob', 'webhook'); // sama seperti http://domain.com/class/*
        $cur_method = $this->router->class.'/'.$this->router->method;
        $dont_redirect_method      = array('country/get_province', 'country/get_city');
        $dont_redirect_class       = array('ajax');

        //kalau halaman ini selain halaman $nologin_ , cek apakah sudha login atau belum
        if(! in_array($cur_method, $nologin_method) &&
           ! in_array($this->router->class, $nologin_class) )
        {
            if(! $this->_is_logged_in() ){

                //note : kalau pas session expired sewaktu jalanin ajax. munculin pesan error tapi jangan redirect.
                if(! in_array($cur_method, $dont_redirect_method) &&
                   ! in_array($this->router->class, $dont_redirect_class) )
                {
                    $this->_to_login_page();
                }else{
                    echo 'Please Re-Login!';
                    exit();
                }

            }
            $this->_check_session_expired();
        }
    }

    protected function _check_menu_access($submenu_code, $permits, $redirect = TRUE, $return_permits = TRUE)
    {
		$submenu_id = 0;
		$detail_submenu = $this->admindb->get_submenu_code($submenu_code);
		if($detail_submenu){
			$submenu_id = $detail_submenu->submenu_id;
		}

        $this->_set_session('submenu_id', $submenu_id);

        $access = FALSE;
        $arr_permits = $this->admindb->get_admin_permits($this->_get_user_id(), $submenu_id);
        if(in_array($permits, $arr_permits)){
            $access = TRUE;
        }

        //Redirect halaman kalau tidak dikasih akses
        if($redirect){
            if($access === FALSE){
                $page = ADMIN_URL . 'home/noaccess?sub=' . $submenu_id;
                redirect($page);
            }
        }

        if($return_permits === TRUE){
            return $arr_permits;
        }else{
            return $access;
        }

    }


    function _get_form_permit($id, $permits)
    {
        $ret['show_form']  = FALSE;
        $ret['title_form'] = '';
        if($id > 0){
            if(in_array('edit', $permits)){
                $ret['show_form']  = TRUE;
                $ret['title_form'] = 'Edit';
            }
        }elseif(in_array('add', $permits)){
            $ret['show_form']  = TRUE;
            $ret['title_form'] = 'Add';
        }
        return $ret;
    }

	protected function _salt_passwd($password)
	{
		return sha1(ADMIN_SALT.$password.ADMIN_SALT);
	}

    /// END LOGIN RELATED

    // BEGIN BERHUBUNGAN DENGAN EXPORT TO EXCEL
    function set_header_xls($filename='download.xls')
    {
        $this->tplmain = $this->tplpath.'template/blank_template';
        $this->output->set_content_type('application/vnd.ms-excel');
        $this->output->set_header('Content-Disposition: attachment;filename='.$filename);
    }
    // END BERHUBUNGAN DENGAN EXPORT TO EXCEL

}

?>

<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class MY_Controller extends CI_Controller {
	function __construct()
	{
		parent::__construct();
        date_default_timezone_set('Asia/Jakarta');
        
        $this->tplpath      = '';
        $this->tplmain      = '';
        $this->meta_title   = '';
        $this->meta_desc    = '';
	}
    
    public function log4php($logname, $logfile, $renew=FALSE)
    {
        //BEGIN LOGGER
        if( ! isset($this->log4php)){
            $log4php_config   = $this->config->item('log4php_config');
            $this->load->library('log4php/Logger', array('name'=>'default'), 'log4php' );
            $this->log4php->configure($log4php_config);
        }
    
        
        if($renew == FALSE && $this->log4php->exists($logname)){
            $logger = $this->log4php->getLogger( $logname );
        
        }else{
        
            $logger = Logger::getLogger($logname);
            $logger->setLevel(LoggerLevel::toLevel(LoggerLevel::DEBUG));
            
            $filelog    = LOG_FOLDER.$logfile;
            $appender = new LoggerAppenderRollingFile($logname);
            $appender->setFile($filelog);
            $appender->setMaxBackupIndex(10);
            $appender->setCompress(true);
            $appender->setAppend(true);
            $appender->setMaxFileSize("50MB");
            $appenderlayout = new LoggerLayoutPattern();
            $pattern = '%date %logger %-5level %msg%n';
            $appenderlayout->setConversionPattern($pattern);
            $appenderlayout->activateOptions();
            $appender->setLayout($appenderlayout);
            $appender->activateOptions();
          
            $logger->removeAllAppenders();
            $logger->addAppender($appender);
        }
        
        //END LOGGER
        return $logger;
    }
    
    //BEGIN TEMPLATE RELATED
    public function _render($view, $data=array())
    {
        $vars['meta_title'] = $this->meta_title;
        $vars['meta_desc']  = $this->meta_desc;
        $vars['content']    = $this->load->view($this->tplpath.$view, $data, TRUE);
        $this->load->view($this->tplmain, $vars);
    }
    
    protected function _set_title($meta_title)
    {
        $this->meta_title = $meta_title;
    }
    
    protected function _set_desc($meta_desc)
    {
        $this->meta_desc = $meta_desc;
    }
    //END TEMPLATE RELATED
    
    
    /// BEGIN SESSION RELATED
    protected function _is_session_set( $name )
    {
        return $this->session->has_userdata( $name );
    }
    protected function _get_session( $name )
    {
        $has_session = $this->session->has_userdata( $name );
        return ( $has_session ? $this->session->userdata( $name ) : FALSE );
    }
    protected function _set_session( $name, $value )
    {
        $this->session->set_userdata($name, $value);
    }
    protected function _unset_session( $name )
    {
        $this->session->unset_userdata( $name );
    }
    /// END SESSION RELATED

}

if ( ! class_exists('MY_Admin'))
{
    //controller for backend
    require_once APPPATH.'core/MY_Admin.php';
}
if ( ! class_exists('MY_Frontend'))
{
    //controller for frontend
    require_once APPPATH.'core/MY_Frontend.php';
}
if ( ! class_exists('MY_Service'))
{
    //controller for frontend
    require_once APPPATH.'core/MY_Service.php';
}
?>

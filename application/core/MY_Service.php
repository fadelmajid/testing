<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Class MY_Service
|--------------------------------------------------------------------------
|
| This class for service / cron purpose
|
*/
class MY_Service extends MY_Controller {
	function __construct()
	{
		parent::__construct();
        
        $this->sleep            = SLEEP_MICROSECOND;
        $this->sleep_no         = COUNT_SLEEP_START;
        $this->sleep_from       = '';//ketika mulai sleep yang pertama langsung record
        
        $this->signal_info = '';
	}
    
    // BEGIN CRON / SERVICE RELATED
    function open_instance($pidfile)
    {
        /*
         * function ini untuk mencegah supaya 1 script tidak running bersamaan pada saat yang sama
        */
        $ret['status']  = TRUE;
        $ret['error_msg'] = '';
        
        $lock_file = fopen( $pidfile , 'c');
        $got_lock = flock($lock_file, LOCK_EX | LOCK_NB, $wouldblock);
        if ($lock_file === false || (!$got_lock && !$wouldblock))
        {
            $ret['status']  = FALSE;
            $ret['error_msg'] = "Unexpected error opening or locking lock file. Perhaps you don't  have permission to write to the lock file or its containing directory? \n";
        }
        else if (!$got_lock && $wouldblock)
        {
            $ret['status']  = FALSE;
            $ret['error_msg'] = "Another instance is already running; terminating.\n";            
        }
        else
        {
            // Lock acquired; let's write our PID to the lock file for the convenience
            // of humans who may wish to terminate the script.
            ftruncate($lock_file, 0);
            fwrite($lock_file, getmypid() . "\n");
        }
        
        $ret['lock_file'] = $lock_file;
        
        return $ret;
    }
    
    function close_instance($lock_file)
    {
        // All done; we blank the PID file and explicitly release the lock 
        // (although this should be unnecessary) before terminating.
        ftruncate($lock_file, 0);
        flock($lock_file, LOCK_UN);
    }
    
    
    function cron_start($params)
    {
        //$params['class']
        //$params['method']
        
        //=== START PID
        $pidfile = PIDFOLDER.$params['class'].'_'. $params['method'] .'.pid';
        $chk_instance = $this->open_instance($pidfile);
        if($chk_instance['status'] == FALSE)
        {
            exit($chk_instance['error_msg']);
        }
        //=== START PID
        
        $ret['pidfile']             = $pidfile;
        $ret['lock_file']           = $chk_instance['lock_file'];
        $ret['logelapsed_method']   = $params['class'].'/'.$params['method'];
        $ret['logelapsed_start']    = date('Y-m-d H:i:s');
        
        $this->benchmark->mark('code_start');
        
        return $ret;
    }
    
    function cron_end($params)
    {
        $this->benchmark->mark('code_end');
        
        $elapsed_time = $this->benchmark->elapsed_time('code_start', 'code_end');
        
        //$run_from = time() - $elapsed_time;
        
        //echo 'Total elapsed time '.time_diff( date('Y-m-d H:i:s', $run_from) ).PHP_EOL;
        
        //=== CLOSE PID
        $this->close_instance($params['lock_file']);
        unlink($params['pidfile']);
        //=== CLOSE PID
        
        return $elapsed_time;
    }
    
    function sleep_log($logger)
    {
        if($this->sleep_from == ''){
            $this->sleep_from = date('Y-m-d H:i:s');
        }
        
        if($this->sleep_no == COUNT_SLEEP_START){
            $str_log = SEPARATOR_LOG.'Sleep '.($this->sleep / SLEEP_DIVIDE).' sec';
            $logger->info($str_log);
            
        }else if($this->sleep_no == COUNT_SLEEP_WARN){
            $str_log = SEPARATOR_LOG.'Sleep from '.time_ago( $this->sleep_from ).' and still counting ... ';
            $logger->info($str_log);
            
            $this->sleep_no = COUNT_SLEEP_START;
        }
        usleep($this->sleep);
        $this->sleep_no = $this->sleep_no + 1;
    }
    
    function sleep_reset($logger)
    {
        if($this->sleep_from != ''){
            $str_log = SEPARATOR_LOG.'Total sleep '.time_diff( $this->sleep_from );
            $logger->info($str_log);
        }
        
        $this->sleep_no   = COUNT_SLEEP_START;
        $this->sleep_from = '';
    }
    
    
    function sig_handler($sig)
    {
        //echo 'Please wait until the program closed gracefully'.PHP_EOL;
        switch($sig) {
            case SIGTERM:
                $this->signal_info   = 'SIGTERM';
                $this->infinite_loop = FALSE;
            break;
            case SIGHUP:
                $this->signal_info   = 'SIGHUP';
                $this->infinite_loop = FALSE;
            break;
            case SIGINT:
                $this->signal_info   = 'SIGINT';
                $this->infinite_loop = FALSE;
            break;
            default:
                $this->signal_info   = 'UNDEFINED SIG '.$sig;
                $this->infinite_loop = FALSE;
        }
    }
    
    // END CRON / SERVICE RELATED
    
}

<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');


require_once APPPATH.'libraries/PHPMailer/PHPMailerAutoload.php';

class Myphpmailer extends PHPMailer{
    
 
	function __construct() {
        parent::__construct();
    
	}
    
    public static function validateAddress($address, $patternselect = 'auto')
    {
        if($address != "") {
            $pattern = "/^([A-Za-z0-9\.|\-|_]{1,60})([@])";
            $pattern .="([A-Za-z0-9\.|\-|_]{1,60})(\.)([A-Za-z]{2,3})$/";
            if(!preg_match($pattern, $address)) {
                return false;
            }else {
                return true;
            }
        }else{
            return false;
        }
    }
    
    
}
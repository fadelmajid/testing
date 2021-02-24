<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Pushnotification{
    
 
	function __construct() {    
    }

    public function send_pushnotification($data){
        //initialize
        $curl = curl_init();

        // OPTIONS:
        $options = $this->get_options($data);
        $curl_opt = array(
            CURLOPT_URL => $options->url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($options->body),
            CURLOPT_HTTPHEADER => $options->header
        );
        curl_setopt_array($curl, $curl_opt);

        // EXECUTE:
        $response = curl_exec($curl);
        $err = curl_error($curl);    

        curl_close($curl);

        if ($err) {
            return $err;
        } else {
            return $response;
        }
    }

    public function get_options($data){
        // set variabel object to call API FIREBASE
        $options = new stdClass();
        $options->header = array();
        $options->body = new stdClass();
        $options->body->notification = array();
        //set variable
        $api_key    = FIREBASE_KEY;

        // set url API
        $options->url = FIREBASE_URL;

        // header for FIREBASE API
        $options->header = array('Authorization : Key='.FIREBASE_KEY, 'Content-Type : application/json');
        // set body for FIREBASE API
        $options->body->to = $data['push_token'];
        $options->body->notification['body'] = $data['text'];
        $options->body->notification['title'] = CONTACT_COMPANY;
        
        return $options;
    }
    
}
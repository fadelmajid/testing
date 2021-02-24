<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Credit_cardapi{


	function __construct() {
    }

    public function void_transaction($data){
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
            CURLOPT_HEADER => 1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => "",
            CURLOPT_HTTPHEADER => $options->header
        );
        curl_setopt_array($curl, $curl_opt);

        // EXECUTE:
        $result = curl_exec($curl);
        $err = curl_error($curl);

        //get header response
        $response = explode("\r\n\r\n",$result);
        $http_code = curl_getinfo( $curl, CURLINFO_HTTP_CODE );
        curl_close($curl);

        if ($err) {
            $data_res['logs']   = "Error" ;
            $data_res['data']   = $err;
            $data_res['http_code'] = $http_code;
            $data_res['options'] = $options;
            return $data_res;
        } else {
            $data_res['logs']   = isset($response[0]) ? $response[0] : '' ;
            $data_res['data']   = isset($response[1]) ? $response[1] : '{}';
            $data_res['http_code'] = $http_code;
            $data_res['options'] = $options;
            return $data_res;
        }
    }

    public function get_options($data){
        // set variabel object to call API Midtrans Gopay
        $options = new stdClass();
        $options->header = array();
        $options->body = new stdClass();

        $auth = base64_encode(MIDTRANS_SERVER_KEY.':');

        // set url API
        $options->url = MIDTRANS_URL.$data->uor_code.'/cancel';
        $options->pylog_type = "Cancel Credit Card";

        // header for Midtrans Credit Card API;
        $options->header[] = 'Content-Type:application/json';
        $options->header[] = 'Accept:application/json';
        $options->header[] = 'Authorization:Basic '.$auth;

        return $options;
    }
}
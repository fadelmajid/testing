<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Gopayapi{


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

    public function get_transaction_status($data){
        $curl = curl_init();

        // OPTIONS:
        $options = $this->get_options_status($data);

        $curl_opt = array(
            CURLOPT_URL => $options->url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_HEADER => 1,
            CURLOPT_CUSTOMREQUEST => "GET",
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
            return $data_res;
        } else {
            $data_res['logs']   = isset($response[0]) ? $response[0] : '' ;
            $data_res['data']   = isset($response[1]) ? $response[1] : '{}';
            $data_res['http_code'] = $http_code;
            return $data_res;
        }
    }

    public function get_options_status($data){
        // set variabel object to call API Midtrans Gopay
        $options = new stdClass();
        $options->header = array();
        $options->body = new stdClass();

        $auth = base64_encode(MIDTRANS_SERVER_KEY.':');

        // set url API
        $options->url = MIDTRANS_URL.$data->uor_code.'/status';
        $options->pylog_type = "Status Gopay";

        // header for Midtrans Credit Card API;
        $options->header[] = 'Content-Type:application/json';
        $options->header[] = 'Accept:application/json';
        $options->header[] = 'Authorization:Basic '.$auth;

        return $options;
    }

    public function get_options($data){
        // get status payment
        $gopay = $this->get_transaction_status($data);
        $res_gopay = json_decode($gopay['data']);

        // validate status
        $response = "";
        if(isset($res_gopay->transaction_status) && ($res_gopay->transaction_status == "settlement" || $res_gopay->transaction_status == "capture")){
            $response = $this->refund_transaction($data);
        } else {
            $response = $this->cancel_transaction($data);
        }

        return $response;
    }

    public function cancel_transaction($data){
        // set variabel object to call API Midtrans Gopay
        $options = new stdClass();
        $options->header = array();
        $options->body = new stdClass();

        $auth = base64_encode(MIDTRANS_SERVER_KEY.':');

        // set url API
        $options->url = MIDTRANS_URL.$data->uor_code.'/cancel';
        $options->pylog_type = "Cancel Gopay";

        // header for Midtrans Credit Card API;
        $options->header[] = 'Content-Type:application/json';
        $options->header[] = 'Accept:application/json';
        $options->header[] = 'Authorization:Basic '.$auth;

        return $options;
    }

    public function refund_transaction($data){
        // set variabel object to call API Midtrans Gopay
        $options = new stdClass();
        $options->header = array();
        $options->body = new stdClass();

        $auth = base64_encode(MIDTRANS_SERVER_KEY.':');

        // set url API
        $options->url = MIDTRANS_URL.$data->uor_code.'/refund/online/direct';
        $options->pylog_type = "Refund Gopay";

        // header for Midtrans Gopay API;
        $options->header[] = 'Content-Type:application/json';
        $options->header[] = 'Accept:application/json';
        $options->header[] = 'Authorization:Basic '.$auth;

        // body request to call Gopay API
        $options->body->amount          = $data->uor_total;
        $options->body->reason          = $data->uor_remarks;

        return $options;
    }
}
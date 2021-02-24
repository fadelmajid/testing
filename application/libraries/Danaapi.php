<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Danaapi{

	function __construct() {
    }

    public function void_transaction($data){
        $curl = curl_init();;

        // validate uod_code
        $detail = json_decode($data->pyhis_data);
        if(empty($detail)){
            $data_res['request'] = "Can't request cancel";
            $data_res['logs']   = "null" ;
            $data_res['data']   = "";
            $data_res['http_code'] = "500";
            $data_res['options'] = "";
            return $data_res;
        }

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
            CURLOPT_POSTFIELDS => json_encode($options->body),
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
            $data_res['request'] = $curl_opt;
            $data_res['logs']   = "Error" ;
            $data_res['data']   = $err;
            $data_res['http_code'] = (string)$http_code;
            $data_res['options'] = $options;
            return $data_res;
        } else {
            $data_res['request'] = $curl_opt;
            $data_res['logs']   = isset($response) ? $response : '' ;
            $data_res['data']   = isset($response) ? (count($response) > 0 ? $response[count($response)-1] : '{}') : '{}';
            $data_res['http_code'] = (string)$http_code;
            $data_res['options'] = $options;
            return $data_res;
        }
    }

    public function sign($data) {
        $signature = '';
        $privateKey = DANA_PRIVATE_KEY;
        
        $data_str = json_encode($data);
        openssl_sign($data_str, $signature, $privateKey, OPENSSL_ALGO_SHA256);
     
        return base64_encode($signature);
    }
  
    public function get_options($data){
        // set time
        $now = date('Y-m-d H:i:s');
        $new_time = date('Y-m-d H:i:s', strtotime('+23 hours', strtotime($data->uor_date)));

        // validate the time
        if($now <= $new_time){
            // cancel order, not charge to ALPHA
            $response = $this->cancel_order($data);
        }else{
            // refund order, charge to ALPHA
            $response = $this->refund_order($data);
        }

        return $response;
    }

    public function refund_order($data){
        // set data
        $detail = json_decode($data->pyhis_data);
        $uor_code = $data->uor_code;
        $sub_total = (string)$data->uor_total;
        $uor_total = $sub_total."00";
        $uuid = uniqid();

        // set variabel object to call API DANA
        $options = new stdClass();
        $options->header = array();
        $options->body = new stdClass();
        $options->body->request = new stdClass();
        $options->body->request->head = new stdClass();
        $options->body->request->body = new stdClass();
        $options->body->request->body->refundAmount = new stdClass();

        // set url API
        $options->url = DANA_REFUND_URL;
        $options->pylog_type = "Refund DANA";

        // header for DANA API
        $options->header[] = 'Content-Type: application/json';

        // body to call DANA API - head
        $options->body->request->head->version = DANA_VERSION;
        $options->body->request->head->function = DANA_FN_REFUND;
        $options->body->request->head->clientId = DANA_CLIENT_ID;
        $options->body->request->head->reqTime = date(DATE_W3C);
        $options->body->request->head->reqMsgId = (string)$uuid;
        $options->body->request->head->clientSecret = DANA_CLIENT_SECRET;
        $options->body->request->head->reserve = new stdClass();

        // body to call DANA API - body
        $options->body->request->body->requestId = $uor_code;
        $options->body->request->body->merchantId = DANA_MERCHANT_ID;
        $options->body->request->body->acquirementId = $detail->uod_code;
        $options->body->request->body->refundAmount->currency = "IDR";
        $options->body->request->body->refundAmount->value = $uor_total;
        
        // set sign
        $sign = $this->sign($options->body->request);

        // request to call DANA API - sign
        $options->body->signature = $sign;

        return $options;
    }

    public function cancel_order($data){
        // set data
        $detail = json_decode($data->pyhis_data);
        $uor_code = $data->uor_code;
        $sub_total = (string)$data->uor_total;
        $uor_total = $sub_total."00";
        $uuid = uniqid();

        // set variabel object to call API DANA
        $options = new stdClass();
        $options->header = array();
        $options->body = new stdClass();
        $options->body->request = new stdClass();
        $options->body->request->head = new stdClass();
        $options->body->request->body = new stdClass();

        // set url API
        $options->url = DANA_CANCEL_URL;
        $options->pylog_type = "Cancel DANA";

        // header for DANA API
        $options->header[] = 'Content-Type: application/json';

        // body to call DANA API - head
        $options->body->request->head->version = DANA_VERSION;
        $options->body->request->head->function = DANA_FN_CANCEL;
        $options->body->request->head->clientId = DANA_CLIENT_ID;
        $options->body->request->head->reqTime = date(DATE_W3C);
        $options->body->request->head->reqMsgId = (string)$uuid;
        $options->body->request->head->clientSecret = DANA_CLIENT_SECRET;
        $options->body->request->head->reserve = new stdClass();

        // body to call DANA API - body
        $options->body->request->body->merchantId = DANA_MERCHANT_ID;
        $options->body->request->body->acquirementId = $detail->uod_code;
        $options->body->request->body->cancelReason = $data->uor_remarks;

        
        // set sign
        $sign = $this->sign($options->body->request);

        // request to call DANA API - sign
        $options->body->signature = $sign;

        return $options;
    }
}
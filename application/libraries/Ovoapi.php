<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Ovoapi{


	function __construct() {
    }

    public function void_transaction($data){
        // define the date -> order / subsorder
        $new_date = (isset($data->uor_date) ? $data->uor_date : $data->subsorder_date);

        // if today, possible to cancel
        if(date('Y-m-d', strtotime($new_date)) == date('Y-m-d')){
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
        }else{
            $data_res['logs']   = "Error" ;
            $data_res['data']   = "";
            $data_res['http_code'] = "500";
            $data_res['options'] = "{}";
            return $data_res;
        }
    }

    public function get_options($data){
        // set variabel object to call API OVO
        $options = new stdClass();
        $options->header = array();
        $options->body = new stdClass();

        //generate hmac sha256
        $random = time();
        $hmac = hash_hmac('sha256', OVO_HEADERS_APPID."$random", OVO_HEADERS_APPKEY);

        $options->body->transactionRequestData = new stdClass();
        $current_date = date("Y-m-d h:i:s.sss");
        $batch_number = "";
        $reference_number = "";
        $number = "";
        $pyhis_data = "";

        if(isset($data->pyhis_data)) {
            $pyhis_data = json_decode($data->pyhis_data, true);
            $batch_number = $pyhis_data['batch_no'];
            $reference_number = $pyhis_data['ref_no'];
            $number = $pyhis_data['number'];
        };

        // set url API
        $options->url = OVO_URL;
        $options->pylog_type = "VOID_P2P";

        // header for OVO API
        $options->header[] = 'random: '. $random;
        $options->header[] = 'hmac: '. $hmac;
        $options->header[] = 'app-id: '.OVO_HEADERS_APPID;
        $options->header[] = 'Content-Type: application/json';

        // body request to call OVO API
        $options->body->type            = "0200"; //
        $options->body->processingCode  = "020040";
        $options->body->amount          = $data->uor_total;
        $options->body->date            = $current_date;
        $options->body->referenceNumber = $reference_number;
        $options->body->tid             = OVO_HEADERS_TID;
        $options->body->mid             = OVO_HEADERS_MID;
        $options->body->merchantId      = OVO_HEADERS_MERCHANTID;
        $options->body->storeCode       = OVO_HEADERS_STORECODE;
        $options->body->appSource       = "POS";
        $options->body->transactionRequestData->batchNo = $batch_number;
        $options->body->transactionRequestData->merchantInvoice = $data->uor_code;
        $options->body->transactionRequestData->phone = $number;

        return $options;
    }

}
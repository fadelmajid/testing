<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Sicepatapi {

	function __construct() {
        $this->ci =& get_instance();
    }

    public function booking_ride($data) {
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
        $http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $err = curl_error($curl);

        $headers = [];
        $response = explode("\r\n\r\n",$result);
        curl_close($curl);
        
        if ($err) {
            $data_res['logs']   = "Error" ;
            $data_res['data']   = $err;
            $data_res['booking_id'] = "";
            $data_res['delivery_fee'] = 0;
            $data_res['distance'] = 0;

            return $data_res;
        } else {
            $data_res['logs']   = isset($response) ? $response : '' ;
            $data_res['data']   = isset($response) ? (count($response) > 0 ? $response[count($response)-1] : '{}') : '{}';

            //  data courier
            $data_courier = json_decode($data_res['data'], true);
            $data_res['delivery_fee'] = 0;
            $data_res['distance'] = 0;
            $data_res['booking_id'] = isset($data_courier['data']['order_id']) ? $data_courier['data']['order_id'] : '';
            return $data_res;
        };
    }

    public function get_options($data, $action = 'booking'){
        // set variabel object to call API SICEPAT
        $options = new stdClass();
        $options->header = array();
        $options->body = new stdClass();
        // set url API
        $options->url = SICEPAT_URL.'/api/orders'; 

        if($action == 'cancel'){
            $options->url = SICEPAT_URL.'/api/orders/'.$data->booking_id.'/cancel'; 
        }

        // header for API SICEPAT
        $options->header[] = 'X-API-Key: '.SICEPAT_KEY;
        $options->header[] = 'Content-Type: application/json';

        // body request to call API SICEPAT
        $options->body->outlet_id = $data->st_id;
        $options->body->order_number = $data->uor_code;
        $options->body->recipient_name = $data->user_name;
        $options->body->recipient_phone = $data->user_phone;
        $options->body->destination_address = $data->uadd_street;
        $options->body->destination_long = $data->uadd_long;
        $options->body->destination_lat = $data->uadd_lat;
        $options->body->reason = $data->uor_remarks;
        $options->body->cancel_time = $data->created_date;

        // cek dulu jumlah pd_id dan parsing qty
        $detail = $data->item_detail;
        $options->body->items = array();
        for($i = 0; $i < count($detail); $i++){
            $options->body->items[$i] = new stdClass();
            $options->body->items[$i]->item_name = $detail[$i]->uorpd_name;
            $options->body->items[$i]->qty = $detail[$i]->pd_qty;
            $options->body->items[$i]->unit_price = $detail[$i]->pd_price;
        }

        return $options;
    }

    public function generate_barcode($uor_code){
        $this->ci->load->library('zend'); //pemanggilan library BARCODE
        $this->ci->load->library("google_cloud_bucket");

        //load in folder Zend
		$this->ci->zend->load('Zend/Barcode'); 
        $logthis    = $this->ci->log4php('faq_delete', APPLOG);
		//generate barcode
        $file = Zend_Barcode::draw('code128', 'image', array('text' => $uor_code), array());

        // $uor_code = time().$uor_code;
        if(!is_dir(UPLOAD_PATH."sicepat/")){
            mkdir(sys_get_temp_dir()."/sicepat/", 0777);
        }

        if(!@fopen(UPLOAD_PATH."sicepat/{$uor_code}.png", 'r')){
            imagepng($file, UPLOAD_PATH."sicepat/{$uor_code}.png");
        }
        // imagepng($file,sys_get_temp_dir()."/sicepat/{$uor_code}.png");
    
        return $uor_code.'.png';
    }

    public function cancel_booking($data) {
        $curl = curl_init();

        // OPTIONS:
        $options = $this->get_options($data, 'cancel');
        
        $curl_opt = array(
            CURLOPT_URL => $options->url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_HEADER => 1,
            CURLOPT_CUSTOMREQUEST => "PUT",
            CURLOPT_POSTFIELDS => json_encode($options->body),
            CURLOPT_HTTPHEADER => $options->header
        );
        curl_setopt_array($curl, $curl_opt);

        // EXECUTE:
        $result = curl_exec($curl);
        $http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $err = curl_error($curl);

        $headers = [];
        $response = explode("\r\n\r\n", $result);
        curl_close($curl);

        if($http_status == 500) {
            $response[0] = '';
            $response[1] = json_encode(array("data" => array("order_id" => "SICEPAT-NA")));
        }

        if ($err) {
            $data_res['logs']   = "Error" ;
            $data_res['data']   = $err;
            $data_res['booking_id'] = "";
            $data_res['delivery_fee'] = 0;
            $data_res['distance'] = 0;

            return $data_res;
        } else {
            $data_res['logs']   = isset($response[0]) ? $response[0] : '' ;
            $data_res['data']   = isset($response[1]) ? $response[1] : '{}';

            //  data courier
            $data_courier = json_decode($data_res['data'], true);
            $data_res['delivery_fee'] = 0;
            $data_res['distance'] = 0;
            $data_res['booking_id'] = isset($data_courier['data']['order_id']) ? $data_courier['data']['order_id'] : '';
            return $data_res;
        };
    }
}
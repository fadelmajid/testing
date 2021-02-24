<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Triplogicapi{

	function __construct() {    
        $this->ci =& get_instance();
    }

    public function booking_ride($data){
        $curl = curl_init();

        // OPTIONS:
        $options = $this->get_options($data);

        if(gettype($options) == 'array' && (int) $options['http_code'] != 200) {
            return $options;
        }

        $curl_opt = array(
            CURLOPT_URL => $options->url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_HEADER => 1,
            CURLOPT_CUSTOMREQUEST => $options->method,
            CURLOPT_POSTFIELDS => json_encode($options->body),
            CURLOPT_HTTPHEADER => $options->header
        );
        curl_setopt_array($curl, $curl_opt);

        // EXECUTE:
        $result = curl_exec($curl);
        $err = curl_error($curl);

        $headers = [];
        $response = explode("\r\n\r\n",$result);

        curl_close($curl);

        //set log variable
        $logthis    = $this->ci->log4php('triplogic_booking_ride', APPLOG);
        $info       = 'Triplogic Booking Ride : '. json_encode($response);
        $logthis->info($info);

        if ($err) {
            $data_res['logs']   = "Error: ". json_encode($response);
            $data_res['data']   = $err;
            $data_res['booking_id'] = "";
            $data_res['delivery_fee'] = 0;
            $data_res['distance'] = 0;
            return $data_res;
        } else {
            $data_res['logs']   = isset($response) ? $response : '' ;
            $data_res['data']   = isset($response) ? (count($response) > 0 ? $response[count($response)-1] : '{}') : '{}';

            $data_courier = json_decode($data_res['data'], true);
            $data_res['distance'] = isset($data_courier['distance']) ? ($data_courier['distance'] > 0 ? $data_courier['distance'] : $data_courier['distance']) : 0;
            $data_res['delivery_fee'] = isset($data_courier['price']) ? $data_courier['price'] : 0;
            $data_res['booking_id'] = isset($data_courier['resSuccess']['res']['order_id']) ? $data_courier['resSuccess']['res']['order_id'] : '';
            return $data_res;
        }
    }

    public function get_options($data, $action = 'booking'){

        $token  = $this->get_token();
        // if return get token != 200 akan return
        if(isset($token['http_code']) && (int) $token['http_code'] != 200 ) {
            return $token;
        }

        // clean phone sender
        $sender = $data->st_phone;
        $sender_phone = preg_replace("/[^0-9]+/", "", $sender);
        $sender_phone = "62" == substr($sender_phone, 0, 2) ? "0". substr($sender_phone, 2, strlen($sender_phone)) : $sender_phone;

        // clean phone recipient
        $recipient = $data->uadd_phone;
        $recipient_phone = preg_replace("/[^0-9]+/", "", $recipient);
        $recipient_phone = "62" == substr($recipient_phone, 0, 2) ? "0". substr($recipient_phone, 2, strlen($recipient_phone)) : $recipient_phone;

        // set variabel object to call API GRAB EXPRESS
        $options = new stdClass();
        $options->header = array();
        $options->body = new stdClass();
        $origin_latlong = $data->st_lat.','.$data->st_long;
        $destination_latlong = $data->uadd_lat.','.$data->uadd_long;

        // header for triplogic API
        $options->header[] = 'Authorization: '. $token['access_token'];
        $options->header[] = 'Accept: application/json';
        $options->header[] = 'Content-Type: application/json';

        // set url API
        $options->path      = 'order/create';
        $options->url       = TRIPLOGIC_URL.$options->path; 
        $options->method    = 'POST';
        // ini akan cek actionnya apa, jika cancel dia hanya butuh data-data ini saja untuk body yang akan di parsing ke triplogic
        if($action == 'cancel'){
            $options->path      = 'order/cancel';
            $options->url       = TRIPLOGIC_URL.$options->path; 
            $options->body->order_id = (int) $data->booking_id;
            $options->body->detail = $data->uor_remarks;

            return $options;
        } 

        // body request to call triplogic API
        $options->body->from = new stdClass();
        $options->body->from->outlet_id = $data->st_id;
        $options->body->from->full_name = "Alpha " .$data->st_name;
        $options->body->from->no_phone = $sender_phone;
        $options->body->from->email = "helo@";
        $options->body->from->address = $data->st_address;
        $options->body->from->lat_long = $origin_latlong;
        $options->body->to = new stdClass();
        $options->body->to->full_name = $data->uadd_person;
        $options->body->to->no_phone = $recipient_phone;
        $options->body->to->email = $data->user_email;
        $options->body->to->address = $data->uadd_street;
        $options->body->to->lat_long = $destination_latlong;
        $options->body->note = (isset($data->uadd_notes) && $data->uadd_notes != '' ? $data->uadd_notes : "");
        $options->body->shipment_name = isset($data->item) ? "(".$data->st_name.") ".$data->item : 'Alpha';
        $options->body->total_normal_price = $data->uor_total;

        return $options;
    }

    public function cancel_booking($data){
        $curl = curl_init();

        // OPTIONS:
        $options = $this->get_options($data, 'cancel');

        if(gettype($options) == 'array' && (int) $options['http_code'] != 200) {
            return $options;
        }
        
        $curl_opt = array(
            CURLOPT_URL => $options->url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_HEADER => 1,
            CURLOPT_CUSTOMREQUEST => $options->method,
            CURLOPT_POSTFIELDS => json_encode($options->body),
            CURLOPT_HTTPHEADER => $options->header
        );
        curl_setopt_array($curl, $curl_opt);

        // EXECUTE:
        $result = curl_exec($curl);
        $err = curl_error($curl);

        $headers = [];
        $response = explode("\r\n\r\n", $result);
        $http_code = curl_getinfo( $curl, CURLINFO_HTTP_CODE );
        curl_close($curl);

        //set log variable
        $logthis    = $this->ci->log4php('triplogic_cancel_booking', APPLOG);
        $info       = 'Triplogic Cancel Booking : '. json_encode($response);
        $logthis->info($info);

        if ($err) {
            $data_res['logs']   = "Error : ". json_encode($response);
            $data_res['data']   = $err;
            $data_res['http_code'] = $http_code;
            return $data_res;
        } else {
            $data_res['logs']   = isset($response) ? $response : '' ;
            $data_res['data']   = isset($response) ? (count($response) > 0 ? $response[count($response)-1] : '{}') : '{}';

            $data = json_decode($data_res['data'], true);
            $data_res['http_code'] = $data['status'];
            return $data_res;
        }
    }

    public function get_token() {
        $curl = curl_init();

        // OPTIONS:
        $options = $this->get_options_token();
        $curl_opt = array(
            CURLOPT_URL => $options->url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_HEADER => 1,
            CURLOPT_CUSTOMREQUEST => $options->method,
            CURLOPT_HTTPHEADER => $options->header
        );
        curl_setopt_array($curl, $curl_opt);

        // EXECUTE:
        $result = curl_exec($curl);
        $err = curl_error($curl);

        $headers = [];
        $response = explode("\r\n\r\n", $result);
        $http_code = curl_getinfo( $curl, CURLINFO_HTTP_CODE );
        curl_close($curl);

        //set log variable
        $logthis    = $this->ci->log4php('triplogic_get_token', APPLOG);
        $info       = 'Triplogic Get Token : '. json_encode($response);
        $info       .= ', Triplogic Get Token Options : '. json_encode($options);
        $logthis->info($info);

        if ($err) {
            $data_res['logs']   = "Error: " . json_encode($response);
            $data_res['http_code'] = $http_code;
            $data_res['access_token'] = "";
            return $data_res;
        } else {
            $data_res['logs']   = isset($response) ? $response : '' ;
            $data_res['data']   = isset($response) ? (count($response) > 0 ? $response[count($response)-1] : '{}') : '{}';
            $data = json_decode($data_res['data'], true);
            $data_res['http_code'] = $data['status'];
            $data_res['access_token'] = $data['token'];

            return $data_res;
        }
    }

    public function get_options_token() {
        $options = new stdClass();
        $options->header = array();

        $options->path      = 'access_token?secret_key='. TRIPLOGIC_SERVER_KEY;
        $options->url       = TRIPLOGIC_URL.$options->path; 
        $options->method    = 'GET';

        // header for TRIPLOGIC API
        $options->header[] = 'Accept: application/json';
        $options->header[] = 'Content-Type: application/json';

        return $options;
    }
}
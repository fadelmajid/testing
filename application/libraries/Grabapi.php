<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Grabapi{
    protected $_date = "";

	function __construct() {
        // date_default_timezone_set("GMT");
        $this->_date = date("D, d M Y H:i:s", strtotime("- 7 hours"))." GMT";
    }

    public function booking_ride($data){
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

            $data_courier = json_decode($data_res['data'], true);
            // kalau distancenya ada dibagi 1000 karena satuannya meter.
            $data_res['distance'] = isset($data_courier['quote']['distance']) ? ($data_courier['quote']['distance'] > 0 ? $data_courier['quote']['distance'] / 1000 : $data_courier['quote']['distance']) : 0;
            $data_res['delivery_fee'] = isset($data_courier['quote']['amount']) ? $data_courier['quote']['amount'] : 0;
            $data_res['booking_id'] = isset($data_courier['deliveryID']) ? $data_courier['deliveryID'] : '';
            return $data_res;
        }
    }

    public function get_options($data, $action = 'booking'){

        // clean phone sender
        $sender = $data->user_phone;
        $sender_phone = preg_replace("/[^0-9]+/", "", $sender);
        $sender_phone = "0" == substr($sender_phone, 0, 1) ? "62". substr($sender_phone, 1, strlen($sender_phone)) : $sender_phone;

        // clean phone recipient
        $recipient = $data->uadd_phone;
        $recipient_phone = preg_replace("/[^0-9]+/", "", $recipient);
        $recipient_phone = "0" == substr($recipient_phone, 0, 1) ? "62". substr($recipient_phone, 1, strlen($recipient_phone)) : $recipient_phone;
        
        // set variabel object to call API GRAB EXPRESS
        $options = new stdClass();
        $options->header = array();
        $options->body = new stdClass();
        $options->body->sender = new stdClass();
        $options->body->recipient = new stdClass();
        $options->body->origin = new stdClass();
        $options->body->destination = new stdClass();

        // set url API
        $options->path      = '/v1/deliveries';
        $options->url       = GRAB_URL.$options->path; 
        $options->method    = 'POST';

        if($action == 'cancel'){
            $options->path      = '/v1/deliveries/'.$data->booking_id;
            $options->url       = GRAB_URL.$options->path; 
            $options->method    = 'DELETE';
        }

        // body request to call grab API
        $options->body->merchantOrderID = $data->uor_code;
        $options->body->serviceType ="INSTANT";
    
        $detail = $data->item_detail;
        $options->body->packages = array();
        for($key = 0; $key < count($detail); $key++){
            $data_item = $detail[$key];
            $options->body->packages[$key] = new stdClass();
            $options->body->packages[$key]->name = $data_item->uorpd_name;
            $options->body->packages[$key]->description = $data_item->uorpd_name;
            $options->body->packages[$key]->quantity = (int) $data_item->pd_qty;
            $options->body->packages[$key]->price = (int) $data_item->total_price;
            $options->body->packages[$key]->dimensions = new stdClass();
            $options->body->packages[$key]->dimensions->height = 1;
            $options->body->packages[$key]->dimensions->width = 1;
            $options->body->packages[$key]->dimensions->depth = 1;
            $options->body->packages[$key]->dimensions->weight = 1000;
        }

        $options->body->sender->firstName = $data->st_name;
        $options->body->sender->lastName = "";
        $options->body->sender->title = "";
        $options->body->sender->companyName = "";
        $options->body->sender->email = "hello@";
        $options->body->sender->phone = $data->st_phone;
        $options->body->sender->smsEnabled = false;
        $options->body->sender->instruction = (isset($data->item) ? "(".$data->st_name.") ".$data->item : 'Alpha');
        $options->body->recipient->firstName = $data->uadd_person;
        $options->body->recipient->lastName = "";
        $options->body->recipient->title = $data->uadd_title;
        $options->body->recipient->companyName = "";
        $options->body->recipient->email = $data->user_email;
        $options->body->recipient->phone = $recipient_phone;
        $options->body->recipient->smsEnabled = true;
        $options->body->recipient->instruction = "Instant";
        $options->body->origin->address = $data->st_address;
        $options->body->origin->keywords = $data->st_name;
        $options->body->origin->coordinates = new stdClass();
        $options->body->origin->coordinates->latitude = (float) $data->st_lat;
        $options->body->origin->coordinates->longitude = (float) $data->st_long;
        $options->body->origin->extract = new stdClass();
        $options->body->destination->address = (isset($data->uadd_notes) && $data->uadd_notes != '' ? "(".$data->uadd_notes.") " : "").$data->uadd_street;
        $options->body->destination->keywords = (isset($data->uadd_title) && $data->uadd_title != '' ? $data->uadd_title : "");
        $options->body->destination->coordinates = new stdClass();
        $options->body->destination->coordinates->latitude = (float) $data->uadd_lat;
        $options->body->destination->coordinates->longitude = (float) $data->uadd_long;
        $options->body->destination->extract = new stdClass();

        $base64_hmac_signature = $this->_generate_hmac_signature($options);

        // header for grab API
        $options->header[] = 'Authorization: '. GRAB_CLIENT_ID .':'. $base64_hmac_signature;
        $options->header[] = 'DATE: '. $this->_date;
        $options->header[] = 'Content-Type: '. GRAB_CONTENT_TYPE;

        return $options;
    }


    private function _generate_hmac_signature($options) {
        $string_to_sign = $this->_generate_content_digest($options);

        $hmac_signature = hash_hmac('sha256', $string_to_sign, GRAB_SERVER_KEY, true);

        $hmac_signature_encoded = base64_encode($hmac_signature);

        return $hmac_signature_encoded;
    }

    private function _generate_content_digest($options) {
        $request_body   = json_encode($options->body, true);
        $content_digest = base64_encode(hash( 'sha256' , $request_body , true ));

        $string_to_sign  = $options->method.PHP_EOL;
        $string_to_sign .= GRAB_CONTENT_TYPE.PHP_EOL;
        $string_to_sign .= $this->_date.PHP_EOL;
        $string_to_sign .= $options->path.PHP_EOL;
        $string_to_sign .= $content_digest.PHP_EOL;
        
        return $string_to_sign;
    }

    public function cancel_booking($data){
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

        if ($err) {
            $data_res['logs']   = "Error" ;
            $data_res['data']   = $err;
            $data_res['http_code'] = $http_code;
            $data_res['booking_id'] = "";
            $data_res['delivery_fee'] = 0;
            $data_res['distance'] = 0;
            return $data_res;
        } else {
            $data_res['logs']   = isset($response) ? $response : '' ;
            $data_res['data']   = isset($response) ? json_encode($response) : '{}';
            $data_res['http_code'] = $http_code;

            $data_courier = json_decode($data_res['data'], true);
            // kalau distancenya ada dibagi 1000 karena satuannya meter.
            $data_res['distance'] = isset($data_courier['quote']['distance']) ? ($data_courier['quote']['distance'] > 0 ? $data_courier['quote']['distance'] / 1000 : $data_courier['quote']['distance']) : 0;
            $data_res['delivery_fee'] = isset($data_courier['quote']['amount']) ? $data_courier['quote']['amount'] : 0;
            $data_res['booking_id'] = isset($data_courier['deliveryID']) ? $data_courier['deliveryID'] : '';
            return $data_res;
        }
    }
}
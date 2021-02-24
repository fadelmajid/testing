<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Gosendapi{


	function __construct() {
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
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($options->body),
            CURLOPT_HTTPHEADER => $options->header
        );
        curl_setopt_array($curl, $curl_opt);

        // EXECUTE:
        $result = curl_exec($curl);
        $err = curl_error($curl);

        $headers = [];
        $response = explode("\r\n\r\n", $result);

        curl_close($curl);

        if ($err) {
            $data_res['logs']   = "Error" ;
            $data_res['data']   = $err;
            $data_res['booking_id'] = "";
            $data_res['delivery_fee'] = 0;
            $data_res['distance'] = 0;
            
            return $data_res;
        } else {
            //get distance and delivery fee
            $data_res_estimate  = $this->estimate_price($data);
            
            $data_res['logs']   = isset($response[0]) ? $response[0] : '' ;
            $data_res['data']   = isset($response[1]) ? $response[1] : '{}';
            $data_estimate      = json_decode($data_res_estimate['data'], true);
            $data_courier       = json_decode($data_res['data'], true);

            $data_res['distance']       = isset($data_estimate['Instant']['distance']) ? $data_estimate['Instant']['distance'] : 0 ;
            $data_res['delivery_fee']   = isset($data_estimate['Instant']['price']['total_price']) ? $data_estimate['Instant']['price']['total_price'] : 0 ;
            $data_res['booking_id']     = isset($data_courier['orderNo']) ? $data_courier['orderNo'] : '';
            return $data_res;
        }
    }

    public function estimate_price($data){
        $curl = curl_init();

        // OPTIONS:
        $options = $this->get_options($data, 'estimate');
        $curl_opt = array(
            CURLOPT_URL => $options->url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_HEADER => 1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_POSTFIELDS => json_encode($options->body),
            CURLOPT_HTTPHEADER => $options->header
        );
        curl_setopt_array($curl, $curl_opt);

        // EXECUTE:
        $result = curl_exec($curl);
        $err = curl_error($curl);

        $headers = [];
        $response = explode("\r\n\r\n", $result);

        curl_close($curl);
        
        if ($err) {
            $data_res['logs']   = "Error" ;
            $data_res['data']   = $err;
            $data_res['delivery_fee'] = 0;
            $data_res['distance'] = 0;
            
            return $data_res;
        } else {
            $data_res['logs']   = isset($response[0]) ? $response[0] : '' ;
            $data_res['data']   = isset($response[1]) ? $response[1] : '{}';

            return $data_res;
        }
    }

    public function get_options($data, $action = 'booking'){
        // set variabel object to call API GO-SEND
        $options = new stdClass();
        $options->header = array();
        $options->body = new stdClass();
        $options->body->routes = array();
        $options->body->routes[0] = new stdClass();
        $origin_latlong = $data->st_lat.','.$data->st_long;
        $destination_latlong = $data->uadd_lat.','.$data->uadd_long;
        $payment_type = 3;

        // set url API
        $options->url = GOSEND_URL.'booking';
        if($action == 'estimate'){
            //url for get distance and delivery fee
            $options->url = GOSEND_URL.'calculate/price?origin='.$origin_latlong.'&destination='.$destination_latlong.'&paymentType='.$payment_type;
        }

        // header for GOSEND API
        $options->header[] = 'Client-ID: '.GOSEND_CLIENT_ID;
        $options->header[] = 'Pass-Key: '.GOSEND_PASS_KEY;
        $options->header[] = 'Content-Type: application/json';

        // body request to call GOSEND API
        $options->body->paymentType = $payment_type;
        $options->body->deviceToken ="";
        $options->body->collection_location = "pickup";
        $options->body->shipment_method = "Instant";
        $options->body->routes[0]->originContactName = $data->st_name;
        $options->body->routes[0]->originContactPhone = $data->st_phone;
        $options->body->routes[0]->originLatLong = $origin_latlong;
        $options->body->routes[0]->originAddress = $data->st_address;
        $options->body->routes[0]->destinationNote = isset($data->uadd_notes) ? $data->uadd_notes : '';
        $options->body->routes[0]->destinationContactName = $data->uadd_person;
        $options->body->routes[0]->destinationContactPhone = $data->uadd_phone;
        $options->body->routes[0]->destinationLatLong = $destination_latlong;
        $options->body->routes[0]->destinationAddress = (isset($data->uadd_notes) && $data->uadd_notes != '' ? "(".$data->uadd_notes.") " : "").$data->uadd_street;
        $options->body->routes[0]->item = isset($data->item) ? "(".$data->st_name.") ".$data->item : 'Alpha';
        $options->body->routes[0]->storeOrderId = $data->uor_code;

        return $options;
    }

    public function cancel_booking($data){
        
        $data_res['http_code']      = "204";
        $data_res['data']           = json_encode(array("orderNo" => $data->booking_id));
        $data_res['delivery_fee']   = 0;
        $data_res['distance']       = 0;

        $data_courier           = json_decode($data_res['data'], true);
        $data_res['booking_id'] = isset($data_courier['orderNo']) ? $data_courier['orderNo'] : '';
        return $data_res;        
        
    }

}
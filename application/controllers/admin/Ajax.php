<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ajax extends MY_Admin {
    function __construct()
	{
		parent::__construct();

        $this->arr_admin = $this->admindb->getarr_admin();
	}

	public function index()
	{
		show_404();
	}


    public function invalidlogin_delete()
    {
        $submenu_code = 'invalid_login';
        $access = $this->_check_menu_access( $submenu_code, 'delete' , false, false);
        if(!$access) {
            exit('Access denied - You are not authorized to access this page.');
        }

        $log_id = $this->input->post('log_id');

        //set log variable
        $logthis    = $this->log4php('invalidlogin_delete', APPLOG);
        $info       = 'Delete Invalid Login ID '. $log_id . ' by '. $this->arr_admin[$this->_get_user_id()] .'. ';
        $msg        = '';

        $ret = $this->admindb->delete_invalidlogin($log_id);
        if ($ret) {
            $msg = 'Success';
        } else {
            $msg = 'Delete Data Failed!';
        }

        //send output
        $info .= $msg;
        $logthis->info($info);
        echo $msg;
    }

    public function faq_delete()
    {
        $this->load->model('staticdb');
        $submenu_code = 'static_faq';
        $access = $this->_check_menu_access( $submenu_code, 'delete' , false, false);
        if(!$access) {
            exit('Access denied - You are not authorized to access this page.');
        }

        $faq_id = $this->input->post('faq_id');

        //set log variable
        $logthis    = $this->log4php('faq_delete', APPLOG);
        $info       = 'Delete FAQ ID '. $faq_id . ' by '. $this->arr_admin[$this->_get_user_id()] .'. ';
        $msg        = '';

        $detail     = $this->staticdb->get_faq($faq_id);

        if (empty($detail)) {
            $msg = 'Invalid FAQ ID';
        }else{
            $ret = $this->staticdb->delete_faq($faq_id);
            if ($ret) {
                $msg = 'Success';
            } else {
                $msg = 'Delete Data Failed!';
            }
        }


        //send output
        $info .= $msg;
        $logthis->info($info);
        echo $msg;
    }

    public function static_image_delete(){
        $submenu_code = 'static_image';
        $access = $this->_check_menu_access( $submenu_code, 'delete' , false, false);
        if(!$access) {
            exit('Access denied - You are not authorized to access this page.');
        }

        $this->load->model('staticdb');
        $stat_id = $this->input->post('stat_id');

        //set log variable
        $logthis    = $this->log4php('static_image_delete', APPLOG);
        $info       = 'Delete Static Image ID '. $stat_id . ' by '. $this->arr_admin[$this->_get_user_id()] .'. ';
        $msg        = '';

        $detail     = $this->staticdb->get_static_image($stat_id);

        if (empty($detail)) {
            $msg = 'Invalid Static Image ID';
        }else{
            $ret = $this->staticdb->delete_static_image($stat_id);
            if ($ret) {
                $msg = 'Success';
            } else {
                $msg = 'Delete Data Failed!';
            }
        }


        //send output
        $info .= $msg;
        $logthis->info($info);
        echo $msg;
    }

    public function partner_promo_delete()
    {
        $this->load->model('partnerdb');
        $submenu_code = 'partner';
        $access = $this->_check_menu_access( $submenu_code, 'delete' , false, false);
        if(!$access) {
            exit('Access denied - You are not authorized to access this page.');
        }

        $ptrpm_id = $this->input->post('ptrpm_id');
        //set log variable
        $logthis    = $this->log4php('partner_promo_delete', APPLOG);
        $info       = 'Delete Partner Promo ID '. $ptrpm_id . ' by '. $this->arr_admin[$this->_get_user_id()] .'. ';
        $msg        = '';

        $detail     = $this->partnerdb->get_partner_promo($ptrpm_id);

        if (empty($detail)) {
            $msg = 'Invalid Partner Promo ID';
        }else{
            $ret = $this->partnerdb->delete_partner_promo($ptrpm_id);
            if ($ret) {
                $msg = 'Success';
            } else {
                $msg = 'Delete Data Failed!';
            }
        }


        //send output
        $info .= $msg;
        $logthis->info($info);
        echo $msg;
    }

    //Function for delete appversion
    public function appversion_delete()
    {
        //load model appversiondb
        $this->load->model('appversiondb');
        //check permission action
        $submenu_code = 'app_version';
        $access = $this->_check_menu_access( $submenu_code, 'delete' , false, false);
        if(!$access) {
            exit('Access denied - You are not authorized to access this page.');
        }

        $ver_id = $this->input->post('ver_id');
        //set log variable
        $logthis    = $this->log4php('appversion_delete', APPLOG);
        $info       = 'Delete App Version ID '. $ver_id . ' by '. $this->arr_admin[$this->_get_user_id()] .'. ';
        $msg        = '';

        $detail     = $this->appversiondb->get_app_version($ver_id);

        //validate app version if exist
        if (empty($detail)) {
            $msg = 'Invalid App Version ID';
        }else{
            $ret = $this->appversiondb->delete_app_version($ver_id);
            if ($ret) {
                $msg = 'Success';
            } else {
                $msg = 'Delete Data Failed!';
            }
        }


        //send output
        $info .= $msg;
        $logthis->info($info);
        echo $msg;
    }

    //=== START USER
    public function user_status_update()
    {
        $this->load->model('userdb');
        // validate admin roles
        $submenu_code = 'user';
        $access = $this->_check_menu_access($submenu_code, 'status', false, false);
        if (!$access) {
            exit('Access denied - You are not authorized to access this page.');
        }

        $user_id = $this->input->post('user_id');
        $new_status = $this->input->post('status');
        $allowed_status = $this->config->item('user')['status'];
        $msg = 'Success';

        if (!$user_id || !$new_status || !in_array($new_status, $allowed_status)) {
            $msg = 'Missing User ID or Status!';
        } else {
            if (!$this->userdb->update($user_id, ['user_status' => $new_status])) {
                $msg = 'Update User Status failed!';
            }
        }

        // insert log
        $info = "Update User ID {$user_id} status to \"".strtoupper($new_status)."\" by {$this->arr_admin[$this->_get_user_id()]}. {$msg}";
        $logthis = $this->log4php('user_status_update', APPLOG);
        $logthis->info($info);
        echo $msg;
    }
    //=== END USER

    //=== START PRODUCT
    public function product_status_update()
    {
        $this->load->model('productdb');
        // validate admin roles
        $submenu_code = 'product';
        $access = $this->_check_menu_access($submenu_code, 'edit', false, false);
        if (!$access) {
            exit('Access denied - You are not authorized to access this page.');
        }

        $product_id = $this->input->post('product_id');
        $new_status = $this->input->post('status');
        $allowed_status = $this->config->item('product')['status'];
        $msg = 'Success';

        if (!$product_id || !$new_status || !in_array($new_status, $allowed_status)) {
            $msg = 'Missing Product ID or Status!';
        } else {
            if (!$this->productdb->update_product($product_id, ['pd_status' => $new_status])) {
                $msg = 'Update Product Status failed!';
            }
        }

        // insert log
        $info = "Update Product ID {$product_id} status to \"".strtoupper($new_status)."\" by {$this->arr_admin[$this->_get_user_id()]}. {$msg}";
        $logthis = $this->log4php('product_status_update', APPLOG);
        $logthis->info($info);
        echo $msg;
    }
    //=== END PRODUCT

    //=== START COURIER
    public function courier_status_update()
    {
        // validate admin roles
        $submenu_code   = 'courier';
        $access         = $this->_check_menu_access($submenu_code, 'edit', false, false);
        if (!$access) {
           exit('Access denied - You are not authorized to access this page.');
        }

        $this->load->model('courierdb');
        $courier_id     = $this->input->post('courier_id');
        $new_status     = $this->input->post('status');
        $allowed_status = $this->config->item('courier')['status'];
        $msg            = 'Success';

        if (!$courier_id || !$new_status || !in_array($new_status, $allowed_status)) {
            $msg = 'Missing Courier ID or Status!';
        } else {
            if (!$this->courierdb->update_courier($courier_id, ['courier_status' => $new_status])) {
                $msg = 'Update Courier Status failed!';
            }
        }

        // insert log
        $info       = "Update Courier ID {$courier_id} status to \"".strtoupper($new_status)."\" by {$this->arr_admin[$this->_get_user_id()]}. {$msg}";
        $logthis    = $this->log4php('courier_status_update', APPLOG);
        $logthis->info($info);
        echo $msg;
    }

    public function courier_delete(){
        // validate admin roles
        $submenu_code   = 'courier';
        $access         = $this->_check_menu_access( $submenu_code, 'delete' , false, false);
        if(!$access) {
           exit('Access denied - You are not authorized to access this page.');
        }

        $this->load->model('courierdb');
        $courier_id     = $this->input->post('courier_id');

        //set log variable
        $logthis    = $this->log4php('courier_delete', APPLOG);
        $info       = 'Delete Courier ID '. $courier_id . ' by '. $this->arr_admin[$this->_get_user_id()] .'. ';
        $msg        = '';

        $detail     = $this->courierdb->get_courier($courier_id);

        if (empty($detail)) {
            $msg = 'Invalid Courier ID';
        }else{
            $ret        = $this->courierdb->delete_courier($courier_id);
            
            if ($ret) {
                $msg = 'Success';
                if($detail->is_default == "1"){
                    $this->courierdb->update_is_default_not_true();
                }
            } else {
                $msg = 'Delete Data Failed!';
            }
        }

        //send output
        $info .= $msg;
        $logthis->info($info);
        echo $msg;
    }
    //=== END COURIER

    //=== START PROMO
    public function promo_status_active()
    {
        $this->load->library('infobip');
        $this->load->model('promodb');

        // validate admin roles
        $submenu_code = 'promo';
        $access = $this->_check_menu_access($submenu_code, 'edit', false, false);
        if (!$access) {
            exit('Access denied - You are not authorized to access this page.');
        }

        $promo_id = $this->input->post('promo_id');
        $promo_data['prm_status'] = $this->config->item('promo')['status']['active'];
        $msg = 'Success';

        if (!$promo_id) {
            $msg = 'Missing Promo ID or Status!';
        } else {
            $is_generated = $this->promodb->is_generated_promo($promo_id);
            if($is_generated){
                $promo_data = [
                    'prm_status' => $this->config->item('promo')['status']['active'],
                    'updated_by' => $this->_get_user_id(),
                    'updated_date' => date('Y-m-d H:i:s')
                ];
                // update status promo to active
                $result = $this->promodb->update_promo($promo_id, $promo_data);
            }else{
                $result = $this->promodb->generate_voucher($this->_get_user_id(), $promo_id);
            }

            if (!$result) {
                $msg = 'Update Promo Status failed!';
            }
        }
        
        // insert log
        $info = "Update Promo ID {$promo_id} status to \"".strtoupper('active')."\" by {$this->arr_admin[$this->_get_user_id()]}. {$msg}";
        $logthis = $this->log4php('promo_status_active', APPLOG);
        $logthis->info($info);
        echo $msg;
    }

    public function promo_status_inactive()
    {
        $this->load->model('promodb');
        // validate admin roles
        $submenu_code = 'promo';
        $access = $this->_check_menu_access($submenu_code, 'edit', false, false);
        if (!$access) {
            exit('Access denied - You are not authorized to access this page.');
        }

        $promo_id = $this->input->post('promo_id');
        $promo_data['prm_status'] = $this->config->item('promo')['status']['inactive'];

        $msg = 'Success';

        if (!$promo_id) {
            $msg = 'Missing Promo ID !';
        } else {
            $update = $this->promodb->update_promo($promo_id, $promo_data);
            if (!$update) {
                $msg = 'Update Promo Status failed!';
            }
        }

        // insert log
        $info = "Update Promo ID {$promo_id} status to \"".strtoupper('inactive')."\" by {$this->arr_admin[$this->_get_user_id()]}. {$msg}";
        $logthis = $this->log4php('promo_status_inactive', APPLOG);
        $logthis->info($info);
        echo $msg;
    }
    //=== END PROMO

    //=== START STORE PRODUCT
    public function store_product_status_update()
    {
        $this->load->model('productdb');
        // validate admin roles
        $submenu_code = 'store_product';
        $access = $this->_check_menu_access($submenu_code, 'edit', false, false);
        if (!$access) {
            exit('Access denied - You are not authorized to access this page.');
        }

        $allowed_status = $this->config->item('store_product')['storepd_status'];
        $store_product_id = $this->input->post('store_product_id');
        $store_product_status = $this->input->post('store_product_status');
        $msg = "Success";

        // validate stpd_id and status
        if (!$store_product_id || !$store_product_status || !in_array($store_product_status, $allowed_status)) {
            $msg = 'Missing Store Product ID or Status!';
            $info = "Update Store Product Status to \"".strtoupper($store_product_status)."\" by {$this->arr_admin[$this->_get_user_id()]}. {$msg}";
        } else {
            // validate if store product exists
            if(!$this->productdb->get_store_product($store_product_id)) {
                $msg = 'Store Product Not Found!';
            } else {
                $data = [
                    'stpd_status' => $store_product_status,
                    'updated_by' => $this->_get_user_id(),
                    'updated_date' => date('Y-m-d H:i:s')
                ];
                // update store product status
                if (!$this->productdb->update_store_product($store_product_id, $data)) {
                    $msg = 'Update Store Product Status failed!';
                }
            }
            $info = "Update Status for Store Product ID {$store_product_id} to \"".strtoupper($store_product_status)."\" by {$this->arr_admin[$this->_get_user_id()]}. {$msg}";
        }

        // insert log
        $logthis = $this->log4php('store_product_status_update', APPLOG);
        $logthis->info($info);
        echo $msg;
    }

    public function store_product_delete()
    {
        $this->load->model('productdb');
        // validate admin roles
        $submenu_code = 'store_product';
        $access = $this->_check_menu_access($submenu_code, 'delete', false, false);
        if (!$access) {
            exit('Access denied - You are not authorized to access this page.');
        }

        $store_product_id = $this->input->post('store_product_id');
        $msg = "Success";

        // validate ID and store product exists
        if (!$store_product_id || !$this->productdb->get_store_product($store_product_id)) {
            $msg = 'Missing Store Product ID or Store Product not found!';
            $info = "Delete Store Product by {$this->arr_admin[$this->_get_user_id()]}. {$msg}";
        } else {
            // delete order track
            if(!$this->productdb->delete_store_product($store_product_id)) {
                $msg = 'Delete Store Product failed!';
            }
            $info = "Delete Store Product ID {$store_product_id} by {$this->arr_admin[$this->_get_user_id()]}. {$msg}";
        }

        //insert log
        $logthis = $this->log4php('store_product_delete', APPLOG);
        $logthis->info($info);
        echo $msg;
    }

    public function get_store() {
        $this->load->model('storedb');
        $st_name    = $this->input->get('term');


        $where      = ' AND st_name LIKE ?';
        $data       = ['%'. $st_name .'%'];
        
        $store      = $this->storedb->getall($where, $data, '', 30);

        echo json_encode($store);
    }

    public function get_product() {
        $this->load->model('productdb');
        $pd_name    = $this->input->get('term');


        $where      = ' AND pd_name LIKE ?';
        $data       = ['%'. $pd_name .'%'];
        
        $product    = $this->productdb->getall_product($where, $data, '', 30);

        echo json_encode($product);
    }
    //=== END STORE PRODUCT

    /**
         * get store courier => JSON {courier_code: int, barista: boolean}
         * validate store courier
         * get vendor name by courier_code
         * validate vendor
         * call lib by vendor name
         * get estimate price by store courier (lib only return price and JSON response)
         * ======================================= SAMPAI SINI DULU
         * if store courier provide/ array.includes by barista
         * check selected store, related or not with building
         * yeay :
         * send by barista, delivery fee = 0
         */

    //=== START ORDER
    private function hit_courier_api($uor_id)
    {
        $this->load->model('courierdb');
        $this->load->model('orderdb');
        $this->load->model('userdb');
        $logthis = $this->log4php('hit_courier_api', APPLOG);
        $info = '';

        // set data to call courier API (Booking)
        $data = $this->orderdb->get_order($uor_id);

        if(!empty($data->st_courier)){
            $data_courier = json_decode($data->st_courier);
            $courier_code = $data_courier->courier_code;
        }else{
            // get courier / vendor default
            $cr_default = $this->courierdb->get_courier_default();
            $courier_code = $cr_default->courier_code;
        }

        // get courier / vendor code
        $vendor = $this->courierdb->get_courier_code($courier_code);

        // validate courier
        if(empty($vendor)){
            $info .= ' Order ID '. $uor_id .' tidak dapat di process karena courier tidak tersedia saat ini (code : '. $courier_code .')';
            $logthis->info($info);
            return false;
        }

        $api = $vendor->courier_code.'api';
        $db = $vendor->courier_code.'db';
        $this->load->library($api);
        $this->load->model($db);

        $track_msg = $this->config->item('order_track')['message'];
        $courier_status = $this->config->item('gosend')['status'];
        $cst_courier_code = $this->config->item('courier')['courier_code'];
        $cst_order = $this->config->item('order')['status'];

        $data->item = $this->orderdb->get_order_item($uor_id);
        $data->item_detail = $this->orderdb->get_order_item_detail($uor_id);

        // call the API
        $data_res = $this->$api->booking_ride($data);
        $data_courier = json_decode($data_res['data'], true);

        $info .= "#1 : ".json_encode($data_res["logs"]);

        if(empty($data_courier)) {
            //validate response and retry hit gosend if get errors phone number
            $dataUser = $this->userdb->get($data->user_id);

            //update orders to change phone number and use phone number from profile user
            $updateData["uadd_phone"]   = $dataUser->user_phone;
            $updateData["uadd_person"]  = $dataUser->user_name;

            $this->orderdb->update_order_address($data->uoradd_id, $updateData);

            $data = $this->orderdb->get_order($uor_id);
            $data->item = $this->orderdb->get_order_item($uor_id);
            $data->item_detail = $this->orderdb->get_order_item_detail($uor_id);
            
            // re-hit the API
            $data_res = $this->$api->booking_ride($data);

            $info .= ",  #2 : " . json_encode($data_res["logs"]);
        }
        // set data to gosend_logs
        $data_req = $this->$api->get_options($data);

        // courier code
        $code = $vendor->courier_code;
        $params = [
            $code.'_type' => 'booking',
            $code.'_endpoint' => $data_req->url,
            $code.'_header' => json_encode($data_req->header),
            $code.'_request' => json_encode($data_req->body),
            $code.'_response' => $data_res['data'],
            'created_date' => date('Y-m-d H:i:s')
        ];
        $this->$db->insert_log($params);

        // set actual delivery price
        if(isset($data_res['delivery_fee']) && $data_res['delivery_fee'] > 0 ) {
            $params = [
                'uor_actual_delivery_fee' => $data_res['delivery_fee'],
                'uor_delivery_distance' => $data_res['distance']
            ];
            $this->orderdb->update_order($uor_id, $params);
        }

        $booking_id = $data_res['booking_id'];
        $uorcr_status = $booking_id != '' ? NULL : $courier_status['cancelled'];
        //set data to user_order_gosend
        $uorgo = [
            'uor_id' => $data->uor_id,
            'booking_id' => $booking_id,
            'uorcr_vendor' => isset($vendor->courier_code) ? $vendor->courier_code : '',
            'uorcr_status' => $uorcr_status,
            'created_date' => date('Y-m-d H:i:s')
        ];
        $courier_id = $this->courierdb->insert_order_courier($uorgo);

        $uortr_text = $booking_id != '' ? $track_msg[0] : $track_msg[6];
        // set varible insert order track
        $insert = [
            'uor_id' => $data->uor_id,
            'uortr_date' => date('Y-m-d H:i:s'),
            'uortr_text' => $uortr_text,
            'created_by' => $this->_get_user_id(),
            'created_date' => date('Y-m-d H:i:s')
        ];
        $this->orderdb->insert_order_track($insert);

        $logthis->info($info);
        return true;
    }

    public function order_status_update()
    {
        $this->load->model('orderdb');
        $this->load->model('courierdb');
        $this->load->library('infobip');
        $this->load->model('userdb');

        // validate admin roles
        $submenu_code = 'ongoing';
        $access = $this->_check_menu_access($submenu_code, 'edit', false, false);
        if (!$access) {
            exit('Access denied - You are not authorized to access this page.');
        }

        $allowed_action = $this->config->item('order')['action'];
        $order_status = $this->config->item('order')['status'];
        $uor_id = $this->input->post('uor_id');

        // validate order and its status
        if (!$uor_id) {
            $result['msg'] = 'Missing Order ID';
            $info = "Update Order Status by {$this->arr_admin[$this->_get_user_id()]}. {$result['msg']}";
        } else {
            $info = '';
            // update order status
            $result = $this->orderdb->update_order_status($uor_id, $allowed_action['update'], $this->_get_user_id());

            // send email when order success
            if($result['new_order_status'] == $order_status['completed']){
                $uor = $this->orderdb->get_order($uor_id);
                $tpl_name = 'order_completed';

                // get order product list
                $where = " AND uor_id = ?";
                $order_product = $this->orderdb->getall_order_product($where, [$uor->uor_id]);

                $pin_title = 'Pickup at';
                $pin_img = UPLOAD_URL.'email/store-pin.png';
                $pin_name = $uor->st_name;
                $pin_desc = $uor->st_address;

                if($uor->uor_delivery_type == "delivery"){
                    $pin_title = 'Delivery to';
                    $pin_img = UPLOAD_URL.'email/delivery-pin.png';
                    $pin_name = $uor->uadd_title;
                    $pin_desc = $uor->uadd_street;
                }

                $tpl_data = [
                    'user_name' => $uor->user_name,
                    'user_email' => $uor->user_email,
                    'uor_total' => $uor->uor_total,
                    'uor_discount' => $uor->uor_discount,
                    'uor_code' => $uor->uor_code,
                    'pin_title' => $pin_title,
                    'pin_img' => $pin_img,
                    'pin_name' => $pin_name,
                    'pin_desc' => $pin_desc,
                    'uor_product' => $order_product,
                    'uor_subtotal' => $uor->uor_subtotal,
                    'uor_delivery_fee' => $uor->uor_delivery_fee,
                    'created_date' => date('d F Y', strtotime($uor->uor_date)),
                    'status' => $uor->uor_status,
                    'subject' => 'Your Order on '.date('l, d F Y', strtotime($uor->uor_date)),
                ];

                // send email to customer when order complete
                $response = $this->infobip->send_email($tpl_name, $tpl_data, $uor->user_email_verified);

                // generate referral only if user_referral is found
                if ($result['ref_email'] == true) {
                    $where_ref = ' AND uref_to = ? ';
                    $data_ref = [$uor->user_id];
                    $user_referral = $this->userdb->get_referral_custom_filter($where_ref, $data_ref);
                    $uref = $this->userdb->get($user_referral->uref_from);
                    $vc_data = $this->promodb->get_voucher($result['vc_id']);
                    $tpl_email = 'referral_voucher';
                    $tpl_voucher = [
                        'user_name' => $uref->user_name,
                        'user_email' => $uref->user_email,
                        'status' => 'referral',
                        'from' => $uor->user_name,
                        'created_date' => date('d F Y', strtotime($uor->uor_date)),
                        'exp_date' => date('d F Y', strtotime($vc_data->prm_end)),
                        'subject' => 'You get referral voucher from '.$uor->user_name
                    ];
                    // send email referral voucher for uref_from
                    $response .= ', '.$this->infobip->send_email($tpl_email, $tpl_voucher, $uor->user_email_verified);
                }
                $info .= $response.', ';
            }

            if($result['hit_3rd_party'] == true){
                $this->hit_courier_api($uor_id);
            }
            $info .= "Update Order ID {$result['uor_id']} Status to \"".strtoupper($result['new_order_status'])."\" by {$this->arr_admin[$this->_get_user_id()]}. {$result['msg']}";
        }

        // insert log
        $logthis = $this->log4php('order_status_update', APPLOG);
        $logthis->info($info);
        echo $result['msg'];
    }

    public function ongoing_delivery_in_process() {
        $this->load->model('orderdb');
        $this->load->model('courierdb');
        $this->load->library('infobip');
        $this->load->library('gosendapi');
        $this->load->model('userdb');

        // validate admin roles
        $submenu_code = 'ongoing';
        $access = $this->_check_menu_access($submenu_code, 'edit', false, false);
        if (!$access) {
            exit('Access denied - You are not authorized to access this page.');
        }

        //set variable from input
        $order_status = $this->config->item('order')['status'];
        $delivery_type = $this->config->item('order')['delivery_type'];
        $uor_id = $this->input->post('uor_id');

        //validate order detail
        $order_detail = $this->orderdb->get_order($uor_id);

        if(empty($order_detail)) {
            $result['msg'] = 'Invalid Order ID.';
            $info = "Update Order Status by {$this->arr_admin[$this->_get_user_id()]}. {$result['msg']}";
        } else if($order_detail->uor_status == $order_status['paid'] && $order_detail->uor_delivery_type == $delivery_type['delivery']) {

            $info = '';
            // update order status
            $data = [
                'uor_status' => $order_status['in_process'],
                'updated_by' => $this->_get_user_id(),
                'updated_date' => date('Y-m-d H:i:s')
            ];
             $this->orderdb->update_order($uor_id, $data);

            //hit to gosend api after update
            $this->hit_courier_api($uor_id);

            $result['msg'] = 'Success';
            $info .= ", Update Order ID {$uor_id} Status to \"In Process\" by {$this->arr_admin[$this->_get_user_id()]}. {$result['msg']}";

        } else {
            $result['msg'] = 'Update failed! Please check your order and refresh this page.';
            $info = "Order ID {$uor_id} tried to change by {$this->arr_admin[$this->_get_user_id()]}. {$result['msg']}";
        }

        // insert log
        $logthis = $this->log4php('order_delivery_in_process', APPLOG);
        $logthis->info($info);

        echo $result['msg'];
    }

    public function ongoing_pickup_in_process() {
        $this->load->model('orderdb');
        $this->load->library('infobip');
        $this->load->model('userdb');

        // validate admin roles
        $submenu_code = 'ongoing';
        $access = $this->_check_menu_access($submenu_code, 'edit', false, false);
        if (!$access) {
            exit('Access denied - You are not authorized to access this page.');
        }

        //set variable from input
        $order_status = $this->config->item('order')['status'];
        $delivery_type = $this->config->item('order')['delivery_type'];
        $uor_id = $this->input->post('uor_id');
        $result = array();
        $info   = "";

        //validate order detail
        $order_detail = $this->orderdb->get_order($uor_id);
        if(empty($order_detail)) {
            $result['msg'] = 'Invalid Order ID.';
            $info = "Update Order Status by {$this->arr_admin[$this->_get_user_id()]}. {$result['msg']}";
        } else if($order_detail->uor_status == $order_status['paid'] && $order_detail->uor_delivery_type == $delivery_type['pickup']){
            // update order status
            $data = [
                'uor_status' => $order_status['in_process'],
                'updated_by' => $this->_get_user_id(),
                'updated_date' => date('Y-m-d H:i:s')
            ];

            $this->orderdb->update_order($uor_id, $data);

            $result['msg'] = 'Success';
            $info .= ", Update Order ID {$uor_id} Status to \"In Process\" by {$this->arr_admin[$this->_get_user_id()]}. {$result['msg']}";

        } else {
            $result['msg'] = 'Update failed! Please check your order and refresh this page.';
            $info = "Order ID {$uor_id} tried to change by {$this->arr_admin[$this->_get_user_id()]}. {$result['msg']}";
        }

        // insert log
        $logthis = $this->log4php('order_pickup_in_process', APPLOG);
        $logthis->info($info);
        echo $result['msg'];
    }

    public function ongoing_pickup_ready_for_pickup() {
        $this->load->model('orderdb');
        $this->load->library('infobip');
        $this->load->model('userdb');

        // validate admin roles
        $submenu_code = 'ongoing';
        $access = $this->_check_menu_access($submenu_code, 'edit', false, false);
        if (!$access) {
            exit('Access denied - You are not authorized to access this page.');
        }

        //set variable from input
        $order_status = $this->config->item('order')['status'];
        $delivery_type = $this->config->item('order')['delivery_type'];
        $uor_id = $this->input->post('uor_id');
        $result = array();
        $info = "";

        //validate order detail
        $order_detail = $this->orderdb->get_order($uor_id);
        if(empty($order_detail)) {
            $result['msg'] = 'Invalid Order ID.';
            $info = "Update Order Status by {$this->arr_admin[$this->_get_user_id()]}. {$result['msg']}";
        } else if($order_detail->uor_status == $order_status['in_process'] && $order_detail->uor_delivery_type == $delivery_type['pickup']){
            // update order status
            $data = [
                'uor_status' => $order_status['ready_for_pickup'],
                'updated_by' => $this->_get_user_id(),
                'updated_date' => date('Y-m-d H:i:s')
            ];

            $this->orderdb->update_order($uor_id, $data);
            $result['msg'] = "Success";
            $info .= ", Update Order ID {$uor_id} Status to \"Ready For Pickup\" by {$this->arr_admin[$this->_get_user_id()]}. {$result['msg']}";

        } else {
            $result['msg'] = 'Update failed! Please check your order and refresh this page.';
            $info = "Order ID {$uor_id} tried to change by {$this->arr_admin[$this->_get_user_id()]}. {$result['msg']}";
        }

        // insert log
        $logthis = $this->log4php('order_pickup_ready_for_pickup', APPLOG);
        $logthis->info($info);
        echo $result['msg'];
    }

    public function ongoing_pickup_completed()
    {
        $this->load->model('orderdb');
        $this->load->library('infobip');
        $this->load->library('pushnotification');
        $this->load->model('authdb');

        // validate admin roles
        $submenu_code = 'ongoing';
        $access = $this->_check_menu_access($submenu_code, 'edit', false, false);
        if (!$access) {
            exit('Access denied - You are not authorized to access this page.');
        }

        //set variable from input
        $order_status = $this->config->item('order')['status'];
        $delivery_type = $this->config->item('order')['delivery_type'];
        $allow_pickup_completed = $this->config->item('order')['allow_pickup_completed'];
        $uor_id = $this->input->post('uor_id');

        //validate order detail
        $order_detail = $this->orderdb->get_order($uor_id);
        if(empty($order_detail)) {
            $result['msg'] = 'Invalid Order ID.';
            $info = "Update Order Status by {$this->arr_admin[$this->_get_user_id()]}. {$result['msg']}";
        } else if(in_array($order_detail->uor_status, $allow_pickup_completed) && $order_detail->uor_delivery_type == $delivery_type['pickup']) {
            $info = '';
            // update order status
            $result = $this->orderdb->change_order_completed($uor_id, $order_status['completed'], $this->_get_user_id());

            // send email when order success
            $uor = $this->orderdb->get_order($result['uor_id']);
            $tpl_name = 'order_completed';

            // get order product list
            $where = " AND uor_id = ?";
            $order_product = $this->orderdb->getall_order_product($where, [$uor->uor_id]);

            $pin_title = 'Pickup at';
            $pin_img = UPLOAD_URL.'email/store-pin.png';
            $pin_name = $uor->st_name;
            $pin_desc = $uor->st_address;

            $tpl_data = [
                'user_name' => $uor->user_name,
                'user_email' => $uor->user_email,
                'uor_total' => $uor->uor_total,
                'uor_discount' => $uor->uor_discount,
                'uor_code' => $uor->uor_code,
                'pin_title' => $pin_title,
                'pin_img' => $pin_img,
                'pin_name' => $pin_name,
                'pin_desc' => $pin_desc,
                'uor_product' => $order_product,
                'uor_subtotal' => $uor->uor_subtotal,
                'uor_delivery_fee' => $uor->uor_delivery_fee,
                'created_date' => date('d F Y', strtotime($uor->uor_date)),
                'status' => $uor->uor_status,
                'subject' => 'Your Order on '.date('l, d F Y', strtotime($uor->uor_date)),
            ];

            // send email to customer when order complete
            $response = $this->infobip->send_email($tpl_name, $tpl_data, $uor->user_email_verified);

            // generate referral only if user_referral is found
            if ($result['ref_email'] == true) {
                $where_ref = ' AND uref_to = ? ';
                $data_ref = [$uor->user_id];
                $user_referral = $this->userdb->get_referral_custom_filter($where_ref, $data_ref);
                $uref = $this->userdb->get($user_referral->uref_from);
                $vc_data = $this->promodb->get_voucher($result['vc_id']);
                $tpl_email = 'referral_voucher';
                $tpl_voucher = [
                    'user_name' => $uref->user_name,
                    'user_email' => $uref->user_email,
                    'status' => 'referral',
                    'from' => $uor->user_name,
                    'created_date' => date('d F Y', strtotime($uor->uor_date)),
                    'exp_date' => date('d F Y', strtotime($vc_data->prm_end)),
                    'subject' => 'You get referral voucher from '.$uor->user_name
                ];
                // send email referral voucher for uref_from
                $response .= ', '.$this->infobip->send_email($tpl_email, $tpl_voucher, $uref->user_email_verified);
            }
            $info .= $response.', ';

            $token_push = $this->authdb->getall_atokenpush_notif_by_user_id($uor->user_id);

            //check token_push if exist
            if(!empty($token_push)) {
                //looping push notification
                foreach($token_push as $push_token) {
                    $user_token = isset($push_token->atoken_pushnotif) ? $push_token->atoken_pushnotif : '';

                    if(!empty($user_token)) {
                        //push notif cancel data
                        $notif_data = [
                            "push_token" => $user_token,
                            "text"       => 'Your order #'.$uor->uor_code.' has been completed',
                        ];

                        $response_push = $this->pushnotification->send_pushnotification($notif_data);
                        $info .= 'push_notification - cancelled '.$response_push;
                    }
                }
            }
            $info .= ", Update Order ID {$result['uor_id']} Status to \"".strtoupper($result['new_order_status'])."\" by {$this->arr_admin[$this->_get_user_id()]}. {$result['msg']}";

        } else {
            $result['msg'] = 'Update failed! Please check your order and refresh this page.';
            $info = "Order ID {$uor_id} tried to change by {$this->arr_admin[$this->_get_user_id()]}. {$result['msg']}";
        }

        // insert log
        $logthis = $this->log4php('order_pickup_completed', APPLOG);
        $logthis->info($info);
        echo $result['msg'];
    }

    public function ongoing_delivery_completed() {
        $this->load->model('orderdb');
        $this->load->library('infobip');
        $this->load->library('pushnotification');
        $this->load->model('authdb');

        // validate admin roles
        $submenu_code = 'ongoing';
        $access = $this->_check_menu_access($submenu_code, 'confirm', false, false);
        if (!$access) {
            exit('Access denied - You are not authorized to access this page.');
        }

        //set variable from input
        $order_status = $this->config->item('order')['status'];
        $delivery_type = $this->config->item('order')['delivery_type'];
        $uor_id = $this->input->post('uor_id');
        $info = "";

        //validate order detail
        $order_detail = $this->orderdb->get_order($uor_id);
        if(empty($order_detail)) {
            $result['msg'] = 'Invalid Order ID.';
            $info = "Update Order Status by {$this->arr_admin[$this->_get_user_id()]}. {$result['msg']}";
        } else if(($order_detail->uor_status == $order_status['in_process'] || $order_detail->uor_status == $order_status['on_delivery']) && $order_detail->uor_delivery_type == $delivery_type['delivery']) {

            $result = $this->orderdb->change_order_completed($order_detail->uor_id, $order_status['completed'], $this->_get_user_id());

            // send email when order success
            $uor = $this->orderdb->get_order($result['uor_id']);
            $tpl_name = 'order_completed';

            // get order product list
            $where = " AND uor_id = ?";
            $order_product = $this->orderdb->getall_order_product($where, [$uor->uor_id]);

            $pin_title  = 'Delivery to';
            $pin_img    = UPLOAD_URL.'email/delivery-pin.png';
            $pin_name   = $uor->uadd_title;
            $pin_desc   = $uor->uadd_street;

            $tpl_data = [
                'user_name' => $uor->user_name,
                'user_email' => $uor->user_email,
                'uor_total' => $uor->uor_total,
                'uor_discount' => $uor->uor_discount,
                'uor_code' => $uor->uor_code,
                'pin_title' => $pin_title,
                'pin_img' => $pin_img,
                'pin_name' => $pin_name,
                'pin_desc' => $pin_desc,
                'uor_product' => $order_product,
                'uor_subtotal' => $uor->uor_subtotal,
                'uor_delivery_fee' => $uor->uor_delivery_fee,
                'created_date' => date('d F Y', strtotime($uor->uor_date)),
                'status' => $uor->uor_status,
                'subject' => 'Your Order on '.date('l, d F Y', strtotime($uor->uor_date)),
            ];

            // send email to customer when order complete
            $response = $this->infobip->send_email($tpl_name, $tpl_data, $uor->user_email_verified);

            // generate referral only if user_referral is found
            if ($result['ref_email'] == true) {
                $where_ref      = ' AND uref_to = ? ';
                $data_ref       = [$uor->user_id];
                $user_referral  = $this->userdb->get_referral_custom_filter($where_ref, $data_ref);
                $uref           = $this->userdb->get($user_referral->uref_from);
                $vc_data        = $this->promodb->get_voucher($result['vc_id']);
                $tpl_email      = 'referral_voucher';
                $tpl_voucher    = [
                    'user_name' => $uref->user_name,
                    'user_email' => $uref->user_email,
                    'status' => 'referral',
                    'from' => $uor->user_name,
                    'created_date' => date('d F Y', strtotime($uor->uor_date)),
                    'exp_date' => date('d F Y', strtotime($vc_data->prm_end)),
                    'subject' => 'You get referral voucher from '.$uor->user_name
                ];
                // send email referral voucher for uref_from
                $response .= ', '.$this->infobip->send_email($tpl_email, $tpl_voucher, $uref->user_email_verified);
            }

            $info .= $response.', ';

            $token_push = $this->authdb->getall_atokenpush_notif_by_user_id($uor->user_id);

            //check token_push if exist
            if(!empty($token_push)) {
                //looping push notification
                foreach($token_push as $push_token) {
                    $user_token = isset($push_token->atoken_pushnotif) ? $push_token->atoken_pushnotif : '';

                    if(!empty($user_token)) {
                        //push notif cancel data
                        $notif_data = [
                            "push_token" => $user_token,
                            "text"       => 'Your order #'.$uor->uor_code.' has been completed',
                        ];

                        $response_push = $this->pushnotification->send_pushnotification($notif_data);
                        $info .= ', push_notification - completed '.$response_push;
                    }
                }
            }
        } else {
            $result['msg'] = 'Update failed! Please check your order and refresh this page.';
            $info = "Order ID {$uor_id} tried to change by {$this->arr_admin[$this->_get_user_id()]}. {$result['msg']}";
        }

        // insert log
        $logthis = $this->log4php('order_pickup_completed', APPLOG);
        $logthis->info($info);
        echo $result['msg'];
    }

    public function cancel_ongoing_status() {
        $this->load->model('orderdb');
        $this->load->model('danadb');
        $this->load->model('authdb');
        $this->load->library('infobip');
        $this->load->library('pushnotification');


        // validate admin roles
        $submenu_code = 'ongoing';
        $access = $this->_check_menu_access($submenu_code, 'confirm', false, false);
        if (!$access) {
            exit('Access denied - You are not authorized to access this page.');
        }

        //set variable from input
        $order_status   = $this->config->item('order')['status'];
        $payment_method = $this->config->item('order')['payment_method'];
        $payment_name   = $this->config->item('order')['payment_name'];
        $delivery_type  = $this->config->item('order')['delivery_type']['delivery'];
        $uor_id         = $this->input->post('uor_id');
        $uor_remarks    = $this->input->post('uor_remarks'); 
        $logthis        = $this->log4php('cancel_ongoing_status', APPLOG);

        //validate order detail
        $order_detail = $this->orderdb->get_order($uor_id);
        if(empty($order_detail)) {
            $result['msg'] = 'Invalid Order ID.';
            $info = "Update Order Status by {$this->arr_admin[$this->_get_user_id()]}. {$result['msg']}";
        } else if($order_detail->uor_status != $order_status['cancelled'] && $order_detail->uor_status != $order_status['completed']) {
            $info = '';
            // update order status
            $result = $this->orderdb->change_order_cancelled($uor_id, $order_status['cancelled'], $this->_get_user_id(), $uor_remarks);

            //insert to logs file
            $info .= " #0 : ".json_encode($result);

            // send email when order cancel
            $uor = $this->orderdb->get_order($result['uor_id']);

            // check if payment method not use wallet
            if($uor->pymtd_id != $payment_method['wallet']) {
                $payment = $this->orderdb->get_payment_method($uor->pymtd_id);
                // hit to ovo function
                $api = $payment->pymtd_code."api";
                $this->load->library($api);

                // call the API
                $data_res = $this->$api->void_transaction($uor); 
                $data_payment = json_decode($data_res['data'], true);

                //insert to logs file
                $info .= ", void_transaction_logging_#1 : ".json_encode($data_res);

                //validate ovo response
                if(!empty($data_payment) && $data_res['http_code'] == "200") {
                    // set data to ovo api
                    $data_req = $data_res['options'];

                    $pymt_logs = [
                        "pymtd_id"          => $uor->pymtd_id,
                        "pyhis_id"          => $uor->pyhis_id,
                        "uor_id"            => $uor->uor_id,
                        "pylog_type"        => $data_req->pylog_type,
                        "pylog_endpoint"    => $data_req->url,
                        "pylog_header"      => json_encode($data_req->header),
                        "pylog_request"     => json_encode($data_req->body),
                        "pylog_response"    => json_encode($data_payment),
                        "created_date"      => date("Y-m-d H:i:s")
                    ];
                    //insert to payment logs
                    $this->orderdb->insert_payment_logs($pymt_logs);
                }
            }

            if($uor->uor_delivery_type == $delivery_type){
                $uor->item          = $this->orderdb->get_order_item($result['uor_id']);
                $uor->item_detail   = $this->orderdb->get_order_item_detail($result['uor_id']);
                $uorcr              = $this->orderdb->get_order_courier($result['uor_id']);

                if(!empty($uorcr)){
                    $uor->booking_id    = $uorcr->booking_id;
            
                    $code = json_decode($uor->st_courier, true);
                    $code = $code["courier_code"];
                    $courier_db = $code."db";
                    $courier_api = $code."api";

                    $this->load->model($courier_db);
                    $this->load->library($courier_api);

                    $data_res = $this->$courier_api->cancel_booking($uor);
                    $data_courier = json_decode($data_res['data'], true);            

                    if(!empty($data_courier)) {
                        $data_req = $this->$courier_api->get_options($uor, 'cancel');

                        $params = [
                            $code.'_type' => 'cancel',
                            $code.'_endpoint' => $data_req->url,
                            $code.'_header' => json_encode($data_req->header),
                            $code.'_request' => json_encode($data_req->body),
                            $code.'_response' => json_encode($data_courier),
                            'created_date' => date('Y-m-d H:i:s')
                        ];
                        $this->$courier_db->insert_log($params);
                    }        
                }
            }
            
            $tpl_name = 'order_cancel_admin';

            $tpl_data = [
                'user_name' => $uor->user_name,
                'user_email' => $uor->user_email,
                'created_date' => date('d F Y', strtotime($uor->uor_date)),
                'uor_code' => $uor->uor_code,
                'status' => $uor->uor_status,
                'subject' => 'Your order #'.$uor->uor_code.' has been cancelled'
            ];

            $response = $this->infobip->send_email($tpl_name, $tpl_data, $uor->user_email_verified);
            $info .= 'Send Email '. $response;

            $token_push = $this->authdb->getall_atokenpush_notif_by_user_id($uor->user_id);

            //check token_push if exist
            if(!empty($token_push)) {
                //looping push notification
                foreach($token_push as $push_token) {
                    $user_token = isset($push_token->atoken_pushnotif) ? $push_token->atoken_pushnotif : '';

                    if(!empty($user_token)) {
                        //push notif cancel data
                        $notif_data = [
                            "push_token" => $user_token,
                            "text"       => 'Your order #'.$uor->uor_code.' has been cancelled',
                        ];

                        $response_push = $this->pushnotification->send_pushnotification($notif_data);
                        $info .= 'push_notification - cancelled '.$response_push;
                    }
                }
            }
            $info .= ", Update Order ID {$result['uor_id']} Status to \"Cancelled\" by {$this->arr_admin[$this->_get_user_id()]}. {$result['msg']}";

        } else {
            $result['msg'] = 'Update failed! Please check your order and refresh this page.';
            $info = "Order ID {$uor_id} tried to change by {$this->arr_admin[$this->_get_user_id()]}. {$result['msg']}";
        }

        // insert log
        $logthis->info($info);
        echo $result['msg'];
    }

    public function ongoing_rebooking()
    {
        $this->load->model('orderdb');
        $this->load->model('courierdb');
        $this->load->model('userdb');

        // validate admin roles
        $submenu_code = 'ongoing';
        $access = $this->_check_menu_access($submenu_code, 'edit', false, false);
        if (!$access) {
            exit('Access denied - You are not authorized to access this page.');
        }

        $uor_id = $this->input->post('uor_id');

        $msg = 'Success';
        // validate order and its status
        if (!$uor_id) {
            $msg = 'Missing Order ID or Update Action!';
            $info = "Rebooking Courier by {$this->arr_admin[$this->_get_user_id()]}.' '.$msg}";
        } else {
            // update order status
            $this->hit_courier_api($uor_id);
            $info = "Rebooking Courier For Order ID {$uor_id} by {$this->arr_admin[$this->_get_user_id()]}.' '.$msg}";
        }

        // insert log
        $logthis = $this->log4php('order_rebooking', APPLOG);
        $logthis->info($info);
        echo $msg;
    }

    public function ongoing_track_add()
    {
        $this->load->model('orderdb');
        // validate admin roles
        $submenu_code = 'ongoing';
        $access = $this->_check_menu_access($submenu_code, 'edit', false, false);
        if (!$access) {
            exit('Access denied - You are not authorized to access this page.');
        }

        $uor_id     = $this->input->post('uor_id');
        $uortr_date = $this->input->post('uortr_date');
        $uortr_text = $this->input->post('uortr_text');
        $uor_condition = $this->config->item('order')['status'];
        $msg = "Success";

        $order = $this->orderdb->get_order($uor_id);
        // validate uor track data
        $info = "Add Order Track by {$this->arr_admin[$this->_get_user_id()]}. {$msg}";
        if (!$uor_id || !$uortr_date || !$uortr_text) {
            $msg = 'Missing Order ID or Track Data or Track Info!';
        } elseif ($order->uor_status == $uor_condition['completed'] || $order->uor_status == $uor_condition['cancelled']){
            $msg = 'This Order Already '.$order->uor_status ;
        } else {
            // validate if order exists
            if(!$this->orderdb->get_order($uor_id)) {
                $msg = 'Order Not Found!';
            } else {
                $data = [
                    'uor_id' => $uor_id,
                    'uortr_date' => $uortr_date,
                    'uortr_text' => $uortr_text,
                    'created_by' => $this->_get_user_id(),
                    'created_date' => date('Y-m-d H:i:s')
                ];
                // create new order track
                if (!$this->orderdb->insert_order_track($data)) {
                    $msg = 'Add Order Track failed!';
                }
            }
            $info = "Add Order Track for Order ID {$uor_id} by {$this->arr_admin[$this->_get_user_id()]}. {$msg}";
        }

        //insert log
        $logthis = $this->log4php('order_track_add', APPLOG);
        $logthis->info($info);
        echo $msg;
    }

    public function ongoing_track_delete()
    {
        $this->load->model('orderdb');
        // validate admin roles
        $submenu_code = 'ongoing';
        $access = $this->_check_menu_access($submenu_code, 'edit', false, false);
        if (!$access) {
            exit('Access denied - You are not authorized to access this page.');
        }

        $uortr_id = $this->input->post('uortr_id');
        $msg = 'Success';

        // validate ID and order track exists
        if (!$uortr_id || !$this->orderdb->get_order_track($uortr_id)) {
            $msg = 'Missing Order Track ID or Order Track not found!';
            $info = "Delete Order Track by {$this->arr_admin[$this->_get_user_id()]}. {$msg}";
        } else {
            // delete order track
            if(!$this->orderdb->delete_order_track($uortr_id)) {
                $msg = 'Delete Order Track failed!';
            }
            $info = "Delete Order Track ID {$uortr_id} by {$this->arr_admin[$this->_get_user_id()]}. {$msg}";
        }

        // insert log
        $logthis = $this->log4php('order_track_delete', APPLOG);
        $logthis->info($info);
        echo $msg;
    }

    // count orders which type is delivery and status is paid
    public function order_count_unprocessed_delivery()
    {
        $this->load->model('orderdb');
        $this->load->model('admindb');
        $where = ' AND uor_delivery_type = ? AND uor_status = ?';
        $data = [
            $this->config->item('order')['delivery_type']['delivery'],
            $this->config->item('order')['status']['paid']
        ];

        //set permission st_id for Ongoing menu
        $user = $this->admindb->get_admin($this->_get_user_id());
        if(!empty($user) && $user->st_id > 0) {
            $where .= "AND st.st_id = ?";
            array_push($data, $user->st_id);
        }
        $order = $this->orderdb->getall_order($where, $data);

        echo count((array)$order);
    }
    //=== END ORDER

    public function get_user_is_active() {
        $this->load->model('userdb');

        $user_email = $this->input->get('term');
        $phone      = clean_phone($user_email);


        $where  = ' AND user.user_status = ? AND user.user_email LIKE ?';
        $data   = ['active', '%'. $user_email .'%'];

        if(!empty($phone)) {
            $where .= ' OR user.user_phone LIKE ?';
            array_push($data, '%'. $phone .'%');
        }

        $user = $this->userdb->getall_user($where, $data, '', 30);

        echo json_encode($user);
    }

    public function get_all_store() {
        $this->load->model('storedb');
        $store_name = $this->input->get('term');


        $where = ' AND st_name LIKE ?';
        $data  = ['%'. $store_name .'%'];

        $user = $this->storedb->getall($where, $data);

        echo json_encode($user);
    }


    public function delete_voucher_default()
    {
        $this->load->model('promodb');
        $submenu_code = 'voucher_default';
        $access = $this->_check_menu_access( $submenu_code, 'delete' , false, false);
        if(!$access) {
            exit('Access denied - You are not authorized to access this page.');
        }

        $vsdef_id = $this->input->post('vsdef_id');

        //set log variable
        $logthis    = $this->log4php('voucher_default_delete', APPLOG);
        $info       = 'Delete VOUCHER DEFAULT ID '. $vsdef_id . ' by '. $this->arr_admin[$this->_get_user_id()] .'. ';
        $msg        = '';

        $detail     = $this->promodb->get_voucher_default($vsdef_id);

        if (empty($detail)) {
            $msg = 'Invalid VOUCHER DEFAULT ID';
        }else{
            $ret = $this->promodb->delete_voucher_default($vsdef_id);
            if ($ret) {
                $msg = 'Success';
            } else {
                $msg = 'Delete Data Failed!';
            }
        }

        //send output
        $info .= $msg;
        $logthis->info($info);
        echo $msg;
    }

    //=== START BANNER
    public function delete_banner()
    {
        $submenu_code   = 'banner';
        $access         = $this->_check_menu_access( $submenu_code, 'delete' , false, false);
        if(!$access) {
            exit('Access denied - You are not authorized to access this page.');
        }

        $this->load->model('bannerdb');
        $ban_id     = $this->input->post('ban_id');
        $ban_ord    = $this->input->post('ban_order');

        //set log variable
        $logthis    = $this->log4php('delete_banner', APPLOG);
        $info       = 'Delete BANNER ID '. $ban_id . ' by '. $this->arr_admin[$this->_get_user_id()] .'. ';
        $msg        = '';
        $detail     = $this->bannerdb->get_banner($ban_id);

        //cek id invalid atau tidak
        if (empty($detail)) {
            $msg = 'Invalid BANNER ID';
        }else{
            $result = $this->bannerdb->delete_banner($ban_id);
            if ($result) {
                $msg = 'Success';
                $this->bannerdb->sort_banner_order($ban_id, $ban_ord);
            } else {
                $msg = 'Delete Data Failed!';
            }
        }

        //send output
        $info .= $msg;
        $logthis->info($info);
        echo $msg;
    }

    public function banner_status_update()
    {
        // validate admin roles
        $submenu_code   = 'banner';
        $access         = $this->_check_menu_access($submenu_code, 'edit', false, false);
        if (!$access) {
            exit('Access denied - You are not authorized to access this page.');
        }

        $this->load->model('bannerdb');
        $ban_id         = $this->input->post('ban_id');
        $new_status     = $this->input->post('status');
        $allowed_status = $this->config->item('banner')['status'];
        $msg            = 'Success';

        if (empty($ban_id) || empty($new_status) || !in_array($new_status, $allowed_status)) {
            $msg = 'Missing Banner ID or Status!';
        } else {
            if (!$this->bannerdb->update_banner($ban_id, ['ban_status' => $new_status])) {
                $msg = 'Update Banner Status failed!';
            }
        }

        // insert log
        $info       = "Update Banner ID {$ban_id} status to \"".strtoupper($new_status)."\" by {$this->arr_admin[$this->_get_user_id()]}. {$msg}";
        $logthis    = $this->log4php('banner_status_update', APPLOG);
        $logthis->info($info);
        echo $msg;
    }
    //=== END BANNER

    public function bulk_process() {
        // validate admin roles
        $submenu_code   = 'ongoing';
        $access = $this->_check_menu_access($submenu_code, 'bulk_process', false, false);
        if (!$access) {
            exit('Access denied - You are not authorized to access this page.');
        }

        $this->load->model('orderdb');
        $order_status   = $this->config->item('order')['status'];
        $delivery_type  = $this->config->item('order')['delivery_type'];
        $bulk_process   = (int) $this->input->post('bulk_process'); // total bulk process
        $st_id          = (int) $this->input->post('st_id'); //st_id is store id
        $date           = $this->input->post('date'); //date
        $info = "";
        $uor_data = [];
        $uor_data['msg']  = "success";

        //check validate if user have permission store
        $user           = $this->admindb->get_admin($this->_get_user_id());
        if($user->st_id > 0 && $user->st_id != $st_id){
            // jika user accesss store nya lebih dari 0 dan st_id nya tidak sama dengan store permissionnya, langsung reject.
            $uor_data['msg'] = "You don't have permission to process this store!";
        } else {
            // process ketika st_id sama dengan permission store dari si user
            $search_where   = ' AND DATE(uor.uor_date) = ? AND uor.uor_status = ? AND uor.uor_delivery_type = ? ';
            $search_data    = [$date, $order_status['paid'], $delivery_type['delivery']];
            $order_data     = ' uor.uor_date ASC ';

            //jika store id lebih dari 0, maka add store id di where query
            if($st_id > 0) {
                $search_where .= " AND st.st_id = ? ";
                array_push($search_data, $st_id);
            }

            //get all order untuk bulk process
            $getuor_bulk = $this->orderdb->getall_order($search_where, $search_data, $order_data, $bulk_process);
            // looping untuk bulk process
            $uor = []; 
            foreach($getuor_bulk as $bulk){
                // update order status
                $data = [
                    'uor_status'    => $order_status['in_process'],
                    'updated_by'    => $this->_get_user_id(),
                    'updated_date'  => date('Y-m-d H:i:s')
                ];
                $this->orderdb->update_order($bulk->uor_id, $data);

                //hit to gosend api after update
                $this->hit_courier_api($bulk->uor_id);
                $uor[] = $bulk->uor_id;
                $info .= ", bulk process". $bulk->uor_id;
            }

            $uor_data['list'] = base64_encode(json_encode($uor));
        }
        // insert log
        $logthis = $this->log4php('bulk_process', APPLOG);
        $logthis->info($info);
        echo json_encode($uor_data);
    }

    //=== START PAYMENT METHOD
    public function delete_payment_method()
    {
        $submenu_code   = 'payment_method';
        $access         = $this->_check_menu_access( $submenu_code, 'delete' , false, false);
        if(!$access) {
            exit('Access denied - You are not authorized to access this page.');
        }

        $this->load->model('orderdb');
        $pymtd_id     = $this->input->post('pymtd_id');

        //set log variable
        $logthis    = $this->log4php('delete_payment_method', APPLOG);
        $info       = 'Delete Payment Method ID '. $pymtd_id . ' by '. $this->arr_admin[$this->_get_user_id()] .'. ';
        $msg        = '';
        $detail     = $this->orderdb->get_payment_method($pymtd_id);

        //cek id invalid atau tidak
        if (empty($detail)) {
            $msg = 'Invalid Payment Method ID';
        }else{
            $result = $this->orderdb->delete_payment_method($pymtd_id);
            if ($result) {
                $msg = 'Success';
            } else {
                $msg = 'Delete Data Failed!';
            }
        }

        //send output
        $info .= $msg;
        $logthis->info($info);
        echo $msg;
    }

    public function payment_method_status_update()
    {
        // validate admin roles
        $submenu_code   = 'payment_method';
        $access         = $this->_check_menu_access($submenu_code, 'edit', false, false);
        if (!$access) {
            exit('Access denied - You are not authorized to access this page.');
        }

        $this->load->model('orderdb');
        $pymtd_id       = $this->input->post('pymtd_id');
        $new_status     = $this->input->post('status');
        $allowed_status = $this->config->item('payment_method')['status'];
        $msg            = 'Success';

        //cek id or status empty or not and new status in array or not
        if (empty($pymtd_id) || empty($new_status) || !in_array($new_status, $allowed_status)) {
            $msg = 'Missing Paymentd Method ID or Status!';
        } else {
            if (!$this->orderdb->update_payment_method($pymtd_id, ['pymtd_status' => $new_status])) {
                $msg = 'Update Payment Method Status failed!';
            }
        }

        // insert log
        $info       = "Update Paymentd Method ID {$pymtd_id} status to \"".strtoupper($new_status)."\" by {$this->arr_admin[$this->_get_user_id()]}. {$msg}";
        $logthis    = $this->log4php('payment_method_status_update', APPLOG);
        $logthis->info($info);
        echo $msg;
    }
    //=== END PAYMENT METHOD
    //=== START PRODUCT COGS
    public function product_cogs_delete()
    {
        $submenu_code   = 'product_cogs';
        $access         = $this->_check_menu_access( $submenu_code, 'delete' , false, false);
        if(!$access) {
            exit('Access denied - You are not authorized to access this page.');
        }

        $this->load->model('productdb');
        $product_cogs   = $this->input->post('pdcogs_id');

        //set log variable
        $logthis    = $this->log4php('product_cogs_delete', APPLOG);
        $info       = 'Delete Product Cogs ID '. $product_cogs . ' by '. $this->arr_admin[$this->_get_user_id()] .'. ';
        $msg        = '';
        $detail     = $this->productdb->get_product_cogs($product_cogs);

        //cek id invalid atau tidak
        if (empty($detail)) {
            $msg = 'Invalid Product Cogs ID';
        }else{
            $result = $this->productdb->delete_product_cogs($product_cogs);
            if ($result) {
                $msg = 'Success';
            } else {
                $msg = 'Delete Data Failed!';
            }
        }

        //send output
        $info .= $msg;
        $logthis->info($info);
        echo $msg;
    }
    //=== END PRODUCT COGS

    //=== START STORE OPERATIONAL
    public function delete_store_operational()
    {
        $submenu_code   = 'store_operational';
        $access         = $this->_check_menu_access( $submenu_code, 'delete' , false, false);
        if(!$access) {
            exit('Access denied - You are not authorized to access this page.');
        }

        $this->load->model('storedb');
        $sto_id     = $this->input->post('sto_id');

        //set log variable
        $logthis    = $this->log4php('delete_store_operational', APPLOG);
        $info       = 'Delete Store Operational ID '. $sto_id . ' by '. $this->arr_admin[$this->_get_user_id()] .'. ';
        $msg        = '';
        $detail     = $this->storedb->get_store_opt($sto_id);

        //cek id invalid atau tidak
        if (empty($detail)) {
            $msg = 'Invalid Store Operational ID';
        }else{
            $result = $this->storedb->delete_store_opt($sto_id);
            if ($result) {
                $msg = 'Success';
            } else {
                $msg = 'Delete Data Failed!';
            }
        }

        //send output
        $info .= $msg;
        $logthis->info($info);
        echo $msg;
    }

    public function store_opt_status_update()
    {
        // validate admin roles
        $submenu_code   = 'store_operational';
        $access         = $this->_check_menu_access($submenu_code, 'edit', false, false);
        if (!$access) {
            exit('Access denied - You are not authorized to access this page.');
        }

        $this->load->model('storedb');
        $sto_id         = $this->input->post('sto_id');
        $new_status     = $this->input->post('status');
        $allowed_status = $this->config->item('store_operational')['stopt_status'];
        $msg            = 'Success';

        if (empty($sto_id) || empty($new_status) || !in_array($new_status, $allowed_status)) {
            $msg = 'Missing Store Operational ID or Status!';
        } else {
            if (!$this->storedb->update_store_opt($sto_id, ['sto_status' => $new_status])) {
                $msg = 'Update Store Operational Status failed!';
            }
        }

        // insert log
        $info       = "Update Store Operational ID {$sto_id} status to \"".strtoupper($new_status)."\" by {$this->arr_admin[$this->_get_user_id()]}. {$msg}";
        $logthis    = $this->log4php('store_opt_status_update', APPLOG);
        $logthis->info($info);
        echo $msg;
    }
    //=== END STORE OPERATIONAL

    //=== START ROLE
    public function copy_permission_to_all_user()
    {
        // validate admin roles
        $submenu_code   = 'role';
        $access         = $this->_check_menu_access($submenu_code, 'copy_to_all', false, false);
        if (!$access) {
            exit('Access denied - You are not authorized to access this page.');
        }

        $this->load->model('admindb');
        $role_id        = $this->input->post('role_id');

        //set log variable
        $logthis    = $this->log4php('copy_permission_to_all_user', APPLOG);
        $info       = 'Copy permission Role ID '. $role_id . ' to All User by '. $this->arr_admin[$this->_get_user_id()] .'. ';
        $msg        = '';
        $detail     = $this->admindb->get_role($role_id);

        //cek id invalid atau tidak
        if (empty($detail)) {
            $msg = 'Invalid Role ID';
        }else{
            $result = $this->admindb->insert_admin_menu_all_user($role_id);
            if ($result) {
                $msg = 'Success';
            } else {
                $msg = 'Copy Permission to All User Failed!';
            }
        }

        //send output
        $info .= $msg;
        $logthis->info($info);
        echo $msg;
    }
    //=== END ROLE    
    //=== END BANNER

    //>> START SUBS PLAN <<
    public function subs_plan_delete()
    {
        $submenu_code   = 'subs_plan';
        $access         = $this->_check_menu_access( $submenu_code, 'delete' , false, false);
        if(!$access) {
            exit('Access denied - You are not authorized to access this page.');
        }

        $this->load->model('subscriptiondb');
        $subsplan_id    = $this->input->post('subsplan_id');

        //set log variable
        $logthis        = $this->log4php('subs_plan_delete', APPLOG);
        $info           = 'Delete Subscription Plan ID '. $subsplan_id . ' by '. $this->arr_admin[$this->_get_user_id()] .'. ';
        $msg            = '';
        $detail         = $this->subscriptiondb->get_subsplan($subsplan_id);

        //cek id invalid atau tidak
        if (empty($detail)) {
            $msg        = 'Invalid Subscription Plan ID';
        }else{
            $result     = $this->subscriptiondb->delete_subs_plan($subsplan_id);
            if ($result) {
                $msg    = 'Success';
            } else {
                $msg    = 'Delete Data Failed!';
            }
        }

        //send output
        $info          .= $msg;
        $logthis->info($info);
        echo $msg;
    }
    //>> END SUBS PLAN <<
    
    //>>START USER DOWNLOAD<<
    public function user_download_delete()
    {
        $submenu_code   = 'user_download';
        $access         = $this->_check_menu_access( $submenu_code, 'delete' , false, false);
        if(!$access) {
            exit('Access denied - You are not authorized to access this page.');
        }

        $this->load->model('user_downloaddb');
        $usrd_id    = $this->input->post('usrd_id');

        //set log variable
        $logthis    = $this->log4php('user_download_delete', APPLOG);
        $info       = 'Delete User Download ID '. $usrd_id . ' by '. $this->arr_admin[$this->_get_user_id()] .'. ';
        $msg        = '';
        $detail     = $this->user_downloaddb->get_user_download($usrd_id);

        //cek id invalid atau tidak
        if (empty($detail)) {
            $msg = 'Invalid User Download ID';
        }else{
            $result = $this->user_downloaddb->delete_user_download($usrd_id);
            if ($result) {
                $msg = 'Success';
            } else {
                $msg = 'Delete Data Failed!';
            }
        }

        //send output
        $info .= $msg;
        $logthis->info($info);
        echo $msg;
    }
    //>>END USER DOWNLOAD<<

    //>>START SUBS ORDER<<
    public function get_user_by_name_is_active() {
        $this->load->model('userdb');

        $user_name  = $this->input->get('term');


        $where      = ' AND user.user_status = ? AND user.user_name LIKE ?';
        $data       = ['active', '%'. $user_name .'%'];

        $user       = $this->userdb->getall_user($where, $data, '', 30);

        echo json_encode($user);
    }

    public function cancel_subsorder_status() {
        $this->load->model('subscriptiondb');
        $this->load->model('orderdb');
        $this->load->model('authdb');
        $this->load->library('infobip');
        $this->load->library('pushnotification');

        // validate admin roles
        $submenu_code = 'subs_order';
        $access = $this->_check_menu_access($submenu_code, 'cancel', false, false);
        if (!$access) {
            exit('Access denied - You are not authorized to access this page.');
        }

        //set variable from input
        $subsorder_status   = $this->config->item('subs_order')['status'];
        $payment_method     = $this->config->item('subs_order')['payment_method'];
        $payment_name       = $this->config->item('subs_order')['payment_name'];
        $subsorder_id       = $this->input->post('subsorder_id');
        $subsorder_remarks  = $this->input->post('subsorder_remarks');
        $logthis            = $this->log4php('cancel_subsorder_status', APPLOG);

        //validate order detail
        $subsorder_detail = $this->subscriptiondb->get_subs_order($subsorder_id);

        if(empty($subsorder_detail)) {
            $result['msg'] = 'Invalid Subs Order ID.';
            $info = "Update Subs Order Status by {$this->arr_admin[$this->_get_user_id()]}. {$result['msg']}";
        } else if($subsorder_detail->subsorder_status != $subsorder_status['cancelled']) {
            $info = '';
            // update order status
            $result = $this->subscriptiondb->change_subsorder_cancelled($subsorder_id, $subsorder_status['cancelled'], $this->_get_user_id(), $subsorder_remarks);

            //insert to logs file
            $info .= " #0 : ".json_encode($result);

            // send email when order cancel
            $subsorder = $this->subscriptiondb->get_subs_order($result['subsorder_id']);

            // check if payment method not use wallet and status waiting_for_payment
            if($subsorder->pymtd_id != $payment_method['wallet']  && $subsorder_status['waiting_for_payment']) {
                $payment = $this->orderdb->get_payment_method($subsorder->pymtd_id);
                // hit to ovo function
                $api = $payment->pymtd_code."api";
                $this->load->library($api);

                // redeclar uor_code, uor_total, dan pyhis_data untuk void_transaction agar tidak membuat function baru
                $subs_order             = new stdClass();
                $subs_order->uor_code   = $subsorder->subsorder_code;
                $subs_order->uor_total  = $subsorder->subsorder_total;
                $subs_order->pyhis_data = $subsorder->sop_data;
                
                // call the API
                $data_res = $this->$api->void_transaction($subs_order);
                $data_payment = json_decode($data_res['data'], true);

                //insert to logs file
                $info .= ", #1 : ".json_encode($data_res);

                //validate ovo response
                if(!empty($data_payment) && $data_res['http_code'] == "200") {
                    // set data to ovo api
                    $data_req = $data_res['options'];

                    $subs_pymt_logs = [
                        "pymtd_id"          => $subsorder->pymtd_id,
                        "sop_id"            => $subsorder->sop_id,
                        "subsorder_id"      => $subsorder->subsorder_id,
                        "splog_type"        => $data_req->pylog_type,
                        "splog_endpoint"    => $data_req->url,
                        "splog_header"      => json_encode($data_req->header),
                        "splog_request"     => json_encode($data_req->body),
                        "splog_response"    => json_encode($data_payment),
                        "created_date"      => date("Y-m-d H:i:s")
                    ];
                    //insert to subs payment logs
                    $this->subscriptiondb->insert_subs_payment_logs($subs_pymt_logs);
                }
            }

            //>>> kodingan email ini jangan dihapus, kemungkinan akan digunakan sewaktu-waktu <<<
            // $tpl_name = 'subsorder_cancel_admin'; 

            // $tpl_data = [
            //     'user_name' => $subsorder->user_name,
            //     'user_email' => $subsorder->user_email,
            //     'created_date' => date('d F Y', strtotime($subsorder->subsorder_date)),
            //     'subsorder_code' => $subsorder->subsorder_code,
            //     'status' => $subsorder->subsorder_status,
            //     'subject' => 'Your order #'.$subsorder->subsorder_code.' has been cancelled'
            // ];

            // $response = $this->infobip->send_email($tpl_name, $tpl_data, $subsorder->user_email_verified);
            // $info .= 'Send Email '. $response;

            $token_push = $this->authdb->getall_atokenpush_notif_by_user_id($subsorder->user_id);
            
            //check token_push if exist
            if(!empty($token_push)) {
                //looping push notification
                foreach($token_push as $push_token) {
                    $user_token = isset($push_token->atoken_pushnotif) ? $push_token->atoken_pushnotif : '';

                    if(!empty($user_token)) {
                        //push notif cancel data
                        $notif_data = [
                            "push_token" => $user_token,
                            "text"       => 'Your subs order #'.$subsorder->subsorder_code.' has been cancelled',
                        ];

                        $response_push = $this->pushnotification->send_pushnotification($notif_data);
                        $info .= 'push_notification - cancelled '.$response_push;
                    }
                }
            }
            $info .= ", Update Subs Order ID {$result['subsorder_id']} Status to \"Cancelled\" by {$this->arr_admin[$this->_get_user_id()]}. {$result['msg']}";

        } else {
            $result['msg'] = 'Update failed! Please check your subs order and refresh this page.';
            $info = "Subs Order ID {$subsorder_id} tried to change by {$this->arr_admin[$this->_get_user_id()]}. {$result['msg']}";
        }

        // insert log
        $logthis->info($info);
        echo $result['msg'];
    }

    //>>END SUBS ORDER<<

    //>>START STORE IMAGE<<
    public function store_img_status_update()
    {
        // validate admin roles
        $submenu_code   = 'store_image';
        $access         = $this->_check_menu_access($submenu_code, 'edit', false, false);
        if (!$access) {
            exit('Access denied - You are not authorized to access this page.');
        }

        $this->load->model('storedb');
        $sti_id         = $this->input->post('sti_id');
        $new_status     = $this->input->post('sti_status');
        $allowed_status = $this->config->item('store_image')['status'];
        $msg            = 'Success';
        
        if (empty($sti_id) || empty($new_status) || !in_array($new_status, $allowed_status)) {
            $msg = 'Missing Store Image ID or Status!';
        } else {
            if (!$this->storedb->update_store_img($sti_id, ['sti_status' => $new_status])) {
                $msg = 'Update Store Image Status failed!';
            }
        }

        // insert log
        $info       = "Update Store Image ID {$sti_id} status to \"".strtoupper($new_status)."\" by {$this->arr_admin[$this->_get_user_id()]}. {$msg}";
        $logthis    = $this->log4php('store_img_status_update', APPLOG);
        $logthis->info($info);
        echo $msg;
    }

    //>>END STORE IMAGE<<

    //>>START BANNER CATALOGUE<<
    public function banner_catalogue_status_update()
    {
        // validate admin roles
        $submenu_code   = 'banner_catalogue';
        $access         = $this->_check_menu_access($submenu_code, 'edit', false, false);
        if (!$access) {
            exit('Access denied - You are not authorized to access this page.');
        }

        $this->load->model('bannerdb');
        $ban_id         = $this->input->post('banc_id');
        $new_status     = $this->input->post('status');
        $allowed_status = $this->config->item('banner')['status'];
        $msg            = 'Success';

        if (empty($ban_id) || empty($new_status) || !in_array($new_status, $allowed_status)) {
            $msg = 'Missing Banner Catalogue ID or Status!';
        } else {
            if (!$this->bannerdb->update_banner_catalogue($ban_id, ['banc_status' => $new_status])) {
                $msg = 'Update Banner Catalogue Status failed!';
            }
        }

        // insert log
        $info       = "Update Banner Catalogue ID {$ban_id} status to \"".strtoupper($new_status)."\" by {$this->arr_admin[$this->_get_user_id()]}. {$msg}";
        $logthis    = $this->log4php('banner_catalogue_status_update', APPLOG);
        $logthis->info($info);
        echo $msg;
    }
    //>>END BANNER CATALOGUE<<
    
    //>>START VOUCHER EMPLOYEE<<
    public function voucher_employee_delete()
    {
        $submenu_code   = 'voucher_employee';
        $access         = $this->_check_menu_access( $submenu_code, 'delete' , false, false);
        if(!$access) {
            exit('Access denied - You are not authorized to access this page.');
        }

        $this->load->model('promodb');
        $vce_id   = $this->input->post('vce_id');

        //set log variable
        $logthis    = $this->log4php('voucher_employee_delete', APPLOG);
        $info       = 'Delete Voucher Employee ID '. $vce_id . ' by '. $this->arr_admin[$this->_get_user_id()] .'. ';
        $msg        = '';
        $detail     = $this->promodb->get_voucher_employee($vce_id);

        //cek id invalid atau tidak
        if (empty($detail)) {
            $msg = 'Invalid Voucher Employee ID';
        }else{
            $result = $this->promodb->delete_voucher_employee($vce_id);
            if ($result) {
                $msg = 'Success';
            } else {
                $msg = 'Delete Data Failed!';
            }
        }

        //send output
        $info .= $msg;
        $logthis->info($info);
        echo $msg;
    }
    //>>END USER DOWNLOAD<<

    //=== START STORE CONFIG
    public function delete_store_config()
    {
        $submenu_code   = 'store_config';
        $access         = $this->_check_menu_access($submenu_code, 'delete' , false, false);
        if(!$access) {
            exit('Access denied - You are not authorized to access this page.');
        }

        $this->load->model('storedb');
        $stcf_id = $this->input->post('stcf_id');

        //set log variable
        $logthis    = $this->log4php('delete_store_config', APPLOG);
        $info       = 'Delete Store Config ID '. $stcf_id . ' by '. $this->arr_admin[$this->_get_user_id()] .'. ';
        $msg        = '';
        $detail     = $this->storedb->get_store_config($stcf_id);

        //cek id invalid atau tidak
        if (empty($detail)) {
            $msg = 'Invalid Store Config ID';
        } else {
            $result = $this->storedb->delete_store_config($stcf_id);
            if ($result) {
                $msg = 'Success';
            } else {
                $msg = 'Delete Data Failed!';
            }
        }

        //send output
        $info .= $msg;
        $logthis->info($info);
        echo $msg;
    }
    //=== END STORE CONFIG
}
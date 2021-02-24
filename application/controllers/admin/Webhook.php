<?php
defined('BASEPATH') OR exit('No direct script access allowed');


class Webhook extends MY_Service {

    function __construct()
	{
        parent::__construct();

        $this->load->model('orderdb');
        $this->load->model('danadb');
        $this->load->model('courierdb');
        $this->load->model('gosenddb');
        $this->load->model('userdb');
        $this->load->model('promodb');
        $this->load->model('authdb');
        $this->load->model('storedb');
        $this->load->library('infobip');
        $this->load->library('gosendapi');
        $this->load->library('pushnotification');
	}

    public function index()
    {
        show_404();
    }

    public function ping()
    {
        echo 'success';
    }

    public function gosend()
    {
        $headers = $this->input->request_headers();
        $body = json_decode(file_get_contents('php://input'),true);
        $msg = 'OK';

        //log request ke file
        $logthis = $this->log4php('webhook_gosend', APPLOG);
        $info = array($body, $headers);
        $logthis->info(json_encode($info));

        $order_status = $this->config->item('order')['status'];
        $payment_method = $this->config->item('order')['payment_method'];
        $gosend_status = $this->config->item('gosend')['status'];
        $gosend_type = $this->config->item('gosend')['type'];
        $track_msg = $this->config->item('order_track')['message'];
        $uor_remarks = $this->config->item('uor_remarks')['note'];

        $status_code = 200;

        //header authorization tampung dulu di variable supaya tidak error index
        $authorization = (isset($headers['Authorization']) ? $headers['Authorization'] : '');

        if ($authorization !== GOSEND_WEBHOOK_TOKEN){
            $msg = 'Invalid Webhook Token';
            $status_code = 500;
        }else{
            // insert data into gosend_logs
            $params = [
                'gosend_type' => '',
                'gosend_endpoint' => '/webhook/gosend',
                'gosend_header' => json_encode($headers),
                'gosend_request' => '',
                'gosend_response' => json_encode($body),
                'created_date' => date('Y-m-d H:i:s')
            ];
            $gosend_id = $this->gosenddb->insert_log($params);

            //set variable
            $entity_id = trim($body['entity_id']);
            $status = trim($body['status']);
            $courier_name = trim($body['driver_name']);
            $courier_phone = trim($body['driver_phone']);
            $url_live = trim($body['live_tracking_url']);

            //get data user order gosend
            $data_book = $this->courierdb->get_order_courier_booking($entity_id);

            //validate entity_id (orderNo) does exist or not in user_order_gosend
            if(!empty($data_book)){
                $detail = $this->orderdb->get_order($data_book->uor_id);
                // validate entity_id (orderNo) & status
                if($status == $gosend_status['out_for_pickup'] && $detail->uor_status == $order_status['in_process']){
                    //set data to update uortrack, uorgsnd & gosend_logs
                    $data = [
                        'update_order_status' => "",
                        'uor_id' => $detail->uor_id,
                        'entity_id' => $entity_id,
                        'uorcr_driver_name' => $courier_name,
                        'uorcr_driver_phone' => $courier_phone,
                        'uorcr_url' => $url_live,
                        'gosend_id' => $gosend_id,
                        'gosend_type' => $gosend_type['pickup'],
                        'uorcr_id' => $data_book->uorcr_id,
                        'uorcr_stat' => $gosend_status['out_for_pickup'],
                        'message' => $track_msg[2],
                        'price' => $body['price']
                    ];
                    $this->updateData($data);

                    $token_push = $this->authdb->getall_atokenpush_notif_by_user_id($detail->user_id);

                    //check token_push if exist
                    if(!empty($token_push)) {
                        //looping push notification
                        foreach($token_push as $push_token) {
                            $user_token = isset($push_token->atoken_pushnotif) ? $push_token->atoken_pushnotif : '';

                            if(!empty($user_token)) {
                                //push notif cancel data
                                $notif_data = [
                                    "push_token" => $user_token,
                                    "text"       => 'We got a driver for you. Order No. '.$detail->uor_code,
                                ];

                                $this->pushnotification->send_pushnotification($notif_data);
                            }
                        }
                    }
                }elseif($status == $gosend_status['out_for_delivery'] && $detail->uor_status == $order_status['in_process']) {
                    //set order status to on_delivery
                    $data = [
                        'update_order_status' => $order_status["on_delivery"],
                        'uor_id' => $detail->uor_id,
                        'entity_id' => $entity_id,
                        'uorcr_driver_name' => $courier_name,
                        'uorcr_driver_phone' => $courier_phone,
                        'uorcr_url' => $url_live,
                        'gosend_id' => $gosend_id,
                        'gosend_type' => $gosend_type['drop'],
                        'uorcr_id' => $data_book->uorcr_id,
                        'uorcr_stat' => $gosend_status['out_for_delivery'],
                        'message' => $track_msg[3],
                        'price' => $body['price']
                    ];
                    $this->updateData($data);
                } elseif ($status == $gosend_status['delivered']  && !in_array($detail->uor_status, array($order_status["completed"], $order_status["cancelled"])))  {
                    //set order status to completed
                    $data = [
                        'update_order_status' => $order_status["completed"],
                        'uor_id' => $detail->uor_id,
                        'entity_id' => $entity_id,
                        'uorcr_driver_name' => $courier_name,
                        'uorcr_driver_phone' => $courier_phone,
                        'uorcr_url' => $url_live,
                        'gosend_id' => $gosend_id,
                        'gosend_type' => $gosend_type['completed'],
                        'uorcr_id' => $data_book->uorcr_id,
                        'uorcr_stat' => $gosend_status['delivered'],
                        'message' => $track_msg[4],
                        'price' => $body['price']
                    ];
                    $this->updateData($data);
                }elseif($status == $gosend_status['cancelled']){
                    //the driver can cancel any time
                    //set data to update uortrack, uorgsnd & gosend_logs
                    $data = [
                        'update_order_status' => "",
                        'uor_id' => $detail->uor_id,
                        'entity_id' => $entity_id,
                        'uorcr_driver_name' => $courier_name,
                        'uorcr_driver_phone' => $courier_phone,
                        'uorcr_url' => $url_live,
                        'gosend_id' => $gosend_id,
                        'gosend_type' => $gosend_type['cancel'],
                        'uorcr_id' => $data_book->uorcr_id,
                        'uorcr_stat' => $gosend_status['cancelled'],
                        'message' => $track_msg[5],
                        'price' => $body['price']
                    ];
                    $this->updateData($data);
                }elseif($status == $gosend_status['no_driver'] && $detail->uor_status == $order_status['in_process']){
                    //set data to update uortrack, uorgsnd & gosend_logs
                    $data = [
                        'update_order_status' => "",
                        'uor_id' => $detail->uor_id,
                        'entity_id' => $entity_id,
                        'uorcr_driver_name' => $courier_name,
                        'uorcr_url' => $url_live,
                        'uorcr_driver_phone' => $courier_phone,
                        'gosend_id' => $gosend_id,
                        'gosend_type' => $gosend_type['no_driver'],
                        'uorcr_id' => $data_book->uorcr_id,
                        'uorcr_stat' => $gosend_status['no_driver'],
                        'message' => $track_msg[6],
                        'price' => $body['price']
                    ];
                    $this->updateData($data);

                    // validate if store already close or not + 15 minutes
                    $st_data = $this->storedb->get($detail->st_id);
                    $now = date('Y-m-d H:i');
                    $st_close = date('Y-m-d H:i', strtotime('+15 minutes', strtotime($st_data->st_delivery_close)));
                    if($st_close > $now){
                        // order gosend
                        $this->call_gosend_api($detail->uor_id);
                    }else{
                        // update order status
                        $this->orderdb->change_order_cancelled($detail->uor_id, $order_status['cancelled'], 0, $uor_remarks['no_driver']);

                        // check if payment method not use wallet
                        if($detail->pymtd_id != $payment_method['wallet'] && date('Y-m-d', strtotime($detail->uor_date)) == date('Y-m-d')) {
                            $payment = $this->orderdb->get_payment_method($detail->pymtd_id);
                            // hit to ovo function
                            $api = $payment->pymtd_code."api";
                            $this->load->library($api);
                            
                            // get access token
                            $uor->dana = $this->danadb->get_user_dana($uor->user_id);

                            // call the API
                            $data_res_void = $this->$api->void_transaction($detail);

                            $data_payment = json_decode($data_res_void['data'], true);

                            //validate ovo response
                            if(!empty($data_payment) && $data_res_void['http_code'] == "200") {
                                // set data to ovo api
                                $data_req = $data_res_void['options'];

                                $pymt_logs = [
                                    "pymtd_id"          => $detail->pymtd_id,
                                    "pyhis_id"          => $detail->pyhis_id,
                                    "uor_id"            => $detail->uor_id,
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

                        // send email order cancel
                        $tpl_name = 'order_cancel_admin';
                        $tpl_data = [
                            'user_name' => $detail->user_name,
                            'user_email' => $detail->user_email,
                            'created_date' => date('d F Y', strtotime($detail->uor_date)),
                            'uor_code' => $detail->uor_code,
                            'uor_remarks' => $uor_remarks['no_driver'],
                            'status' => $detail->uor_status,
                            'subject' => 'Your order #'.$detail->uor_code.' has been cancelled'
                        ];
                        $this->infobip->send_email($tpl_name, $tpl_data, $detail->user_email_verified);

                        // send email to cs alpha
                        $tpl_data['user_email'] = EMAIL_REPLY_TO;
                        $this->infobip->send_email($tpl_name, $tpl_data, 1);
                    }
                }else{
                    //set variable update gosend log
                    $data = [
                        'gosend_type' => $status
                    ];
                    $this->gosenddb->update_log($gosend_id, $data);

                    //set variable update order gosend status
                    $updOrd = [
                        'uorcr_driver_name' => $courier_name,
                        'uorcr_url' => $url_live,
                        'uorcr_driver_phone' => $courier_phone,
                        'updated_date' => date('Y-m-d H:i:s')
                    ];
                    $this->courierdb->update_order_courier($data_book->uorcr_id, $updOrd);
                }
            }else{
                $msg = 'Booking ID not found';
                $status_code = 500;
            }
        }
        $this->output->set_status_header($status_code);
        echo $msg;
    }

    public function call_gosend_api($uor_id)
    {
        $track_msg = $this->config->item('order_track')['message'];
        $gosend_status = $this->config->item('gosend')['status'];
        $courier = $this->config->item('courier')['courier_code'];
        $logthis = $this->log4php('call_gosend_api', APPLOG);

        // set data to call gosend API (Booking)
        $data = $this->orderdb->get_order($uor_id);
        $data->item = $this->orderdb->get_order_item($uor_id);

        // call the API
        $data_res = $this->gosendapi->booking_ride($data);
        $data_gosend = json_decode($data_res['data'], true);
        $info = "#1 : ".json_encode($data_res["logs"]);

        if(empty($data_gosend)) {
            //validate response and retry hit gosend if get errors phone number
            $dataUser = $this->userdb->get($data->user_id);

            //update orders to change phone number and use phone number from profile user
            $updateData["uadd_phone"]   = $dataUser->user_phone;
            $updateData["uadd_person"]  = $dataUser->user_name;

            $this->orderdb->update_order_address($data->uoradd_id, $updateData);

            $data = $this->orderdb->get_order($uor_id);
            $data->item = $this->orderdb->get_order_item($uor_id);

            // re-hit the API
            $data_res = $this->gosendapi->booking_ride($data);
            $data_gosend = json_decode($data_res['data'], true);
            $info .= ",  #2 : " . json_encode($data_res["logs"]);
        }

        // set data to gosend_logs
        $data_req = $this->gosendapi->get_options($data);
        $params = [
            'gosend_type' => 'booking',
            'gosend_endpoint' => $data_req->url,
            'gosend_header' => json_encode($data_req->header),
            'gosend_request' => json_encode($data_req->body),
            'gosend_response' => $data_res['data'],
            'created_date' => date('Y-m-d H:i:s')
        ];
        $this->gosenddb->insert_log($params);

        $booking_id = isset($data_gosend['orderNo']) ? $data_gosend['orderNo'] : '';
        $uorcr_status = $booking_id != '' ? NULL : $gosend_status['cancelled'];
        //set data to user_order_courier
        $uorgo = [
            'uor_id' => $data->uor_id,
            'booking_id' => $booking_id,
            'uorcr_vendor' => $courier['gosend'],
            'uorcr_status' => $uorcr_status,
            'created_date' => date('Y-m-d H:i:s')
        ];
        $this->courierdb->insert_order_courier($uorgo);

        $uortr_text = $booking_id != '' ? $track_msg[0] : $track_msg[6];
        // set varible insert order track
        $insert = [
            'uor_id' => $data->uor_id,
            'uortr_date' => date('Y-m-d H:i:s'),
            'uortr_text' => $uortr_text,
            'created_by' => 0,
            'created_date' => date('Y-m-d H:i:s')
        ];
        $this->orderdb->insert_order_track($insert);

        $logthis->info($info);
    }

    public function updateData ($data)
    {
        $gosend_status = $this->config->item('gosend')['status'];
        $order_status = $this->config->item('order')['status'];

        //set variable update gosend log
        $updLog = [
            'gosend_type' => $data['gosend_type']
        ];
        $this->gosenddb->update_log($data['gosend_id'], $updLog);

        //set variable update order gosend status
        $updOrd = [
            'uorcr_driver_name' => $data['uorcr_driver_name'],
            'uorcr_driver_phone' => $data['uorcr_driver_phone'],
            'uorcr_status' => $data['uorcr_stat'],
            'uorcr_url' => $data['uorcr_url'],
            'updated_date' => date('Y-m-d H:i:s')
        ];
        $this->courierdb->update_order_courier($data['uorcr_id'], $updOrd);

        //set variable to insert order track message/text
        $insert = [
            'uor_id' => $data['uor_id'],
            'uortr_date' => date('Y-m-d H:i:s'),
            'uortr_text' => $data['message'],
            'created_by' => 0,
            'created_date' => date('Y-m-d H:i:s')
        ];
        $this->orderdb->insert_order_track($insert);

        // set actual delivery price
        if($data['price'] > 0){
            $params = [
                'uor_actual_delivery_fee' => $data['price']
            ];
            $this->orderdb->update_order($insert['uor_id'],$params);
        }

        //set variable to update user order status
        //update order status when gosend status delivery / complete
        if($data['update_order_status'] != ""){
            $info = '';

            // update order status completed
            if($data['update_order_status'] == $order_status["completed"]){
                $result = $this->orderdb->change_order_completed($insert['uor_id'], $order_status['completed'], 0);

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
                    $response .= ', '.$this->infobip->send_email($tpl_email, $tpl_voucher, $uor->user_email_verified);
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
                            $info .= ', push_notification - cancelled '.$response_push;
                        }
                    }
                }
            } else {
                // update order status
                $data = [
                    'uor_status' => $data['update_order_status'],
                    'updated_by' => 0,
                    'updated_date' => date('Y-m-d H:i:s')
                ];
                $this->orderdb->update_order($insert['uor_id'], $data);
            }
        }
    }

    function auto_approve()
    {
        //NOTE : FUNCTION INI HANYA SEMENTARA SEBELUM STICKER DI BERLAKUKAN, SEHARUSNYA NANTI DI HAPUS LAGI
        declare(ticks = 1);

        //=== CRON START
        unset($cron);
        $cron['class']  = $this->router->class;
        $cron['method'] = $this->router->method;
        $cron_instance  = $this->cron_start($cron);

        //log request ke file
        $logthis = $this->log4php('auto_approve', APPLOG);

        $today = date('Y-m-d');
        $fivemins = date('Y-m-d H:i:s', strtotime('-5 minutes'));

        $ignore_store = array(1);
        $store24hours = array(13);
        $limit_process = 5;


        $info = 'Start auto approve order >= '. $fivemins;
        $logthis->info($info);

        //GET PENDING STORE
        $sql = "SELECT distinct(addr.st_id) st_id
                FROM `user_order` ord
                INNER JOIN `user_order_address` addr ON addr.uor_id = ord.uor_id
                WHERE
                    ord.`uor_date` <= ?
                    AND ord.`uor_delivery_type` = 'delivery'
                    AND ord.`uor_status` = 'paid'";
        $query = $this->orderdb->db->query($sql, array($fivemins));
        $list_store = $query->result();

        //LOOPING STORE
        foreach ($list_store as $key => $value) {
            //KALAU STORE ID INI ADA DIDALAM YANG DI IGNORE, SKIP
            if(in_array($value->st_id, $ignore_store)){
                $info = 'Ignore '. $st_data->st_name;
                $logthis->info($info);
                continue;
            }

            //CHECK STATUS STORE ACTIVE & JAM OPERASIONAL
            $st_data = $this->storedb->get($value->st_id);
            $now = date('Y-m-d H:i');
            $st_close = date('Y-m-d H:i', strtotime('+15 minutes', strtotime($st_data->st_delivery_close)));
            if($st_close > $now){
                //lanjut
            }else{;
                $info = 'Store '. $st_data->st_name.' has been closed';
                $logthis->info($info);
                continue;
            }

            $sql_where = '';
            $sql_data  = array();
            $sql_data[]= $fivemins;
            $sql_data[]= $st_data->st_id;
            //kalau bukan toko 24 jam harus tambahin > hari ini
            if(! in_array($value->st_id, $store24hours)){
                $sql_where = ' AND ord.`uor_date` >= ? ';
                $sql_data[]= $today;
            }

            //SELECT ORDER TERBARU LIMIT 5 (KALAU ID PANIN JANGAN ADA UORDATE > TODAY)
            $sqlorder = "SELECT ord.uor_id
                    FROM `user_order` ord
                    INNER JOIN `user_order_address` addr ON addr.uor_id = ord.uor_id
                    WHERE
                        ord.`uor_date` <= ?
                        AND addr.st_id = ?
                        AND ord.`uor_delivery_type` = 'delivery'
                        AND ord.`uor_status` = 'paid'
                        ". $sql_where ."
                    LIMIT ". $limit_process;
            $queryorder = $this->orderdb->db->query($sqlorder, $sql_data);
            $list_order = $queryorder->result();

            //LOOPING ORDER
            foreach($list_order as $kord => $vord){
                //GET ORDER BY ID
                $detail_order = $this->orderdb->get_order($vord->uor_id);

                //VALIDATE, KALAU ORDER STATUS BUKAN PAID LANGSUNG SKIP
                if($detail_order->uor_status != 'paid'){
                    $info = 'Skip process order '. $vord->uor_id .', because the status is '.$detail_order->uor_status;
                    $logthis->info($info);
                    continue;
                }

                //UPDATE
                $data = [
                    'uor_status' => 'in_process',
                    'updated_by' => 0,
                    'updated_date' => date('Y-m-d H:i:s')
                ];
                $this->orderdb->update_order($detail_order->uor_id, $data);

                //HIT GOSEND
                $this->call_gosend_api($detail_order->uor_id);

                $info = 'Automatically process order '. $detail_order->uor_id. ', '. $st_data->st_name;
                $logthis->info($info);
            }
        }

        $info = 'Finished auto approve order >= '. $fivemins;
        $logthis->info($info);
        //=== CRON END
        $this->cron_end($cron_instance);
    }
}

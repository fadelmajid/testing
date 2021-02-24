<?php
defined('BASEPATH') OR exit('No direct script access allowed');


class Cronjob extends MY_Service {

    function __construct()
	{
		parent::__construct();

	}

    public function index()
    {
        show_404();
    }

    public function remind_voucher(){
        //load
        $this->load->library('pushnotification');
        $this->load->model('promodb');
        $this->load->model('authdb');

        $where          = " AND prm.prm_status = ? AND vc.vc_status = ? ";
        $prm_status     = $this->config->item('promo')['status']['active'];
        $vc_status      = $this->config->item('voucher')['status']['active'];
        $data_get       = [$prm_status, $vc_status];
        $data_voucher   = $this->promodb->getall_voucher($where, $data_get);
        $info           = "";
        $logthis        = $this->log4php('cronjob_remind_voucher', APPLOG);

        if(!empty($data_voucher)){
            foreach($data_voucher as $val){
                $date_push  = date('Y-m-d', strtotime('+2 days'));
                $date_end   = date('Y-m-d', strtotime($val->prm_end));

                //untuk cek ketika batas promo sudah h-2 (h-2, h-1, h)
                if($date_end == $date_push){
                    $token_push = $this->authdb->getall_atokenpush_notif_by_user_id($val->user_id);

                    //check token_push if exist
                    if(!empty($token_push)) {
                        //looping push notification

                        foreach($token_push as $push_token) {
                            $user_token = isset($push_token->atoken_pushnotif) ? $push_token->atoken_pushnotif : '';

                            if(!empty($user_token)) {
                                //push notif cancel data
                                $notif_data = [
                                    "push_token" => $user_token,
                                    "text"       => 'Your voucher #'.$val->vc_code.' will expire in 2 days',
                                ];

                                $response_push = $this->pushnotification->send_pushnotification($notif_data);
                                $info .= ', push_notification - expired '.$response_push;
                            }
                        }
                        $info .= ", Expired Voucher ID ".$val->vc_id;
                    }
                }
            }
            $logthis->info($info);
        }
    }

    public function cancel_order($type='paid')
    {
        $this->load->model('authdb');
        $this->load->model('orderdb');
        $this->load->model('danadb');
        $this->load->model('storedb');
        $this->load->library('pushnotification');

        $order_status  = $this->config->item('order');
        $user_status = $this->config->item('user')['status'];
        $payment_method = $this->config->item('order')['payment_method'];
        $payment_name   = $this->config->item('order')['payment_name'];
        $delivery_type  = $this->config->item('order')['delivery_type']['delivery'];
        $logthis = $this->log4php('cancel_order', APPLOG);

        $ord_status = $order_status['status']['paid'];
        $uor_remarks = "We have to cancel the order that hasn't been proceed today, please make sure whether you want to pick the order at our store or to be delivered to you.";
        if($type != $order_status['status']['paid']){
            $ord_status = $order_status['status']['waiting_for_payment'];
            $uor_remarks = "The order has been cancelled due to payment time limit has been reached";
        }

        $infinite_loop = true;
        $info = "";
        while($infinite_loop == true){
            $date_hour_ago = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s')) - 3600);
            $where = "AND uor_status = ? AND uor_date <= ? ";
            $data = [$ord_status, $date_hour_ago];
            $list_order = $this->orderdb->getall_order($where, $data, ' uor_id ASC ', 1);

            if(empty($list_order)){
                $infinite_loop = false;
            }else{
                foreach($list_order as $value) {
                    $uor_id = $value->uor_id;

                    $result = $this->orderdb->change_order_cancelled($uor_id, $order_status['status']['cancelled'], 0, $uor_remarks);

                    $uor = $this->orderdb->get_order($result['uor_id']);

                    // check if payment method not use wallet
                    if($uor->pymtd_id != $payment_method['wallet']) {
                        $payment = $this->orderdb->get_payment_method($uor->pymtd_id);
                        // hit to ovo function
                        $api = $payment->pymtd_code."api";
                        $this->load->library($api);

                        // get access token
                        $uor->dana = $this->danadb->get_user_dana($uor->user_id);

                        // call the API
                        $data_res = $this->$api->void_transaction($uor);

                        $data_ovo = json_decode($data_res['data'], true);

                        //insert to logs file
                        $info = "cron_void_transaction_logging_#1 : ".json_encode($data_res["logs"]);

                        //validate ovo response
                        if(!empty($data_ovo) && $data_res['http_code'] == "200") {
                            // set data to ovo api
                            $data_req = $data_res['options'];

                            $ovo_logs = [
                                "pymtd_id"          => $uor->pymtd_id,
                                "pyhis_id"          => $uor->pyhis_id,
                                "uor_id"            => $uor->uor_id,
                                "pylog_type"        => $data_req->pylog_type,
                                "pylog_endpoint"    => $data_req->url,
                                "pylog_header"      => json_encode($data_req->header),
                                "pylog_request"     => json_encode($data_req->body),
                                "pylog_response"    => json_encode($data_ovo),
                                "created_date"      => date("Y-m-d H:i:s")
                            ];
                            //insert to payment logs
                            $this->orderdb->insert_payment_logs($ovo_logs);
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

                    $token_push = $this->authdb->getall_atokenpush_notif_by_user_id($value->user_id);

                    //check token_push if exist
                    if(!empty($token_push)) {
                        //looping push notification
                        foreach($token_push as $push_token) {
                            $user_token = isset($push_token->atoken_pushnotif) ? $push_token->atoken_pushnotif : '';

                            if(!empty($user_token)) {
                                //push notif cancel data
                                $notif_data = [
                                    "push_token" => $user_token,
                                    "text"       => 'Your order #'.$value->uor_code.' has been cancelled',
                                ];

                                $response_push = $this->pushnotification->send_pushnotification($notif_data);
                                $info .= ', push_notification - cancelled '.$response_push;

                            }
                        }
                    }
                    $info .= ", Cancelled Order ID ".$uor_id;
                    $logthis->info($info);
                }
            }
        }
    }

    public function check_topup_wallet()
    {
        // cron ini dijalankan jam 10 malam setiap hari
        $this->load->library('infobip');
        $this->load->model('walletdb');
        $this->load->model('admindb');

        $user_topup         = $this->config->item('user_topup');
        $email_admin        = $user_topup['email_admin'];
        $tpl_data['date']   = date('Y-m-d');

        // send email when order success
        $where              = " AND DATE(utop.created_date) = ? GROUP BY utop.user_id, user.user_name, user.user_email, user.user_phone, utop.utop_payment HAVING total_topup > ?";
        $data               = [date('Y-m-d'), $user_topup['limit']['average']];
        $tpl_data['user']   = $this->walletdb->getall_topuphis_valid($where, $data);
        $tpl_name           = 'warning_check_top_up';
        $info               = '';
        $tpl_data['arr_admin'] = $this->admindb->getarr_admin();

        if(!empty($tpl_data['user'])) {
            foreach($email_admin as $email) {
                $tpl_data['user_email'] = $email;
                $tpl_data['subject']    = '[WARNING] Adminpanel - Wallet Top up more than 500k '.date('l, d F Y');
                $tpl_data['status']     = 'warning report';
                // send email to administrator
                $response = $this->infobip->send_email($tpl_name, $tpl_data, 1);
                $info .= "Send email to ". $email. " - ". $response;
            }

            $logthis = $this->log4php('check_topup_wallet', APPLOG);
            $logthis->info($info);
        }
    }

    public function birthday()
    {
        // cron ini dijalankan jam 10 malam setiap hari
        $this->load->library('infobip');
        $this->load->model('userdb');
        $this->load->model('promodb');

        // set logging file
        $logthis = $this->log4php('birthday_voucher', APPLOG);
        $info = "";

        // set time
        $year = date('Y');
        $yesterday = date('d',strtotime("-1 days"));

        // load status user
        $status     = $this->config->item('user')['status'];

        // set data to get user detail
        $data = [date('m'),date('d'), $yesterday, $status['active']];
        $where = " AND MONTH(user_birthday) = ? AND ( DAY(user_birthday) = ? OR DAY(user_birthday) = ? ) AND user_status = ? ";
        $user_birthday = $this->userdb->get_all_birthday_user($where, $data, " user_id ");

        // check user who birthday = now
        if(!empty($user_birthday)){
            $promo_id = $this->check_promo_birthday();
        }

        // looping user
        for($i=0; $i<count($user_birthday); $i++){
            $detail_user = $user_birthday[$i];
            $where_vcb = " AND user_id = ? AND vcb_year = ? ";
            $data_vcb = [$detail_user->user_id, $year];
            $vc_birthday = $this->promodb->get_all_voucher_birthday($where_vcb, $data_vcb, " user_id ASC ", 1);


            if(empty($vc_birthday)){
                // get promo birthday
                $promo = $this->promodb->get_promo($promo_id);
                // logging into file
                $info .= "[vc_birthday] promo ".json_encode($promo);

                // generate voucher birthday
                $voucher_birthday = $this->promodb->create_general_voucher($promo->prm_id, true, $promo->prm_custom_code, $detail_user->user_id);
                // logging into file
                $info .= "[vc_birthday] id : ".json_encode($voucher_birthday);

                // insert voucher birthday
                $data_birthday = [
                    "vcb_year" => (int)$year,
                    "user_id" => $detail_user->user_id,
                    "created_date" => date('Y-m-d H:i')
                ];
                $insert_vcb = $this->promodb->insert_voucher_birthday($data_birthday);
                // logging into file
                $data_log = [
                    "vcb_id" => $insert_vcb,
                    "data" => $data
                ];
                $info .= "[vc_birthday] id : ".json_encode($data_log);

                $tpl_email = 'birthday_voucher';
                $tpl_voucher = [
                    'user_name' => $detail_user->user_name,
                    'created_date' => date('d F Y'),
                    'exp_date' => date('d F Y', strtotime($promo->prm_end)),
                    'user_email' => $detail_user->user_email,
                    'subject' => 'Happy Birthday '. $detail_user->user_name .'!',
                    'status' => 'birthday'
                ];
                // send email birthday voucher
                $info .= ', '.$this->infobip->send_email($tpl_email, $tpl_voucher, $detail_user->user_email_verified);
            }else{
                //logging into file
                $info .= "[vc_birthday] has been created : ".json_encode($detail_user);
            }
        }

        $logthis->info($info);
    }

    private function check_promo_birthday()
    {
        $this->load->model('promodb');
        $logthis    = $this->log4php('create_promo_birthday', APPLOG );

        $promo_type = $this->config->item('promo')['type'];
        $discount_type = $this->config->item('promo')['discount_type'];
        $status = $this->config->item('promo')['status'];
        $promo_code = $this->config->item('promo')['promo_code'];

        //checking promo
        $date = date("Y-m-d");
        $end_date = date('Y-m-d 23:59:59',strtotime("+1 months"));
        $promo_name = 'Birthday Voucher '.date('d/m/y');
        $promo_custom_code = $promo_code['birthday'].date('ymd');
        $promo_start = $date;
        $promo_end = $end_date;
        $promo = $this->promodb->get_promo_custom_filter(' AND prm_name = ?', [$promo_name]);
        $voucher_default = $this->promodb->get_voucher_default_by_vcdef_code($promo_code['emp']);
        $static_image = $this->promodb->get_static_image_by_stat_code($promo_code['free']);

        if (!$promo) {
            //create new promo
            $prm_rules = [
                'limit_usage' => 0,
                'custom_function' => null,
                'disc_type' => $discount_type['freecup'],
                'disc_nominal' => 1,
                'disc_max' => 0,
                'min_order' => 0,
                'delivery_included' => false,
                'free_delivery' => false,
                'item_type' => $voucher_default->vcdef_type,
                'item_list' => json_decode($voucher_default->vcdef_list, true)
            ];

            //set data promo
            $data = [
                'prm_name' => $promo_name,
                'prm_custom_code' => $promo_custom_code,
                'prm_start' => $promo_start,
                'prm_end' => $promo_end,
                'prm_type' => $promo_type['generated'],
                'prm_img' => $static_image->stat_img,
                'prm_status' => $status['active'],
                'prm_rules' => json_encode($prm_rules),
                'created_by'=> 0,
                'created_date' => date('Y-m-d H:i:s'),
            ];
            $promo_id = $this->promodb->insert_promo($data);

            $info = "Insert new promo #" . $promo_id . " " . $promo_custom_code;
            $logthis->info($info);
        }else{
            $promo_id = $promo->prm_id;
            $info = "Found promo #" . $promo->prm_id . " " . $promo->prm_custom_code;
            $logthis->info($info);
        }
        return $promo_id;
    }

    public function clean_data()
    {
        // cron ini dijalankan jam 1 pagi setiap hari
        $this->load->model('authdb');
        $this->load->model('gosenddb');
        $this->load->model('grabdb');
        $this->load->model('promodb');
        $this->load->model('userdb');

        $logthis = $this->log4php('clean_data', APPLOG);

        $info = "Execute Cronjob";

        $min3days = date('Y-m-d', strtotime("-3 days", time()));
        $min60days = date('Y-m-d', strtotime("-60 days", time()));
        $today = date('Y-m-d');

        $this->authdb->copy_auth_token();
        $info .= ", copy_auth_token() ";

        $this->authdb->delete_auth_code($min3days);
        $info .= ", delete_auth_code('". $min3days ."') ";

        $this->authdb->delete_auth_code_sms($min3days);
        $info .= ", delete_auth_code_sms('". $min3days ."') ";

        $this->authdb->update_expired_auth_token($min60days);
        $info .= ", update_expired_auth_token('". $min60days ."') ";

        $this->authdb->delete_inactive_auth_token();
        $info .= ", delete_inactive_auth_token() ";

        $this->gosenddb->delete_logs($min3days);
        $info .= ", delete_logs('". $min3days ."') ";

        $this->grabdb->delete_logs($min3days);
        $info .= ", delete_logs('". $min3days ."') ";

        $this->promodb->update_expired_promo($today);
        $info .= ", update_expired_promo('". $today ."') ";

        $this->userdb->clean_cart($min3days);
        $info .= ", clean_cart('". $min3days ."') ";

        $logthis->info($info);
    }

    public function update_store_hours() {
        $this->load->model('storedb');
        $stopt_status = $this->config->item('store_operational')['stopt_status'];
        $store_opt_status = $this->config->item('store_operational')['store_opt_status'];
        $store_type = $this->config->item('store_operational')['store_type'];
        $total_update = 0;
        $logthis = $this->log4php('update_store_hours', APPLOG);

        $info = "Update store hours date = ".date('Y-m-d H:i:s');

        //get all store yang diurutkan dari id terbesar
        $store = $this->storedb->getall();

        foreach($store as $st) {
            $day = strtolower(date('l'));
            $select = "store_opt.sto_id, store_opt.{$day}, store_opt.st_id";
            $where = " AND store_opt.sto_status = ? AND store_opt.st_id = ? AND store_opt.end_date >= ? ";
            $data = [$stopt_status['active'], $st->st_id, date('Y-m-d').' 23:59:59'];
            $sort = " store_opt.sto_id DESC ";
            $store_opt = $this->storedb->getall_store_opt($select, $where, $data, $sort, $limit = 1);

            if(!empty($store_opt)) {
                $row_opt = $store_opt[0];
                $store_day = json_decode($row_opt->$day, TRUE);
                $update_data = [];
                if($store_day['status'] == $store_opt_status['close']) {
                    $update_data['st_status'] = $stopt_status['inactive'];
                } else {
                    /*
                    Modified: 2019-06-11 by Hans (Hotfix 1.16.68)
                    Description:
                        Diubah supaya cronjob ini hanya mengubah data yang diperlukan saja.
                        Jika tipe store-nya 'delivery_only' maka hanya mengubah data jam delivery saja.
                        Jika tipe store-nya 'pickup_only' maka hanya mengubah data jam pickup saja.
                        Hal ini dilakukan karena sebelum code ini diubah ada skenario pada saat tipe store-nya 'delivery_only',
                        jam pickup menjadi kosong dan menyebabkan store tersebut tidak muncul di list store halaman admin.
                    */
                    if($store_day['st_type'] == $store_type['delivery_only']) {
                        $update_data['st_delivery_open'] = $store_day['delivery']['open'];
                        $update_data['st_delivery_close'] = $store_day['delivery']['close'];
                    }
                    else if($store_day['st_type'] == $store_type['pickup_only']) {
                        $update_data['st_open'] = $store_day['pickup']['open'];
                        $update_data['st_close'] = $store_day['pickup']['close'];
                    }
                    else {
                        $update_data['st_open'] = $store_day['pickup']['open'];
                        $update_data['st_close'] = $store_day['pickup']['close'];
                        $update_data['st_delivery_open'] = $store_day['delivery']['open'];
                        $update_data['st_delivery_close'] = $store_day['delivery']['close'];
                    }

                    $update_data['st_status'] = $stopt_status['active'];
                    $update_data['st_type'] = $store_day['st_type'];
                }
                $update_data['updated_by']      = 0;
                $update_data['updated_date']    = date('Y-m-d H:i:s');

                if($this->storedb->update($row_opt->st_id, $update_data)){
                    $total_update++;
                    $info .= ", Update Store ID {$row_opt->st_id} Operational Hours by System. ". json_encode($update_data);
                };
            }
        }
        $info .= ", Total update store = ". $total_update;
        $logthis->info($info);
    }


    public function check_store_hours() {
        $this->load->library('infobip');
        $this->load->model('storedb');
        $store_status = $this->config->item('store')['status'];
        $user_topup         = $this->config->item('user_topup');
        $email_admin        = $user_topup['email_admin'];

        $logthis = $this->log4php('check_store_hours', APPLOG);

        $info = "Check store hours date = ".date('Y-m-d H:i:s');

        $select = "store_opt.st_id";
        $where = " AND store_opt.sto_status = ? AND store_opt.end_date >= ? ";
        $data = [$store_status['active'], date('Y-m-d').' 23:59:59'];
        $sort = ' store.st_id ASC ';
        $groubby = " GROUP BY store_opt.st_id ";
        $store_opt = $this->storedb->getall_store_opt($select, $where, $data, $sort, $limit = 0, $groubby);
        $opt_data = [];

        foreach($store_opt as $opt_id) {
            $opt_data[] = $opt_id->st_id;
        };

        //get store operational yang diurutkan dari id terbesar
        $where      = " AND st_id IN ? AND updated_date < ? ";
        $data       = [$opt_data, date('Y-m-d').' 00:00:00'];
        $sort       = " st_id ASC ";
        $tpl_name   = 'warning_check_store';
        $store      = $this->storedb->getall($where, $data, $sort);

        if(!empty($store)) {
            //send email to admin alpha
            //list email admin ada di variable.php
            foreach($email_admin as $email) {
                $tpl_data['user_email'] = $email;
                $tpl_data['subject']    = '[REPORT] Adminpanel - Store update daily '.date('l, d F Y');
                $tpl_data['status']     = 'report';
                $tpl_data['data']       = $store;
                $tpl_data['total']      = count($store);
                // send email to administrator
                $response = $this->infobip->send_email($tpl_name, $tpl_data, 1);
                $info .= "Send email to ". $email. " - ". $response;
            }
        }

        $logthis->info($info);
    }

    public function subscription_voucher()
    {
        // cron ini dijalankan jam 1 malam setiap hari
        $this->load->model('userdb');
        $this->load->model('subscriptiondb');
        $this->load->model('promodb');

        // set logging file
        $logthis = $this->log4php('subscription_voucher', APPLOG);
        $info = "";

        // set const
        $counter = $this->config->item('subscription')['counter'];
        $status = $this->config->item('subscription')['status'];

        // set time
        $today = date('Y-m-d');

        // load status user
        $status     = $this->config->item('user')['status'];

        // set data to get user detail
        $data = [$counter, $status['active'], $today];
        $where = " AND sc_counter > ? AND sc_status = ? AND last_generate < ?";
        $user_subscription = $this->subscriptiondb->getall_subscription_user($where, $data);

        // check is there any user subscription
        if(!empty($user_subscription)){
            // looping user
            for($i = 0; $i < count($user_subscription); $i++){
                $detail_subs = $user_subscription[$i];

                // create promo subscription
                $promo_id = $this->check_promo_subscription($detail_subs->subsplan_id, $detail_subs->subsplan_promo);

                // get user info
                $user = $this->userdb->get($detail_subs->user_id);

                // get promo subscription
                $promo = $this->promodb->get_promo($promo_id);
                // logging into file
                $info .= ", [vc_subscription] promo : ".json_encode($promo);

                // generate voucher subscription, by quantity subscounter
                for($j=0; $j < ($detail_subs->sc_counter * $detail_subs->subsorder_qty); $j++){
                    $voucher_subscription = $this->promodb->create_general_voucher($promo->prm_id, true, $promo->prm_custom_code, $user->user_id);
                    // logging into file
                    $info .= ", [vc_subscription] id : ".json_encode($voucher_subscription);    
                }
                // update user_subscription
                $data = [
                    "sc_counter" => $counter,
                    "last_generate" => date('Y-m-d'),
                    "updated_date" => date('Y-m-d H:i:s')
                ];
                $update_subs = $this->subscriptiondb->update_subs_counter($detail_subs->sc_id, $data);

                // logging into file
                $info .= ", [vc_subscription] update subs counter : ".json_encode($update_subs);
            }
        }else{
            //logging into file
            $info .= ", [vc_subscription] empty : ".json_encode($user_subscription);
        }

        $logthis->info($info);
    }

    private function check_promo_subscription($id, $subsplan_promo)
    {
        $this->load->model('promodb');
        $this->load->model('subscriptiondb');
        $logthis = $this->log4php('create_promo_subscription', APPLOG );

        $promo_type = $this->config->item('promo')['type'];
        $status = $this->config->item('promo')['status'];

        // get subsplan
        $subsplan = $this->subscriptiondb->get_subsplan($id);

        // validate subsplan_promo
        $rules = json_decode($subsplan_promo, true);

        // checking promo
        $date = date("Y-m-d");
        $end_date = date('Y-m-d 23:59:59',strtotime("+".$subsplan->subsplan_duration." days"));
        $promo_name = $subsplan->subsplan_name;
        $promo_custom_code = $subsplan->subsplan_code.date('ymd');
        $promo_start = $date;
        $promo_end = $end_date;

        // get the promo
        $promo = $this->promodb->get_promo_custom_filter(' AND prm_custom_code = ?', [$promo_custom_code]);

        // check the promo
        if (!$promo) {
            //set data promo
            $data = [
                'prm_name' => $promo_name,
                'prm_custom_code' => $promo_custom_code,
                'prm_start' => $promo_start,
                'prm_end' => $promo_end,
                'prm_type' => $promo_type['generated'],
                'prm_img' => $rules['image'],
                'prm_status' => $status['active'],
                'prm_rules' => json_encode($rules),
                'created_by'=> 0,
                'created_date' => date('Y-m-d H:i:s'),
            ];
            $promo_id = $this->promodb->insert_promo($data);

            $info = "Insert new promo #" . $promo_id . " " . $promo_custom_code;
            $logthis->info($info);
        }else{
            $promo_id = $promo->prm_id;
            $info = "Found promo #" . $promo->prm_id . " " . $promo->prm_custom_code;
            $logthis->info($info);
        }
        return $promo_id;
    }

    function reminder_subscription()
    {
        // cron ini dijalankan jam 9 pagi setiap hari
        $this->load->model('userdb');
        $this->load->model('subscriptiondb');
        $this->load->model('authdb');
        $this->load->library('pushnotification');

        // set logging file
        $logthis = $this->log4php('cronjob_reminder_subscription', APPLOG);
        $info = "";

        // set const
        $expired = $this->config->item('subscription')['expired'];
        $status = $this->config->item('subscription')['status'];

        // load status user
        $status     = $this->config->item('user')['status'];

        // set data to get user detail
        $data = [$expired, $status['active']];
        $where = " AND sc_counter = ? AND sc_status = ? ";
        $reminder_subscription = $this->subscriptiondb->getall_subscription_user($where, $data);

        if(!empty($reminder_subscription)){
            // looping user
            foreach($reminder_subscription as $value_subs){

                // get subsplan
                $subsplan = $this->subscriptiondb->get_subsplan($value_subs->subsplan_id);

                // get all push token
                $token_push = $this->authdb->getall_atokenpush_notif_by_user_id($value_subs->user_id);

                //check token_push if exist
                if(!empty($token_push)) {
                    //looping push notification
                    foreach($token_push as $push_token) {
                        $user_token = isset($push_token->atoken_pushnotif) ? $push_token->atoken_pushnotif : '';

                        if(!empty($user_token)) {
                            //push notif reminder
                            $notif_data = [
                                "push_token" => $user_token,
                                "text"       => 'Your '.$subsplan->subsplan_name.' subscription will expire in '.$value_subs->sc_counter.' days',
                            ];

                            $response_push = $this->pushnotification->send_pushnotification($notif_data);
                            $info .= ', push_notification - remider subscription '.$response_push;
                        }
                    }
                }
                $info .= ", Subscription Order - User ID ".$value_subs->user_id;
            }
        }else{
            $info .= ", there is no subscription will expire ";
        }

        $logthis->info($info);
    }

    public function voucher_tgif() {
        $this->load->model('promodb');
        $this->load->model('bannerdb');
        $logthis    = $this->log4php('cronjob_voucher_tgif', APPLOG );
        $day = strtolower(date('l'));
        $status = $this->config->item('promo')['status'];
        $promo_code = $this->config->item('promo')['promo_code'];
        $banner_id = $this->config->item('banner')['name']['tgif'];
        $day_name = $this->config->item('store_operational')['day_name'];
        $now =  date('Y-m-d H:i:s');
        $end_berkah = "2019-05-31 23:59:59";
        $info = "";

        if ($now <= $end_berkah) {
            //checking promo
            $date = date("Y-m-d 21:00:00");
            $end_date = date('Y-m-d 06:00:00', strtotime('+1 days'));
            $promo_start = $date;
            $promo_end = $end_date;
            $where = ' AND prm_custom_code LIKE ? AND prm_id = ? ';
            $data = [$promo_code['brkh'], 900];
            $promo = $this->promodb->get_promo_custom_filter($where, $data);

            if(!empty($promo)) {
                $promo_data = [
                    'prm_status' => $status['active'],
                    'prm_start' => $promo_start,
                    'prm_end' => $promo_end,
                ];

                $this->promodb->update_promo($promo->prm_id, $promo_data);
                $info = "Update promo #" . $promo->prm_id . " " . $promo_code['brkh'];
            } else{
                $info = "Not Found promo ". $promo_code['tgif'];
            }
        }

        $logthis->info($info);
    }

    function hit_courier_api($uor_id)
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
            //validate response and retry hit courier if get errors phone number
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
        // set data to courier_logs
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

        $booking_id = $data_res['booking_id'];
        $uorcr_status = $booking_id != '' ? NULL : $courier_status['cancelled'];
        //set data to user_order_courier
        $uorgo = [
            'uor_id' => $data->uor_id,
            'booking_id' => $booking_id,
            'uorcr_vendor' => $vendor->courier_code,
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
            'created_by' => 0,
            'created_date' => date('Y-m-d H:i:s')
        ];
        $this->orderdb->insert_order_track($insert);

        $logthis->info($info);
        return true;
    }

    function auto_approve_courier()
    {
        $this->load->model('storedb');
        $this->load->model('orderdb');
        //NOTE : FUNCTION INI HANYA SEMENTARA SEBELUM STICKER DI BERLAKUKAN, SEHARUSNYA NANTI DI HAPUS LAGI
        declare(ticks = 1);

        //=== CRON START
        unset($cron);
        $cron['class']  = $this->router->class;
        $cron['method'] = $this->router->method;
        $cron_instance  = $this->cron_start($cron);
        $delivery_type  = $this->config->item('order')['delivery_type']['delivery'];
        $status         = $this->config->item('order')['status'];

        //log request ke file
        $logthis    = $this->log4php('webhoook_auto_approve_courier', APPLOG);

        $today      = date('Y-m-d');
        $fivemins   = date('Y-m-d H:i:s');

        $ignore_store   = array();
        $store24hours   = array(13);
        $data           = [$fivemins, $delivery_type, $status['paid']];

        $info  = 'Start auto approve courier order <= '. $fivemins;
        $logthis->info($info);

        //GET PENDING STORE
        $list_store = $this->orderdb->get_pending_store($data);

        //LOOPING STORE
        foreach ($list_store as $key => $value) {
            //KALAU STORE ID INI ADA DIDALAM YANG DI IGNORE, SKIP
            if(in_array($value->st_id, $ignore_store)){
                $info = 'Ignore '. $st_data->st_name;
                $logthis->info($info);
                continue;
            }

            //CHECK STATUS STORE ACTIVE & JAM OPERASIONAL
            $st_data    = $this->storedb->get($value->st_id);
            $now        = date('Y-m-d H:i');
            $st_close   = date('Y-m-d H:i', strtotime('+15 minutes', strtotime($st_data->st_delivery_close)));
            if($st_close > $now){
                //lanjut
            }else{;
                $info = 'Store '. $st_data->st_name.' has been closed';
                $logthis->info($info);
                continue;
            }

            $sql_where = '';
            $sql_data  = [$fivemins, $st_data->st_id, $delivery_type, $status['paid']];
            //kalau bukan toko 24 jam harus tambahin > hari ini
            if(! in_array($value->st_id, $store24hours)){
                $sql_where = ' AND ord.uor_date >= ? ';
                $sql_data[]= $today;
            }

            //SELECT ORDER TERBARU LIMIT 5 (KALAU ID PANIN JANGAN ADA UORDATE > TODAY)
            $list_order = $this->orderdb->get_order_paid($sql_where, $sql_data);

            //LOOPING ORDER
            foreach($list_order as $kord => $vord){
                //GET ORDER BY ID
                $detail_order = $this->orderdb->get_order($vord->uor_id);

                //VALIDATE, KALAU ORDER STATUS BUKAN PAID LANGSUNG SKIP
                if($detail_order->uor_status != $status['paid']){
                    $info = 'Skip process order '. $vord->uor_id .', because the status is '.$detail_order->uor_status;
                    $logthis->info($info);
                    continue;
                }

                //UPDATE
                $data = [
                    'uor_status'    => $status['in_process'],
                    'updated_by'    => 0,
                    'updated_date'  => date('Y-m-d H:i:s')
                ];
                $this->orderdb->update_order($detail_order->uor_id, $data);

                //HIT COURIER
                $this->hit_courier_api($detail_order->uor_id);

                $info .= ', Automatically process order '. $detail_order->uor_id. ', '. $st_data->st_name;
                $logthis->info($info);
            }
        }

        $info .= ', Finished auto approve courier order <= '. $fivemins;
        $logthis->info($info);
        //=== CRON END
        $this->cron_end($cron_instance);
    }

    function auto_close_open_store()
    {
        //NOTE : FUNCTION INI HANYA SEMENTARA SEBELUM STICKER DI BERLAKUKAN, SEHARUSNYA NANTI DI HAPUS LAGI
        declare(ticks = 1);

        //=== CRON START
        unset($cron);
        $cron['class']  = $this->router->class;
        $cron['method'] = $this->router->method;
        $cron_instance  = $this->cron_start($cron);

        //log request ke file
        $logthis    = $this->log4php('auto_close_open_store', APPLOG);

        $this->load->model('storedb');

        $date_now = date('Y-m-d H:i:s');
        $store_status = $this->config->item('store')['status'];
        $store_type = $this->config->item('store')['type'];
        $order_status = $this->config->item('order')['status'];
        $delivery_type = $this->config->item('order')['delivery_type'];

        $store_constant = $this->storedb->get_store_constant();
        
        $info = [
            'info' => 'Data Store Constant: ',
            'data' => $store_constant
        ];
        $logthis->info(json_encode($info));

        $where = "
            AND st_default_status = ?
            AND st_default_type != ?
        ";
        $data = [
            $store_status['active'],
            $store_type['pickup_only']
        ];
        $active_store_config = $this->storedb->getall($where, $data);

        // To get all st_id only
        $array_st_id = array_column($active_store_config, 'st_id');

        $data = [];
        $data[] = $store_constant->stct_min_cup;
        $data[] = $store_constant->stct_max_cup;
        $data[] = $store_constant->stct_min_order;
        $data[] = $store_constant->stct_max_order;

        $where_st_id = "";
        // This foreach to prepare sql where st_id query 
        foreach($array_st_id as $st_id) 
        {
            $data[] = $st_id;
            $where_st_id .= '?,';
        }
        $where_st_id = substr($where_st_id,0,-1);

        $where = "
            AND uoradd.st_id IN (".$where_st_id.")
            AND
            (
                (
                    uor.uor_status = ?
                    AND uor.uor_date BETWEEN DATE_ADD(?, INTERVAL 
                            (-IF(stcf.stcf_range_data IS NULL, ?, stcf.stcf_range_data))
                        YEAR) AND ?
                )
                OR
                (
                    uor.uor_delivery_type = ?
                    AND uor.uor_status = ?
                    AND uor.updated_date BETWEEN DATE_ADD(?, INTERVAL
                            (-IF(stcf.stcf_range_data IS NULL, ?, stcf.stcf_range_data))
                        YEAR) AND ?
                )
                OR 
                (
                    uor.uor_delivery_type = ?
                    AND uor.uor_status = ?
                    AND uor.updated_date BETWEEN DATE_ADD(?, INTERVAL 
                            (-IF(stcf.stcf_range_data IS NULL, ?, stcf.stcf_range_data))
                        YEAR) AND ?
                )
            )
        ";

        $data[] = $order_status['paid'];
        $data[] = $date_now;
        $data[] = $store_constant->stct_range_data;
        $data[] = $date_now;

        $data[] = $delivery_type['delivery'];
        $data[] = $order_status['in_process'];
        $data[] = $date_now;
        $data[] = $store_constant->stct_range_data;
        $data[] = $date_now;
        
        $data[] = $delivery_type['pickup'];
        $data[] = $order_status['completed'];
        $data[] = $date_now;
        $data[] = $store_constant->stct_range_data;
        $data[] = $date_now;
        
        $info = [
            'info' => 'WHERE Sql Query: ',
            'data' => $data
        ];
        $logthis->info(json_encode($info));

        $data_copy = $data;
        $data = array_merge($data,$data_copy);
        
        $active_store = $this->storedb->get_all_active_store_data($where, $data);
        
        $data = [
            'updated_by' => 0,
            'updated_date' => $date_now 
        ];
        $updated_ids = "";
        // This foreach to open or close the store
        foreach($active_store as $store)
        {
            $do_update = false;

            // Close store if exceeded maximum cup or maximum order
            if($store->total_cup >= $store->max_cup || $store->total_order >= $store->max_order)
            {
                // If the store type is ALL and current status is Open
                if(($store->st_default_type == $store_type['all']) && ($store->st_type == $store->st_default_type)) 
                {
                    $data['st_type'] = $store_type['pickup_only'];
                    $do_update = true;
                }
                // If the store default type is delivery_only
                else
                {
                    // If the store current status is Open
                    if($store->st_status == $store->st_default_status)
                    {
                        $data['st_status'] = $store_status['inactive'];
                        $do_update = true;
                    }
                }
            }
            // Open store if reach minimum cup or minimum order
            else if($store->total_cup <= $store->min_cup || $store->total_order <= $store->min_order)
            {
                // If the store type is ALL and current status is Close
                if(($store->st_default_type == $store_type['all']) && ($store->st_type != $store->st_default_type)) 
                {
                    $data['st_type'] = $store->st_default_type;
                    $do_update = true;
                }
                // If the store default type is delivery_only
                else
                {
                    // If the store current status is Close
                    if($store->st_status != $store->st_default_status)
                    {
                        $data['st_status'] = $store->st_default_status;
                        $do_update = true;
                    }
                }            
            }

            if($do_update)
            {   
                $updated_ids .= $store->st_id. ', ';
                $update = $this->storedb->update($store->st_id, $data);
            }
        }
        $info = [
            'info' => 'Updated st_id(s)',
            'data' => $updated_ids
        ];
        $logthis->info(json_encode($info));

        //=== CRON END
        $this->cron_end($cron_instance);
    }
}

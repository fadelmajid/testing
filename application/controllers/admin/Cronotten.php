<?php
defined('BASEPATH') OR exit('No direct script access allowed');


class Cronotten extends MY_Service {

    function __construct()
	{
		parent::__construct();

        $this->load->model('promodb');
        $this->load->model('userdb');
        $this->log_key = 'generate_employee_voucher';

	}

    public function index()
    {
        show_404();
    }

    public function generate_employee_voucher()
    {
        $logthis    = $this->log4php($this->log_key, APPLOG );

        //get promo id
        $promo_id   = $this->check_promo_employee();

        $promo_detail = $this->promodb->get_promo($promo_id);

        //generate to all employee

        //generate to head of barista
        $limit_employee = 10;
        $all_employee = $this->get_data_employee();
        $this->process_additional_voucher($all_employee, $promo_id, $promo_detail->prm_custom_code, $limit_employee);

    }

    private function process_additional_voucher($all_data, $promo_id, $prm_custom_code, $limit)
    {
        foreach($all_data as $key=>$value){
            //select user by phone number
            $this->userdb->db->where('user_phone', $value['phone']);
            $this->userdb->db->limit(1);
            $query = $this->userdb->db->get($this->userdb->tbl_user);
            $detail_user = $query->row();

            //generate sebanyak yang dibutuhkan
            $this->generate_voucher($promo_id, $prm_custom_code, $detail_user->user_id, $limit);
        }
    }

    private function generate_voucher($prm_id, $prm_code, $user_id, $limit)
    {
        $logthis    = $this->log4php($this->log_key, APPLOG );
        $status     = $this->config->item('promo')['status'];

        //hitung dulu kurang berapa
        $this->promodb->db->select('count(*) total');
        $this->promodb->db->where('prm_id', $prm_id);
        $this->promodb->db->where('user_id', $user_id);
        $query = $this->promodb->db->get($this->promodb->tbl_voucher);
        $voucher_detail = $query->row();
        $total = $limit - $voucher_detail->total;


        $info = "Create ". $total ." voucher for user #".$user_id.", ".$prm_code;

        //looping sisanya
        for($i=0; $i<$total; $i++){

            $loop_refresh = true;

            // keeps generating vc_code until vc_code not found in db
            while ($loop_refresh) {
                $vc_code = $this->promodb->generate_voucher_code($prm_code);
                $voucher = $this->promodb->get_voucher_custom_filter(' AND vc_code = ?', [$vc_code]);

                if (!$voucher) {
                    $loop_refresh = false;
                }
            }

            //create new voucher
            $data = [
                'prm_id' => $prm_id,
                'user_id' => $user_id,
                'vc_code' => $vc_code,
                'vc_status' => $status['active'],
                'created_date' => date('Y-m-d H:i:s'),
            ];
            $this->promodb->insert_voucher($data);
            $vc_id = $this->db->insert_id();

            $info .= ", vc:".$vc_id;

        }
        $logthis->info($info);
    }

    private function check_promo_employee()
    {
        $logthis    = $this->log4php($this->log_key, APPLOG );

        $promo_type = $this->config->item('promo')['type'];
        $discount_type = $this->config->item('promo')['discount_type'];
        $status = $this->config->item('promo')['status'];
        $promo_code = $this->config->item('promo')['promo_code'];
        $store_type = $this->config->item('store')['type'];

        //checking promo
        $promo_name = 'Otten Voucher '.date('m/y');
        $promo_custom_code = $promo_code['emp'].'OT'.date('ym');
        $promo_start = date('Y-m-01');
        $promo_end = date('Y-m-t 23:59:59');
        $promo = $this->promodb->get_promo_custom_filter(' AND prm_custom_code = ?', [$promo_custom_code]);
        $voucher_default = $this->promodb->get_voucher_default_by_vcdef_code($promo_code['emp']);

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
                'delivery_type' => $store_type['pickup_only'],
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
                'prm_img' => 'promo/freecup_190116.png',
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

    private function get_data_employee()
    {
        $arr_emp[] = array('phone' => '+628568511172');
        $arr_emp[] = array('phone' => '+6282163639696');
        $arr_emp[] = array('phone' => '+6282272879168');
        $arr_emp[] = array('phone' => '+6282143284923');
        $arr_emp[] = array('phone' => '+6287888566730');
        $arr_emp[] = array('phone' => '+6285804119997');
        $arr_emp[] = array('phone' => '+6281906378351');
        $arr_emp[] = array('phone' => '+628119582826');
        $arr_emp[] = array('phone' => '+62895600438047');
        $arr_emp[] = array('phone' => '+6285776030060');
        $arr_emp[] = array('phone' => '+6287773786937');
        $arr_emp[] = array('phone' => '+6287776608778');
        $arr_emp[] = array('phone' => '+6282210920353');
        $arr_emp[] = array('phone' => '+6287770386562');
        $arr_emp[] = array('phone' => '+6287883488542');
        $arr_emp[] = array('phone' => '+6285718351535');
        $arr_emp[] = array('phone' => '+6281284277252');
        $arr_emp[] = array('phone' => '+6287884151269');
        $arr_emp[] = array('phone' => '+628128151005');
        $arr_emp[] = array('phone' => '+6281212732656');
        $arr_emp[] = array('phone' => '+6285691977782');
        $arr_emp[] = array('phone' => '+6281313406276');
        $arr_emp[] = array('phone' => '+6283877267514');
        $arr_emp[] = array('phone' => '+6283827433041');
        $arr_emp[] = array('phone' => '+628888944636');
        $arr_emp[] = array('phone' => '+6285710971126');
        $arr_emp[] = array('phone' => '+6282114919907');
        $arr_emp[] = array('phone' => '+6287772932226');
        $arr_emp[] = array('phone' => '+6287885832677');
        $arr_emp[] = array('phone' => '+6281318840021');
        $arr_emp[] = array('phone' => '+6281384992020');
        $arr_emp[] = array('phone' => '+6287888082836');
        $arr_emp[] = array('phone' => '+62817722344');
        $arr_emp[] = array('phone' => '+6282275121178');
        $arr_emp[] = array('phone' => '+6281290886035');
        $arr_emp[] = array('phone' => '+62895391510812');
        $arr_emp[] = array('phone' => '+6285811492492');
        $arr_emp[] = array('phone' => '+6281394420922');
        $arr_emp[] = array('phone' => '+628998712298');
        $arr_emp[] = array('phone' => '+6287777309699');
        $arr_emp[] = array('phone' => '+6282166696885');
        $arr_emp[] = array('phone' => '+6282267571811');
        $arr_emp[] = array('phone' => '+628567725059');
        $arr_emp[] = array('phone' => '+628111919931');
        $arr_emp[] = array('phone' => '+6285959117771');
        $arr_emp[] = array('phone' => '+6281905299051');
        $arr_emp[] = array('phone' => '+6285719701990');
        $arr_emp[] = array('phone' => '+6281315664367');
        $arr_emp[] = array('phone' => '+6281285322357');
        $arr_emp[] = array('phone' => '+6285212065318');
        $arr_emp[] = array('phone' => '+628121662020');
        $arr_emp[] = array('phone' => '+6281216712120');
        $arr_emp[] = array('phone' => '+6281287888954');
        $arr_emp[] = array('phone' => '+6285779412846');
        $arr_emp[] = array('phone' => '+6287776492200');
        $arr_emp[] = array('phone' => '+6289523927196');
        return $arr_emp;
    }

}

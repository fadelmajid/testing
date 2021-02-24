<?php
defined('BASEPATH') OR exit('No direct script access allowed');


class Croncny extends MY_Service {

    function __construct()
	{
		parent::__construct();

        $this->load->model('promodb');
        $this->load->model('userdb');
        $this->log_key = 'generate_cny_voucher';

	}

    public function index()
    {
        show_404();
    }

    public function generate_cny_voucher()
    {
        $logthis    = $this->log4php($this->log_key, APPLOG );

        //get promo id
        $promo_id   = $this->check_promo_cny();

        $promo_detail = $this->promodb->get_promo($promo_id);

        //generate to winner
        $limit_winner = 1;
        $all_winner = $this->get_data_winner();
        $this->process_additional_voucher($all_winner, $promo_id, $promo_detail->prm_custom_code, $limit_winner);

    }

    private function process_additional_voucher($all_data, $promo_id, $prm_custom_code, $limit)
    {
        foreach($all_data as $key=>$value){
            $today = date('Y-m-d');
            if($today >= $value['start'] && $today <= $value['end']){
                //select user by phone number
                $this->userdb->db->where('user_phone', $value['phone']);
                $this->userdb->db->limit(1);
                $query = $this->userdb->db->get($this->userdb->tbl_user);
                $detail_user = $query->row();

                //generate sebanyak yang dibutuhkan
                $this->generate_voucher($promo_id, $prm_custom_code, $detail_user->user_id, $limit);
            }
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

    private function check_promo_cny()
    {
        $logthis    = $this->log4php($this->log_key, APPLOG );

        $promo_type = $this->config->item('promo')['type'];
        $discount_type = $this->config->item('promo')['discount_type'];
        $status = $this->config->item('promo')['status'];
        $promo_code = 'CNY';

        //checking promo
        $promo_name = 'Golden FOREtune';
        $promo_custom_code = $promo_code.date('ymd');
        $promo_start = date('Y-m-d');
        $promo_end = date('Y-m-d 23:59:59');
        $promo = $this->promodb->get_promo_custom_filter(' AND prm_custom_code = ?', [$promo_custom_code]);
        $voucher_default = $this->promodb->get_voucher_default_by_vcdef_code($promo_code);

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
                'free_delivery' => true,
                'item_type' => 'blacklist',
                'item_list' => []
            ];

            //set data promo
            $data = [
                'prm_name' => $promo_name,
                'prm_custom_code' => $promo_custom_code,
                'prm_start' => $promo_start,
                'prm_end' => $promo_end,
                'prm_type' => $promo_type['generated'],
                'prm_img' => 'promo/freecup-imlek3.png',
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

    private function get_data_winner()
    {
        $arr_winner[] = array('name' => 'Ricken Rora', 'email' => 'rickenrora@gmail.com', 'phone' => '+6281294043330', 'start' => '2019-02-04', 'end' => '2019-03-08');
        $arr_winner[] = array('name' => 'Virsa May Dwi nadya', 'email' => 'virsamaydwi@gmail.com', 'phone' => '+6287831364571', 'start' => '2019-02-04', 'end' => '2019-03-08');

        $arr_winner[] = array('name' => 'Ryan', 'email' => 'ryanlov.aj@gmail.com', 'phone' => '+6281382700889', 'start' => '2019-02-08', 'end' => '2019-03-08');
        $arr_winner[] = array('name' => 'Maya Tundjoeng', 'email' => 'maya.tundjoeng@gmail.com', 'phone' => '+6287884037863', 'start' => '2019-02-08', 'end' => '2019-03-08');
        $arr_winner[] = array('name' => 'Juta Kartikasari', 'email' => 'million_angel@yahoo.com', 'phone' => '+6287774776843', 'start' => '2019-02-08', 'end' => '2019-03-08');
        $arr_winner[] = array('name' => 'Dimaz Setiawan', 'email' => 'dimasetiawan13@gmail.com', 'phone' => '+6281310008941', 'start' => '2019-02-08', 'end' => '2019-03-08');
        $arr_winner[] = array('name' => 'Bagus Alvin Candhika', 'email' => 'bagusalvin8@gmail.com', 'phone' => '+6287716402248', 'start' => '2019-02-08', 'end' => '2019-03-08');
        $arr_winner[] = array('name' => 'Anna', 'email' => 'chs.sunshine@gmail.com', 'phone' => '+6281280070031', 'start' => '2019-02-08', 'end' => '2019-03-08');
        $arr_winner[] = array('name' => 'Rini', 'email' => 'sririni2315@gmail.com', 'phone' => '+628111792400', 'start' => '2019-02-08', 'end' => '2019-03-08');
        $arr_winner[] = array('name' => 'Putri', 'email' => 'nurmasithap@yahoo.com', 'phone' => '+6281294633749', 'start' => '2019-02-08', 'end' => '2019-03-08');
        $arr_winner[] = array('name' => 'DanielF', 'email' => 'daniel_aimar_84@yahoo.com', 'phone' => '+6285817605559', 'start' => '2019-02-08', 'end' => '2019-03-08');
        $arr_winner[] = array('name' => 'Rilia', 'email' => 'riliaika19@gmail.com', 'phone' => '+628151657282', 'start' => '2019-02-08', 'end' => '2019-03-08');
        $arr_winner[] = array('name' => 'Angelina kwan', 'email' => 'angelina.k.kwan@gmail.com', 'phone' => '+6281288358171', 'start' => '2019-02-08', 'end' => '2019-03-08');
        $arr_winner[] = array('name' => 'Zsa Zsa', 'email' => 'ryzkyzsazsa@gmail.com', 'phone' => '+6281294369444', 'start' => '2019-02-08', 'end' => '2019-03-08');
        $arr_winner[] = array('name' => 'Richie Maureen', 'email' => 'rchmaureen@gmail.com', 'phone' => '+628176619291', 'start' => '2019-02-08', 'end' => '2019-03-08');
        $arr_winner[] = array('name' => 'Mike erliany', 'email' => 'mike_erliani@yahoo.com', 'phone' => '+628164807629', 'start' => '2019-02-08', 'end' => '2019-03-08');
        $arr_winner[] = array('name' => 'Jessica', 'email' => 'jessicamarvella@yahoo.com', 'phone' => '+6281219577571', 'start' => '2019-02-08', 'end' => '2019-03-08');


        $arr_winner[] = array('name' => 'Fadli', 'email' => 'fadlifadli20@icloud.com', 'phone' => '+6281288526919', 'start' => '2019-02-15', 'end' => '2019-03-16');
        $arr_winner[] = array('name' => 'Vita', 'email' => 'vit_arum@yahoo.com', 'phone' => '+6281288252220', 'start' => '2019-02-15', 'end' => '2019-03-16');
        $arr_winner[] = array('name' => 'Meiftah', 'email' => 'miftahmiftah335@gmail.com', 'phone' => '+6287883326912', 'start' => '2019-02-15', 'end' => '2019-03-16');
        $arr_winner[] = array('name' => 'Leny', 'email' => 'lenyng@hotmail.com', 'phone' => '+628111336791', 'start' => '2019-02-15', 'end' => '2019-03-16');
        $arr_winner[] = array('name' => 'melli', 'email' => 'melliasriani@gmail.com', 'phone' => '+628111090744', 'start' => '2019-02-15', 'end' => '2019-03-16');
        $arr_winner[] = array('name' => 'Putri', 'email' => 'jokowaluyopratama@gmail.com', 'phone' => '+6287886589981', 'start' => '2019-02-15', 'end' => '2019-03-16');
        $arr_winner[] = array('name' => 'toni', 'email' => 'antonidimanche@gmail.com', 'phone' => '+6285813445910', 'start' => '2019-02-15', 'end' => '2019-03-16');

        $arr_winner[] = array('name' => 'M ihsan', 'email' => 'ihsan_m37@yahoo.com', 'phone' => '+6285694057251', 'start' => '2019-02-25', 'end' => '2019-03-25');
        $arr_winner[] = array('name' => 'Josephine', 'email' => 'josephinefani@gmail.com', 'phone' => '+6287777500015', 'start' => '2019-02-26', 'end' => '2019-03-26');
        $arr_winner[] = array('name' => 'Shanandra', 'email' => 'shanandra.felita@hotmail.com', 'phone' => '+6281196207258', 'start' => '2019-03-09', 'end' => '2019-04-09');
        $arr_winner[] = array('name' => 'Mia Nur Aida', 'email' => 'mia.nur@aiesec.net', 'phone' => '+6287875601001', 'start' => '2019-03-12', 'end' => '2019-04-12');









        return $arr_winner;
    }

}
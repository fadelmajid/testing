<?php
defined('BASEPATH') OR exit('No direct script access allowed');


class Campaign_token extends MY_Service {

    function __construct()
	{
		parent::__construct();
	}

    public function index()
    {
        show_404();
    }

    public function generate_campaign_token()
    {
        $this->load->model('promodb');
        $info         = "";
        $logthis      = $this->log4php('generate_campaign_token', APPLOG );
        $status       = $this->config->item('order')['status']['completed'];
        $voucher_tgif = $this->config->item('promo')['promo_code']['tgif'];
        $start_date   = "2019-04-01 00:00:00";
        $end_date     = "2019-04-30 23:59:59";
        
        $data_user    = [
                        "status" => $status, 
                        "start_date" => $start_date, 
                        "end_date" => $end_date
                        ];
        
        //get data user who have orders and subtotal > 0
        $total_user     = $this->promodb->getall_user_order($data_user);
        $campaign_token = $this->promodb->getall_campaign_token();
        
        //if the campaign_token already, not generate again
        if($campaign_token)
        {
            echo "The campaign token has been generated\n";
        }else{
            //looping sebanyak user yang sudah order dan mempunyai subtotal > 0
            $user_generated = 0;
            $tot_token      = 0;
            $const          = 19040000;
            foreach($total_user as $user){
                $arr_data                   = $data_user;
                $arr_data["user_id"]        = $user->user_id;
                $arr_data["voucher_tgif"]   = $voucher_tgif;
                $transaction                = $this->promodb->get_total_transaction($arr_data);
                $total_transaction          = $transaction[0]->total;
                $total_tgif                 = $transaction[1]->total;

                if(!isset($total_transaction)){
                    $total_transaction = 0; 
                }
                if(!isset($total_tgif)){
                    $total_tgif        = 0;
                }

                $total        = $total_transaction + $total_tgif;

                //looping token per user
                $total_token  = floor($total / 5);

                if($total_token > 0){
                    $info    .= " Create ". $total_token ." token for user #".$user->user_id;
                    $token    = $this->promodb->generate_token($user->user_id, $total_token, $const);
                    if(!$token){
                        $info .= "Failed to create ". $total_token ." token for user #".$user->user_id;
                    } 
                    $const = $token;
                    $user_generated = $user_generated + 1;
                    $tot_token = $tot_token + $total_token;
                }
            }
            echo "Total User : ". count($total_user) . "\n";
            echo "Total User yang di generate : ". $user_generated . "\n";
            echo "Total User yang tidak di generate : ". (count($total_user) - $user_generated) . "\n";
            echo "Total token yang tergenerate : ". $tot_token ."\n";
        }

        $logthis->info($info);
    }
}

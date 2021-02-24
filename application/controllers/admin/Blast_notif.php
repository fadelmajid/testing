<?php
defined('BASEPATH') OR exit('No direct script access allowed');


class Blast_notif extends MY_Service {

    function __construct()
	{
		parent::__construct();
        $this->load->library('pushnotification');

	}

    public function index()
    {
        show_404();
    }

    public function buy_one_get_one(){
        $logthis = $this->log4php('buy_one_get_one', APPLOG);
        $data = $this->_data_user();
        $info = "";
        foreach($data as $push_token) {
            //push notif buy_one_get_one data
            $notif_data = [
                "push_token" => $push_token['atoken_pushnotif'],
                "text"       => 'PROMO! Buy 1 Get 1 + Free Delivery!',
            ];

            $response_push = $this->pushnotification->send_pushnotification($notif_data);
            $info = ' blast_push_token >> buy_one_get_one '. $push_token['user_id'] .', response push = '.$response_push;
            $logthis->info($info);
        }
        
    }

    public function cashback(){
        $logthis = $this->log4php('cashback', APPLOG);
        $data = $this->_data_user();
        $info = "";
        foreach($data as $push_token) {
            //push notif cashback data
            $notif_data = [
                "push_token" => $push_token['atoken_pushnotif'],
                "text"       => 'CASHBACK 100%! Only Today!',
            ];

            $response_push = $this->pushnotification->send_pushnotification($notif_data);
            $info = ' blast_push_token >> cashback '. $push_token['user_id'] .', response push = '.$response_push;
            $logthis->info($info);
        }
    }

    public function voucher_register(){
        $logthis = $this->log4php('voucher_register', APPLOG);
        $data = $this->_data_user();
        $info = "";
        foreach($data as $push_token) {
            //push notif voucher_register data
            $notif_data = [
                "push_token" => $push_token['atoken_pushnotif'],
                "text"       => 'Claim your FREE Coffee now + Free Delivery!',
            ];

            $response_push = $this->pushnotification->send_pushnotification($notif_data);
            $info = ' blast_push_token >> voucher_register '. $push_token['user_id'] .', response push = '.$response_push;
            $logthis->info($info);
        }
    }

    private function _data_user() {
        return array(
            array('user_id' => '0','atoken_pushnotif' => 'cZDSiXRRabY:APA91bFohVtyn4teQS5vnHxq_wxza_4bUoJokLV1mVICO-tEyfRxADbe8_MhlKm183pxyOQ1COQP5S9B0eOO-9VNRdhVRO4BNsJmGMujVjW59w7fsA4MbxUEzSW_f_MC41jvqyQwkYGN'),
            array('user_id' => '2','atoken_pushnotif' => 'c3pXQPuGHgg:APA91bEuw5scBjukACXI0F-nFiujV2QqA5OLdq3b-slFECjKhYKrvUq7OUQaUY-Njp7wYfofmrY4gu8ecDqA1ePtI-xOSz2zSrxqdmlHPlh9zuUDP-YZO-QohHZwmNektAN7NwQlJg0s'),
            // array('user_id' => '3','atoken_pushnotif' => 'cZmMdEhhZS8:APA91bHdvz1ex-AKERaDNshvIeyZ9lua0cerSRjDZLVemRz7rpIzMtj4Gxqpi6RUJ2MmplgbbqATrtd_KjdNhk70m0wTYvesc-wWJw1BHGBSEO7SjZzh_OMA_fZYl4W4AtXXkRQMhf6L'),
        );
    }

    public function backup_buy_one_get_one()
    {
        $table_name = 'buy_one_history';
        $this->load->dbforge();

        if (!$this->db->table_exists($table_name))
        {
            $fields = array(
                'buy_his_id' => array(
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => TRUE,
                    'auto_increment' => TRUE
                ),
                'user_id' => array(
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => TRUE,
                ),
                'atoken_pushnotif' => array(
                    'type' =>'TEXT',
                    'null' => TRUE,
                )
            );
            $this->dbforge->add_field($fields);
            $this->dbforge->add_key('buy_his_id', TRUE);
            $this->dbforge->add_key('user_id');

            $this->dbforge->create_table($table_name);
            echo 'Database created!';
        }

        $data = $this->_data_user();
        
        foreach($data as $buy_data) {
            $this->db->insert($table_name,$buy_data);
        }
        
    }

    public function backup_cashback()
    {
        $table_name = 'cashback_history';
        $this->load->dbforge();

        if (!$this->db->table_exists($table_name))
        {
            $fields = array(
                'cashback_id' => array(
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => TRUE,
                    'auto_increment' => TRUE
                ),
                'user_id' => array(
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => TRUE,
                ),
                'atoken_pushnotif' => array(
                    'type' =>'TEXT',
                    'null' => TRUE,
                )
            );
            $this->dbforge->add_field($fields);
            $this->dbforge->add_key('cashback_id', TRUE);
            $this->dbforge->add_key('user_id');

            $this->dbforge->create_table($table_name);
            echo 'Database created!';
        }

        $data = $this->_data_user();
        
        foreach($data as $buy_data) {
            $this->db->insert($table_name,$buy_data);
        } 
    }

    public function backup_voucher_register()
    {
        $table_name = 'voucher_register_history';
        $this->load->dbforge();

        if (!$this->db->table_exists($table_name))
        {
            $fields = array(
                'vrhis_id' => array(
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => TRUE,
                    'auto_increment' => TRUE
                ),
                'user_id' => array(
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => TRUE,
                ),
                'atoken_pushnotif' => array(
                    'type' =>'TEXT',
                    'null' => TRUE,
                )
            );
            $this->dbforge->add_field($fields);
            $this->dbforge->add_key('vrhis_id', TRUE);
            $this->dbforge->add_key('user_id');

            $this->dbforge->create_table($table_name);
            echo 'Database created!';
        }

        $data = $this->_data_user();
        
        foreach($data as $buy_data) {
            $this->db->insert($table_name,$buy_data);
        } 
    }
}

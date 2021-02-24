<?php
defined('BASEPATH') OR EXIT('No direct script access allowed');
/**
*
*/
class Walletdb extends MY_Model {
    function __construct()
    {
        parent::__construct();
    }
    
    //=== BEGIN WALLET
    function get_wallet($id)
    {
        $sql = "SELECT * FROM {$this->tbl_wallet} WHERE user_id = ? ORDER BY uwal_id ASC LIMIT 1";
        $query= $this->db->query($sql, [$id]);
        return $query->row();
    }

    function insert_wallet($data)
    {
        $ret = $this->db->insert($this->tbl_wallet, $data);
        if($ret){
            return $this->db->insert_id();
        }else{
            return FALSE;
        }
    }

    function update_wallet($id, $data)
    {
        $this->db->where('uwal_id', $id);
        return $this->db->update($this->tbl_wallet, $data); 
    }

    function refund_wallet($order)
    {
        $user_id = $order->user_id;
        $wallet = $this->get_wallet($user_id);
        
        $new_balance = $wallet->uwal_balance + $order->uor_total;
        $now = date('Y-m-d H:i:s');

        $data = [
            'uwal_balance' => $new_balance,
            'updated_date' => $now
        ];
        
        if ($this->update_wallet($wallet->uwal_id, $data)) {
            $uwhis_type = $this->config->item('wallet')['history_type'];
            $data = [
                'uwal_id' => $wallet->uwal_id,
                'user_id' => $wallet->user_id,
                'uwhis_type' => $uwhis_type['refund'],
                'uwhis_primary' => $order->uor_id,
                'uwhis_nominal' => $order->uor_total,
                'created_date' => $now,
            ];
            return $this->insert_history($data);
        }
        return false;
    }
    //=== END WALLET

    //=== START WALLET HISTORY
    function getall_history($id, $order_by = ' created_date DESC')
    {
        $history_type = $this->config->item('wallet')['history_type'];

        $data = [
            $history_type['order'],
            $history_type['refund'],
            $id,
            $history_type['order'],
            $history_type['refund'],
            $id
        ];

        $sql =
            "SELECT *, null as uor_id, null as uor_code FROM {$this->tbl_wallet_history} 
            WHERE uwhis_type NOT IN (?, ?) AND uwal_id = ? 
            UNION ALL 
            SELECT uwhis.*, uor.uor_id, uor.uor_code FROM {$this->tbl_wallet_history} uwhis 
            JOIN user_order uor ON uwhis.uwhis_primary = uor.uor_id 
            WHERE uwhis_type in (?, ?) AND uwhis.uwal_id = ? 
            ORDER BY {$order_by}";
        
        $query= $this->db->query($sql, $data);
        return $query->result();
    }

    function insert_history($data)
    {
        $ret = $this->db->insert($this->tbl_wallet_history, $data);
        if($ret){
            return $this->db->insert_id();
        }else{
            return FALSE;
        }
    }

    function getall_topuphis_valid($where = '', $data = [], $order = '', $limit=0) {
        $orderby    = ($order == "" ? " utop.user_id ASC " : $order);

        // search dulu.
        $sql    = "SELECT utop.user_id, SUM(utop.utop_nominal) as total_topup, COUNT(utop.utop_nominal) AS count_topup, utop.utop_payment,
                    user.user_name, user.user_email as email, user.user_phone
                    FROM {$this->tbl_user_topup} AS utop
                    INNER JOIN {$this->tbl_user} AS user ON utop.user_id = user.user_id
                    WHERE 1=1 {$where} ORDER BY {$orderby}";

        if($limit > 0){
            $data[] = $limit;
            $sql    .= " LIMIT ? ";
        }
        $query      = $this->db->query($sql, $data);
        
        return $query->result();
    }
    //=== END WALLET HISTORY
}
?>

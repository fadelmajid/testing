<?php
defined('BASEPATH') OR EXIT('No direct script access allowed');
/**
*
*/
class Userdb extends MY_Model {
    function __construct()
    {
        parent::__construct();
    }

    //=== BEGIN USER
    function get($id)
    {
        $sql    = "SELECT user.*, wallet.uwal_balance 
                    FROM $this->tbl_user AS user 
                    INNER JOIN $this->tbl_wallet AS wallet ON wallet.user_id = user.user_id
                    WHERE user.user_id = ? LIMIT 1";
        $query = $this->db->query($sql, $id);
        return $query->row();
    }

    function getall_user($where = '', $data = [], $order = '', $limit=0)
    {
        $orderby    = ($order == "" ? " user_name ASC " : $order);

        // search dulu.
        $sql    = "SELECT user.*, uwal.uwal_balance 
                    FROM $this->tbl_user AS user 
                    INNER JOIN $this->tbl_wallet AS uwal ON uwal.user_id = user.user_id
                    WHERE 1=1 ". $where ." ORDER BY ".$orderby;
        if($limit > 0){
            $data[] = $limit;
            $sql    .= " LIMIT ? ";
        }
        $query      = $this->db->query($sql, $data);
        
        return $query->result();
    }

    function getpaging($where = '', $data = [], $order_by = ' user.user_name ASC', $page = 1)
    {
        $page       = ($page <= 0 ? 1 : intval($page));
        $last_row   = $this->row_per_page * $page;
        $first_row  = $last_row - $this->row_per_page;

        $sql =
            "SELECT user.*, uwal.uwal_balance FROM {$this->tbl_user} user
            JOIN {$this->tbl_wallet} uwal ON user.user_id = uwal.user_id
            WHERE 1=1 {$where} ORDER BY {$order_by}";
        $query= $this->db->query($sql, $data);
        $total_row  = $query->num_rows();

        $data[] = $this->row_per_page;
        $data[] = intval($first_row > $total_row ? $total_row : $first_row);
        $sql .= " LIMIT ? OFFSET ? ";
        $query = $this->db->query($sql, $data);

        return [
            'data' => $query->result(),
            'total_row' => $total_row,
            'per_page' => $this->row_per_page,
        ];
    }

    function update($user_id, $data)
    {
        $this->db->where('user_id', $user_id);
        return $this->db->update($this->tbl_user, $data);
    }

    function getpaging_topup($where = '', $data = [], $order_by = ' utop.utop_id DESC', $page = 1)
    {
        $page       = ($page <= 0 ? 1 : intval($page));
        $last_row   = $this->row_per_page * $page;
        $first_row  = $last_row - $this->row_per_page;

        $sql =
            "SELECT utop.*, user.user_name, user.user_email, user.user_phone FROM {$this->tbl_user_topup} utop
            JOIN {$this->tbl_user} user ON utop.user_id = user.user_id
            WHERE 1=1 {$where} ORDER BY {$order_by}";
        $query= $this->db->query($sql, $data);
        $total_row  = $query->num_rows();

        $data[] = $this->row_per_page;
        $data[] = intval($first_row > $total_row ? $total_row : $first_row);
        $sql .= " LIMIT ? OFFSET ? ";
        $query = $this->db->query($sql, $data);

        return [
            'data' => $query->result(),
            'total_row' => $total_row,
            'per_page' => $this->row_per_page,
        ];
    }
    //=== END USER

    function insert_user_topups($data) { 
        //load model
        $this->load->model('walletdb');
        //start transaction
        $this->db->trans_begin();
        //insert data to user topups
        $user_topup = $this->db->insert($this->tbl_user_topup, $data);
        if(!$user_topup){
            $this->db->trans_rollback();
        }
        $user_topup_id = $this->db->insert_id();
        //get and check wallet if exist
        $check_balance = $this->walletdb->get_wallet($data['user_id']);

        //update balance
        $current_balance = $check_balance->uwal_balance + $data['utop_nominal'];
        $data_wallet = [
            'user_id' => $data['user_id'],
            'uwal_balance' => $current_balance,
            'updated_date' => $data['created_date']
        ];
        $this->walletdb->update_wallet($check_balance->uwal_id, $data_wallet);

        //insert wallet history
        $data_history_wallet = [
            'uwal_id'       => $check_balance->uwal_id,
            'user_id'       => $data['user_id'],
            'uwhis_type'    => 'topup',
            'uwhis_primary' => $user_topup_id,
            'uwhis_nominal'  => $data['utop_nominal'],
            'created_date'  => $data['created_date']
        ];
        $this->walletdb->insert_history($data_history_wallet);

        if($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
        } else {
            $this->db->trans_commit();
            return true;
        }
        
    }

    function update_user_withdraw($data) { 
        //load model
        $this->load->model('walletdb');
        //start transaction
        $this->db->trans_begin();
        //insert data to user topups
        $user_topup = $this->db->insert($this->tbl_user_withdraw, $data);
        if(!$user_topup){
            $this->db->trans_rollback();
        }
        $user_withdraw_id = $this->db->insert_id();
        //get and check wallet if exist
        $check_balance = $this->walletdb->get_wallet($data['user_id']);

        //update balance
        $current_balance = $check_balance->uwal_balance - $data['uwd_nominal'];
        $data_wallet = [
            'user_id' => $data['user_id'],
            'uwal_balance' => $current_balance,
            'updated_date' => $data['created_date'],
        ];
        $this->walletdb->update_wallet($check_balance->uwal_id, $data_wallet);

        //insert wallet history
        $data_history_wallet = [
            'uwal_id'       => $check_balance->uwal_id,
            'user_id'       => $data['user_id'],
            'uwhis_type'    => 'withdraw',
            'uwhis_primary' => $user_withdraw_id,
            'uwhis_nominal' => -$data['uwd_nominal'],
            'created_date'  => $data['created_date']
        ];
        $this->walletdb->insert_history($data_history_wallet);

        if($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
        } else {
            $this->db->trans_commit();
            return true;
        }
        
    }

    //=== BEGIN USER ADDRESS

    function getall_address($where = '', $data = [], $order = '', $limit=0)
    {
        $orderby    = ($order == "" ? " uadd.uadd_id ASC " : $order);

        // search dulu row nya.
        $sql        = "SELECT uadd.*
                        FROM ".$this->tbl_user_address." uadd
                        WHERE 1=1 ". $where ." ORDER BY ".$orderby;
        if($limit > 0){
            $data[] = $limit;
            $sql    .= " LIMIT ? ";
        }
        $query      = $this->db->query($sql, $data);

        return $query->result();
    }
    //=== END USER ADDRESS

    //=== BEGIN USER REFERRAL
    function get_referral_custom_filter($where = '', $data = [])
    {
        $sql = "SELECT * FROM {$this->tbl_user_referral} WHERE 1=1 {$where} LIMIT 1";
        $query= $this->db->query($sql, $data);
        return $query->row();
    }

    function update_referral($uref_id, $data)
    {
        $this->db->where('uref_id', $uref_id);
        return $this->db->update($this->tbl_user_referral, $data);
    }

    function getpaging_referral($where = '', $data = [], $order_by = ' utop.utop_id DESC', $page = 1)
    {
        $page       = ($page <= 0 ? 1 : intval($page));
        $last_row   = $this->row_per_page * $page;
        $first_row  = $last_row - $this->row_per_page;

        $sql = "SELECT uref.*, from_data.user_name AS from_name, from_data.user_phone AS from_phone, from_data.user_email AS from_email, 
        to_data.user_name AS to_name, to_data.user_phone AS to_phone, to_data.user_email AS to_email FROM user_referral uref
        INNER JOIN {$this->tbl_user} from_data ON uref.uref_from = from_data.user_id
        INNER JOIN {$this->tbl_user} to_data ON uref.uref_to = to_data.user_id
        WHERE 1=1 {$where} ORDER BY {$order_by}";
        $query= $this->db->query($sql, $data);
        $total_row  = $query->num_rows();

        $data[] = $this->row_per_page;
        $data[] = intval($first_row > $total_row ? $total_row : $first_row);
        $sql .= " LIMIT ? OFFSET ? ";
        $query = $this->db->query($sql, $data);

        return [
            'data' => $query->result(),
            'total_row' => $total_row,
            'per_page' => $this->row_per_page,
        ];
    }
    //=== END USER REFERRAL

    //=== START USER WITHDRAW
    function getpaging_withdraw($where = '', $data = [], $order_by = ' uwd.uwd_id DESC', $page = 1)
    {
        $page       = ($page <= 0 ? 1 : intval($page));
        $last_row   = $this->row_per_page * $page;
        $first_row  = $last_row - $this->row_per_page;

        $sql =
            "SELECT uwd.*, user.user_name, user.user_email, bank.bank_code, user.user_phone FROM {$this->tbl_user_withdraw} uwd
            INNER JOIN {$this->tbl_user} user ON uwd.user_id = user.user_id
            LEFT JOIN {$this->tbl_bank} bank ON bank.bank_id = uwd.ubank_id
            WHERE 1=1 {$where} ORDER BY {$order_by}";
        $query= $this->db->query($sql, $data);
        $total_row  = $query->num_rows();

        $data[] = $this->row_per_page;
        $data[] = intval($first_row > $total_row ? $total_row : $first_row);
        $sql .= " LIMIT ? OFFSET ? ";
        $query = $this->db->query($sql, $data);

        return [
            'data' => $query->result(),
            'total_row' => $total_row,
            'per_page' => $this->row_per_page,
        ];
    }
    //=== END USER WITHDRAW

    function getall_push_token($where = '', $data = [], $order = '', $limit=0)
    {
        $orderby    = ($order == "" ? " upushtoken_id ASC " : $order);

        // search dulu.
        $sql    = "SELECT *
                    FROM $this->tbl_user_pushtoken 
                    WHERE 1=1 ". $where ." ORDER BY ".$orderby;
        if($limit > 0){
            $data[] = $limit;
            $sql    .= " LIMIT ? ";
        }
        $query = $this->db->query($sql, $data);
        
        return $query->result();
    }


    //=== BEGIN PROMO
    function get_va($id)
    {
        $sql = "SELECT va.*, user.user_name, user.user_email, user.user_phone, bank.bank_code
                    FROM {$this->tbl_user_va} va
                    INNER JOIN {$this->tbl_user} user ON va.user_id = user.user_id
                    INNER JOIN {$this->tbl_bank} bank ON bank.bank_id = va.bank_id
                    WHERE va.uva_id = ? LIMIT 1";
        $query = $this->db->query($sql, [$id]);
        return $query->row();
    }

    function getall_va($where = '', $data = [], $order = '', $limit=0)
    {
        $orderby    = ($order == "" ? " va.created_date DESC " : $order);

        // search dulu row nya.
        $sql = "SELECT va.*, user.user_name, user.user_email, user.user_phone, bank.bank_code
                    FROM {$this->tbl_user_va} va
                    INNER JOIN {$this->tbl_user} user ON va.user_id = user.user_id
                    INNER JOIN {$this->tbl_bank} bank ON bank.bank_id = va.bank_id
                WHERE 1=1 ". $where ." ORDER BY ".$orderby;
        if($limit > 0){
            $data[] = $limit;
            $sql    .= " LIMIT ? ";
        }
        $query      = $this->db->query($sql, $data);

        return $query->result();
    }

    function getpaging_va($where = '', $data = [], $order = ' va.created_date ASC ', $page = 1)
    {
        $page       = ($page <= 0 ? 1 : intval($page));
        $last_row   = $this->row_per_page * $page;
        $first_row  = $last_row - $this->row_per_page;

        $orderby    = ($order == "" ? " va.created_date DESC " : $order);

        // search dulu row nya.
        $sql = "SELECT va.*, user.user_name, user.user_email, user.user_phone, bank.bank_code
                    FROM {$this->tbl_user_va} va
                    INNER JOIN {$this->tbl_user} user ON va.user_id = user.user_id
                    INNER JOIN {$this->tbl_bank} bank ON bank.bank_id = va.bank_id
                WHERE 1=1 {$where} ORDER BY {$orderby}";

        $query      = $this->db->query($sql, $data);
        $total_row  = $query->num_rows();

        $data[] = $this->row_per_page;
        $data[] = intval($first_row > $total_row ? $total_row : $first_row);
        $sql        .= " LIMIT ? OFFSET ? ";
        $query      = $this->db->query($sql, $data);
        // echo $this->db->last_query();
        $result['data']         = $query->result();
        $result['total_row']    = $total_row;
        $result['perpage']      = $this->row_per_page;
        return $result;
    }

    //=== START EMONEY
    function getpaging_emoney($where = '', $data = [], $order = "", $page = 1)
    {
        $page       = ($page <= 0 ? 1 : intval($page));
        $last_row   = $this->row_per_page * $page;
        $first_row  = $last_row - $this->row_per_page;

        $orderby    = ($order == "" ? " user.user_name ASC, pymtd.pymtd_name ASC " : $order);

        $sql =
            "SELECT emy.emy_id, user.user_name, pymtd.pymtd_name, emy.emy_number, emy.created_date, emy.updated_date 
                FROM {$this->tbl_user_emoney} emy
                INNER JOIN {$this->tbl_user} 
                ON user.user_id=emy.user_id
                INNER JOIN {$this->tbl_payment_method} pymtd
                ON pymtd.pymtd_id=emy.pymtd_id
                WHERE 1=1 {$where} ORDER BY $orderby";

        $query      = $this->db->query($sql, $data);
        $total_row  = $query->num_rows();

        $data[]     = $this->row_per_page;
        $data[]     = intval($first_row > $total_row ? $total_row : $first_row);
        $sql       .= " LIMIT ? OFFSET ? ";
        $query      = $this->db->query($sql, $data);

        $result['data']         = $query->result();
        $result['total_row']    = $total_row;
        $result['perpage']      = $this->row_per_page;
        return $result;
    }
    //=== END EMONEY

    //>>Start User Email Token<<
    function getpaging_email_token($where = '', $data = [], $order = "", $page = 1) {
        $page       = ($page <= 0 ? 1 : intval($page));
        $last_row   = $this->row_per_page * $page;
        $first_row  = $last_row - $this->row_per_page;

        $order_by   = ($order == "" ? " uet.uet_id ASC" : $order);

        $sql        =   "SELECT uet.uet_id, uet.user_id, uet.user_email, uet.uet_token, uet.uet_status,
                        uet.expired_date, uet.created_date, uet.updated_date
                        FROM {$this->tbl_user_email_token} uet
                        WHERE 1=1 {$where} ORDER BY {$order_by}";
                        
        $query      = $this->db->query($sql, $data);
        $total_row  = $query->num_rows();
        $data[]     = $this->row_per_page;
        $data[]     = intval($first_row > $total_row ? $total_row : $first_row);
        $sql       .= " LIMIT ? OFFSET ? ";
        $query      = $this->db->query($sql, $data);

        return [
            'data'      => $query->result(),
            'total_row' => $total_row,
            'per_page'  => $this->row_per_page,
        ];
        
    }

    function getall_emailtokenhis_valid($where = '', $data = [], $order = '', $limit=0) {
        $orderby    = ($order == "" ? " uet.uet_id ASC " : $order);

        $sql        =   "SELECT uet.uet_id, user.user_id, user.user_email, uet.uet_token, uet.uet_status,
                        uet.expired_date, uet.created_date, uet.updated_date as user.user_id, email,
                        FROM {$this->tbl_user_email_token} AS uet 
                        INNER JOIN {$this->tbl_user} AS user ON uet.user_id = user.user_id
                        WHERE 1=1 {$where} ORDER BY {$orderby}";

        if($limit > 0){
            $data[] = $limit;
            $sql    .= " LIMIT ? ";
        }

        $query      = $this->db->query($sql, $data);
        return $query->result();
    }
    //>>End User Email Token<<

    // START BIRTHDAY
    public function get_all_birthday_user($where = '', $data = [], $order_by = ' user_id ASC ', $limit = 0)
    {
        $sql = "SELECT * FROM {$this->tbl_user} WHERE 1=1 {$where} ORDER BY {$order_by} ";
        if($limit > 0){
            $data[] = $limit;
            $sql    .= " LIMIT ? ";
        }

        $query      = $this->db->query($sql, $data);
        return $query->result();
    }
    // END BIRTHDAY

    // BEGIN CART
    public function clean_cart($date)
    {
        //remove cart dan cart product yang saling berkaitan
        $sql = "DELETE cart, cart_product
                FROM cart
                INNER JOIN cart_product ON cart.cart_id = cart_product.cart_id
                WHERE cart.created_date < ? AND (cart.updated_date < ? OR cart.updated_date IS NULL)";
        $this->db->query($sql, array($date, $date));

        //remove cart yang tidak ada cart_product
        $sql = "DELETE
                FROM cart
                WHERE cart.created_date < ? AND (cart.updated_date < ? OR cart.updated_date IS NULL)";
        return $this->db->query($sql, array($date, $date));

    }

    // END CART
}
?>

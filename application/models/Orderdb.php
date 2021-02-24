<?php
defined('BASEPATH') OR EXIT('No direct script access allowed');

class Orderdb extends MY_Model {
    function __construct()
    {
        parent::__construct();
    }

    //=== BEGIN ORDER
    function get_order($id)
    {
        $sql = "SELECT uor.*,
                    user.user_name, user.user_code, user.user_email, user.user_phone, user.user_email_verified, user.user_img, user.user_status, user.last_login, user.last_activity,
                    uoradd.uoradd_id, uoradd.st_id, uoradd.uadd_title, uoradd.uadd_person, uoradd.uadd_notes, uoradd.uadd_phone, uoradd.uadd_street, uoradd.uadd_lat, uoradd.uadd_long,
                    st.st_name, st.st_type, st.st_phone, st.st_address, st.st_lat, st.st_long, st.st_open, st.st_close, st.st_delivery_open, st.st_delivery_close, st.st_status, st.st_courier,
                    pymtd.pymtd_name, pymtd.pymtd_id, pymtd.pymtd_code, uorpy.pyhis_id, uorpy.pyhis_data
                    FROM {$this->tbl_order} uor
                    INNER JOIN {$this->tbl_user} user ON uor.user_id = user.user_id
                    INNER JOIN {$this->tbl_user_order_payment} uorpy ON uorpy.uor_id = uor.uor_id
                    INNER JOIN {$this->tbl_payment_method} pymtd ON pymtd.pymtd_id = uorpy.pymtd_id
                    INNER JOIN {$this->tbl_order_address} uoradd ON uor.uor_id = uoradd.uor_id
                    INNER JOIN {$this->tbl_store} st ON uoradd.st_id = st.st_id
                    WHERE uor.uor_id = ? LIMIT 1";
        $query = $this->db->query($sql, [$id]);
        return $query->row();
    }

    function getpaging_order($where="", $data=array(), $order="", $page=1)
    {
        $page       = ($page <= 0 ? 1 : intval($page));
        $last_row   = $this->row_per_page * $page;
        $first_row  = $last_row - $this->row_per_page;

        $orderby    = ($order == "" ? " uor_delivery_type ASC, uor_status ASC, uor_date ASC " : $order);

        // search dulu row nya.
        $sql        = "SELECT uor.*, user.user_name, user.user_phone, user.user_email, st.st_name, pymtd.pymtd_name, uoradd.uadd_person, uoradd.uadd_phone, st.st_id, st.st_courier, pymtd.pymtd_id
                        FROM {$this->tbl_order} uor
                        INNER JOIN  {$this->tbl_user} user ON uor.user_id = user.user_id
                        INNER JOIN {$this->tbl_user_order_payment} uorpy ON uorpy.uor_id = uor.uor_id
                        INNER JOIN {$this->tbl_payment_method} pymtd ON pymtd.pymtd_id = uorpy.pymtd_id
                        INNER JOIN {$this->tbl_order_address} uoradd ON uor.uor_id = uoradd.uor_id
                        INNER JOIN {$this->tbl_store} st ON uoradd.st_id = st.st_id
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

    function getall_order($where = '', $data = [], $order = '', $limit=0)
    {
        $orderby    = ($order == "" ? " uor_delivery_type ASC, uor_status ASC, uor_date ASC " : $order);

        // search dulu row nya.
        $sql        = "SELECT uor.*, user.user_name, user.user_phone, user.user_email, st.st_name, pymtd.pymtd_name, uoradd.uadd_person, uoradd.uadd_phone, st.st_courier, pymtd.pymtd_id
                        FROM ".$this->tbl_order." uor
                        INNER JOIN ". $this->tbl_user ." user ON uor.user_id = user.user_id
                        INNER JOIN ". $this->tbl_user_order_payment ." uorpy ON uorpy.uor_id = uor.uor_id
                        INNER JOIN ". $this->tbl_payment_method ." pymtd ON pymtd.pymtd_id = uorpy.pymtd_id
                        INNER JOIN ". $this->tbl_order_address ." uoradd ON uor.uor_id = uoradd.uor_id
                        INNER JOIN ". $this->tbl_store ." st ON uoradd.st_id = st.st_id
                        WHERE 1=1 ". $where ." ORDER BY ".$orderby;
        if($limit > 0){
            $data[] = $limit;
            $sql    .= " LIMIT ? ";
        }
        $query      = $this->db->query($sql, $data);

        return $query->result();
    }

    function get_order_courier($uor_id){
        $this->db->where('uor_id', $uor_id);
        $query = $this->db->get($this->tbl_user_order_courier);
        return $query->row();
    }

    function update_order($uor_id, $data)
    {
        $this->db->where('uor_id', $uor_id);
        return $this->db->update($this->tbl_order, $data);
    }

    function update_order_address($uoradd_id, $data)
    {
        $this->db->where('uoradd_id', $uoradd_id);
        return $this->db->update($this->tbl_order_address, $data);
    }

    function update_order_status($uor_id, $action, $adm_id)
    {
        $this->load->model('locktransdb');
        $this->load->model('userdb');
        $this->load->model('promodb');
        $this->load->model('walletdb');
        $this->load->model('admindb');

        $arr_admin = $this->admindb->getarr_admin();

        $allowed_action = $this->config->item('order')['action'];
        $order_status = $this->config->item('order')['status'];
        $delivery_type = $this->config->item('order')['delivery_type'];
        $new_order_status = '';
        $now = date('Y-m-d H:i:s');
        $status = true;
        $msg = 'Order Status Update failed!';
        $hit_3rd_party = false;

        //begin transaction
        $this->db->trans_begin();

        // add lock trans
        $this->locktransdb->insert("Update order status by ". (isset($arr_admin[$adm_id]) ? $arr_admin[$adm_id] : 'ID:'.$adm_id));

        // validate order and its status
        $order = $this->get_order($uor_id);
        $where_voucher = " AND uor_id = ? ";
        $voucher_data = $this->getall_order_voucher($select = "", $where_voucher, [$uor_id]);
        if (!$order) {
            $status = false;
            $msg = 'Order not found.';
        } elseif (in_array($order->uor_status, [$order_status['completed'], $order_status['cancelled']])) {
            $status = false;
            $msg = 'Order status is already completed/cancelled.';
        }

        if ($status) {
            // get new order status
            if ($action === $allowed_action['cancel']) {
                $new_order_status = $order_status['cancelled'];
            } elseif ($order->uor_delivery_type === $delivery_type['pickup']) {
                if ($order->uor_status === $order_status['paid']) {
                    $new_order_status = $order_status['in_process'];
                } elseif ($order->uor_status === $order_status['in_process']) {
                    $new_order_status = $order_status['ready_for_pickup'];
                } elseif ($order->uor_status === $order_status['ready_for_pickup']) {
                    $new_order_status = $order_status['completed'];
                }
            } elseif ($order->uor_delivery_type === $delivery_type['delivery']){
                if ($order->uor_status === $order_status['paid']) {
                    $new_order_status = $order_status['in_process'];
                    $hit_3rd_party = true;
                } elseif ($order->uor_status === $order_status['in_process']) {
                    $new_order_status = $order_status['on_delivery'];
                } elseif ($order->uor_status === $order_status['on_delivery']) {
                    $new_order_status = $order_status['completed'];
                }
            }

            // update order status
            $data = [
                'uor_status' => $new_order_status,
                'updated_by' => $adm_id,
                'updated_date' => $now
            ];
            $this->update_order($order->uor_id, $data);

            // if status is completed, find referral where purchased_status is false
            $vc_id = '';
            $ref_email = false;
            if ($new_order_status === $order_status['completed']) {
                $where = ' AND uref_to = ? AND uref_to_purchased = ? ';
                $data = [$order->user_id, 0];
                $user_referral = $this->userdb->get_referral_custom_filter($where, $data);

                // generate referral only if user_referral is found
                if (!empty($user_referral)) {
                    // generate referral voucher for uref_from
                    $vc_id = $this->promodb->generate_referral_voucher($user_referral->uref_from);

                    // set uref_to_purchased to true
                    $data = [
                        'uref_to_purchased' => 1,
                        'updated_date' => $now
                    ];
                    $this->userdb->update_referral($user_referral->uref_id, $data);
                    $ref_email = true;
                }
            }

            // if status is cancelled, refund wallet if uor_total > 0
            if ($new_order_status === $order_status['cancelled']) {
                if ($order->uor_total > 0) {
                    $this->walletdb->refund_wallet($order);
                }

                // if order used voucher, rollback voucher
                if (!empty($voucher) && count($voucher) > 0) {
                    $this->promodb->rollback_used_voucher($order->uor_id);
                }
            }
        }

        // end transaction
        if (!$this->db->trans_status() || !$status) {
            $this->db->trans_rollback();
        } else {
            $msg = 'Success';
            $this->db->trans_commit();
        }

        return [
            'msg' => $msg,
            'uor_id' => $uor_id,
            'new_order_status' => $new_order_status,
            'hit_3rd_party' => $hit_3rd_party,
            'ref_email' => $ref_email,
            'vc_id' => $vc_id
        ];
    }

    function change_order_completed($uor_id, $status, $adm_id)
    {
        $this->load->model('locktransdb');
        $this->load->model('userdb');
        $this->load->model('promodb');
        $this->load->model('admindb');

        $arr_admin = $this->admindb->getarr_admin();
        $now = date('Y-m-d H:i:s');
        $msg = 'Update Order Status "Completed" failed!';

        //begin transaction
        $this->db->trans_begin();

        // add lock trans
        $this->locktransdb->insert("Update order status ".$status." by ". (isset($arr_admin[$adm_id]) ? $arr_admin[$adm_id] : 'ID:'.$adm_id));

        // update order status
        $data = [
            'uor_status' => $status,
            'updated_by' => $adm_id,
            'updated_date' => $now
        ];
        $this->update_order($uor_id, $data);

        //get order detail after update
        $order = $this->get_order($uor_id);

        // find referral where purchased_status is false
        $vc_id = '';
        $ref_email = false;

        $where = ' AND uref_to = ? AND uref_to_purchased = ? ';
        $data = [$order->user_id, 0];
        $user_referral = $this->userdb->get_referral_custom_filter($where, $data);

        // generate referral only if user_referral is found
        if (!empty($user_referral)) {
            // generate referral voucher for uref_from
            $vc_id = $this->promodb->generate_referral_voucher($user_referral->uref_from);

            // set uref_to_purchased to true
            $data = [
                'uref_to_purchased' => 1,
                'updated_date' => $now
            ];
            $this->userdb->update_referral($user_referral->uref_id, $data);
            $ref_email = true;
        }

        // end transaction
        if (!$this->db->trans_status() || !$status) {
            $this->db->trans_rollback();
        } else {
            $msg = 'Success';
            $this->db->trans_commit();
        }

        return [
            'msg' => $msg,
            'uor_id' => $uor_id,
            'new_order_status' => $order->uor_status,
            'ref_email' => $ref_email,
            'vc_id' => $vc_id
        ];
    }

    function change_order_cancelled($uor_id, $status, $adm_id, $uor_remarks = '')
    {
        $this->load->model('locktransdb');
        $this->load->model('userdb');
        $this->load->model('promodb');
        $this->load->model('walletdb');
        $this->load->model('admindb');

        $arr_admin = $this->admindb->getarr_admin();
        $payment_method = $this->config->item('order')['payment_method'];
        $payment_refund = $this->config->item('payment')['allow_refund_payment'];
        $refund_status = $this->config->item('payment')['allow_refund_status'];

        $new_order_status = '';
        $now = date('Y-m-d H:i:s');
        $msg = 'Update Order Status "Cancelled" failed!';

        //begin transaction
        $this->db->trans_begin();

        // add lock trans
        $this->locktransdb->insert("Update order status ".$status." by ". (isset($arr_admin[$adm_id]) ? $arr_admin[$adm_id] : 'ID:'.$adm_id));

        // get order after first
        $order = $this->get_order($uor_id);
        $where_voucher = " AND uor_id = ? ";
        $voucher_data = $this->getall_order_voucher($select = "", $where_voucher, [$uor_id]);
        // if payment method and status allow to refund & wallet if uor_total > 0, then refund to wallet
        // wallet / credit card
        $data_wallet = '';
        if(in_array($order->pymtd_id, $payment_refund) && $order->uor_total > 0 && in_array($order->uor_status, $refund_status)){
            $data_wallet = $this->walletdb->refund_wallet($order);
        }

        // update order status
        $data = [
            'uor_status' => $status,
            'uor_remarks' => $uor_remarks,
            'updated_by' => $adm_id,
            'updated_date' => $now
        ];
        $this->update_order($uor_id, $data);

        // if order used voucher, rollback voucher
        if (!empty($voucher_data) && count($voucher_data) > 0) {
            $this->promodb->rollback_used_voucher($order->uor_id);
        }

        // end transaction
        if (!$this->db->trans_status() || !$status) {
            $this->db->trans_rollback();
        } else {
            $msg = 'Success';
            $this->db->trans_commit();
        }

        return [
            'msg' => $msg,
            'uor_id' => $uor_id,
            'wallet' => $data_wallet
        ];
    }

    function get_pending_store($data)
    {
        $sql = "SELECT distinct(addr.st_id) st_id
                FROM `user_order` ord
                INNER JOIN `user_order_address` addr ON addr.uor_id = ord.uor_id
                WHERE
                    ord.`uor_date` <= ?
                    AND ord.`uor_delivery_type` = ?
                    AND ord.`uor_status` = ? ";
        $query = $this->db->query($sql, $data);
        return $query->result();
    }

    function get_order_paid($where, $data, $limit_process = 5)
    {
        $sql = "SELECT ord.uor_id
                FROM `user_order` ord
                INNER JOIN `user_order_address` addr ON addr.uor_id = ord.uor_id
                WHERE
                    ord.`uor_date` <= ?
                    AND addr.st_id = ?
                    AND ord.`uor_delivery_type` = ?
                    AND ord.`uor_status` = ?
                    ". $where ."
                    LIMIT ". $limit_process;
        $query = $this->db->query($sql, $data);
        return $query->result();
    }

    //=== END ORDER

    //=== START ORDER PRODUCT
    function getall_order_product($where = '', $data = [], $order = '', $limit=0)
    {
        $orderby    = ($order == "" ? " uorpd_id ASC " : $order);

        // search dulu row nya.
        $sql        = "SELECT *
                        FROM ".$this->tbl_order_product." uorpd
                        WHERE 1=1 ". $where ." ORDER BY ".$orderby;
        if($limit > 0){
            $data[] = $limit;
            $sql    .= " LIMIT ? ";
        }
        $query      = $this->db->query($sql, $data);

        return $query->result();
    }

    function get_order_item($uor_id)
    {
        // search dulu row nya.
        $sql        = "SELECT GROUP_CONCAT(item) name FROM (
                            SELECT CONCAT(pd_qty, ' ' ,uorpd_name) item FROM (
                                SELECT uorpd_name, SUM(uorpd_qty) pd_qty FROM ". $this->tbl_order_product ." WHERE `uor_id` = ? GROUP BY uorpd_name
                            )uorpd
                        )product";
        $query      = $this->db->query($sql, array($uor_id));
        $row        = $query->row();
        return $row->name;
    }

    function get_order_item_detail($uor_id)
    {
        // search dulu row nya.
        $sql        = "
                        SELECT pd_id, uorpd_name, SUM(uorpd_qty) pd_qty, MIN(uorpd_final_price) pd_price, SUM(uorpd_total) total_price
                        FROM ".$this->tbl_order_product."
                        WHERE `uor_id` = ? GROUP BY pd_id, uorpd_name
                    ";
        $query      = $this->db->query($sql, array($uor_id));

        return $query->result();
    }
    //=== END ORDER PRODUCT

    //=== START ORDER TRACK
    function get_order_track($id)
    {
        $this->db->where('uortr_id', $id);
        $this->db->limit(1);
        $query = $this->db->get($this->tbl_order_track);
        return $query->row();
    }

    function getall_order_track($where = '', $data = [], $order = '', $limit=0)
    {
        $orderby    = ($order == "" ? " uortr_date DESC, uortr_id DESC " : $order);

        // search dulu row nya.
        $sql        = "SELECT uortr.*
                        FROM ".$this->tbl_order_track." uortr
                        WHERE 1=1 ". $where ." ORDER BY ".$orderby;

        if($limit > 0){
            $data[] = $limit;
            $sql    .= " LIMIT ? ";
        }
        $query      = $this->db->query($sql, $data);

        return $query->result();
    }

    function insert_order_track($data)
    {
        if ($this->db->insert($this->tbl_order_track, $data)) {
            return $this->db->insert_id();
        }
        return false;
    }

    function delete_order_track($id)
    {
        if ($this->db->delete( $this->tbl_order_track , array('uortr_id' => $id))) {
            return true;
        }
        return false;
    }

    function getall_latest_track($data)
    {
        $newdata = array();

        // data must be array and not null
        if(is_array($data) && count($data)>0){

            // looping to set query binding
            $where='';
            for($i=0;$i<count($data);$i++){
                $where .= '?,';
            }
            $where = substr($where, 0, -1);

            // search table tbl_order_track
            $sql = "SELECT MAX(uortr_id) AS uortr_id
                        FROM ".$this->tbl_order_track."
                        WHERE uor_id IN (".$where.")
                    GROUP BY uor_id";

            $query = $this->db->query($sql, $data);
            $result = $query->result();

            //process table tbl_order_track
            $where_uortr = '';
            $data_uortr = array();
            foreach($result as $key=>$value){
                $where_uortr .= '?,';
                $data_uortr[] = $value->uortr_id;
            }
            $where_uortr = substr($where_uortr, 0, -1);

            //check dulu kalau ada isinya baru lanjut ke order track
            if($where_uortr != ''){
                // search row
                $sql = "SELECT *
                        FROM ".$this->tbl_order_track."
                        WHERE uortr_id IN (".$where_uortr.") ORDER BY uor_id DESC";

                $query = $this->db->query($sql, $data_uortr);
                $result = $query->result();

                // looping to set variable
                foreach ($result as $key => $value){
                    $newdata[$value->uor_id] = $value;
                }
            }

        }

        return $newdata;
    }


    function insert_payment_logs($data) {
        if ($this->db->insert($this->tbl_payment_logs, $data)) {
            return $this->db->insert_id();
        }
        return false;
    }
    //=== END ORDER TRACK

    //=== START PAGING PAYMENT LOGS
    function getpaging_payment_logs($where = '', $data = [], $order = "", $page = 1)
    {
        $page       = ($page <= 0 ? 1 : intval($page));
        $last_row   = $this->row_per_page * $page;
        $first_row  = $last_row - $this->row_per_page;

        $orderby    = ($order == "" ? " created_date ASC " : $order);

        $sql =
            "SELECT * FROM {$this->tbl_payment_logs}
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
    //=== END PAGING PAYMENT LOGS

    //=== START PAYMENT METHOD
    function get_payment_method($id)
    {
        $this->db->where('pymtd_id', $id);
        $this->db->limit(1);
        $query = $this->db->get($this->tbl_payment_method);
        return $query->row();
    }

    function get_payment_method_by_name($name)
    {
        $this->db->where('pymtd_name', $name);
        $this->db->limit(1);
        $query = $this->db->get($this->tbl_payment_method);
        return $query->row();
    }

    function get_payment_method_by_code($code)
    {
        $this->db->where('pymtd_code', $code);
        $this->db->limit(1);
        $query = $this->db->get($this->tbl_payment_method);
        return $query->row();
    }

    function getpaging_payment_method($where = '', $data = [], $order = '', $page = 1)
    {
        $orderby    = ($order == "" ? " pymtd_order ASC " : $order);
        $page       = ($page <= 0 ? 1 : intval($page));
        $last_row   = $this->row_per_page * $page;
        $first_row  = $last_row - $this->row_per_page;

        $sql        = "SELECT *
                        FROM {$this->tbl_payment_method}
                        WHERE 1=1 {$where} ORDER BY {$orderby}";

        $query = $this->db->query($sql, $data);
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

    function getall_payment_method($where = '', $data = [], $order = '', $limit=0)
    {
        $orderby    = ($order == "" ? " pymtd_order ASC " : $order);

        // search dulu row nya.
        $sql        = "SELECT *
                        FROM {$this->tbl_payment_method}
                        WHERE 1=1 {$where} ORDER BY {$orderby}";
        if($limit > 0){
            $data[] = $limit;
            $sql    .= " LIMIT ? ";
        }
        $query      = $this->db->query($sql, $data);

        return $query->result();
    }

    function insert_payment_method($data)
    {
        $result = $this->db->insert($this->tbl_payment_method, $data);
        if ($result) {
            return $this->db->insert_id();
        }
        return false;
    }

    function update_payment_method($id, $data)
    {
        $this->db->where('pymtd_id', $id);
        return $this->db->update($this->tbl_payment_method, $data);
    }

    function delete_payment_method($id)
    {
        $this->db->where('pymtd_id', $id);
        return $this->db->delete($this->tbl_payment_method);
    }

    function sort_payment_method_order($pymtd_id, $pymtd_order){
        $order          = " pymtd_order ASC ";
        $list_payment   = $this->getall_payment_method($where = "", $data = [], $order);

        $no = 1;
        foreach($list_payment as $value){
            if($no == $pymtd_order && $value->pymtd_id != $pymtd_id){
                $no++;
                unset($updata);
                $updata['pymtd_order'] = $no;
                $this->update_payment_method($value->pymtd_id, $updata);
                $no++;
            }elseif($no != $pymtd_order && $value->pymtd_id != $pymtd_id){
                unset($updata);
                $updata['pymtd_order'] = $no;
                $this->update_payment_method($value->pymtd_id, $updata);
                $no++;
            }
        }
    }
    //=== END PAYMENT METHOD

    function getall_user_order_voucher($data) {

        $newdata = array();

        // data must be array and not null
        if(is_array($data) && count($data)>0){

            // looping to set query binding
            $where='';
            for($i=0;$i<count($data);$i++){
                $where .= '?,';
            }
            $where = substr($where, 0, -1);

            // search table tbl_order_track
            $sql = "SELECT *
                FROM {$this->tbl_user_order_voucher} uov
                WHERE 1=1 AND uor_id IN ({$where}) ORDER BY uov_id ASC";

            $query = $this->db->query($sql, $data);
            $result = $query->result();
            
            foreach($result as $key => $value) {
                $newdata[$value->uor_id][] = $value->vc_code;
            }

        }

        return $newdata;
    }

    function getall_order_voucher($select = "uov.*", $where = '', $data = [], $order = '', $limit=0)
    {
        $orderby    = ($order == "" ? " uov_id DESC " : $order);
        $select    = ($select == "" ? " uov.* " : $select);

        // search dulu row nya.
        $sql        = "SELECT {$select}
                        FROM {$this->tbl_user_order_voucher} uov
                        WHERE 1=1 {$where} ORDER BY {$orderby}";

        if($limit > 0){
            $data[] = $limit;
            $sql    .= " LIMIT ? ";
        }
        $query      = $this->db->query($sql, $data);

        return $query->result();
    }

    function getall_search_order_voucher($search = "") {
        $newdata = array();

        $select = "uov.uor_id";
        $where_ouv = " AND vc_code LIKE ? ";
        $data_uov = ["%". $search ."%"];
        $all_uov = $this->orderdb->getall_order_voucher($select, $where_ouv, $data_uov);

        foreach($all_uov as $key => $value) {
            $newdata[] = $value->uor_id;
        }

        return $newdata;
    }
}
?>

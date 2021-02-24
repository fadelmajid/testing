<?php 
defined('BASEPATH') OR EXIT('No direct script access allowed');
/**
*
*/
class Subscriptiondb extends MY_Model {
    function __construct()
    {
        parent::__construct();
    }

    function get_subsplan($id)
    {
        $this->db->where('subsplan_id', $id);
        $this->db->limit(1);
        $query = $this->db->get($this->tbl_subs_plan);
        return $query->row();
    }

    function getall_subscription_user($where = '', $data = [], $order = '', $limit=0)
    {
        $orderby    = ($order == "" ? " sc_id ASC " : $order);

        // search dulu row nya.
        $sql        = "SELECT *
                        FROM ".$this->tbl_subs_counter." sc
                        WHERE 1=1 ". $where ." ORDER BY ".$orderby;
        if($limit > 0){
            $data[] = $limit;
            $sql    .= " LIMIT ? ";
        }
        $query      = $this->db->query($sql, $data);
        
        return $query->result();
    }
    
    function update_subs_counter($id, $data)
    {
        $this->db->where('sc_id', $id);
        return $this->db->update($this->tbl_subs_counter, $data); 
    }
    //>>START SUBS PLAN<<

    function getall_subs_plan($where = '', $data = [], $order = '', $limit=0)
    {
        // search dulu row nya.
        $sql        = "SELECT * FROM {$this->tbl_subs_plan} WHERE 1=1 {$where}";
        if($limit > 0){
            $data[] = $limit;
            $sql    .= " LIMIT ? ";
        }
        $query      = $this->db->query($sql, $data);

        return $query->result();
    }

    function getpaging_subs_plan($where = '', $data = [], $order = '', $page = 1)
    {
        $order_by   = ($order == "" ? " subsplan_id ASC " : $order);
        $page       = ($page <= 0 ? 1 : intval($page));
        $last_row   = $this->row_per_page * $page;
        $first_row  = $last_row - $this->row_per_page;

        $sql        = "SELECT *
                        FROM {$this->tbl_subs_plan}
                        WHERE 1=1 {$where}
                        ORDER BY {$order_by}";
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

    function insert_subs_plan($data)
    {
        $this->db->insert($this->tbl_subs_plan, $data);
        return $this->db->insert_id();
    }

    function update_subs_plan($id, $data)
    {
        $this->db->where('subsplan_id', $id);
        return $this->db->update($this->tbl_subs_plan, $data);
    }

    function delete_subs_plan($id)
    {
        if ($this->db->delete( $this->tbl_subs_plan , array('subsplan_id' => $id))) {
            return true;
        }
        return false;
    }

    function get_subs_plan_by_code($code){
        $this->db->where('subsplan_code', $code);
        $this->db->limit(1);
        $query = $this->db->get($this->tbl_subs_plan);
        return $query->row();
    }
    //>>END SUBSCRIPTION PLAN<< 
    
    //>>START SUBSCRIPTION ORDER<<
    function get_subs_order($id)
    {
        $sql        =   "SELECT subsorder.*, subsplan.subsplan_name, usr.user_name, usr.user_email, usr.user_email_verified,
                        usr.user_phone, pymtd.pymtd_name, pymtd.pymtd_id, sop.sop_id, sop.sop_data
                        FROM {$this->tbl_subs_order} subsorder
                        INNER JOIN {$this->tbl_subs_order_detail} AS sod ON sod.subsorder_id = subsorder.subsorder_id
                        INNER JOIN {$this->tbl_subs_plan} AS subsplan ON subsplan.subsplan_id = sod.subsplan_id
                        INNER JOIN {$this->tbl_user} usr ON subsorder.user_id = usr.user_id
                        INNER JOIN {$this->tbl_subs_order_payment} sop ON subsorder.subsorder_id = sop.subsorder_id
                        INNER JOIN {$this->tbl_payment_method} pymtd ON sop.pymtd_id = pymtd.pymtd_id
                        WHERE subsorder.subsorder_id = ? LIMIT 1";
        $query = $this->db->query($sql, $id);
        return $query->row();
    }

    function get_subs_order_custom_filter($where = '', $data = [])
    {
        $sql = "SELECT * FROM {$this->tbl_subs_order} WHERE 1=1 {$where} LIMIT 1";
        $query= $this->db->query($sql, $data);
        return $query->row();
    }

    function getpaging_subs_order($where = '', $data = [], $order = "", $page = 1) {
        $page       = ($page <= 0 ? 1 : intval($page));
        $last_row   = $this->row_per_page * $page;
        $first_row  = $last_row - $this->row_per_page;

        $order_by   = ($order == "" ? " subsorder.subsorder_id ASC" : $order);

        $sql        =   "SELECT subsorder.*, subsplan.subsplan_name, usr.user_name, usr.user_email,
                        usr.user_phone, pymtd.pymtd_name, pymtd.pymtd_id, sop.sop_id, sop.sop_data
                        FROM {$this->tbl_subs_order} subsorder
                        INNER JOIN {$this->tbl_subs_order_detail} AS sod ON sod.subsorder_id = subsorder.subsorder_id
                        INNER JOIN {$this->tbl_subs_plan} AS subsplan ON subsplan.subsplan_id = sod.subsplan_id
                        INNER JOIN {$this->tbl_user} usr ON subsorder.user_id = usr.user_id
                        INNER JOIN {$this->tbl_subs_order_payment} sop ON subsorder.subsorder_id = sop.subsorder_id
                        INNER JOIN {$this->tbl_payment_method} pymtd ON sop.pymtd_id = pymtd.pymtd_id
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

    function getall_subs_order($where = '', $data = [], $order = '', $limit=0) {
        $orderby   = ($order == "" ? " subsorder_id ASC" : $order);

        $sql        =   "SELECT *
                        FROM {$this->tbl_subs_order}
                        WHERE 1=1 {$where} ORDER BY {$order_by}";

        if($limit > 0){
            $data[] = $limit;
            $sql    .= " LIMIT ? ";
        }

        $query      = $this->db->query($sql, $data);
        return $query->result();
    }

    function getall_subs_order_detail($where = '', $data = [], $order = '', $limit=0){
        $orderby   = ($order == "" ? " sod.subsdetail_id ASC" : $order);

        $sql        =   "SELECT sod.*, subsplan.subsplan_name
                        FROM {$this->tbl_subs_order_detail} sod
                        INNER JOIN {$this->tbl_subs_plan} subsplan ON subsplan.subsplan_id = sod.subsplan_id
                        WHERE 1=1 {$where} ORDER BY {$orderby}";

        if($limit > 0){
            $data[] = $limit;
            $sql    .= " LIMIT ? ";
        }

        $query      = $this->db->query($sql, $data);
        return $query->result();
    }

    function update_subs_order($subsorder_id, $data)
    {
        $this->db->where('subsorder_id', $subsorder_id);
        return $this->db->update($this->tbl_subs_order, $data);
    }

    function change_subsorder_cancelled($subsorder_id, $subsorder_status, $adm_id, $subsorder_remarks = '')
    {
        $this->load->model('locktransdb');
        $this->load->model('userdb');
        $this->load->model('promodb');
        $this->load->model('walletdb');
        $this->load->model('admindb');

        $arr_admin = $this->admindb->getarr_admin();
        $payment_method = $this->config->item('subs_order')['payment_method'];
        $payment_refund = $this->config->item('payment')['allow_refund_payment'];
        $refund_status  = $this->config->item('payment')['allow_refund_status'];

        //$new_subsorder_status = '';
        $now = date('Y-m-d H:i:s');
        $msg = 'Update Subs Order Status "Cancelled" failed!';

        //begin transaction
        $this->db->trans_begin();

        // add lock trans
        $this->locktransdb->insert("Update subs order status ".$subsorder_status." by ". (isset($arr_admin[$adm_id]) ? $arr_admin[$adm_id] : 'ID:'.$adm_id));

        // get order after first
        $subsorder = $this->get_subs_order($subsorder_id);
        // if payment method and status allow to refund & wallet if uor_total > 0, then refund to wallet
        // wallet / credit card
        $data_wallet = '';
        if(in_array($subsorder->pymtd_id, $payment_refund) && $subsorder->subsorder_total > 0 && in_array($subsorder->subsorder_status, $refund_status)){
            // redeclar uor_id, user_id, dan uor_total untuk refund_wallet agar tidak membuat function baru
            $subs_order             = new stdClass();
            $subs_order->uor_id     = $subsorder->subsorder_id;
            $subs_order->user_id    = $subsorder->user_id;
            $subs_order->uor_total  = $subsorder->subsorder_total;
            $data_wallet            = $this->walletdb->refund_wallet($subs_order);
        }

        // update order status
        $data = [
            'subsorder_status'  => $subsorder_status,
            'subsorder_remarks' => $subsorder_remarks,
            'updated_by'        => $adm_id,
            'updated_date'      => $now
        ];
        $this->update_subs_order($subsorder_id, $data);

        // end transaction
        if (!$this->db->trans_status() || !$subsorder_status) {
            $this->db->trans_rollback();
        } else {
            $msg = 'Success';
            $this->db->trans_commit();
        }

        return [
            'msg'           => $msg,
            'subsorder_id'  => $subsorder_id,
            'wallet'        => $data_wallet
        ];
    }
    //>>END SUBSCRIPTION ORDER<<

    // START SUBS PAYMENT LOGS
    function insert_subs_payment_logs($data) {
        if ($this->db->insert($this->tbl_subs_payment_logs, $data)) {
            return $this->db->insert_id();
        }
        return false;
    }
    // END SUBS PAYMENT LOGS
    
    //>>START SUBSCRIPTION COUNTER<<
    function get_subs_counter($id)
    {
        $sql    =   "SELECT *
                    FROM {$this->tbl_subs_counter}
                    WHERE  sc_id = ? LIMIT 1";
        $query = $this->db->query($sql, $id);
        return $query->row();
    }

    function getpaging_subs_counter($where = '', $data = [], $order = "", $page = 1) {
        $page       = ($page <= 0 ? 1 : intval($page));
        $last_row   = $this->row_per_page * $page;
        $first_row  = $last_row - $this->row_per_page;

        $order_by   = ($order == "" ? " sc.sc_id ASC" : $order);

        $sql        =   "SELECT sc.*, sp.subsplan_name
                        FROM {$this->tbl_subs_counter} sc
                        INNER JOIN {$this->tbl_subs_plan} sp ON sp.subsplan_id = sc.subsplan_id
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

    function getall_subs_counter($where = '', $data = [], $order = '', $limit=0) {
        $orderby   = ($order == "" ? " sc_id ASC" : $order);

        $sql        =   "SELECT *
                        FROM {$this->tbl_subs_counter}
                        WHERE 1=1 {$where} ORDER BY {$order_by}";

        if($limit > 0){
            $data[] = $limit;
            $sql    .= " LIMIT ? ";
        }

        $query      = $this->db->query($sql, $data);
        return $query->result();
    }
    //>>END SUBSCRIPTION COUNTER<<

    //=== START PAGING SUBS PAYMENT LOGS
    function getpaging_subs_payment_logs($where = '', $data = [], $order = "", $page = 1)
    {
        $page       = ($page <= 0 ? 1 : intval($page));
        $last_row   = $this->row_per_page * $page;
        $first_row  = $last_row - $this->row_per_page;

        $orderby    = ($order == "" ? " created_date ASC " : $order);

        $sql =
            "SELECT * FROM {$this->tbl_subs_payment_logs}
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

}
?>
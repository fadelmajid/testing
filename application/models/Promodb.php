<?php
defined('BASEPATH') OR EXIT('No direct script access allowed');
/**
*
*/
class Promodb extends MY_Model {
    function __construct()
    {
        parent::__construct();
    }

    //=== BEGIN PROMO
    function get_promo($id)
    {
        $this->db->where('prm_id', $id);
        $this->db->limit(1);
        $query = $this->db->get($this->tbl_promo);
        return $query->row();
    }

    function get_promo_code($code)
    {
        $this->db->where('prm_custom_code', $code);
        $this->db->limit(1);
        $query = $this->db->get($this->tbl_promo);
        return $query->row();
    }

    function getall_promo($where = '', $data = [], $order = '', $limit=0)
    {
        // search dulu row nya.
        $sql = "SELECT * FROM {$this->tbl_promo} WHERE 1=1 {$where}";
        if($limit > 0){
            $data[] = $limit;
            $sql    .= " LIMIT ? ";
        }
        $query      = $this->db->query($sql, $data);

        return $query->result();
    }

    function getpaging_promo($where = '', $data = [], $order_by = ' prm_name ASC', $page = 1)
    {
        $page       = ($page <= 0 ? 1 : intval($page));
        $last_row   = $this->row_per_page * $page;
        $first_row  = $last_row - $this->row_per_page;

        $sql = "SELECT * FROM {$this->tbl_promo} WHERE 1=1 {$where} ORDER BY {$order_by}";
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

    function get_promo_custom_filter($where = '', $data = [])
    {
        $sql = "SELECT * FROM {$this->tbl_promo} WHERE 1=1 {$where} LIMIT 1";
        $query= $this->db->query($sql, $data);
        return $query->row();
    }

    function find_create_free_cup_promo($promo_name, $promo_custom_code, $prm_rules, $stat_code, $exp_date="")
    {
        $promo_type = $this->config->item('promo')['type'];
        $status = $this->config->item('promo')['status'];
        $promo_code = $this->config->item('promo')['promo_code'];

        $promo_start = date('Y-m-d');

        if(empty($exp_date)){
            $promo_end = date('Y-m-d 23:59:59', strtotime(PROMO_FREECUP_PERIOD_MONTHS.' months'));      
        }else{
            $promo_end = "{$exp_date} 23:59:59";     
        }
  
        $where = ' AND prm_custom_code = ? ';
        $where_data = [$promo_custom_code];

        $promo = $this->get_promo_custom_filter($where, $where_data);
        $static_image = $this->get_static_image_by_stat_code($stat_code);

        if (empty($promo)) {
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

            $promo_id = $this->insert_promo($data);
            return $this->get_promo($promo_id);
        }
        return $promo;
    }

    function insert_promo($data)
    {
        $this->db->insert($this->tbl_promo, $data);
        return $this->db->insert_id();
    }

    function update_promo($id, $data)
    {
        $this->db->where('prm_id', $id);
        return $this->db->update($this->tbl_promo, $data);
    }
    //=== END PROMO

    //=== START VOUCHER
    function get_voucher($id)
    {
        $sql = "SELECT * FROM {$this->tbl_voucher} vc INNER JOIN {$this->tbl_promo} prm ON vc.prm_id = prm.prm_id WHERE vc.vc_id = ? LIMIT 1";
        $query = $this->db->query($sql, [$id]);
        return $query->row();
    }

    function is_generated_promo($prm_id)
    {
        $sql = "SELECT * FROM {$this->tbl_voucher} vc WHERE vc.prm_id = ? LIMIT 1";
        $query = $this->db->query($sql, [$prm_id]);
        $row = $query->row();
        return (empty($row) ? false : true);
    }

    function getpaging_voucher($where = '', $data = [], $order = '', $page = 1)
    {
        $orderby    = ($order == "" ? " vc.vc_id ASC " : $order);
        $page       = ($page <= 0 ? 1 : intval($page));
        $last_row   = $this->row_per_page * $page;
        $first_row  = $last_row - $this->row_per_page;

        $sql = "SELECT vc.*, prm.prm_name, user.user_name, user.user_phone, user.user_email
                FROM {$this->tbl_voucher} vc
                INNER JOIN {$this->tbl_promo} prm ON vc.prm_id = prm.prm_id
                LEFT JOIN  {$this->tbl_user} user ON vc.user_id = user.user_id
                WHERE 1=1 {$where} ORDER BY {$orderby}";
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
    
    function getall_voucher($where = '', $data = [], $order = '', $limit=0)
    {
        $orderby    = ($order == "" ? " vc.vc_id ASC " : $order);
        // search dulu row nya.
        $sql        =
                    "SELECT vc.*, prm.prm_id, prm.prm_start, prm.prm_end, prm.prm_status
                    FROM {$this->tbl_voucher} vc
                    INNER JOIN {$this->tbl_promo} prm ON vc.prm_id = prm.prm_id
                    WHERE 1=1 {$where}";
        
        if($limit > 0){
            $data[] = $limit;
            $sql   .= " LIMIT ? ";
        }
        $query      = $this->db->query($sql, $data);

        return $query->result();
    }

    function get_voucher_custom_filter($where = '', $data = [])
    {
        $sql = "SELECT * FROM {$this->tbl_voucher} WHERE 1=1 {$where} LIMIT 1";
        $query= $this->db->query($sql, $data);
        return $query->row();
    }


    function get_voucher_unassigned_custom_filter($where = '', $data = [])
    {
        $sql = "SELECT * FROM {$this->tbl_voucher_unassigned} WHERE 1=1 {$where} LIMIT 1";
        $query= $this->db->query($sql, $data);
        return $query->row();
    }

    function generate_voucher($adm_id, $promo_id)
    {
        $this->load->model('locktransdb');
        $this->load->model('admindb');

        $promo_type = $this->config->item('promo')['type'];
        $promo = $this->get_promo($promo_id);
        $arr_admin = $this->admindb->getarr_admin();
        $prm_rules = json_decode($promo->prm_rules, true);

        $res = true;
        $loop_refresh = true;

        // validate promo
        if (!$promo) {
            $res = false;
        }

        // validate limit usage, create voucher as much as limit usage
        if($res == true){
            //begin transaction
            $this->db->trans_begin();

            // add lock trans
            $this->locktransdb->insert("Generate promo/voucher by ". (isset($arr_admin[$adm_id]) ? $arr_admin[$adm_id] : 'ID:'.$adm_id));

            // promo type generated or not?
            if($promo->prm_type !== $promo_type['generated']){
                // generating voucher once
                $this->create_general_voucher($promo_id, false, $promo->prm_custom_code);
            }else{
                for($i=1; $i <= $prm_rules['limit_usage']; $i++){
                    // generating voucher
                    $this->create_unassigned_voucher($promo_id, $promo->prm_custom_code, $promo->prm_end);
                }
            }

            $promo_data = [
                'prm_status' => $this->config->item('promo')['status']['active'],
                'updated_by' => $adm_id,
                'updated_date' => date('Y-m-d H:i:s')
            ];
            // update status promo to active
            $update = $this->update_promo($promo_id, $promo_data);

            // end transaction
            if (!$this->db->trans_status()) {
                $this->db->trans_rollback();
                $res = false;
            } else {
                $this->db->trans_commit();
            }
        }

        return $res;
    }

    function create_general_voucher($promo_id, $is_generated, $prm_custom_code, $user_id = 0){
        $status = $this->config->item('voucher')['status'];
        $loop_refresh = true;

        if($is_generated){
            // keeps generating vc_code until vc_code not found in db
            while ($loop_refresh) {
                $vc_code = $this->generate_voucher_code($prm_custom_code);
                $voucher = $this->get_voucher_custom_filter(' AND vc_code = ?', [$vc_code]);

                if (!$voucher) {
                    $loop_refresh = false;
                }
            }
        }else{
            $vc_code = $prm_custom_code;
        }

        //create new voucher
        $data = [
            'prm_id' => $promo_id,
            'user_id' => $user_id,
            'vc_code' => $vc_code,
            'vc_status' => $status['active'],
            'created_date' => date('Y-m-d H:i:s'),
        ];
        $this->insert_voucher($data);

        return $this->db->insert_id();
    }

    function create_unassigned_voucher($promo_id, $prm_custom_code, $expired_date, $user_id = 0){
        $status = $this->config->item('voucher')['status'];
        $loop_refresh = true;

        // keeps generating vc_code until vc_code not found in db
        while ($loop_refresh) {
            $prm_custom_code = "";
            $limit = 12;
            $vcu_code = $this->generate_voucher_code($prm_custom_code, $limit);
            $voucher = $this->get_voucher_unassigned_custom_filter(' AND vcu_code = ?', [$vcu_code]);

            if (!$voucher) {
                $loop_refresh = false;
            }
        }

        //create new voucher
        $data = [
            'prm_id'        => $promo_id,
            'user_id'       => $user_id,
            'vcu_code'      => $vcu_code,
            'vcu_status'    => $status['active'],
            'expired_date'  => $expired_date,
            'created_date'  => date('Y-m-d H:i:s'),
        ];

        $this->insert_voucher_unassigned($data);

        return $this->db->insert_id();
    }

    function generate_referral_voucher($user_id)
    {
        $status = $this->config->item('voucher')['status'];
        $promo_code = $this->config->item('promo')['promo_code'];
        $discount_type = $this->config->item('promo')['discount_type'];

        $promo_name = 'User Referral '.date('d/m/y');
        $promo_custom_code =  $promo_code['ref'].date('ymd');
        $voucher_default = $this->get_voucher_default_by_vcdef_code($promo_code['ref']);

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
        
        $promo = $this->find_create_free_cup_promo($promo_name, $promo_custom_code, $prm_rules, $promo_code['ref']);
        $loop_refresh = true;

        // keeps generating vc_code until vc_code not found in db
        while ($loop_refresh) {
            $vc_code = $this->generate_voucher_code($promo->prm_custom_code);
            $voucher = $this->get_voucher_custom_filter(' AND vc_code = ?', [$vc_code]);

            if (!$voucher) {
                $loop_refresh = false;
            }
        }

        //create new voucher
        $data = [
            'prm_id' => $promo->prm_id,
            'user_id' => $user_id,
            'vc_code' => $vc_code,
            'vc_status' => $status['active'],
            'created_date' => date('Y-m-d H:i:s'),
        ];
        $this->insert_voucher($data);
        return $this->db->insert_id();
    }

    function generate_voucher_code($prefix, $limit = 6)
    {
        $mili = substr(microtime(), 2, 3);
        $rstr = '_'.substr(base_convert(mt_rand(), 10, 36), 0, 9);
        $vc_code = strtoupper($prefix.substr(hash('sha256', $mili.$prefix.$rstr), 1, $limit));
        return $vc_code;
    }

    function insert_voucher($data)
    {
        $this->db->insert($this->tbl_voucher, $data);
        return $this->db->insert_id();
    }

    function update_voucher($id, $data)
    {
        $this->db->where('vc_id', $id);
        return $this->db->update($this->tbl_voucher, $data);
    }

    function rollback_used_voucher($uor_id)
    {
        $promo_type = $this->config->item('promo')['type'];
        $voucher_status = $this->config->item('voucher')['status'];

        // get voucher history
        $where = ' AND uor_id = ? AND vchis_status = ?';
        $data = ['uor_id' => $uor_id, 'vchis_status' => $voucher_status['used']];
        $voucher_history = $this->get_voucher_history_custom_filter($where, $data);

        if (!empty($voucher_history)) {
            // update voucher history status to cancelled
            $data['vchis_status'] = $voucher_status['cancelled'];
            $this->update_voucher_history($voucher_history->vchis_id, $data);

            // get order used voucher and if vc_status is used
            // and promo type is not unlimited, set vc_status back to active
            $voucher = $this->get_voucher($voucher_history->vc_id);
            if ($voucher && $voucher->vc_status === $voucher_status['used'] && $voucher->prm_type !== $promo_type['unlimited']
            ) {
                $data = [
                    'vc_status' => $voucher_status['active'],
                    'updated_date' => date('Y-m-d H:i:s'),
                ];
                return $this->update_voucher($voucher->vc_id, $data);
            }
            return true;
        }
        return false;
    }
    //=== END VOUCHER

    //=== START CAMPAIGN TOKEN
    function getall_campaign_token()
    {
        $sql    = "SELECT * FROM {$this->tbl_campaign_token}";
        $query  = $this->db->query($sql);
        return $query->result();
    }

    function getall_user_order($data = array())
    {
        $status         = $data['status'];
        $star_date      = $data['start_date'];
        $end_date       = $data['end_date'];
        $sql_data       = [$status, 0, $star_date, $end_date];

        $sql = "SELECT uor.user_id
                FROM {$this->tbl_order} uor 
                INNER JOIN {$this->tbl_user} usr ON usr.user_id = uor.user_id 
                WHERE uor.uor_status = ? 
                AND uor.uor_subtotal > ?
                AND uor.uor_date >= ? 
                AND uor.uor_date <= ? 
                GROUP BY uor.user_id";
                
        $query = $this->db->query($sql, $sql_data);
        return $query->result();
    }

    function get_total_transaction($data = array())
    {
        $user_id        = $data['user_id'];
        $status         = $data['status'];
        $voucher_tgif   = $data['voucher_tgif'];
        $start_date     = $data['start_date'];
        $end_date       = $data['end_date'];
        $sql_data       = [$user_id, $status, 0, $start_date, $end_date, $user_id, $status, 0, $voucher_tgif.'%', $start_date, $end_date];

        $sql = "SELECT COUNT(uor.uor_id) as total
                FROM {$this->tbl_order} uor 
                WHERE uor.user_id = ? 
                AND uor.uor_status = ? 
                AND uor.uor_subtotal > ?  
                AND uor.uor_date >= ? AND uor.uor_date <= ? 
                
                UNION ALL
                
                SELECT COUNT(uor.uor_id) as total
                FROM {$this->tbl_order} uor 
                INNER JOIN {$this->tbl_user_order_voucher} uov ON uov.uor_id = uor.uor_id 
                WHERE uor.user_id = ? 
                AND uor.uor_status = ? 
                AND uor.uor_subtotal > ?
                AND uov.vc_code LIKE ?  
                AND uor.uor_date >= ? AND uor.uor_date <= ? ";

        $query = $this->db->query($sql, $sql_data);
        return $query->result();
    }

    function get_ctoken_custom_filter($where = '', $data = [])
    {
        $sql = "SELECT * FROM {$this->tbl_campaign_token} WHERE 1=1 {$where} LIMIT 1";
        $query= $this->db->query($sql, $data);
        return $query->row();
    }

    function generate_token($user_id, $limit, $const)
    {   
        //begin transaction
        $this->db->trans_begin();

        for($i=0; $i < $limit; $i++){

            $loop_refresh = true; 

            // keeps generating ctoken_code until ctoken_code not found in db
            while ($loop_refresh) {
                $const          = $const + 1;
                $ctoken_code    = $const;
                $ctoken         = ($limit == $i ? true : false); 
                if (!$ctoken) {
                    $loop_refresh = false;
                }
            }

            //create new campaign_token
            $data = [
                'user_id' => $user_id,
                'ctoken_code' => $ctoken_code,
                'created_by' => 0,
                'created_date' => date('Y-m-d H:i:s'),
            ];
            
            $token = $this->insert_ctoken($data);
            if(!$token){
                $this->db->trans_rollback();
            }
            $ctoken_id = $this->db->insert_id();
        }
        
        // end transaction
        if($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            return false;
        } else {
            $this->db->trans_commit();
            return $const;
        }            
    }

    function insert_ctoken($data)
    {
        //begin transaction
        $this->db->trans_begin();

        $ctoken = $this->db->insert($this->tbl_campaign_token, $data);
        if(!$ctoken){
            $this->db->trans_rollback();
        }

        $ctoken_id = $this->db->insert_id();
        
        //end transaction
        if($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
        } else {
            $this->db->trans_commit();
            return true;
        }
    }


    //=== END CAMPAIGN TOKEN

    //=== START VOUCHER HISTORY
    function getpaging_voucher_his($where = '', $data = [], $order = '', $page = 1)
    {
        $orderby    = ($order == "" ? " vchis_id DESC " : $order);
        $page       = ($page <= 0 ? 1 : intval($page));
        $last_row   = $this->row_per_page * $page;
        $first_row  = $last_row - $this->row_per_page;

        $sql = "SELECT vchis.*, vc.vc_code, prm.prm_name, uor.uor_id ,uor.uor_code
                FROM {$this->tbl_voucher_history} vchis
                INNER JOIN {$this->tbl_voucher} vc ON vchis.vc_id = vc.vc_id
                INNER JOIN {$this->tbl_promo} prm ON vc.prm_id = prm.prm_id
                INNER JOIN {$this->tbl_order} uor ON vchis.uor_id = uor.uor_id
                WHERE 1=1 {$where} ORDER BY {$orderby}";
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

    function get_voucher_history_custom_filter($where = '', $data = [])
    {
        $sql = "SELECT * FROM {$this->tbl_voucher_history} WHERE 1=1 {$where} LIMIT 1";
        $query= $this->db->query($sql, $data);
        return $query->row();
    }

    function update_voucher_history($id, $data)
    {
        $this->db->where('vchis_id', $id);
        return $this->db->update($this->tbl_voucher_history, $data);
    }
    //=== END VOUCHER HISTORY

    //=== START VOUCHER UNASSIGNED
    function getall_voucher_unassigned($where="", $data=array(), $order="", $limit=0)
    {
        $orderby    = ($order == "" ? " vcu_id ASC " : $order);

        $sql        = "SELECT vcu.*, prm.prm_name
                    FROM {$this->tbl_voucher_unassigned} vcu
                    INNER JOIN {$this->tbl_promo} prm ON vcu.prm_id = prm.prm_id
                    WHERE 1=1 {$where} ORDER BY {$orderby}";

        if($limit > 0){
            $data[] = $limit;
            $sql    .= " LIMIT ? ";
        }
        $query      = $this->db->query($sql, $data);

        return $query->result();

    } 

    function getpaging_voucher_unassigned($where = '', $data = [], $order = '', $page = 1)
    {
        $orderby    = ($order == "" ? " vcu_id ASC " : $order);
        $page       = ($page <= 0 ? 1 : intval($page));
        $last_row   = $this->row_per_page * $page;
        $first_row  = $last_row - $this->row_per_page;

        $sql        = "SELECT vcu.*, prm.prm_name
                    FROM {$this->tbl_voucher_unassigned} vcu
                    INNER JOIN {$this->tbl_promo} prm ON vcu.prm_id = prm.prm_id
                    WHERE 1=1 {$where} ORDER BY {$orderby}";

        $query      = $this->db->query($sql, $data);
        $total_row  = $query->num_rows();

        $data[]     = $this->row_per_page;
        $data[]     = intval($first_row > $total_row ? $total_row : $first_row);
        $sql        .= " LIMIT ? OFFSET ? ";
        $query = $this->db->query($sql, $data);

        return [
            'data'      => $query->result(),
            'total_row' => $total_row,
            'per_page'  => $this->row_per_page,
        ];
    }
    //=== END VOUCHER UNASSIGNED


    // BEGIN UNTUK KEPERLUAAN CRONJOB
    function update_expired_promo($date)
    {
        $promo_status = $this->config->item('promo')['status'];
        $sql = "UPDATE `promo` SET `prm_status` = ? WHERE `prm_end` <= ? AND prm_status = ? ";
        $res = $this->db->query($sql, [$promo_status['expired'], $date, $promo_status['active']]);
    }
    // END UNTUK KEPERLUAAN CRONJOB

    // BEGIN UNTUK VOUCHER DEFAULT
    function get_voucher_default($id) {
        $this->db->where('vcdef_id', $id);
        $this->db->limit(1);
        $query = $this->db->get($this->tbl_voucher_default);
        return $query->row();

    }

    function getall_voucher_default($where = '', $data = [], $order = '', $limit=0)
    {
        // search dulu row nya.
        $sql = "SELECT * FROM {$this->tbl_voucher_default} WHERE 1=1 {$where}";
        if($limit > 0){
            $data[] = $limit;
            $sql    .= " LIMIT ? ";
        }
        $query      = $this->db->query($sql, $data);

        return $query->result();
    }

    function getpaging_voucher_default($where = '', $data = [], $order_by = ' created_at ASC', $page = 1)
    {
        $page       = ($page <= 0 ? 1 : intval($page));
        $last_row   = $this->row_per_page * $page;
        $first_row  = $last_row - $this->row_per_page;

        $sql = "SELECT * FROM {$this->tbl_voucher_default} WHERE 1=1 {$where} ORDER BY {$order_by}";
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

    function insert_voucher_default($data)
    {
        $this->db->insert($this->tbl_voucher_default, $data);
        return $this->db->insert_id();
    }

    function update_voucher_default($id, $data)
    {
        $this->db->where('vcdef_id', $id);
        return $this->db->update($this->tbl_voucher_default, $data);
    }

    function delete_voucher_default($id)
    {
        $this->db->where('vcdef_id', $id);
        return $this->db->delete($this->tbl_voucher_default);
    }
    
    function get_voucher_default_by_vcdef_code($vcdef_code)
    {
        $this->db->where('vcdef_code', $vcdef_code);
        $this->db->limit(1);
        $query = $this->db->get($this->tbl_voucher_default);
        return $query->row();
    }
    // END UNTUK VOUCHER DEFAULT

    public function get_static_image_by_stat_code($stat_code) 
    {
        $this->db->where('stat_code', $stat_code);
        $this->db->limit(1);
        $query = $this->db->get($this->tbl_static_image);
        return $query->row();

    }

    function insert_voucher_unassigned($data)
    {
        $this->db->insert($this->tbl_voucher_unassigned, $data);
        return $this->db->insert_id();
    }
    // START VOUCHER BIRTHDAY
    public function get_all_voucher_birthday($where = '', $data = [], $order_by = " vcb_id ASC ", $limit = 0)
    {
        // search dulu row nya.
        $sql = " SELECT * FROM {$this->tbl_voucher_birthday} WHERE 1=1 {$where} ORDER BY {$order_by} ";
        if($limit > 0){
            $data[] = $limit;
            $sql    .= " LIMIT ? ";
        }
        $query      = $this->db->query($sql, $data);

        return $query->result();
    }

    function insert_voucher_birthday($data)
    {
        $this->db->insert($this->tbl_voucher_birthday, $data);
        return $this->db->insert_id();
    }
    // END

    //>>START VOUCHER EMPLOYEE<<
    function get_voucher_employee($id) {
        $this->db->where('vce_id', $id);
        $this->db->limit(1);
        $query = $this->db->get($this->tbl_voucher_employee);
        return $query->row();
    }

    function getall_voucher_employee($where = '', $data = [], $order = '', $limit=0)
    {
        // search dulu row nya.
        $sql        =
                    "SELECT *
                    FROM {$this->tbl_voucher_employee}
                    WHERE 1=1 {$where}";
        
        if($limit > 0){
            $data[] = $limit;
            $sql   .= " LIMIT ? ";
        }
        $query      = $this->db->query($sql, $data);

        return $query->result();
    }

    function getpaging_voucher_employee($where = '', $data = [], $order_by = '', $page = 1)
    {
        $page       = ($page <= 0 ? 1 : intval($page));
        $last_row   = $this->row_per_page * $page;
        $first_row  = $last_row - $this->row_per_page;
        $orderby    = ($order_by == "" ? " vce_id ASC " : $order_by);

        $sql        =
                        "SELECT *
                        FROM {$this->tbl_voucher_employee}
                        WHERE 1=1 {$where} ORDER BY {$orderby}";
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

    function insert_voucher_employee($data)
    {
        $this->db->insert($this->tbl_voucher_employee, $data);
        return $this->db->insert_id();
    }

    function update_voucher_employee($id, $data)
    {
        $this->db->where('vce_id', $id);
        return $this->db->update($this->tbl_voucher_employee, $data);
    }

    function delete_voucher_employee($id)
    {
        $this->db->where('vce_id', $id);
        return $this->db->delete($this->tbl_voucher_employee);
    }
    //>>END VOUCHER EMPLOYEE<<
}
?>

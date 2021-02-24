<?php
defined('BASEPATH') OR EXIT('No direct script access allowed');
/**
*
*/
class Authdb extends MY_Model {
    function __construct()
    {
        parent::__construct();
    }

    //=== START AUTH CODE
    function get_auth_code($id)
    {
        $this->db->where('acode_id', $id);
        $this->db->limit(1);
        $query = $this->db->get($this->tbl_auth_code);
        return $query->row();
    }

    // BEGIN UNTUK KEPERLUAAN CRONJOB
    function copy_auth_token()
    {
        $sql = " INSERT IGNORE INTO ". $this->tbl_history_pushtoken ." (user_id, hpt_platform, hpt_pushtoken, created_date)
                    SELECT user_id, atoken_platform, atoken_pushnotif, created_date FROM ". $this->tbl_auth_token ." WHERE atoken_pushnotif NOT IN ('', 'BLACKLISTED')  ORDER BY user_id ASC ";
        return $this->db->query($sql);
    }
    // END UNTUK KEPERLUAAN CRONJOB

    function getpaging_auth_code($where = '', $data = [], $order = '', $page = 1)
    {
        $orderby    = ($order == "" ? " acode_id DESC " : $order);
        $page       = ($page <= 0 ? 1 : intval($page));
        $last_row   = $this->row_per_page * $page;
        $first_row  = $last_row - $this->row_per_page;

        $sql = "SELECT * FROM {$this->tbl_auth_code} WHERE 1=1 {$where} ORDER BY {$orderby}";
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

    function getall_auth_code($where = '', $data = [], $order = '', $limit=0)
    {
        $orderby    = ($order == "" ? " acode_id DESC " : $order);

        // search dulu row nya.
        $sql        = "SELECT *
                        FROM ".$this->tbl_auth_code." auth_code
                        WHERE 1=1 ". $where ." ORDER BY ".$orderby;
        if($limit > 0){
            $data[] = $limit;
            $sql    .= " LIMIT ? ";
        }
        $query      = $this->db->query($sql, $data);

        return $query->result();
    }

    function get_auth_code_custom_filter($where = '', $data = [])
    {
        $sql = "SELECT * FROM {$this->tbl_auth_code} WHERE 1=1 {$where} LIMIT 1";
        $query= $this->db->query($sql, $data);
        return $query->row();
    }
    //=== END AUTH CODE

    // START AUTH SMS
    function getpaging_auth_sms($where = '', $data = [], $order = '', $page = 1)
    {
        $orderby    = ($order == "" ? " ascent_id DESC " : $order);
        $page       = ($page <= 0 ? 1 : intval($page));
        $last_row   = $this->row_per_page * $page;
        $first_row  = $last_row - $this->row_per_page;

        $sql = "SELECT * FROM {$this->tbl_auth_sms} WHERE 1=1 {$where} ORDER BY {$orderby}";
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
    // END AUTH SMS

    // START AUTH TOKEN
    function getpaging_auth_token($where = '', $data = [], $order = '', $page = 1)
    {
        $orderby    = ($order == "" ? " atoken_id DESC " : $order);
        $page       = ($page <= 0 ? 1 : intval($page));
        $last_row   = $this->row_per_page * $page;
        $first_row  = $last_row - $this->row_per_page;

        $sql = "SELECT atoken.*, user.user_name, user.user_phone, user.user_email FROM {$this->tbl_auth_token} atoken
                INNER JOIN {$this->tbl_user} user ON atoken.user_id = user.user_id
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

    function getall_atokenpush_notif_by_user_id($id)
    {
        $user_status = $this->config->item('user')['status'];
        $this->db->where('user_id', $id);
        $this->db->where('atoken_status', $user_status['active']);
        $query = $this->db->get($this->tbl_auth_token);
        return $query->result();
    }
    // END AUTH TOKEN


    // BEGIN UNTUK KEPERLUAAN CRONJOB

    function delete_auth_code($date)
    {
        $sql = "DELETE FROM {$this->tbl_auth_code} WHERE `created_date` < ?";
        return $this->db->query($sql, [$date]);
    }
    function delete_auth_code_sms($date)
    {
        $sql = "DELETE FROM {$this->tbl_auth_sms} WHERE `created_date` < ?";
        return $this->db->query($sql, [$date]);
    }

    function update_expired_auth_token($date)
    {
        $auth_token_status = $this->config->item('auth_token')['status'];
        $sql = "UPDATE {$this->tbl_auth_token} SET atoken_status = ? WHERE `expired_date` < ? AND atoken_status = ?";
        return $this->db->query($sql, [$auth_token_status['inactive'], $date, $auth_token_status['active']]);
    }

    function delete_inactive_auth_token()
    {
        $auth_token_status = $this->config->item('auth_token')['status'];
        $sql = "DELETE FROM {$this->tbl_auth_token} WHERE atoken_status = ?";
        return $this->db->query($sql, [$auth_token_status['inactive']]);
    }


    // END UNTUK KEPERLUAAN CRONJOB
}
?>
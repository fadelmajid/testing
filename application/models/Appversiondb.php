<?php 
defined('BASEPATH') OR EXIT('No direct script access allowed');
/**
*
*/
class Appversiondb extends MY_Model {
    function __construct()
    {
        parent::__construct();
    }

    //=== START BANK
    function get_app_version($id)
    {
        $this->db->where('ver_id', $id);
        $this->db->limit(1);
        $query = $this->db->get($this->tbl_app_version);
        return $query->row();
    }

    function get_version_by_code_platform($ver_code, $ver_platform)
    {
        $this->db->where('ver_code', $ver_code);
        $this->db->where('ver_platform', $ver_platform);
        $this->db->limit(1);
        $query = $this->db->get($this->tbl_app_version);
        return $query->row();
    }

    function getpaging_app_version($where = '', $data = [], $order = '', $page = 1)
    {
        $orderby    = ($order == "" ? " ver_id DESC " : $order);
        $page       = ($page <= 0 ? 1 : intval($page));
        $last_row   = $this->row_per_page * $page;
        $first_row  = $last_row - $this->row_per_page;

        $sql = "SELECT * FROM {$this->tbl_app_version} WHERE 1=1 {$where} ORDER BY {$orderby}";
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

    function getall_app_version($where = '', $data = [], $order = '', $limit=0)
    {
        $orderby    = ($order == "" ? " ver_id DESC " : $order);

        // search dulu row nya.
        $sql        = "SELECT * FROM ".$this->tbl_app_version." WHERE 1=1 ". $where ." ORDER BY ".$orderby;
        if($limit > 0){
            $data[] = $limit;
            $sql    .= " LIMIT ? ";
        }
        $query      = $this->db->query($sql, $data);
        
        return $query->result();
    }

    function get_app_version_custom_filter($where = '', $data = [])
    {
        $sql = "SELECT * FROM {$this->tbl_app_version} WHERE 1=1 {$where} LIMIT 1";
        $query= $this->db->query($sql, $data);
        return $query->row();
    }

    function insert_app_version($data)
    {
        $result = $this->db->insert($this->tbl_app_version, $data);
        if ($result) {
            return $this->db->insert_id();
        }
        return false;
    }
    
    function update_app_version($id, $data)
    {
        $this->db->where('ver_id', $id);
        return $this->db->update($this->tbl_app_version, $data); 
    }

    function delete_app_version($ver_id)
    {
        if ($this->db->delete( $this->tbl_app_version , ['ver_id' => $ver_id])) {
            return true;
        }
        return false;
    }
    //=== END BANK
}
?>
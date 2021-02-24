<?php 
defined('BASEPATH') OR EXIT('No direct script access allowed');
/**
*
*/
class Bankdb extends MY_Model {
    function __construct()
    {
        parent::__construct();
    }

    //=== START BANK
    function get_bank($id)
    {
        $this->db->where('bank_id', $id);
        $this->db->limit(1);
        $query = $this->db->get($this->tbl_bank);
        return $query->row();
    }

    function getpaging_bank($where = '', $data = [], $order = '', $page = 1)
    {
        $orderby    = ($order == "" ? " bank_id ASC " : $order);
        $page       = ($page <= 0 ? 1 : intval($page));
        $last_row   = $this->row_per_page * $page;
        $first_row  = $last_row - $this->row_per_page;

        $sql = "SELECT * FROM {$this->tbl_bank} WHERE 1=1 {$where} ORDER BY {$orderby}";
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

    function getall_bank($where = '', $data = [], $order = '', $limit=0)
    {
        $orderby    = ($order == "" ? " bank_id ASC " : $order);

        // search dulu row nya.
        $sql        = "SELECT *
                        FROM ".$this->tbl_bank." bank
                        WHERE 1=1 ". $where ." ORDER BY ".$orderby;
        if($limit > 0){
            $data[] = $limit;
            $sql    .= " LIMIT ? ";
        }
        $query      = $this->db->query($sql, $data);
        
        return $query->result();
    }

    function get_bank_custom_filter($where = '', $data = [])
    {
        $sql = "SELECT * FROM {$this->tbl_bank} WHERE 1=1 {$where} LIMIT 1";
        $query= $this->db->query($sql, $data);
        return $query->row();
    }

    function insert_bank($data)
    {
        $result = $this->db->insert($this->tbl_bank, $data);
        if ($result) {
            return $this->db->insert_id();
        }
        return false;
    }
    
    function update_bank($id, $data)
    {
        $this->db->where('bank_id', $id);
        return $this->db->update($this->tbl_bank, $data); 
    }

    function delete_bank($bank_id)
    {
        if ($this->db->delete( $this->tbl_bank , ['bank_id' => $bank_id])) {
            return true;
        }
        return false;
    }
    //=== END BANK
}
?>
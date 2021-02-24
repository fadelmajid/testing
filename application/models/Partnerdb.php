<?php 
defined('BASEPATH') OR EXIT('No direct script access allowed');
/**
*
*/
class Partnerdb extends MY_Model {
    function __construct()
    {
        parent::__construct();
    }

    //=== START PARTNER
    function get_partner($id)
    {
        $this->db->where('ptr_id', $id);
        $this->db->limit(1);
        $query = $this->db->get($this->tbl_partner);
        return $query->row();
    }

    function get_partner_by_code($code){
        $this->db->where('ptr_code', $code);
        $this->db->limit(1);
        $query = $this->db->get($this->tbl_partner);
        return $query->row();
    }

    function getpaging_partner($where = '', $data = [], $order = '', $page = 1)
    {
        $orderby    = ($order == "" ? " ptr_id ASC " : $order);
        $page       = ($page <= 0 ? 1 : intval($page));
        $last_row   = $this->row_per_page * $page;
        $first_row  = $last_row - $this->row_per_page;

        $sql = "SELECT * FROM {$this->tbl_partner}
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

    function getall_partner($where = '', $data = [], $order = '', $limit=0)
    {
        $orderby    = ($order == "" ? " ptr_id ASC " : $order);

        // search dulu row nya.
        $sql        = "SELECT *
                        FROM ".$this->tbl_partner." partner
                        WHERE 1=1 ". $where ." ORDER BY ".$orderby;
        if($limit > 0){
            $data[] = $limit;
            $sql    .= " LIMIT ? ";
        }
        $query      = $this->db->query($sql, $data);
        
        return $query->result();
    }

    function get_partner_custom_filter($where = '', $data = [])
    {
        $sql = "SELECT * FROM {$this->tbl_partner} WHERE 1=1 {$where} LIMIT 1";
        $query= $this->db->query($sql, $data);
        return $query->row();
    }

    function insert_partner($data)
    {
        $result = $this->db->insert($this->tbl_partner, $data);
        if ($result) {
            return $this->db->insert_id();
        }
        return false;
    }
    
    function update_partner($id, $data)
    {
        $this->db->where('ptr_id', $id);
        return $this->db->update($this->tbl_partner, $data); 
    }
    //=== END PARTNER

    //=== START PARTNER PROMO
    function get_partner_promo($id)
    {
        $this->db->where('ptrpm_id', $id);
        $this->db->limit(1);
        $query = $this->db->get($this->tbl_partner_promo);
        return $query->row();
    }

    function getall_partner_promo($where = '', $data = [], $order = '', $limit=0)
    {
        $orderby    = ($order == "" ? " prm_id DESC " : $order);

        // search dulu row nya.
        $sql = "SELECT prm.*, ptrpm.ptrpm_id FROM {$this->tbl_promo} prm
                INNER JOIN {$this->tbl_partner_promo} ptrpm ON prm.prm_id = ptrpm.prm_id
                WHERE 1=1 {$where} ORDER BY {$orderby}";

        if($limit > 0){
            $data[] = $limit;
            $sql    .= " LIMIT ? ";
        }
        $query      = $this->db->query($sql, $data);

        return $query->result();
    }

    function getpaging_partner_promo($where = '', $data = [], $order = '', $page = 1)
    {
        $orderby    = ($order == "" ? " ptr_id ASC " : $order);
        $page       = ($page <= 0 ? 1 : intval($page));
        $last_row   = $this->row_per_page * $page;
        $first_row  = $last_row - $this->row_per_page;

        $sql = "SELECT prm.*, ptrpm.ptrpm_id FROM {$this->tbl_promo} prm
                INNER JOIN {$this->tbl_partner_promo} ptrpm ON prm.prm_id = ptrpm.prm_id
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

    function insert_partner_promo($data)
    {
        $result = $this->db->insert($this->tbl_partner_promo, $data);
        if ($result) {
            return $this->db->insert_id();
        }
        return false;
    }
    
    function update_partner_promo($id, $data)
    {
        $this->db->where('ptrpm_id', $id);
        return $this->db->update($this->tbl_partner_promo, $data); 
    }

    function delete_partner_promo($ptrpm_id)
    {
        if ($this->db->delete( $this->tbl_partner_promo , ['ptrpm_id' => $ptrpm_id])) {
            return true;
        }
        return false;
    }
    
}
?>
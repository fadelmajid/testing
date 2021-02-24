<?php
defined('BASEPATH') OR EXIT('No direct script access allowed');
/**
*
*/
class Staticdb extends MY_Model {
    function __construct()
    {
        parent::__construct();
    }
    
    //=== START FAQ
    function get_faq($id)
    {
        $this->db->where('faq_id', $id);
        $this->db->limit(1);
        $query = $this->db->get($this->tbl_static_faq);
        return $query->row();
    }

    function getpaging_faq($where = '', $data = [], $order_by = ' faq_order ASC', $page = 1)
    {
        $page       = ($page <= 0 ? 1 : intval($page));
        $last_row   = $this->row_per_page * $page;
        $first_row  = $last_row - $this->row_per_page;

        $sql = "SELECT * FROM {$this->tbl_static_faq} WHERE 1=1 {$where} ORDER BY {$order_by}";
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

    function getall_faq($where = '', $data = [], $order = '', $limit=0)
    {
        $orderby    = ($order == "" ? " faq_order ASC " : $order);

        // search dulu row nya.
        $sql        = "SELECT *
                        FROM ".$this->tbl_static_faq." faq
                        WHERE 1=1 ". $where ." ORDER BY ".$orderby;
        if($limit > 0){
            $data[] = $limit;
            $sql    .= " LIMIT ? ";
        }
        $query      = $this->db->query($sql, $data);
        
        return $query->result();
    }

    function insert_faq($data)
    {
        $result = $this->db->insert($this->tbl_static_faq, $data);
        if ($result) {
            return $this->db->insert_id();
        }
        return false;
    }
    
    function update_faq($id, $data)
    {
        $this->db->where('faq_id', $id);
        return $this->db->update($this->tbl_static_faq, $data); 
    }

    function delete_faq($faq_id)
    {
        if ($this->db->delete( $this->tbl_static_faq , ['faq_id' => $faq_id])) {
            return true;
        }
        return false;
    }
    //=== END FAQ

    //>>START STATIC IMAGE<<
    public function get_static_image($id){
        $this->db->where('stat_id', $id);
        $this->db->limit(1);
        $query = $this->db->get($this->tbl_static_image);
        return $query->row();
    }

    public function get_static_image_by_code($code){
        $this->db->where('stat_code', $code);
        $this->db->limit(1);
        $query = $this->db->get($this->tbl_static_image);
        return $query->row();
    }

    public function getpaging_static_image($where = '', $data = [], $order = '', $page = 1){
        $page       = ($page <= 0 ? 1 : intval($page));
        $last_row   = $this->row_per_page * $page;
        $first_row  = $last_row - $this->row_per_page;
        $order_by   = ($order == "" ? " stat_id ASC" : $order);

        $sql        = "SELECT * FROM {$this->tbl_static_image} WHERE 1=1 {$where} ORDER BY {$order_by}";
        $query      = $this->db->query($sql, $data);
        $total_row  = $query->num_rows();
        
        $data[]     = $this->row_per_page;
        $data[]     = intval($first_row > $total_row ? $total_row : $first_row);
        $sql       .= " LIMIT ? OFFSET ? ";
        $query = $this->db->query($sql, $data);

        return [
            'data'      => $query->result(),
            'total_row' => $total_row,
            'per_page'  => $this->row_per_page,
        ];
    }

    public function getall_static_image($where = '', $data = [], $order = '', $limit=0){
        $orderby    = ($order == "" ? " stat_id ASC " : $order);

        // search dulu row nya.
        $sql    = "SELECT *
                    FROM ".$this->tbl_static_image." image
                    WHERE 1=1 ". $where ." ORDER BY ".$orderby;
        if($limit > 0){
            $data[]  = $limit;
            $sql    .= " LIMIT ? ";
        }
        $query      = $this->db->query($sql, $data);
        
        return $query->result();
    }

    public function insert_static_image($data){
        $result = $this->db->insert($this->tbl_static_image, $data);
        if ($result) {
            return $this->db->insert_id();
        }
        return false;
    }

    public function update_static_image($id, $data){
        $this->db->where('stat_id', $id);
        return $this->db->update($this->tbl_static_image, $data); 
    }

    public function delete_static_image($stat_id){
        if ($this->db->delete( $this->tbl_static_image , ['stat_id' => $stat_id])) {
            return true;
        }
        return false;
    }
    //>>END STATIC IMAGE<<
}
?>

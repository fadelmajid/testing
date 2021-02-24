<?php
defined('BASEPATH') OR EXIT('No direct script access allowed');

class User_downloaddb extends MY_Model {
    protected $_database_clone;

    function __construct()
    {
        parent::__construct();
    }

    //>>START USER DOWNLOAD<<
    public function get_user_download($id){
        $sql    = "SELECT *
                    FROM ".$this->tbl_user_download."
                    WHERE usrd_id = ?";
        $query  = $this->db->query($sql, [$id]);

        return $query->row();
    }

    public function getpaging_user_download($where = '', $data = [], $order = '', $page = 1){
        $orderby    = ($order == "" ? " usrd_id ASC" : $order);
        $page       = ($page <= 0 ? 1 : intval($page));
        $last_row   = $this->row_per_page * $page;
        $first_row  = $last_row - $this->row_per_page;

        $sql        = "SELECT *
                        FROM {$this->tbl_user_download}
                        WHERE 1=1 {$where} ORDER BY {$orderby}";

        $query      = $this->db->query($sql, $data);
        $total_row  = $query->num_rows();

        $data[]     = $this->row_per_page;
        $data[]     = intval($first_row > $total_row ? $total_row : $first_row);
        $sql        .= " LIMIT ? OFFSET ? ";
        $query      = $this->db->query($sql, $data);

        return [
            'data'      => $query->result(),
            'total_row' => $total_row,
            'per_page'  => $this->row_per_page,
        ];
    }

    public function getall_user_download($where = '', $data = [], $order = '', $limit=0){
        $orderby    = ($order == "" ? " usrd_id ASC " : $order);

        // search dulu row nya.
        $sql        = "SELECT *
                        FROM ".$this->tbl_user_download."
                        WHERE 1=1 ". $where ." ORDER BY ".$orderby;
        if($limit > 0){
            $data[] = $limit;
            $sql    .= " LIMIT ? ";
        }
        $query      = $this->db->query($sql, $data);

        return $query->result();
    }

    public function insert_user_download($data){
        $result = $this->db->insert($this->tbl_user_download, $data);
        if ($result) {
            return $this->db->insert_id();
        }
        return false;
    }

    public function update_user_download($id, $data){
        $this->db->where('usrd_id', $id);
        return $this->db->update($this->tbl_user_download, $data);
    }

    public function delete_user_download($id){
        $this->db->where('usrd_id', $id);
        return $this->db->delete($this->tbl_user_download);
    }

    //>>END USER DOWNLOAD<<
}
?>
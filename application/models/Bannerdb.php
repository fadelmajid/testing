<?php 
defined('BASEPATH') OR EXIT('No direct script access allowed');
/**
*
*/
class Bannerdb extends MY_Model {
    function __construct()
    {
        parent::__construct();
    }

    //=== START BANNER
    function get_banner($id)
    {
        $this->db->where('ban_id', $id);
        $this->db->limit(1);
        $query = $this->db->get($this->tbl_banner);
        return $query->row();
    }

    function getpaging_banner($where = '', $data = [], $order = '', $page = 1)
    {
        $orderby    = ($order == "" ? " ban_order ASC, ban_name ASC " : $order);
        $page       = ($page <= 0 ? 1 : intval($page));
        $last_row   = $this->row_per_page * $page;
        $first_row  = $last_row - $this->row_per_page;

        $sql        = "SELECT * FROM {$this->tbl_banner} 
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

    function getall_banner($where = '', $data = [], $order = '', $limit=0)
    {
        $orderby    = ($order == "" ? " ban_order ASC, ban_name ASC " : $order);
        // search dulu row nya.
        $sql        = "SELECT *
                        FROM ".$this->tbl_banner." banner
                        WHERE 1=1 ". $where ." ORDER BY ".$orderby;
        if($limit > 0){
            $data[] = $limit;
            $sql    .= " LIMIT ? ";
        }
        $query      = $this->db->query($sql, $data);
        
        return $query->result();
    }

    function get_banner_custom_filter($where = '', $data = [])
    {
        $sql    = "SELECT * FROM {$this->tbl_banner} WHERE 1=1 {$where} LIMIT 1";
        $query  = $this->db->query($sql, $data);
        return $query->row();
    }

    function insert_banner($data)
    {
        $result = $this->db->insert($this->tbl_banner, $data);
        if ($result) {
            return $this->db->insert_id();
        }
        return false;
    }
    
    function update_banner($id, $data)
    {
        $this->db->where('ban_id', $id);
        return $this->db->update($this->tbl_banner, $data); 
    }

    function delete_banner($ban_id)
    {
        $this->db->where('ban_id', $ban_id);
        return $this->db->delete($this->tbl_banner); 
    }

    function sort_banner_order($banner_id, $banner_order){
        $order          = " banner.ban_order ASC ";
        $list_banner    = $this->getall_banner($where = "", $data = [], $order);

        $no = 1;
        foreach($list_banner as $value){
            if($no == $banner_order && $value->ban_id != $banner_id){
                $no++;
                unset($updata);
                $updata['ban_order'] = $no;
                $this->update_banner($value->ban_id, $updata);
                $no++;
            }elseif($no != $banner_order && $value->ban_id != $banner_id){
                unset($updata);
                $updata['ban_order'] = $no;
                $this->update_banner($value->ban_id, $updata);
                $no++;
            }
        }
    }
    //=== END BANNER

    //>>START BANNER CATALOGUE<<
    function get_banner_catalogue($id)
    {
        $this->db->where('banc_id', $id);
        $this->db->limit(1);
        $query = $this->db->get($this->tbl_banner_catalogue);
        return $query->row();
    }

    function getpaging_banner_catalogue($where = '', $data = [], $order = '', $page = 1)
    {
        $orderby    = ($order == "" ? " banc_order ASC, banc_name ASC " : $order);
        $page       = ($page <= 0 ? 1 : intval($page));
        $last_row   = $this->row_per_page * $page;
        $first_row  = $last_row - $this->row_per_page;

        $sql        = "SELECT * FROM {$this->tbl_banner_catalogue} 
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

    function getall_banner_catalogue($where = '', $data = [], $order = '', $limit=0)
    {
        $orderby    = ($order == "" ? " banc_order ASC, banc_name ASC " : $order);
        // search dulu row nya.
        $sql        = "SELECT *
                        FROM ".$this->tbl_banner_catalogue."
                        WHERE 1=1 ". $where ." ORDER BY ".$orderby;
        if($limit > 0){
            $data[] = $limit;
            $sql    .= " LIMIT ? ";
        }
        $query      = $this->db->query($sql, $data);
        
        return $query->result();
    }

    function get_banner_catalogue_custom_filter($where = '', $data = [])
    {
        $sql    = "SELECT * FROM {$this->tbl_banner_catalogue} WHERE 1=1 {$where} LIMIT 1";
        $query  = $this->db->query($sql, $data);
        return $query->row();
    }

    function insert_banner_catalogue($data)
    {
        $result = $this->db->insert($this->tbl_banner_catalogue, $data);
        if ($result) {
            return $this->db->insert_id();
        }
        return false;
    }
    
    function update_banner_catalogue($id, $data)
    {
        $this->db->where('banc_id', $id);
        return $this->db->update($this->tbl_banner_catalogue, $data); 
    }

    function sort_banner_catalogue_order($banner_id, $banner_order){
        $order          = " banc_order ASC ";
        $list_banner    = $this->getall_banner_catalogue($where = "", $data = [], $order);

        $no = 1;
        foreach($list_banner as $value){
            if($no == $banner_order && $value->banc_id != $banner_id){
                $no++;
                unset($updata);
                $updata['banc_order'] = $no;
                $this->update_banner_catalogue($value->banc_id, $updata);
                $no++;
            }elseif($no != $banner_order && $value->banc_id != $banner_id){
                unset($updata);
                $updata['banc_order'] = $no;
                $this->update_banner_catalogue($value->banc_id, $updata);
                $no++;
            }
        }
    }
    //>>END BANNER CATALOGUE<<
}
?>
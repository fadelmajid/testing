<?php
defined('BASEPATH') OR EXIT('No direct script access allowed');

class Courierdb extends MY_Model {
    public function __construct(){
        parent::__construct();
    }

    //==== START COURIER
    public function get_courier($id){
        $this->db->where('courier_id', $id);
        $this->db->limit(1);
        $query = $this->db->get($this->tbl_courier);
        return $query->row();
    }

    public function getpaging_courier($where = '', $data = [], $order = '', $page = 1){
        $orderby    = ($order == "" ? " courier_id ASC, courier_code ASC " : $order);
        $page       = ($page <= 0 ? 1 : intval($page));
        $last_row   = $this->row_per_page * $page;
        $first_row  = $last_row - $this->row_per_page;

        $sql        = "SELECT *
                        FROM {$this->tbl_courier}
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

    public function getall_courier($where = '', $data = [], $order = '', $limit=0){
        $orderby    = ($order == "" ? " courier_id ASC " : $order);

        // search dulu row nya.
        $sql        = "SELECT *
                        FROM ".$this->tbl_courier."
                        WHERE 1=1 ". $where ." ORDER BY ".$orderby;
        if($limit > 0){
            $data[] = $limit;
            $sql    .= " LIMIT ? ";
        }
        $query      = $this->db->query($sql, $data);

        return $query->result();
    }

    public function update_is_default($courier_id, $is_default){
        $order =  " courier_id ASC ";
        $list_courier = $this->getall_courier($where = "", $data = [], $order);
        $is_default_new = 1;
        
        foreach($list_courier as $value){
            if($is_default_new == $is_default && $value->courier_id != $courier_id){
                $is_default_new = 0;
                unset($updata);
                $updata['is_default'] = $is_default_new;
                $this->update_courier($value->courier_id, $updata);
                $is_default_new = 1;
            }elseif($is_default_new != $is_default && $value->courier_id != $courier_id){
                unset($updata);
                $updata['is_default'] = $is_default_new;
                $this->update_courier($value->courier_id, $updata);
                $is_default_new = 1;
            }
        }
    }

    public function insert_courier($id, $data){
        $result = $this->db->insert($this->tbl_courier, $data);
        if ($result) {
            return $this->db->insert_id();
        }
        return false;
    }

    public function get_courier_code($code)
    {
        $sql = "SELECT * FROM {$this->tbl_courier} WHERE courier_code = ? LIMIT 1";
        $query= $this->db->query($sql, [$code]);
        return $query->row();
    }

    public function update_courier($id, $data){
        $this->db->where('courier_id', $id);
        return $this->db->update($this->tbl_courier, $data);
    }

    public function get_courier_custom_filter($where = '', $data = [])
    {
        $sql = "SELECT * FROM {$this->tbl_courier} WHERE 1=1 {$where} LIMIT 1";
        $query= $this->db->query($sql, $data);
        return $query->row();
    }

    public function delete_courier($id){
        $this->db->where('courier_id', $id);
        return $this->db->delete($this->tbl_courier);
    }

    public function update_is_default_not_true()
    {
        $data   = $this->getall_courier($where = 'AND is_default = ?', $data = [0], $order = 'courier_id DESC', $limit=1);
        $sql    = $this->update_courier($data[0]->courier_id, ["is_default" => 1]);
        return $sql;
    }

    public function get_courier_default()
    {
        $sql = "SELECT * FROM {$this->tbl_courier} WHERE is_default = ? LIMIT 1";
        $query= $this->db->query($sql, [1]);
        return $query->row();
    }

    //==== END COURIER

    //==== START USER ORDER COURIER
    public function insert_order_courier($data)
    {
        $result = $this->db->insert($this->tbl_user_order_courier, $data);
        if ($result) {
            return $this->db->insert_id();
        }
        return false;
    }

    public function get_order_courier($id)
    {
        $sql = "SELECT * FROM {$this->tbl_user_order_courier} WHERE uorcr_id = ? LIMIT 1";
        $query= $this->db->query($sql, [$id]);
        return $query->row();
    }

    public function get_order_courier_booking($id)
    {
        $sql = "SELECT * FROM {$this->tbl_user_order_courier} WHERE booking_id LIKE ? LIMIT 1";
        $query= $this->db->query($sql, [$id]);
        return $query->row();
    }

    public function update_order_courier($id, $data)
    {
        $this->db->where('uorcr_id', $id);
        return $this->db->update($this->tbl_user_order_courier, $data);
    }

    public function getall_latest_courier($data)
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

            // search row
            $sql = "SELECT MAX(uorcr_id) AS uorcr_id
                        FROM ".$this->tbl_user_order_courier."
                        WHERE uor_id IN (".$where.")
                    GROUP BY uor_id";
            $query = $this->db->query($sql, $data);
            $result = $query->result();

            //process table tbl_order_track
            $where_courier = '';
            $data_courier = array();
            foreach($result as $key=>$value){
                $where_courier .= '?,';
                $data_courier[] = $value->uorcr_id;
            }
            $where_courier = substr($where_courier, 0, -1);

            //check dulu kalau ada isinya baru lanjut ke order track
            if($where_courier != ''){
                // search row
                $sql = "SELECT *
                        FROM ".$this->tbl_user_order_courier."
                        WHERE uorcr_id IN (".$where_courier.") ORDER BY uor_id DESC";

                $query = $this->db->query($sql, $data_courier);
                $result = $query->result();

                // looping to set variable
                foreach ($result as $key => $value){
                    $newdata[$value->uor_id] = $value;
                }
            }

        }

        return $newdata;
    }

    //==== END
    
}
?>

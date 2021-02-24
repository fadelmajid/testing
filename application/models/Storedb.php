<?php
defined('BASEPATH') OR EXIT('No direct script access allowed');
/**
*
*/
class Storedb extends MY_Model {
    function __construct()
    {
        parent::__construct();
    }
    
    //=== START STORE
    function get($id)
    {
        $this->db->where('st_id', $id);
        $this->db->limit(1);
        $query = $this->db->get($this->tbl_store);
        return $query->row();
    }

    function get_by_name($name)
    {
        $this->db->where('st_name', $name);
        $this->db->limit(1);
        $query = $this->db->get($this->tbl_store);
        return $query->row();
    }

    function get_by_code($code)
    {
        $this->db->where('st_code', $code);
        $this->db->limit(1);
        $query = $this->db->get($this->tbl_store);
        return $query->row();
    }

    public function get_courier()
    {
        $sql = "SELECT courier_code FROM {$this->tbl_courier}";
        $query= $this->db->query($sql);
        return $query->result();
    }

    public function getpaging($where = '', $data = [], $order = '', $page = 1)
    {
        $orderby    = ($order == "" ? " st_id ASC " : $order);

        $page       = ($page <= 0 ? 1 : intval($page));
        $last_row   = $this->row_per_page * $page;
        $first_row  = $last_row - $this->row_per_page;

        $sql ="SELECT * FROM {$this->tbl_store} WHERE 1=1 {$where} ORDER BY {$orderby}";
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

    function getall($where = '', $data = [], $order = '', $limit = 0)
    {
        $orderby = ($order == "" ? " st_id ASC " : $order);

        $sql = "SELECT * FROM {$this->tbl_store} WHERE 1=1 {$where} ORDER BY {$orderby}";

        if($limit > 0){
            $data[] = $limit;
            $sql    .= " LIMIT ? ";
        }

        $query = $this->db->query($sql, $data);
        return $query->result();
    }

    function insert($data)
    {
        $result = $this->db->insert($this->tbl_store, $data);
        if ($result) {
            return $this->db->insert_id();
        }
        return false;
    }
    
    function update($id, $data)
    {
        $this->db->where('st_id', $id);
        return $this->db->update($this->tbl_store, $data); 
    }
    //=== END STORE

    //=== START STORE PRODUCT
    function getall_product($where = '', $data = [], $order = '', $limit=0)
    {
        $orderby    = ($order == "" ? " cat.cat_name ASC, pd.pd_name ASC " : $order);

        // search dulu row nya.
        $sql        = "SELECT stpd.*, pd.pd_name, pd.pd_img,  pd.pd_status, cat.cat_name 
                        FROM ".$this->tbl_store_product." stpd 
                        INNER JOIN ". $this->tbl_product ." pd ON stpd.pd_id = pd.pd_id
                        INNER JOIN ". $this->tbl_category ." cat ON pd.cat_id = cat.cat_id
                        WHERE 1=1 ". $where ." ORDER BY ".$orderby;
        if($limit > 0){
            $data[] = $limit;
            $sql    .= " LIMIT ? ";
        }
        $query      = $this->db->query($sql, $data);
        
        return $query->result();
    }

    function getarr_store()
    {
        $arr = array();
        $result = $this->getall();
        foreach($result as $val){
            $arr[ $val->st_id ] = $val->st_name;
        }
        return $arr;
    }
    //=== END STORE PRODUCT

    //=== START STORE OPERATIONAL
    function get_store_opt($id)
    {
        $this->db->where('sto_id', $id);
        $this->db->limit(1);
        $query = $this->db->get($this->tbl_store_operational);
        return $query->row();
    }

    function getpaging_store_opt($where = '', $data = [], $order = '', $page = 1)
    {
        $orderby    = ($order == "" ? " sto_id ASC " : $order);

        $page       = ($page <= 0 ? 1 : intval($page));
        $last_row   = $this->row_per_page * $page;
        $first_row  = $last_row - $this->row_per_page;

        $sql        = "SELECT store_opt.*,store.st_name FROM {$this->tbl_store_operational} store_opt
                        INNER JOIN {$this->tbl_store} store
                        ON store.st_id=store_opt.st_id
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

    function getall_store_opt($select = '', $where = '', $data = [], $order = '', $limit = 0, $groupby = "")
    {
        $orderby    = ($order == "" ? " sto_id ASC " : $order); 
        $selectby   = ($select == "" ? " store_opt.*,store.st_name " : $select);

        $sql        = "SELECT {$selectby} FROM {$this->tbl_store_operational} store_opt
                        INNER JOIN {$this->tbl_store} store
                        ON store.st_id = store_opt.st_id 
                        WHERE 1=1 {$where} {$groupby} ORDER BY {$orderby}";
        
        if($limit > 0){
            $data[] = $limit;
            $sql    .= " LIMIT ? ";
        }
        
        $query      = $this->db->query($sql, $data);
        return $query->result();
    }

    function insert_store_opt($data)
    {
        $result = $this->db->insert($this->tbl_store_operational, $data);
        if ($result) {
            return $this->db->insert_id();
        }
        return false;
    }
    
    function update_store_opt($id, $data)
    {
        $this->db->where('sto_id', $id);
        return $this->db->update($this->tbl_store_operational, $data); 
    }

    function delete_store_opt($sto_id)
    {
        $this->db->where('sto_id', $sto_id);
        return $this->db->delete($this->tbl_store_operational); 
    }
    //=== END STORE OPERATIONAL

    //>>START STORE IMAGE<<
    function get_store_img($id)
    {
        $this->db->where('sti_id', $id);
        $this->db->limit(1);
        $query = $this->db->get($this->tbl_store_image);
        return $query->row();
    }

    function getpaging_store_img($where = '', $data = [], $order = '', $page = 1)
    {
        $orderby    = ($order == "" ? " sti_order ASC " : $order);

        $page       = ($page <= 0 ? 1 : intval($page));
        $last_row   = $this->row_per_page * $page;
        $first_row  = $last_row - $this->row_per_page;

        $sql        = "SELECT sti.*,st.st_name
                        FROM {$this->tbl_store_image} sti
                        INNER JOIN {$this->tbl_store} st
                        ON st.st_id = sti.st_id
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

    function getall_store_img($where = '', $data = [], $order = '', $limit = 0)
    {
        $orderby    = ($order == "" ? " sti_order ASC " : $order); 

        $sql        = "SELECT sti.*,st.st_name
                        FROM {$this->tbl_store_image} sti
                        INNER JOIN {$this->tbl_store} st
                        ON st.st_id = sti.st_id
                        WHERE 1=1 {$where} ORDER BY {$orderby}";
        
        if($limit > 0){
            $data[] = $limit;
            $sql    .= " LIMIT ? ";
        }
        
        $query      = $this->db->query($sql, $data);
        return $query->result();
    }

    function insert_store_img($data)
    {
        $result = $this->db->insert($this->tbl_store_image, $data);
        if ($result) {
            return $this->db->insert_id();
        }
        return false;
    }
    
    function update_store_img($id, $data)
    {
        $this->db->where('sti_id', $id);
        return $this->db->update($this->tbl_store_image, $data); 
    }

    function delete_store_img($sto_id)
    {
        $this->db->where('sti_id', $sto_id);
        return $this->db->delete($this->tbl_store_image); 
    }

    function sort_store_img_order($sti_id, $sti_order){
        $order          = " sti.sti_order ASC ";
        $list_st_img    = $this->getall_store_img($where = "", $data = [], $order);

        $no = 1;
        foreach($list_st_img as $value){
            if($no == $sti_order && $value->sti_id != $sti_id){
                $no++;
                unset($updata);
                $updata['sti_order'] = $no;
                $this->update_store_img($value->sti_id, $updata);
                $no++;
            }elseif($no != $sti_order && $value->sti_id != $sti_id){
                unset($updata);
                $updata['sti_order'] = $no;
                $this->update_store_img($value->sti_id, $updata);
                $no++;
            }
        }
    }
    //>>END STORE IMAGE<<

    //=== START STORE CONSTANT CONFIG
    function get_store_constant()
    {
        $query = $this->db->get($this->tbl_store_constant);
        return $query->row();
    }

    function update_store_constant($id, $data)
    {
        $this->db->where('stct_id', $id);
        return $this->db->update($this->tbl_store_constant, $data); 
    }

    function getpaging_store_config($where = '', $data = [], $order = '', $page = 1)
    {
        // $this->row_per_page = 1;

        $orderby    = ($order == "" ? " stcf.stcf_id ASC " : $order);

        $page       = ($page <= 0 ? 1 : intval($page));
        $last_row   = $this->row_per_page * $page;
        $first_row  = $last_row - $this->row_per_page;

        $sql        = "SELECT stcf.*, st.st_id, st.st_name FROM {$this->tbl_store_config} stcf
                        INNER JOIN {$this->tbl_store} st
                        ON st.st_id = stcf.st_id
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

    function get_store_config($id)
    {
        $this->db->where('stcf_id', $id);
        $this->db->limit(1);
        $query = $this->db->get($this->tbl_store_config);
        return $query->row();
    }

    function check_duplicate_store_config($stcf_id, $st_id)
    {
        $array = array('stcf_id !=' => $stcf_id, 'st_id' => $st_id);
        $this->db->where($array);
        $this->db->limit(1);
        $query = $this->db->get($this->tbl_store_config);
        return $query->row();
    }

    function insert_store_config($data)
    {
        $result = $this->db->insert($this->tbl_store_config, $data);
        if ($result) {
            return $this->db->insert_id();
        }
        return false;
    }

    function update_store_config($id, $data)
    {
        $this->db->where('stcf_id', $id);
        return $this->db->update($this->tbl_store_config, $data); 
    }

    function delete_store_config($id)
    {
        $this->db->where('stcf_id', $id);
        return $this->db->delete($this->tbl_store_config); 
    }

    function get_all_active_store_data($where = '', $data = [])
    {
        $sql = "
        SELECT 
            st_id,
            st_type,
            st_default_type,
            st_status,
            st_default_status,
            min_cup,
            max_cup,
            min_order,
            max_order,
            SUM(total_cup) AS total_cup,
            SUM(total_order) AS total_order
        FROM (
            /* Query to get Total Cup */ 
            SELECT
                uoradd.st_id,
                st.st_type,
                st.st_default_type,
                st.st_status,
                st.st_default_status,
                IF(stcf.stcf_min_cup IS NULL, ?, stcf.stcf_min_cup) AS min_cup,
                IF(stcf.stcf_max_cup IS NULL, ?, stcf.stcf_max_cup) AS max_cup,
                IF(stcf.stcf_min_order IS NULL, ?, stcf.stcf_min_order) AS min_order,
                IF(stcf.stcf_max_order IS NULL, ?, stcf.stcf_max_order) AS max_order,
                SUM(uorpd.uorpd_qty) AS total_cup,
                0 AS total_order
            FROM {$this->tbl_order_address} uoradd
            INNER JOIN {$this->tbl_order} uor
                ON uoradd.uor_id = uor.uor_id
            INNER JOIN {$this->tbl_order_product} uorpd
                ON uoradd.uor_id = uorpd.uor_id
            LEFT JOIN {$this->tbl_store_config} stcf
                ON uoradd.st_id = stcf.st_id
            LEFT JOIN {$this->tbl_store} st
                ON uoradd.st_id = st.st_id
            WHERE
                1 = 1
                {$where}
            GROUP BY uoradd.st_id

            UNION ALL

            /* Query to get Total Order */
            SELECT
                uoradd.st_id,
                st.st_type,
                st.st_default_type,
                st.st_status,
                st.st_default_status,
                IF(stcf.stcf_min_cup IS NULL, ?, stcf.stcf_min_cup),
                IF(stcf.stcf_max_cup IS NULL, ?, stcf.stcf_max_cup),
                IF(stcf.stcf_min_order IS NULL, ?, stcf.stcf_min_order),
                IF(stcf.stcf_max_order IS NULL, ?, stcf.stcf_max_order),
                0 AS total_cup,
                COUNT(uoradd.st_id) AS total_order
            FROM {$this->tbl_order_address} uoradd
            INNER JOIN {$this->tbl_order} uor
                ON uoradd.uor_id = uor.uor_id
            LEFT JOIN {$this->tbl_store_config} stcf
                ON uoradd.st_id = stcf.st_id
            LEFT JOIN {$this->tbl_store} st
                ON uoradd.st_id = st.st_id
            WHERE
                1 = 1
                {$where}
            GROUP BY uoradd.st_id
        ) active_store
        GROUP BY st_id
        ";
        $query = $this->db->query($sql, $data);
        return $query->result();
    }
    //=== END STORE CONSTANT CONFIG
}
?>

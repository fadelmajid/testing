<?php
defined('BASEPATH') OR EXIT('No direct script access allowed');
/**
*
*/
class Productdb extends MY_Model {
    function __construct()
    {
        parent::__construct();
    }
    
    //=== START PRODUCT
    function get_product($id)
    {
        $this->db->where('pd_id', $id);
        $this->db->limit(1);
        $query = $this->db->get($this->tbl_product);
        return $query->row();
    }

    function get_product_by_name($name)
    {
        $this->db->where('pd_name', $name);
        $this->db->limit(1);
        $query = $this->db->get($this->tbl_product);
        return $query->row();
    }

    function getpaging_product($where = '', $data = [], $order = '', $page = 1)
    {
        $orderby    = ($order == "" ? " pd.pd_order ASC, pd.pd_name ASC " : $order);
        $page       = ($page <= 0 ? 1 : intval($page));
        $last_row   = $this->row_per_page * $page;
        $first_row  = $last_row - $this->row_per_page;

        $sql        = "SELECT pd.*, cat.cat_name
                        FROM ".$this->tbl_product." pd
                        INNER JOIN ". $this->tbl_category ." cat ON pd.cat_id = cat.cat_id
                        WHERE 1=1 ". $where ." ORDER BY ".$orderby;

        $query = $this->db->query($sql, $data);
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

    function getall_product($where = '', $data = [], $order = '', $limit=0)
    {
        $orderby    = ($order == "" ? " pd.pd_order ASC, pd.pd_name ASC " : $order);

        // search dulu row nya.
        $sql        = "SELECT pd.*, cat.cat_name
                        FROM ".$this->tbl_product." pd
                        INNER JOIN ". $this->tbl_category ." cat ON pd.cat_id = cat.cat_id
                        WHERE 1=1 ". $where ." ORDER BY ".$orderby;
        if($limit > 0){
            $data[] = $limit;
            $sql    .= " LIMIT ? ";
        }
        $query      = $this->db->query($sql, $data);
        
        return $query->result();
    }

    function insert_product($data)
    {
        $result = $this->db->insert($this->tbl_product, $data);
        if ($result) {
            return $this->db->insert_id();
        }
        return false;
    }
    
    function update_product($id, $data)
    {
        $this->db->where('pd_id', $id);
        return $this->db->update($this->tbl_product, $data); 
    }

    function sort_product_order($product_id, $product_order){
        $order = " pd.pd_order ASC ";
        $list_product = $this->getall_product($where = "", $data = [], $order);
        
        $no = 1;
        foreach($list_product as $value){
            if($no == $product_order && $value->pd_id != $product_id){
                $no++;
                unset($updata);
                $updata['pd_order'] = $no;
                $this->update_product($value->pd_id, $updata);
                $no++;
            }elseif($no != $product_order && $value->pd_id != $product_id){
                unset($updata);
                $updata['pd_order'] = $no;
                $this->update_product($value->pd_id, $updata);
                $no++;
            }
        }
    }
    //=== END PRODUCT

    //=== START STORE PRODUCT
    function get_store_product($id)
    {
        $this->db->where('stpd_id', $id);
        $this->db->limit(1);
        $query = $this->db->get($this->tbl_store_product);
        return $query->row();
    }
    
    function get_store_product_custom_filter($where = '', $data = [])
    {
        $sql = "SELECT * FROM {$this->tbl_store_product} WHERE 1=1 {$where} LIMIT 1";
        $query= $this->db->query($sql, $data);
        return $query->row();
    }

    function getpaging_store_product($where = '', $data = [], $order = '', $page = 1)
    {
        $orderby    = ($order == "" ? " stpd.stpd_id ASC " : $order);

        $page       = ($page <= 0 ? 1 : intval($page));
        $last_row   = $this->row_per_page * $page;
        $first_row  = $last_row - $this->row_per_page;

        $sql ="SELECT stpd.*, pd.pd_name, st.st_name, cat.cat_name FROM {$this->tbl_product} pd 
            INNER JOIN {$this->tbl_store_product} stpd ON pd.pd_id = stpd.pd_id 
            INNER JOIN {$this->tbl_store} st ON stpd.st_id = st.st_id 
            INNER JOIN {$this->tbl_category} cat ON pd.cat_id = cat.cat_id 
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

    function getall_store_product($where = '', $data = [], $order = '', $limit = 0)
    {
        $orderby = ($order == "" ? " st_id ASC " : $order);

        $sql = "SELECT st_id FROM {$this->tbl_store_product} WHERE 1=1 {$where} ORDER BY {$orderby}";

        if($limit > 0){
            $data[] = $limit;
            $sql    .= " LIMIT ? ";
        }

        $query = $this->db->query($sql, $data);
        return $query->result();
    }

    function get_all_store_product($where = '', $data = [], $order = '', $limit = 0)
    {
        $orderby = ($order == "" ? " st_id ASC " : $order);

        $sql = "SELECT * FROM {$this->tbl_store_product} WHERE 1=1 {$where} ORDER BY {$orderby}";

        if($limit > 0){
            $data[] = $limit;
            $sql    .= " LIMIT ? ";
        }

        $query = $this->db->query($sql, $data);
        return $query->result();
    }

    function insert_store_product($data)
    {
        $result = $this->db->insert($this->tbl_store_product, $data);
        if ($result) {
            return $this->db->insert_id();
        }
        return false;
    }

    function update_store_product($id, $data)
    {
        $this->db->where('stpd_id', $id);
        return $this->db->update($this->tbl_store_product, $data); 
    }

    function delete_store_product($id)
    {
        if ($this->db->delete( $this->tbl_store_product , ['stpd_id' => $id])) {
            return true;
        }
        return false;
    }
    
    function import_store_product($stores_id, $adm_id)
    {
        $this->load->model('locktransdb');
        $this->load->model('admindb');

        $arr_admin = $this->admindb->getarr_admin();

        //begin transaction
        $this->db->trans_begin();
        
        // add lock trans
        $this->locktransdb->insert("Import products to Store ID ". implode(',', $stores_id) ."  by ". (isset($arr_admin[$adm_id]) ? $arr_admin[$adm_id] : 'ID:'.$adm_id));

        // find not existed products in each store
        foreach ($stores_id as $store_id) {
            $sql = "SELECT pd_id FROM {$this->tbl_product} WHERE pd_id NOT IN (SELECT pd_id FROM {$this->tbl_store_product} where st_id = ?)";
            $query = $this->db->query($sql, [$store_id]);

            // insert new store product
            foreach ($query->result() as $product) {
                $params = [
                    'st_id' => $store_id,
                    'pd_id' => $product->pd_id,
                    'stpd_status' => $this->config->item('store_product')['status']['active'],
                    'created_by' => $adm_id,
                    'created_date' => date('Y-m-d H:i:s'),
                ];
                $this->productdb->insert_store_product($params);
            }
        }

        // end transaction
        if (!$this->db->trans_status()) {
            $this->db->trans_rollback();
            return false;
        } 
        $this->db->trans_commit();
        return true;
    }

    function import_store_product_n_bulk($data, $adm_id)
    {
        $this->load->model('locktransdb');
        $this->load->model('admindb');

        $arr_admin = $this->admindb->getarr_admin();

        //begin transaction
        $this->db->trans_begin();
        
        // add lock trans
        $this->locktransdb->insert("Import products to Store ID ". implode(',', $data['st_id']) ."  by ". (isset($arr_admin[$adm_id]) ? $arr_admin[$adm_id] : 'ID:'.$adm_id));

        $st_id              = [];
        if(isset($data['st_id_ex'])){
            //untuk st_id dgn pengecualian
            $sql            = "SELECT st_id FROM {$this->tbl_store} where st_id NOT IN ? ";
            $query_st_id    = $this->db->query($sql, [$data['st_id_ex']]);
                        
            foreach($query_st_id->result() as $val){
                $st_id[]    = $val->st_id;
            }
        }else{
            $st_id          = $data['st_id'];
        }

        // find not existed products in each store
        foreach ($st_id as $store_id) {
            // insert new store product
            foreach ($data['pd_id'] as $key => $product_id) {

                $sql        = "SELECT * FROM {$this->tbl_store_product} where st_id = ? AND pd_id = ?";
                $query      = $this->db->query($sql, [$store_id, $product_id]);
                $params     = [
                    'st_id'         => $store_id,
                    'pd_id'         => $product_id,
                    'stpd_status'   => isset($data['pd_status'][$key]) ? $data['pd_status'][$key] : $data['stpd_status'],
                ];

                if(empty($query->result())){
                    $params['created_by']    = $adm_id;
                    $params['created_date']  = date('Y-m-d H:i:s');
                    $this->insert_store_product($params);
                }
                else{
                    $stpd_id                 = $query->result()[0];
                    $params['updated_by']    = $data['updated_by'];
                    $params['updated_date']  = date('Y-m-d H:i:s');
                    $this->update_store_product($stpd_id->stpd_id, $params);
                }
            }
        }

        // end transaction
        if (!$this->db->trans_status()) {
            $this->db->trans_rollback();
            return false;
        } 
        $this->db->trans_commit();
        return true;
    }   
    //=== END STORE PRODUCT

    //=== BEGIN CATEGORY
    function get_category($id)
    {
        $this->db->where('cat_id', $id);
        $this->db->limit(1);
        $query = $this->db->get($this->tbl_category);
        return $query->row();
    }

    function get_category_by_name($name)
    {
        $this->db->where('cat_name', $name);
        $this->db->limit(1);
        $query = $this->db->get($this->tbl_category);
        return $query->row();
    }

    function getall_category($where = '', $data = [], $order = '', $limit=0)
    {
        $orderby    = ($order == "" ? " cat_name ASC " : $order);

        // search dulu row nya.
        $sql        = "SELECT *
                        FROM ".$this->tbl_category." cat
                        WHERE 1=1 ". $where ." ORDER BY ".$orderby;
        if($limit > 0){
            $data[] = $limit;
            $sql    .= " LIMIT ? ";
        }
        $query      = $this->db->query($sql, $data);
        
        return $query->result();
    }

    function getpaging_category($where = '', $data = [], $order = '', $page = 1)
    {
        $orderby    = ($order == "" ? " cat_name ASC " : $order);

        $page       = ($page <= 0 ? 1 : intval($page));
        $last_row   = $this->row_per_page * $page;
        $first_row  = $last_row - $this->row_per_page;

        $sql = "SELECT * FROM {$this->tbl_category} WHERE 1=1 {$where} ORDER BY {$orderby}";
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

    function insert_category($data)
    {
        $result = $this->db->insert($this->tbl_category, $data);
        if ($result) {
            return $this->db->insert_id();
        }
        return false;
    }

    function update_category($id, $data)
    {
        $this->db->where('cat_id', $id);
        return $this->db->update($this->tbl_category, $data); 
    }

    function sort_category_order($category_id, $category_order){
        $order =  " cat.cat_order ASC ";
        $list_category = $this->getall_category($where = "", $data = [], $order);
        $no = 1;
        foreach($list_category as $value){
            if($no == $category_order && $value->cat_id != $category_id){
                $no++;
                unset($updata);
                $updata['cat_order'] = $no;
                $this->update_category($value->cat_id, $updata);
                $no++;
            }elseif($no != $category_order && $value->cat_id != $category_id){
                unset($updata);
                $updata['cat_order'] = $no;
                $this->update_category($value->cat_id, $updata);
                $no++;
            }
        }
    }
    //=== END CATEGORY

    //=== START PRODUCT COGS
    function get_product_cogs($id)
    {
        $this->db->where('pdcogs_id', $id);
        $this->db->limit(1);
        $query = $this->db->get($this->tbl_product_cogs);
        return $query->row();
    }

    function get_cogs_date()
    {
        $sql        = "SELECT cogs_date
                        FROM {$this->tbl_product_cogs}";
        $query      = $this->db->query($sql);
        return $query->result();
    }

    function getpaging_product_cogs($where = '', $data = [], $order = '', $page = 1)
    {
        $page       = ($page <= 0 ? 1 : intval($page));
        $last_row   = $this->row_per_page * $page;
        $first_row  = $last_row - $this->row_per_page;

        $orderby    = ($order == "" ? " pd_cogs.pdcogs_id ASC, pd.pd_name ASC " : $order);

        $sql =
            "SELECT pd_cogs.*, pd.pd_name
                FROM {$this->tbl_product_cogs} pd_cogs
                INNER JOIN {$this->tbl_product} pd
                ON pd.pd_id = pd_cogs.pd_id
                WHERE 1=1 {$where} ORDER BY $orderby";

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

    function getall_product_cogs($where = '', $data = [], $order = '', $limit=0)
    {
        $orderby    = ($order == "" ? " pd_cogs.pdcogs_id ASC" : $order);

        $sql =
            "SELECT pd_cogs.*, pd.pd_name
                FROM {$this->tbl_product_cogs} pd_cogs
                INNER JOIN {$this->tbl_product} pd
                ON pd.pd_id = pd_cogs.pd_id
                WHERE 1=1 {$where} ORDER BY $orderby";

        if($limit > 0){
            $data[] = $limit;
            $sql    .= " LIMIT ? ";
        }
        $query      = $this->db->query($sql, $data);
        
        return $query->result();
    }

    function insert_product_cogs($data)
    {
        $result = $this->db->insert($this->tbl_product_cogs, $data);
        if ($result) {
            return $this->db->insert_id();
        }
        return false;
    }
    
    function update_product_cogs($id, $data)
    {
        $this->db->where('pdcogs_id', $id);
        return $this->db->update($this->tbl_product_cogs, $data); 
    }

    function delete_product_cogs($id)
    {
        $this->db->where('pdcogs_id', $id);
        return $this->db->delete($this->tbl_product_cogs); 
    }

    function delete_product_cogs_perdate($data)
    {
        $this->db->where('cogs_date >= ', $data['start_date']);
        $this->db->where('cogs_date <= ', $data['end_date']);
        $this->db->where('pd_id', $data['pd_id']);
        return $this->db->delete($this->tbl_product_cogs); 
    }

    //=== END PRODUCT COGS
}
?>

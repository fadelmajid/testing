<?php
defined('BASEPATH') OR EXIT('No direct script access allowed');
/**
*
*/
class Admindb extends MY_Model {
    function __construct()
	{
		parent::__construct();
    }
    
    //=== BEGIN ADMIN
    function get_admin($admin_id)
    {
        $this->db->where( 'admin_id' , $admin_id);
        $this->db->limit(1);
        $query = $this->db->get( $this->tbl_setup_admin );
        return $query->row();
    }
    
    function get_admin_login($username, $password)
    {
        $this->db->where( 'admin_username' , $username);
        $this->db->where( 'admin_password' , $password);
        $this->db->limit(1);
        $query = $this->db->get( $this->tbl_setup_admin );
        return $query->row();
    }
    
    function get_duplicate_username($username, $admin_id)
    {
        $this->db->where( 'admin_username' , $username);
        $this->db->where( 'admin_id != ' , $admin_id);
        $this->db->limit(1);
        $query = $this->db->get( $this->tbl_setup_admin );
        return $query->row();
    }

    function get_duplicate_email($email, $admin_id)
    {
        $this->db->where( 'admin_email' , $email);
        $this->db->where( 'admin_id != ' , $admin_id);
        $this->db->limit(1);
        $query = $this->db->get( $this->tbl_setup_admin );
        return $query->row();
    }
    
    function getall_admin($where="", $data=array(), $order="", $limit=0)
    {
        $orderby    = ($order == "" ? " admin_id ASC " : $order);

        // search dulu row nya.
        $sql        = "SELECT adm.*, role.role_name
                        FROM ".$this->tbl_setup_admin." adm
                        INNER JOIN ".$this->tbl_setup_role." role ON role.role_id = adm.role_id
                        WHERE 1=1 ". $where ." ORDER BY ".$orderby;
        if($limit > 0){
            $data[] = $limit;
            $sql    .= " LIMIT ? ";
        }
        $query      = $this->db->query($sql, $data);
        
        return $query->result();
    }


    function getall_store_barista($where = '', $data = [], $order = '', $limit = 0)
    {
        $orderby = ($order == "" ? " st_id ASC " : $order);

        $sql = "SELECT st_id FROM {$this->tbl_setup_admin} WHERE 1=1 {$where} ORDER BY {$orderby}";

        if($limit > 0){
            $data[] = $limit;
            $sql    .= " LIMIT ? ";
        }

        $query = $this->db->query($sql, $data);

        return $query->result();
    }
    
    function getpaging_admin($where="", $data=array(), $order="", $page=1)
    {
        $page       = ($page <= 0 ? 1 : intval($page));
        $last_row   = $this->row_per_page * $page;
        $first_row  = $last_row - $this->row_per_page;

        $orderby    = ($order == "" ? " admin_id ASC " : $order);

        // search dulu row nya.
        $sql        = "SELECT adm.*, role.role_name
                        FROM ".$this->tbl_setup_admin." adm
                        INNER JOIN ".$this->tbl_setup_role." role ON role.role_id = adm.role_id
                        WHERE 1=1 ". $where ." ORDER BY ".$orderby;

        $query      = $this->db->query($sql, $data);
        $total_row  = $query->num_rows();
        
        $data[] = $this->row_per_page;
        $data[] = intval($first_row > $total_row ? $total_row : $first_row);
        $sql        .= " LIMIT ? OFFSET ? ";
        $query      = $this->db->query($sql, $data);
        //echo $this->db->last_query();
        $result['data']         = $query->result();
        $result['total_row']    = $total_row;
        $result['perpage']      = $this->row_per_page;
        return $result;
    }   
    
    function getarr_admin()
    {
        $arr = array();
        $result = $this->getall_admin();
        foreach($result as $val){
            $arr[ $val->admin_id ] = $val->admin_fullname;
        }
        return $arr;
    }  

    function insert_admin($data)
    {
        $ret = $this->db->insert($this->tbl_setup_admin, $data);
        if($ret){
            return $this->db->insert_id();
        }else{
            return FALSE;
        }
    }
    
    function update_admin($admin_id, $data)
    {
        $this->db->where('admin_id', $admin_id);
        $res = $this->db->update( $this->tbl_setup_admin , $data);
        return $res;
    }
    
    function update_last_login($admin_id)
    {
        $data['admin_lastlogin'] = date('Y-m-d H:i:s');
        $this->db->where('admin_id', $admin_id);
        $res = $this->db->update( $this->tbl_setup_admin , $data); 
        return $res;
    }
    
    function get_admin_matrix($admin_id)
    {
        $this->db->where( 'admin_id' , $admin_id);
        $query = $this->db->get( $this->tbl_setup_admin_menu );
        $result= $query->result();
        
        $arrdata = array();
        if( ! empty($result)){
            foreach($result as $value){
                $arrdata[ $value->submenu_id ] = $value->permits;
            }
        }
        return $arrdata;
    }
    
    
    function insert_admin_menu($admin_id, $matrix)
    {
        $this->db->delete( $this->tbl_setup_admin_menu , array('admin_id' => $admin_id));
        
        //insert all roles menu by role key
        //looping semua checkbox yang ada di form roles
        if( ! empty($matrix)){
            foreach($matrix as $submenu_id=>$permits){
                unset($data_ins);
                $data_ins['admin_id']   = $admin_id;
                $data_ins['submenu_id'] = $submenu_id;
                $data_ins['permits']    = implode(',', $permits);
                $this->db->insert($this->tbl_setup_admin_menu, $data_ins);
            }
        }
    }

    function insert_admin_menu_all_user($role_id)
    {
        //get data setup role menu by role id
        $this->db->where( 'role_id' , $role_id);
        $query      = $this->db->get( $this->tbl_setup_role_menu );
        $matrix     = $query->result();

        //get all admin by role id
        $this->db->select( 'admin_id');
        $this->db->where( 'role_id' , $role_id);
        $query      = $this->db->get( $this->tbl_setup_admin );
        $arr_admin  = $query->result();

        for($a = 0; $a < count($arr_admin); $a++){
            $row_admin = $arr_admin[$a];
            
            $this->db->delete( $this->tbl_setup_admin_menu , array('admin_id' => $row_admin->admin_id));

            if(!empty($matrix)){

                for($b = 0; $b < count($matrix); $b++){
                    $row_matrix = $matrix[$b];
                    unset($data_ins);
                    $data_ins['admin_id']   = $row_admin->admin_id;
                    $data_ins['submenu_id'] = $row_matrix->submenu_id;
                    $data_ins['permits']    = $row_matrix->permits;
                    $this->db->insert($this->tbl_setup_admin_menu, $data_ins);
                }
            }
        }
        return true;
    }

    function insert_admin_menu_one_user_barista($admin_id, $role_id)
    {
        //get data setup role menu by role id
        $this->db->where( 'role_id' , $role_id);
        $query      = $this->db->get( $this->tbl_setup_role_menu );
        $matrix     = $query->result();
         
        $this->db->delete( $this->tbl_setup_admin_menu , array('admin_id' => $admin_id));

        //insert all roles menu by role key
        //looping semua checkbox yang ada di form roles
        if( ! empty($matrix)){
            foreach($matrix as $submenu_id => $row_matrix){
                unset($data_ins);
                $data_ins['admin_id']   = $admin_id;
                $data_ins['submenu_id'] = $row_matrix->submenu_id;
                $data_ins['permits']    = $row_matrix->permits;
                $this->db->insert($this->tbl_setup_admin_menu, $data_ins);
            }
        }
        return true;
    }
    //=== END ADMIN

    //=== FUNCTION UNTUK GENERATE / VALIDATE MENU
    function get_admin_menu($admin_id)
    {
        $this->db->select('menu.menu_id, menu.menu_name, menu.menu_icon, sub.submenu_id, sub.submenu_name, sub.submenu_url,  sub.submenu_target , admenu.permits');
        $this->db->from($this->tbl_setup_admin_menu.' admenu');
        $this->db->join($this->tbl_setup_menu_sub.' sub', 'sub.submenu_id = admenu.submenu_id');
        $this->db->join($this->tbl_setup_menu.' menu', 'menu.menu_id = sub.menu_id');
        $this->db->where('admenu.admin_id', $admin_id);
        $this->db->order_by( 'menu.menu_order' , 'ASC');  
        $this->db->order_by( 'sub.submenu_order' , 'ASC');    
        $query = $this->db->get();
        return $query->result();
    }
    
    function generate_menu($admin_id)
    {
        $list_menu = $this->get_admin_menu($admin_id);
        $no = -1;
        $last_menu = '';
        $str_menu  = '';
        foreach($list_menu as $val){
            
            //create header menu
            if($last_menu != $val->menu_id){
                
                //kalau bukan header yang pertama tutup ul nya.
                if($no > -1 ){
                    $str_menu .= '</ul></li>';
                }
                
                $str_menu .= '<li id="nav_menuid_'. $val->menu_id.'" >';
                $str_menu .= '<a id="anav_menuid_'. $val->menu_id.'" href="#ulnav_menuid_'. $val->menu_id.'"><i class="fa '. $val->menu_icon .'"></i><span>'. $val->menu_name .'</span><span class="fa arrow"></span></a>';
                $str_menu .= '<ul id="ulnav_menuid_'. $val->menu_id.'" class="nav nav-second-level collapse">';
                
                $last_menu = $val->menu_id;
                $no++;
                
            }
            
            
            $str_menu .= '<li><a href="'. ADMIN_URL . $val->submenu_url .'" id="nav_submenuid_'. $val->submenu_id .'_'. $val->menu_id .'" target="'. $val->submenu_target .'"><span>'. $val->submenu_name .'</span></a></li>';
            
        }
        $str_menu .= '</ul></li>';
        
        
        $filemenu = APPPATH.'views/'.ADMIN_MENU_FOLDER .'_menu_'.$admin_id.'.tpl';
        // Write the contents to the file, 
        // using the FILE_APPEND flag to append the content to the end of the file
        // and the LOCK_EX flag to prevent anyone else writing to the file at the same time
        file_put_contents($filemenu, $str_menu);
        
        return TRUE;
    }
    
    
    function get_admin_permits($admin_id, $submenu_id)
    {
        $this->db->select('permits');
        $this->db->where('admin_id', $admin_id);
        $this->db->where('submenu_id', $submenu_id);
        
        
        $query = $this->db->get( $this->tbl_setup_admin_menu );
        $result= $query->row();
        
        
        $ret = array();
        if($result){
            
            //explode berdasarkan koma
            $arr_permit = explode(',' , $result->permits);
            
            foreach($arr_permit as $val){
                //pastikan tidak ada spasi , contoh : "view, add,edit, delete"
                if(trim($val) != '' ){
                    $ret[] = trim($val);
                }
            }
            
        }
        
        return $ret;
    }
    
    //=== END FUNCTION UNTUK GENERATE / VALIDATE MENU
      
    //=== BEGIN MENU
    function get_menu($menu_id)
    {
        $this->db->where( 'menu_id' , $menu_id);
        $this->db->limit(1);
        $query = $this->db->get( $this->tbl_setup_menu );
        return $query->row();
    }
    
    function getall_menu($where="", $data=array(), $order="", $limit=0)
    {
        $orderby    = ($order == "" ? " menu_order ASC " : $order);

        // search dulu row nya.
        $sql        = "SELECT *
                        FROM ".$this->tbl_setup_menu." 
                        WHERE 1=1 ". $where ." ORDER BY ".$orderby;
        if($limit > 0){
            $data[] = $limit;
            $sql    .= " LIMIT ? ";
        }
        $query      = $this->db->query($sql, $data);
        
        return $query->result();
    }
    
    function getpaging_menu($where="", $data=array(), $order="", $page=1)
    {
        $page       = ($page <= 0 ? 1 : intval($page));
        $last_row   = $this->row_per_page * $page;
        $first_row  = $last_row - $this->row_per_page;

        $orderby    = ($order == "" ? " menu_order ASC " : $order);

        // search dulu row nya.
        $sql        = "SELECT menu.*
                        FROM ".$this->tbl_setup_menu." menu
                        WHERE 1=1 ". $where ." ORDER BY ".$orderby;

        $query      = $this->db->query($sql, $data);
        $total_row  = $query->num_rows();
        
        $data[] = $this->row_per_page;
        $data[] = intval($first_row > $total_row ? $total_row : $first_row);
        $sql        .= " LIMIT ? OFFSET ? ";
        $query      = $this->db->query($sql, $data);
        //echo $this->db->last_query();
        $result['data']         = $query->result();
        $result['total_row']    = $total_row;
        $result['perpage']      = $this->row_per_page;


        return $result;
    }
    
    function insert_menu($data)
    {
        $ret = $this->db->insert($this->tbl_setup_menu, $data);
        if($ret){
            return $this->db->insert_id();
        }else{
            return FALSE;
        }
    }
    
    function update_menu($menu_id, $data)
    {
        $this->db->where('menu_id', $menu_id);
        $res = $this->db->update( $this->tbl_setup_menu , $data); 
        return $res;
    }
    
    function sort_menu_order($menu_id, $menu_order){
        $list_menu = $this->getall_menu();
        $no = 1;
        foreach($list_menu as $value){
            if($no == $menu_order && $value->menu_id != $menu_id){
                $no++;
                unset($updata);
                $updata['menu_order'] = $no;
                $this->update_menu($value->menu_id, $updata);
                $no++;
            }elseif($no != $menu_order && $value->menu_id != $menu_id){
                unset($updata);
                $updata['menu_order'] = $no;
                $this->update_menu($value->menu_id, $updata);
                $no++;
            }
        }
    }
    //=== END MENU
    
    
    
    //=== BEGIN SUB MENU
    
    function get_submenu($submenu_id)
    {
        $this->db->select('sub.*, menu.menu_name');
        $this->db->from($this->tbl_setup_menu_sub.' sub');
        $this->db->join($this->tbl_setup_menu.' menu', 'menu.menu_id = sub.menu_id');
        $this->db->where('sub.submenu_id', $submenu_id);
        $this->db->limit('1');    
        $query = $this->db->get();
        return $query->row();
    }
    
    function get_submenu_code($submenu_code)
    {
        $this->db->select('sub.*, menu.menu_name');
        $this->db->from($this->tbl_setup_menu_sub.' sub');
        $this->db->join($this->tbl_setup_menu.' menu', 'menu.menu_id = sub.menu_id');
        $this->db->where('sub.submenu_code', $submenu_code);
        $this->db->limit('1');    
        $query = $this->db->get();
        return $query->row();
    }
    
    function getall_submenu($where="", $data=array(), $order="", $limit=0)
    {
        
        $orderby    = ($order == "" ? " submenu_order ASC " : $order);

        // search dulu row nya.
        $sql        = "SELECT sub.*, menu.menu_name
                        FROM ".$this->tbl_setup_menu_sub." sub
                        INNER JOIN ".$this->tbl_setup_menu." menu ON menu.menu_id = sub.menu_id
                        WHERE 1=1 ". $where ." ORDER BY ".$orderby;
        if($limit > 0){
            $data[] = $limit;
            $sql    .= " LIMIT ? ";
        }
        $query      = $this->db->query($sql, $data);
        
        return $query->result();
    }
    
    function getpaging_submenu($where="", $data=array(), $order="", $page=1)
    {
        $page       = ($page <= 0 ? 1 : intval($page));
        $last_row   = $this->row_per_page * $page;
        $first_row  = $last_row - $this->row_per_page;

        $orderby    = ($order == "" ? " submenu_order ASC " : $order);

        // search dulu row nya.
        $sql        = "SELECT sub.*
                        FROM ".$this->tbl_setup_menu_sub." sub
                        WHERE 1=1 ". $where ." ORDER BY ".$orderby;

        $query      = $this->db->query($sql, $data);
        $total_row  = $query->num_rows();
        
        $data[] = $this->row_per_page;
        $data[] = intval($first_row > $total_row ? $total_row : $first_row);
        $sql        .= " LIMIT ? OFFSET ? ";
        $query      = $this->db->query($sql, $data);
        //echo $this->db->last_query();
        $result['data']         = $query->result();
        $result['total_row']    = $total_row;
        $result['perpage']      = $this->row_per_page;


        return $result;
    }
    
    function insert_submenu($data)
    {
        $ret = $this->db->insert($this->tbl_setup_menu_sub, $data);
        if($ret){
            return $this->db->insert_id();
        }else{
            return FALSE;
        }
    }
    
    
    function update_submenu($submenu_id, $data)
    {
        $this->db->where('submenu_id', $submenu_id);
        $res = $this->db->update( $this->tbl_setup_menu_sub , $data); 
        return $res;
    }
    
    
    function sort_submenu_order($menu_id, $submenu_id, $submenu_order){
        $list_submenu = $this->getall_submenu(' AND sub.menu_id = ? ', array($menu_id) );
        $no = 1;
        foreach($list_submenu as $value){
            if($no == $submenu_order && $value->submenu_id != $submenu_id){
                $no++;
                unset($updata);
                $updata['submenu_order'] = $no;
                $this->update_submenu($value->submenu_id, $updata);
                $no++;
            }elseif($no != $submenu_order && $value->submenu_id != $submenu_id){
                unset($updata);
                $updata['submenu_order'] = $no;
                $this->update_submenu($value->submenu_id, $updata);
                $no++;
            }
        }
    }
    //=== END SUB MENU
       
    //=== BEGIN ROLE
    function get_role($role_id)
    {
        $this->db->where( 'role_id' , $role_id);
        $this->db->limit(1);
        $query = $this->db->get( $this->tbl_setup_role );
        return $query->row();
    }
    
    function getall_role($where="", $data=array(), $order="", $limit=0)
    {
        $orderby    = ($order == "" ? " role_id ASC " : $order);

        // search dulu row nya.
        $sql        = "SELECT *
                        FROM ".$this->tbl_setup_role." role
                        WHERE 1=1 ". $where ." ORDER BY ".$orderby;
        if($limit > 0){
            $data[] = $limit;
            $sql    .= " LIMIT ? ";
        }
        $query      = $this->db->query($sql, $data);
        
        return $query->result();
    }
    
    function getpaging_role($where="", $data=array(), $order="", $page=1)
    {
        $page       = ($page <= 0 ? 1 : intval($page));
        $last_row   = $this->row_per_page * $page;
        $first_row  = $last_row - $this->row_per_page;

        $orderby    = ($order == "" ? " role_id ASC " : $order);

        // search dulu row nya.
        $sql        = "SELECT role.*
                        FROM ".$this->tbl_setup_role." role
                        WHERE 1=1 ". $where ." ORDER BY ".$orderby;

        $query      = $this->db->query($sql, $data);
        $total_row  = $query->num_rows();
        
        $data[] = $this->row_per_page;
        $data[] = intval($first_row > $total_row ? $total_row : $first_row);
        $sql        .= " LIMIT ? OFFSET ? ";
        $query      = $this->db->query($sql, $data);
        //echo $this->db->last_query();
        $result['data']         = $query->result();
        $result['total_row']    = $total_row;
        $result['perpage']      = $this->row_per_page;


        return $result;
    }
    
    function insert_role($data, $matrix)
    {
        $ret = $this->db->insert($this->tbl_setup_role, $data);
        if($ret){
            $role_id = $this->db->insert_id();
            $this->insert_role_menu($role_id, $matrix);
            return $role_id;
        }else{
            return FALSE;
        }
    }
    
    function update_role($role_id, $data, $matrix)
    {
        $this->db->where('role_id', $role_id);
        $res = $this->db->update( $this->tbl_setup_role , $data);
        $this->insert_role_menu($role_id, $matrix);
        
        return $res;
    }
    
    function insert_role_menu($role_id, $matrix)
    {
        $this->db->delete( $this->tbl_setup_role_menu , array('role_id' => $role_id));
        
        //insert all roles menu by role key
        //looping semua checkbox yang ada di form roles
        if( ! empty($matrix)){
            foreach($matrix as $submenu_id=>$permits){
                unset($data_ins);
                $data_ins['role_id']    = $role_id;
                $data_ins['submenu_id'] = $submenu_id;
                $data_ins['permits']    = implode(',',$permits);
                $this->db->insert($this->tbl_setup_role_menu, $data_ins);
                
            }
        }
    }
    
    function get_role_permits($role_id)
    {
        $this->db->where( 'role_id' , $role_id);
        $query = $this->db->get( $this->tbl_setup_role_menu );
        $result= $query->result();
        
        $arrdata = array();
        if( ! empty($result)){
            foreach($result as $value){
                $arrdata[ $value->submenu_id ] = $value->permits;
            }
        }
        return $arrdata;
    }
    //=== END ROLE
    
    //=== BEGIN INVALID_LOGIN
    function get_invalidlogin_bloked($ipaddress)
    {
        $timestart  = date("Y-m-d H:i:s", strtotime('-'.INVALID_LOGIN_TIME.' hours') );
        
        $this->db->select("COUNT(*) total");
        $this->db->where("log_ipaddress", $ipaddress);
        $this->db->where("log_time >= ", $timestart);   
        $query = $this->db->get( $this->tbl_setup_admin_invalidlogin );
        $result= $query->row();
        if($result->total >= INVALID_LOGIN_LIMIT){
            return TRUE;
            //kalau ada salah password 10x return TRUE
        }else{
            return FALSE;
        }
        
    }
    
    function getpaging_invalidlogin($where="", $data=array(), $order="", $page=1)
    {
        $page       = ($page <= 0 ? 1 : intval($page));
        $last_row   = $this->row_per_page * $page;
        $first_row  = $last_row - $this->row_per_page;

        $orderby    = ($order == "" ? " log_id DESC " : $order);

        // search dulu row nya.
        $sql        = "SELECT *
                        FROM ".$this->tbl_setup_admin_invalidlogin."
                        WHERE 1=1 ". $where ." ORDER BY ".$orderby;

        $query      = $this->db->query($sql, $data);
        $total_row  = $query->num_rows();
        
        $data[] = $this->row_per_page;
        $data[] = intval($first_row > $total_row ? $total_row : $first_row);
        $sql        .= " LIMIT ? OFFSET ? ";
        $query      = $this->db->query($sql, $data);
        //echo $this->db->last_query();
        $result['data']         = $query->result();
        $result['total_row']    = $total_row;
        $result['perpage']      = $this->row_per_page;


        return $result;
    }
    
    function insert_invalidlogin($data)
    {
        $ret = $this->db->insert($this->tbl_setup_admin_invalidlogin, $data);
        if($ret){
            return $this->db->insert_id();
        }else{
            return FALSE;
        }
    }
    
    function delete_invalidlogin($log_id)
    {
        return $this->db->delete( $this->tbl_setup_admin_invalidlogin , array('log_id' => $log_id));
    }
    //=== END INVALID LOGIN 
   
}
?>

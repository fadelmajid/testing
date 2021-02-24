<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Setup extends MY_Admin {

    function __construct()
	{
		parent::__construct();
        
	}
    
	public function index()
	{
        show_404();
	}
    
    public function menu()
    {
        // VALIDASI MENU AKSES DAN ASSIGN TITLE
        $submenu_code = 'menu';
        $permits = $this->_check_menu_access( $submenu_code, 'view');//kalau tidak ada permission untuk view langsung redirect & return permits
        $this->_set_title('Setup Menu');
        
        $this->load->library('pagination');
        
        //set variable
        $page   = $this->input->get('page');
        $search = set_var($this->input->get('search'), '');
        $socol     = set_var($this->input->get('sc'), '');
        $soby     = set_var($this->input->get('sb'), 'ASC');//ASC or DESC
        $xtravar['search'] = $search;
        
        //set sortable col
        $allow_sort['id']       = 'menu_id';
        $allow_sort['name']     = 'menu_name';
        $allow_sort['icon']     = 'menu_icon';
        $allow_sort['desc']     = 'menu_desc';
        $allow_sort['order']    = 'menu_order';
        
        //start query
        $url_query    = 'search='.$search.'&sc='.$socol.'&sb='.$soby;
        $search_where = " AND ( menu_id LIKE ? OR menu_name LIKE ? OR menu_desc LIKE ? )";
        $search_data  = array($search.'%', '%'. $search .'%', '%'. $search .'%');
        $search_order = sort_table_order($allow_sort, $socol, $soby);
		$all_data = $this->admindb->getpaging_menu($search_where, $search_data, $search_order, $page);
        
        //start pagination setting
        $config['base_url']             = ADMIN_URL.'setup/menu'.($url_query != '' ? '?'.$url_query : '');
        $config['total_rows']           = $all_data['total_row'];
        $config['per_page']             = $all_data['perpage'];
        $this->pagination->initialize($config);
        //end pagination setting
        
        
        // SELECT DATA & ASSIGN VARIABLE $DATA
        $data['form_url']       = $config['base_url'];
        $data['page_url']       = str_replace($url_query, '', $config['base_url']);
        $data['xtravar']        = $xtravar;
        $data['search']         = $search;
        $data['permits']        = $permits;
        $data['all_data']       = $all_data['data'];
        $data['pagination']     = $this->pagination->create_links();
        
        $this->_render('setup/menu', $data);
    }
    
    
    public function menu_add($id=0)
    {
        // VALIDASI MENU AKSES DAN ASSIGN TITLE
        $submenu_code = 'menu';
        if($id > 0){
            $permits = $this->_check_menu_access( $submenu_code, 'edit');//kalau tidak ada permission untuk edit langsung redirect & return permits
            $this->_set_title('Edit Menu');
        }else{
            $permits = $this->_check_menu_access( $submenu_code, 'add');//kalau tidak ada permission untuk add langsung redirect & return permits
            $this->_set_title('Add Menu');
        }
        // SET FORM TITLE & FORM PERMIT
        $form_permit = $this->_get_form_permit($id, $permits);
        
        //BEGIN VALIDATE ID
        if($id > 0){
            $detail = $this->admindb->get_menu($id);
            if(empty($detail)){ redirect(ADMIN_URL); }
        }
        //END VALIDATE ID
        
        
        $this->load->library('form_validation');
        $this->form_validation->set_error_delimiters(PREFIX_ERROR_DELIMITER, SUFFIX_ERROR_DELIMITER);
        $this->form_validation->set_rules('menu_name', 'Menu Name', 'strip_tags|trim|required');
        $this->form_validation->set_rules('menu_icon', 'Menu Icon', 'strip_tags|trim|required');
        $this->form_validation->set_rules('menu_desc', 'Description', 'strip_tags|trim');
        $this->form_validation->set_rules('menu_order', 'Order No', 'trim|required|integer');
        
        // PROSES DATA & SET VARIABLE MESSAGE
        // JANGAN LUPA VALIDASI APAKAH ADA PERMITS UNTUK EDIT / ADD
        
        $err_msg = array();
        if ($this->form_validation->run() == TRUE){
            
            unset($params);
            $params['menu_name']    = $this->input->post('menu_name');
            $params['menu_icon']    = $this->input->post('menu_icon');
            $params['menu_desc']    = $this->input->post('menu_desc');
            $params['menu_order']   = $this->input->post('menu_order');

            if($id > 0 && in_array('edit', $permits)){
                $ret = $this->admindb->update_menu($id, $params);
                if($ret){
                    $this->admindb->sort_menu_order($id, $params['menu_order']);
                    $err_msg['msg']  = 'Edit Menu Success';
                    $err_msg['type'] = 'success';//success, info, warning, danger
                }else{
                    $err_msg['msg']  = 'Edit Menu Failed!';
                    $err_msg['type'] = 'danger';//success, info, warning, danger
                }

            }else if($id <= 0 && in_array('add', $permits)){
                $ret = $this->admindb->insert_menu($params);
                if($ret){
                    $this->admindb->sort_menu_order($ret, $params['menu_order']);
                    $err_msg['msg']  = 'Add Menu Success'.js_clearform();
                    $err_msg['type'] = 'success';//success, info, warning, danger
                }else{
                    $err_msg['msg']  = 'Add Menu Failed!';
                    $err_msg['type'] = 'danger';//success, info, warning, danger
                }
                
            }else{
                $err_msg['msg']  = 'Access denied - You are not authorized to access this page.';
                $err_msg['type'] = 'danger';//success, info, warning, danger
            }
        }


        // ASSIGN VARIABLE UNTUK VIEW
        $data['msg']            = set_form_msg($err_msg);
        $data['permits']        = $permits;
        $data['show_form']      = $form_permit['show_form'];
        $data['title_form']     = $form_permit['title_form'];
        $data['detail']         = $this->admindb->get_menu($id);


        $this->_render('setup/menu_add', $data);
    }
  
    public function submenu($menu_id=0)
    {
        // VALIDASI MENU AKSES DAN ASSIGN TITLE
        $submenu_code = 'menu';
        $permits = $this->_check_menu_access( $submenu_code, 'view');//kalau tidak ada permission untuk view langsung redirect & return permits
        $this->_set_title('Setup Submenu');
        
        $this->load->library('pagination');
        
        $detail_menu = $this->admindb->get_menu($menu_id);
        
        //set variable
        $page   = $this->input->get('page');
        $search = set_var($this->input->get('search'), '');
        $socol     = set_var($this->input->get('sc'), '');
        $soby     = set_var($this->input->get('sb'), 'ASC');//ASC or DESC
        $xtravar['search'] = $search;
        
        //set sortable col
        $allow_sort['id']       = 'submenu_id';
        $allow_sort['code']     = 'submenu_code';
        $allow_sort['name']     = 'submenu_name';
        $allow_sort['desc']     = 'submenu_desc';
        $allow_sort['order']    = 'submenu_order';
        $allow_sort['permit']   = 'submenu_permits';
        $allow_sort['url']      = 'submenu_url';
        
        //start query
        $url_query    = 'search='.$search.'&sc='.$socol.'&sb='.$soby;
        $search_where = " AND menu_id = ? AND (  submenu_id LIKE ? OR submenu_name LIKE ? OR submenu_code LIKE ? OR submenu_desc LIKE ?
                                OR submenu_permits LIKE ? OR submenu_url LIKE ? )";
        $search_data  = array($menu_id, $search .'%', '%'. $search .'%', '%'. $search .'%', '%'. $search .'%', '%'. $search .'%', '%'. $search .'%');
        $search_order = sort_table_order($allow_sort, $socol, $soby);
		$all_data = $this->admindb->getpaging_submenu($search_where, $search_data, $search_order, $page);
        
        //start pagination setting
        $config['base_url']             = ADMIN_URL.'setup/submenu/'.$menu_id.'/'.($url_query != '' ? '?'.$url_query : '');
        $config['total_rows']           = $all_data['total_row'];
        $config['per_page']             = $all_data['perpage'];
        $this->pagination->initialize($config);
        //end pagination setting
        
        
        // SELECT DATA & ASSIGN VARIABLE $DATA
        $data['form_url']       = $config['base_url'];
        $data['page_url']       = str_replace($url_query, '', $config['base_url']);
        $data['xtravar']        = $xtravar;
        $data['search']         = $search;
        $data['permits']        = $permits;
        $data['all_data']       = $all_data['data'];
        $data['pagination']     = $this->pagination->create_links();
        $data['detail_menu']    = $detail_menu;
        $data['menu_id']        = $menu_id;
        
        $this->_render('setup/submenu', $data);
    }
    
    public function submenu_add($menu_id=0, $id=0)
    {
        // VALIDASI MENU AKSES DAN ASSIGN TITLE
        $submenu_code = 'menu';
        if($id > 0){
            $permits = $this->_check_menu_access( $submenu_code, 'edit');//kalau tidak ada permission untuk edit langsung redirect & return permits
            $this->_set_title('Edit Submenu');
        }else{
            $permits = $this->_check_menu_access( $submenu_code, 'add');//kalau tidak ada permission untuk add langsung redirect & return permits
            $this->_set_title('Add Submenu');
        }
        // SET FORM TITLE & FORM PERMIT
        $form_permit = $this->_get_form_permit($id, $permits);
        
        //BEGIN VALIDATE ID
        if($id > 0){
            $detail = $this->admindb->get_submenu($id);
            if(empty($detail)){ redirect(ADMIN_URL); }
        }
        //END VALIDATE ID
        
        $detail_menu = $this->admindb->get_menu($menu_id);
        //BEGIN VALIDATE ID
        if(empty($detail_menu)){ redirect(ADMIN_URL); }
        //END VALIDATE ID
        $all_menu    = $this->admindb->getall_menu();
        
        $this->load->library('form_validation');
        $this->form_validation->set_error_delimiters(PREFIX_ERROR_DELIMITER, SUFFIX_ERROR_DELIMITER);
        $this->form_validation->set_rules('menu_id', 'Menu', 'trim|required|integer');
        $this->form_validation->set_rules('submenu_code', 'Submenu Code', 'trim|strtolower|alpha_dash|callback__is_unique_submenu_code');
        $this->form_validation->set_rules('submenu_name', 'Submenu Name', 'strip_tags|trim|required');
        $this->form_validation->set_rules('submenu_desc', 'Description', 'strip_tags|trim');
        $this->form_validation->set_rules('submenu_permits', 'Permits', 'strip_tags|trim|required');
        $this->form_validation->set_rules('submenu_order', 'Order', 'trim|required|integer');
        $this->form_validation->set_rules('submenu_url', 'Url', 'strip_tags|trim|required');
        $this->form_validation->set_rules('submenu_target', 'Target', 'strip_tags|trim|required');
        
        
        // PROSES DATA & SET VARIABLE MESSAGE
        // JANGAN LUPA VALIDASI APAKAH ADA PERMITS UNTUK EDIT / ADD
        
        $err_msg = array();
        if ($this->form_validation->run() == TRUE){
            
            unset($params);
            $params['menu_id']          = $this->input->post('menu_id');
            $params['submenu_code']     = $this->input->post('submenu_code');
            $params['submenu_name']     = $this->input->post('submenu_name');
            $params['submenu_desc']     = $this->input->post('submenu_desc');
            $params['submenu_permits']  = $this->input->post('submenu_permits');
            $params['submenu_order']    = $this->input->post('submenu_order');
            $params['submenu_url']      = $this->input->post('submenu_url');
            $params['submenu_target']   = $this->input->post('submenu_target');

            if($id > 0 && in_array('edit', $permits)){
                $ret = $this->admindb->update_submenu($id, $params);
                if($ret){
                    $this->admindb->sort_submenu_order($params['menu_id'], $id, $params['submenu_order']);
                    $err_msg['msg']  = 'Edit Submenu Success';
                    $err_msg['type'] = 'success';//success, info, warning, danger
                }else{
                    $err_msg['msg']  = 'Edit Submenu Failed!';
                    $err_msg['type'] = 'danger';//success, info, warning, danger
                }

            }else if($id <= 0 && in_array('add', $permits)){
                $ret = $this->admindb->insert_submenu($params);
                if($ret){
                    $this->admindb->sort_submenu_order($params['menu_id'], $ret, $params['submenu_order']);
                    $err_msg['msg']  = 'Add Submenu Success'.js_clearform();
                    $err_msg['type'] = 'success';//success, info, warning, danger
                }else{
                    $err_msg['msg']  = 'Add Submenu Failed!';
                    $err_msg['type'] = 'danger';//success, info, warning, danger
                }
                
            }else{
                $err_msg['msg']  = 'Access denied - You are not authorized to access this page.';
                $err_msg['type'] = 'danger';//success, info, warning, danger
            }
        }


        // ASSIGN VARIABLE UNTUK VIEW
        $data['msg']            = set_form_msg($err_msg);
        $data['permits']        = $permits;
        $data['show_form']      = $form_permit['show_form'];
        $data['title_form']     = $form_permit['title_form'];
        $data['detail']         = $this->admindb->get_submenu($id);
        $data['menu_id']        = $menu_id;
        $data['detail_menu']    = $detail_menu;
        $data['all_menu']       = $all_menu;

        $this->_render('setup/submenu_add', $data);
    }
    
    public function roles()
    {
        // VALIDASI MENU AKSES DAN ASSIGN TITLE
        $submenu_code = 'role';
        $permits = $this->_check_menu_access( $submenu_code, 'view');//kalau tidak ada permission untuk view langsung redirect & return permits
        $this->_set_title('Setup Roles');
        
        $this->load->library('pagination');
        
        //set variable
        $page   = $this->input->get('page');
        $search = set_var($this->input->get('search'), '');
        $socol     = set_var($this->input->get('sc'), 'id');
        $soby     = set_var($this->input->get('sb'), 'ASC');//ASC or DESC
        $xtravar['search'] = $search;
        
        //set sortable col
        $allow_sort['id']      = 'role_id';
        $allow_sort['name']    = 'role_name';
        $allow_sort['desc']    = 'role_desc';
        
        //start query
        $url_query    = 'search='.$search.'&sc='.$socol.'&sb='.$soby;
        $search_where = " AND ( role_id LIKE ? OR role_name LIKE ? OR role_desc LIKE ? )";
        $search_data  = array($search.'%', '%'. $search .'%', '%'. $search .'%');
        $search_order = sort_table_order($allow_sort, $socol, $soby);
		$all_data = $this->admindb->getpaging_role($search_where, $search_data, $search_order, $page);
        
        //start pagination setting
        $config['base_url']             = ADMIN_URL.'setup/roles'.($url_query != '' ? '?'.$url_query : '');
        $config['total_rows']           = $all_data['total_row'];
        $config['per_page']             = $all_data['perpage'];
        $this->pagination->initialize($config);
        //end pagination setting
        
        
        // SELECT DATA & ASSIGN VARIABLE $DATA
        $data['form_url']       = $config['base_url'];
        $data['page_url']       = str_replace($url_query, '', $config['base_url']);
        $data['xtravar']        = $xtravar;
        $data['search']         = $search;
        $data['permits']        = $permits;
        $data['all_data']       = $all_data['data'];
        $data['pagination']     = $this->pagination->create_links();
        
        $this->_render('setup/roles', $data);
    }
    
    public function roles_add($id=0)
    {
        // VALIDASI MENU AKSES DAN ASSIGN TITLE
        $submenu_code = 'role';
        if($id > 0){
            $permits = $this->_check_menu_access( $submenu_code, 'edit');//kalau tidak ada permission untuk edit langsung redirect & return permits
            $this->_set_title('Edit Role');
        }else{
            $permits = $this->_check_menu_access( $submenu_code, 'add');//kalau tidak ada permission untuk add langsung redirect & return permits
            $this->_set_title('Add Role');
        }
        // SET FORM TITLE & FORM PERMIT
        $form_permit = $this->_get_form_permit($id, $permits);
        
        //BEGIN VALIDATE ID
        if($id > 0){
            $detail = $this->admindb->get_role($id);
            if(empty($detail)){ redirect(ADMIN_URL); }
        }
        //END VALIDATE ID
        
        $this->load->library('form_validation');
        $this->form_validation->set_error_delimiters(PREFIX_ERROR_DELIMITER, SUFFIX_ERROR_DELIMITER);
        $this->form_validation->set_rules('role_name', 'Role Name', 'strip_tags|trim|required');
        $this->form_validation->set_rules('role_desc', 'Description', 'strip_tags|trim');
        
        
        // PROSES DATA & SET VARIABLE MESSAGE
        // JANGAN LUPA VALIDASI APAKAH ADA PERMITS UNTUK EDIT / ADD
        
        $err_msg = array();
        if ($this->form_validation->run() == TRUE){
            
            unset($params);
            $params['role_name']      = $this->input->post('role_name');
            $params['role_desc']      = $this->input->post('role_desc');
            $matrix_permits = $this->input->post('matrix_permits');

            if($id > 0 && in_array('edit', $permits)){
                $ret = $this->admindb->update_role($id, $params, $matrix_permits);
                if($ret){
                    $err_msg['msg']  = 'Edit Role Success';
                    $err_msg['type'] = 'success';//success, info, warning, danger
                }else{
                    $err_msg['msg']  = 'Edit Role Failed!';
                    $err_msg['type'] = 'danger';//success, info, warning, danger
                }

            }else if($id <= 0 && in_array('add', $permits)){
                $ret = $this->admindb->insert_role($params, $matrix_permits);
                if($ret){
                    $err_msg['msg']  = 'Add Role Success'.js_clearform();
                    $err_msg['type'] = 'success';//success, info, warning, danger
                }else{
                    $err_msg['msg']  = 'Add Role Failed!';
                    $err_msg['type'] = 'danger';//success, info, warning, danger
                }
                
            }else{
                $err_msg['msg']  = 'Access denied - You are not authorized to access this page.';
                $err_msg['type'] = 'danger';//success, info, warning, danger
            }
        }


        // CREATE TABLE MENU & SUBMENU
        $list_menu = $this->admindb->getall_menu();
        $no=0;
        foreach($list_menu as $value){
            $submenu = $this->admindb->getall_submenu(' AND sub.menu_id = ? ', array($value->menu_id) );
            //kalau ada sub menu baru di munculin.
            if(! empty($submenu)){
                $list_matrix[$no]['menu_name'] = $value->menu_name;
                $list_matrix[$no]['submenu']   = $submenu;
                $no++;
            }
        }
        


        // ASSIGN VARIABLE UNTUK VIEW
        $data['msg']            = set_form_msg($err_msg);
        $data['permits']        = $permits;
        $data['show_form']      = $form_permit['show_form'];
        $data['title_form']     = $form_permit['title_form'];
        $data['detail']         = $this->admindb->get_role($id);
        $data['list_matrix']    = $list_matrix;
        $data['roles_menu']     = $this->admindb->get_role_permits($id);


        $this->_render('setup/roles_add', $data);
    }
    
    public function admins()
    {
        // VALIDASI MENU AKSES DAN ASSIGN TITLE
        $submenu_code = 'admin';
        $permits = $this->_check_menu_access( $submenu_code, 'view');//kalau tidak ada permission untuk view langsung redirect & return permits
        $this->_set_title('Setup Admins');
        
        $this->load->library('pagination');
        $this->load->model("storedb");
        
        //set variable
        $page               = $this->input->get('page');
        $search             = set_var($this->input->get('search'), '');
        $socol              = set_var($this->input->get('sc'), 'id');
        $soby               = set_var($this->input->get('sb'), 'ASC');//ASC or DESC
        $xtravar['search']  = $search;
        $barista            = $this->config->item('setup_admin')['role']['barista'];
        
        //set sortable col
        $allow_sort['id']       = 'admin_id';
        $allow_sort['role']     = 'role_name';
        $allow_sort['user']     = 'admin_username';
        $allow_sort['name']     = 'admin_fullname';
        $allow_sort['email']    = 'admin_email';
        $allow_sort['store']    = 'st_id';

        //start query
        $url_query    = 'search='.$search.'&sc='.$socol.'&sb='.$soby;
        $search_where = " AND ( admin_id LIKE ? OR role_name LIKE ? OR admin_username LIKE ?
                                 OR admin_fullname LIKE ? OR admin_email LIKE ?) AND adm.role_id != ?";
        $search_data  = array($search.'%', '%'. $search .'%', '%'. $search .'%', '%'. $search .'%', '%'. $search .'%', $barista);
        $search_order = sort_table_order($allow_sort, $socol, $soby);
		$all_data = $this->admindb->getpaging_admin($search_where, $search_data, $search_order, $page);
        
        //start pagination setting
        $config['base_url']             = ADMIN_URL.'setup/admins'.($url_query != '' ? '?'.$url_query : '');
        $config['total_rows']           = $all_data['total_row'];
        $config['per_page']             = $all_data['perpage'];
        $this->pagination->initialize($config);
        //end pagination setting

        $all_store = $this->storedb->getarr_store();
        
        // SELECT DATA & ASSIGN VARIABLE $DATA
        $data['form_url']       = $config['base_url'];
        $data['page_url']       = str_replace($url_query, '', $config['base_url']);
        $data['xtravar']        = $xtravar;
        $data['search']         = $search;
        $data['permits']        = $permits;
        $data['all_store']      = $all_store;
        $data['all_data']       = $all_data['data'];
        $data['pagination']     = $this->pagination->create_links();
        
        $this->_render('setup/admins', $data);
    }
    
    public function admins_add($id=0)
    {
        // VALIDASI MENU AKSES DAN ASSIGN TITLE
        $submenu_code = 'admin';
        if($id > 0){
            $permits = $this->_check_menu_access( $submenu_code, 'edit');//kalau tidak ada permission untuk edit langsung redirect & return permits
            $this->_set_title('Edit Admin');
        }else{
            $permits = $this->_check_menu_access( $submenu_code, 'add');//kalau tidak ada permission untuk add langsung redirect & return permits
            $this->_set_title('Add Admin');
        }
        // SET FORM TITLE & FORM PERMIT
        $form_permit = $this->_get_form_permit($id, $permits);
        
        //BEGIN VALIDATE ID
        if($id > 0){
            $detail = $this->admindb->get_admin($id);
            if(empty($detail)){ redirect(ADMIN_URL); }
        }
        //END VALIDATE ID
        
        $this->load->model('storedb');
        $this->load->library('form_validation');
        $this->form_validation->set_error_delimiters(PREFIX_ERROR_DELIMITER, SUFFIX_ERROR_DELIMITER);
        $this->form_validation->set_rules('admin_id', 'ID', 'strip_tags|trim');
        $this->form_validation->set_rules('role_id', 'Role', 'trim|required|integer');
        $this->form_validation->set_rules('admin_username', 'Username', 'strip_tags|trim|required|callback__username_check');
        $this->form_validation->set_rules('admin_fullname', 'Fullname', 'strip_tags|trim|required');
        $this->form_validation->set_rules('admin_email', 'Email', 'strip_tags|trim|required|valid_email|callback__email_check');
        $this->form_validation->set_rules('admin_password', 'Password', 'trim|min_length[4]|max_length[32]');
        $this->form_validation->set_rules('admin_allowlogin', 'Allow Login', 'trim|required');
        $this->form_validation->set_rules('st_id', 'Store Permits', 'trim|required|callback__valid_store');
        
        $all_roles      = $this->admindb->getall_role();
        $arr_admin      = $this->admindb->getarr_admin();
        
        // PROSES DATA & SET VARIABLE MESSAGE
        // JANGAN LUPA VALIDASI APAKAH ADA PERMITS UNTUK EDIT / ADD
         
        $err_msg = array();
        if ($this->form_validation->run() == TRUE){
            
            unset($params);
            $params['role_id']          = $this->input->post('role_id');
            $params['st_id']            = $this->input->post('st_id');
            $params['admin_username']   = $this->input->post('admin_username');
            $params['admin_fullname']   = $this->input->post('admin_fullname');
            $params['admin_email']      = $this->input->post('admin_email');
            $params['admin_allowlogin'] = $this->input->post('admin_allowlogin');
            $params['created_by']       = $this->_get_user_id();
            $params['created_date']     = date('Y-m-d H:i:s');
            
            if($this->input->post('admin_password') != ''){
                $params['admin_password']   = $this->_salt_passwd($this->input->post('admin_password'));
            }
            $matrix_permits = $this->input->post('matrix_permits');

            if($id > 0 && in_array('edit', $permits)){
                $ret = $this->admindb->update_admin($id, $params);
                if($ret){
                    $this->admindb->insert_admin_menu($id, $matrix_permits);
                    $err_msg['msg']  = 'Edit Admin Success';
                    $err_msg['type'] = 'success';//success, info, warning, danger
                }else{
                    $err_msg['msg']  = 'Edit Admin Failed!';
                    $err_msg['type'] = 'danger';//success, info, warning, danger
                }
            }else if($id <= 0 && in_array('add', $permits)){
                $ret = $this->admindb->insert_admin($params);
                if($ret){
                    $this->admindb->insert_admin_menu($ret, $matrix_permits);
                    $err_msg['msg']  = 'Add Admin Success'.js_clearform();
                    $err_msg['type'] = 'success';//success, info, warning, danger
                }else{
                    $err_msg['msg']  = 'Add Admin Failed!';
                    $err_msg['type'] = 'danger';//success, info, warning, danger
                }
            }else{
                $err_msg['msg']  = 'Access denied - You are not authorized to access this page.';
                $err_msg['type'] = 'danger';//success, info, warning, danger
            }
        }

        // CREATE TABLE MENU & SUBMENU
        $list_menu = $this->admindb->getall_menu();
        $no=0;
        foreach($list_menu as $value){
            $submenu = $this->admindb->getall_submenu(' AND sub.menu_id = ? ', array($value->menu_id) );
            //kalau ada sub menu baru di munculin.
            if(! empty($submenu)){
                $list_matrix[$no]['menu_name'] = $value->menu_name;
                $list_matrix[$no]['submenu']   = $submenu;
                $no++;
            }
        }
        $all_store = $this->storedb->getall();

        // ASSIGN VARIABLE UNTUK VIEW
        $data['msg']            = set_form_msg($err_msg);
        $data['permits']        = $permits;
        $data['show_form']      = $form_permit['show_form'];
        $data['title_form']     = $form_permit['title_form'];
        $data['detail']         = $this->admindb->get_admin($id);
        $data['all_roles']      = $all_roles;
        $data['arr_admin']      = $arr_admin;
        $data['list_matrix']    = $list_matrix;
        $data['admin_menu']     = $this->admindb->get_admin_matrix($id);
        $data['store_data']     = $all_store;
        
        $this->_render('setup/admins_add', $data);
    }
    
    
    public function invalid_login()
    {
        // VALIDASI MENU AKSES DAN ASSIGN TITLE
        $submenu_code = 'invalid_login';
        $permits = $this->_check_menu_access( $submenu_code, 'view');//kalau tidak ada permission untuk view langsung redirect & return permits
        $this->_set_title('Log Invalid Login');
        
        $this->load->library('pagination');
        
        //set variable
        $page   = $this->input->get('page');
        $search = set_var($this->input->get('search'), '');
        $socol  = set_var($this->input->get('sc'), 'id');
        $soby   = set_var($this->input->get('sb'), 'DESC');//ASC or DESC
        $xtravar['search'] = $search;
        
        //set sortable col
        $allow_sort['id']       = 'log_id';
        $allow_sort['name']     = 'log_username';
        $allow_sort['pass']     = 'log_password';
        $allow_sort['ipaddress']= 'log_ipaddress';
        $allow_sort['time']     = 'log_time';
        
        //start query
        $url_query    = 'search='.$search.'&sc='.$socol.'&sb='.$soby;
        $search_where = " AND ( log_id LIKE ? OR log_username LIKE ? OR log_password LIKE ? OR log_ipaddress LIKE ? )";
        $search_data  = array($search.'%', '%'. $search .'%', '%'. $search .'%', $search .'%');
        $search_order = sort_table_order($allow_sort, $socol, $soby);
		$all_data = $this->admindb->getpaging_invalidlogin($search_where, $search_data, $search_order, $page);
        
        //start pagination setting
        $config['base_url']             = ADMIN_URL.'setup/invalid_login'.($url_query != '' ? '?'.$url_query : '');
        $config['total_rows']           = $all_data['total_row'];
        $config['per_page']             = $all_data['perpage'];
        $this->pagination->initialize($config);
        //end pagination setting
        
        
        // SELECT DATA & ASSIGN VARIABLE $DATA
        $data['form_url']       = $config['base_url'];
        $data['page_url']       = str_replace($url_query, '', $config['base_url']);
        $data['xtravar']        = $xtravar;
        $data['search']         = $search;
        $data['permits']        = $permits;
        $data['all_data']       = $all_data['data'];
        $data['pagination']     = $this->pagination->create_links();
        
        $this->_render('setup/invalid_login', $data);
    }
    
    
    public function ajax_get_role_matrix($id=0)
    {
        
        $aJson  = $this->admindb->get_role_permits($id);
        echo json_encode($aJson);
        
    }
  
    public function _username_check($username)
    {
        $admin_id = set_var($this->input->post('admin_id'), '0');
        
        $detail   = $this->admindb->get_duplicate_username($username, $admin_id);
        if(empty($detail)){
            return TRUE;
        }else{
            $this->form_validation->set_message('_username_check', 'Sorry, that username is already taken');
            return FALSE;
        }
    }
    
    public function _email_check($email)
    {
        $admin_id = set_var($this->input->post('admin_id'), '0');
        
        $detail   = $this->admindb->get_duplicate_email($email, $admin_id);
        if(empty($detail)){
            return TRUE;
        }else{
            $this->form_validation->set_message('_email_check', 'Sorry, that email is already exist');
            return FALSE;
        }
    }
    
	public function _is_unique_submenu_code($submenu_code)
	{
		$submenu_id = set_var($this->input->post('submenu_id'), '0');
		
		$detail_submenu = $this->admindb->get_submenu_code($submenu_code);
		if(empty($detail_submenu)){
            return TRUE;
        }else{
			if($submenu_id == $detail_submenu->submenu_id){
				return TRUE;
			}else{
				$this->form_validation->set_message('_is_unique_submenu_code', 'Sorry, that code is already exist');
				return FALSE;	
			}
        }
    }

    public function _valid_store($st_id) 
    {

        $this->load->model('storedb');
        
        //validate store id
        $st_id = intval($this->input->post('st_id'), 0);
        if($st_id > 0) {
            if(empty($this->storedb->get($st_id))) {
                $this->form_validation->set_message('st_id', 'Invalid Store ID.');
                return FALSE;
            }
        }

        return TRUE;
    }


    //>>START SETUP BARISTA<<

    public function barista()
    {
        // VALIDASI MENU AKSES DAN ASSIGN TITLE
        $submenu_code = 'setup_barista';
        $permits      = $this->_check_menu_access( $submenu_code, 'view');//kalau tidak ada permission untuk view langsung redirect & return permits
        $this->_set_title('Setup Barista');
        
        $this->load->library('pagination');
        $this->load->model("storedb");
        $this->load->model("admindb");
        
        //set variable
        $page              = $this->input->get('page');
        $search            = set_var($this->input->get('search'), '');
        $st_id             = intval($this->input->get('st_id'));
        $socol             = set_var($this->input->get('sc'), 'id');
        $soby              = set_var($this->input->get('sb'), 'ASC');//ASC or DESC
        $xtravar['search'] = $search;
        $xtravar['st_id']  = $st_id;
        $role_id           = $this->config->item('setup_admin')['role']['barista'];
        
        //set sortable col
        $allow_sort['id']       = 'admin_id';
        $allow_sort['user']     = 'admin_username';
        $allow_sort['name']     = 'admin_fullname';
        $allow_sort['email']    = 'admin_email';
        $allow_sort['store']    = 'st_id';

        //start query
        $url_query    = 'search='.$search.'&sc='.$socol.'&sb='.$soby.'&st_id='.$st_id;
        $search_where = " AND ( admin_id LIKE ? OR admin_username LIKE ?
                        OR admin_fullname LIKE ? OR admin_email LIKE ? ) AND adm.role_id = ?";
        $search_data  = array($search.'%', '%'. $search .'%', '%'. $search .'%', '%'. $search .'%', $role_id);

        $user = $this->admindb->get_admin($this->_get_user_id());
        $store_permits = 0;
        if($user->st_id > 0) {
            $store_permits = $user->st_id;
            $search_where .= " AND adm.st_id = ? ";
            array_push($search_data, $user->st_id);
        }else {
            if($st_id > 0) {
                $search_where .= " AND adm.st_id = ? ";
                array_push($search_data, $st_id);
            }
        }

        $search_order = sort_table_order($allow_sort, $socol, $soby);
		$all_data     = $this->admindb->getpaging_admin(
                            $search_where,
                            $search_data,
                            $search_order,
                            $page
                        );

        //start pagination setting
        $config['base_url']     = ADMIN_URL.'setup/barista'.($url_query != '' ? '?'.$url_query : '');
        $config['total_rows']   = $all_data['total_row'];
        $config['per_page']     = $all_data['perpage'];
        $this->pagination->initialize($config);
        //end pagination setting

        $all_store = $this->storedb->getarr_store();
        $store     = $this->storedb->getall();

        // SELECT DATA & ASSIGN VARIABLE $DATA
        $data['form_url']       = $config['base_url'];
        $data['page_url']       = str_replace($url_query, '', $config['base_url']);
        $data['xtravar']        = $xtravar;
        $data['search']         = $search;
        $data['permits']        = $permits;
        $data['all_store']      = $all_store;
        $data['store_data']     = $store;
        $data['store_permits']  = $store_permits;
        $data['st_id']          = $st_id;
        $data['all_data']       = $all_data['data'];
        $data['pagination']     = $this->pagination->create_links();

        $this->_render('setup/barista', $data);
    }
    
    public function barista_add($id=0)
    {
        // VALIDASI MENU AKSES DAN ASSIGN TITLE
        $submenu_code = 'setup_barista';
        if($id > 0){
            //kalau tidak ada permission untuk edit langsung redirect & return permits
            $permits = $this->_check_menu_access( $submenu_code, 'edit');
            $this->_set_title('Edit Barista');
        }else{
            //kalau tidak ada permission untuk add langsung redirect & return permits
            $permits = $this->_check_menu_access( $submenu_code, 'add');
            $this->_set_title('Add Barista');
        }
        // SET FORM TITLE & FORM PERMIT
        $form_permit = $this->_get_form_permit($id, $permits);
        
        //BEGIN VALIDATE ID
        if($id > 0){
            $detail = $this->admindb->get_admin($id);
            if(empty($detail)){
                redirect(ADMIN_URL);
            }
        }
        //END VALIDATE ID
        
        $this->load->model('storedb');
        $this->load->library('form_validation');
        $this->form_validation->set_error_delimiters(PREFIX_ERROR_DELIMITER, SUFFIX_ERROR_DELIMITER);
        $this->form_validation->set_rules('barista_id', 'ID', 'strip_tags|trim');
        $this->form_validation->set_rules('barista_username', 'Username', 'strip_tags|trim|required|callback__username_check');
        $this->form_validation->set_rules('barista_fullname', 'Fullname', 'strip_tags|trim|required');
        $this->form_validation->set_rules('barista_email', 'Email', 'strip_tags|trim|required|valid_email|callback__email_check');
        $this->form_validation->set_rules('barista_password', 'Password', 'trim|min_length[4]|max_length[50]');
        $this->form_validation->set_rules('barista_allowlogin', 'Allow Login', 'trim|required');
        $this->form_validation->set_rules('st_id', 'Store Permits', 'trim|required|callback__valid_store');

        $arr_barista    = $this->admindb->getarr_admin();
        
        // PROSES DATA & SET VARIABLE MESSAGE
        // JANGAN LUPA VALIDASI APAKAH ADA PERMITS UNTUK EDIT / ADD
        $role_id        = $this->config->item('setup_admin')['role']['barista'];
        $detail         = $this->admindb->get_role($role_id);

        $err_msg = array();
        if ($this->form_validation->run() == TRUE){
            
            unset($params);
            $params['st_id']              = $this->input->post('st_id');
            $params['role_id']            = $role_id;
            $params['admin_username']     = $this->input->post('barista_username');
            $params['admin_fullname']     = $this->input->post('barista_fullname');
            $params['admin_email']        = $this->input->post('barista_email');
            $params['admin_allowlogin']   = $this->input->post('barista_allowlogin');
            $params['created_by']         = $this->_get_user_id();
            $params['created_date']       = date('Y-m-d H:i:s');

            if($this->input->post('barista_password') != ''){
                $params['admin_password']   = $this->_salt_passwd($this->input->post('barista_password'));
            }

            if($id > 0 && in_array('edit', $permits)){
                $ret = $this->admindb->update_admin($id, $params);
                if($ret){
                    $this->admindb->insert_admin_menu_one_user_barista($id, $role_id);
                    $err_msg['msg']  = 'Edit Barista Success';
                    $err_msg['type'] = 'success';//success, info, warning, danger
                }else{
                    $err_msg['msg']  = 'Edit Barista Failed!';
                    $err_msg['type'] = 'danger';//success, info, warning, danger
                }
            }else if($id <= 0 && in_array('add', $permits)){
                $ret = $this->admindb->insert_admin($params);
                if($ret){
                    $this->admindb->insert_admin_menu_one_user_barista($ret, $role_id);
                    $err_msg['msg']  = 'Add Barista Success'.js_clearform();
                    $err_msg['type'] = 'success';//success, info, warning, danger
                }else{
                    $err_msg['msg']  = 'Add Barista Failed!';
                    $err_msg['type'] = 'danger';//success, info, warning, danger
                }
            }else{
                $err_msg['msg']  = 'Access denied - You are not authorized to access this page.';
                $err_msg['type'] = 'danger';//success, info, warning, danger
            }
        }

        //CREATE TABLE MENU & SUBMENU
        $list_menu = $this->admindb->getall_menu();
        $no=0;
        foreach($list_menu as $value){
            $submenu = $this->admindb->getall_submenu(' AND sub.menu_id = ? ', array($value->menu_id) );
            //kalau ada sub menu baru di munculin.
            if(! empty($submenu)){
                $list_matrix[$no]['menu_name'] = $value->menu_name;
                $list_matrix[$no]['submenu']   = $submenu;
                $no++;
            }
        }

        $all_store = $this->storedb->getall();

        // ASSIGN VARIABLE UNTUK VIEW
        $data['msg']            = set_form_msg($err_msg);
        $data['permits']        = $permits;
        $data['show_form']      = $form_permit['show_form'];
        $data['title_form']     = $form_permit['title_form'];
        $data['detail']         = $this->admindb->get_admin($id);
        $data['arr_barista']    = $arr_barista;
        $data['store_data']     = $all_store;
        
        $this->_render('setup/barista_add', $data);
    }
    
    //>>END SETUP BARISTA<<
}

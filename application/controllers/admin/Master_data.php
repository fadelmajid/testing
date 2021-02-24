<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Master_data extends MY_Admin
{
    function __construct()
    {
        parent::__construct();
    }
    
    public function index()
    {
        show_404();
    }

    //=== START CATEGORY
    public function category()
    {
        $this->load->model('productdb');
        // validasi menu dan assign title
        $submenu_code = 'category';
        $permits = $this->_check_menu_access($submenu_code, 'view');

        $this->_set_title('Categories');
        $this->load->library('pagination');
        
        //set variable
        $current_path = 'master_data/category';
        $current_url = ADMIN_URL.$current_path;
        $page = $this->input->get('page');
        $search = set_var($this->input->get('search'), '');
        $sort_col = set_var($this->input->get('sc'), 'order');
        $sort_by = set_var($this->input->get('sb'), 'ASC');//ASC or DESC
        $xtra_var['search'] = $search;

        //set sortable col
        $allow_sort = [
            'id' => 'cat_id',
            'name' => 'cat_name',
            'order' => 'cat_order'
        ];
        
        //start query
        $url_query = "search={$search}&sc={$sort_col}&sb={$sort_by}";
        $search_where = " AND (cat_id LIKE ? OR cat_name LIKE ?) ";
        $search_data = [$search.'%', "%{$search}%", ];
        $search_order = sort_table_order($allow_sort, $sort_col, $sort_by);
        $all_data = $this->productdb->getpaging_category(
            $search_where,
            $search_data,
            $search_order,
            $page
        );
        
        // start pagination setting
        $config = [
            'base_url' => $current_url.($url_query != '' ? '?'.$url_query : ''),
            'total_rows' => $all_data['total_row'],
            'per_page' => $all_data['per_page']
        ];

        $this->pagination->initialize($config);
        // end pagination setting

        // select data & assign variable $data
        $data = [
            'current_url' => $current_url,
            'form_url' => $config['base_url'],
            'page_url' => str_replace($url_query, '', $config['base_url']),
            'xtra_var' => $xtra_var,
            'search' => $search,
            'permits' => $permits,
            'all_data' => $all_data['data'],
            'pagination' => $this->pagination->create_links()
        ];
        $this->_render($current_path, $data);
    }
    
    public function category_add($id = 0)
    {
        $this->load->model('productdb');
        // validate user roles
        $submenu_code = 'category';
        if ($id > 0) {
            $permits = $this->_check_menu_access($submenu_code, 'edit');
            $this->_set_title('Edit Category');
        } else {
            $permits = $this->_check_menu_access($submenu_code, 'add');
            $this->_set_title('Add Category');
        }
        $form_permit = $this->_get_form_permit($id, $permits);
        
        // validate if category exists
        if ($id > 0) {
            $category = $this->productdb->get_category($id);
            if (empty($category)) {
                redirect(ADMIN_URL);
            }
        }
        
        $this->load->library('form_validation');
        $this->form_validation->set_error_delimiters(PREFIX_ERROR_DELIMITER, SUFFIX_ERROR_DELIMITER);
        $this->form_validation->set_rules('cat_name', 'Category Name', 'strip_tags|trim|required|callback__is_unique_category_name');
        $this->form_validation->set_rules('cat_order', 'Category Order', 'strip_tags|trim|integer');
        $this->form_validation->set_rules('cat_img', 'Category Image', 'callback__check_upload_image_cat_img');
        
        $err_msg = [];
        if ($this->form_validation->run()) {
            //BEGIN UPLOAD IMAGE
            $image_name = '';
            if (is_uploaded_file($_FILES['cat_img']['tmp_name'])) {

                $this->load->library("google_cloud_bucket");
                $image_name = str_replace(UPLOAD_PATH, '', CATEGORY_IMAGE_PATH).$_FILES['cat_img']['name'];

                if(!@fopen(UPLOAD_URL.$image_name, 'r')){
                    $data = [
                        "source" => $_FILES['cat_img']['tmp_name'],
                        "name"   => $image_name
                    ];
                    
                    $this->google_cloud_bucket->upload_image($data);
                }
            }
            //END UPLOAD IMAGE

            $params = [
                'cat_name' => $this->input->post('cat_name'),
                'cat_order' => set_var($this->input->post('cat_order'), 1)
            ];

            if($image_name != ''){
                $params['cat_img']  = $image_name;
            }

            if ($id > 0 && in_array('edit', $permits)) { 
                $params['updated_by'] = $this->_get_user_id();
                $params['updated_date'] = date('Y-m-d H:i:s');

                if ($this->productdb->update_category($id, $params)) {
                    $this->productdb->sort_category_order($id, $params['cat_order']);
                    $err_msg = [
                        'msg' => 'Edit Category Success',
                        'type' => 'success'
                    ];
                } else {
                    $err_msg = [
                        'msg' => 'Edit Category Failed!',
                        'type' => 'danger'
                    ];
                }
            } else if ($id <= 0 && in_array('add', $permits)) {
                $params['created_by'] = $this->_get_user_id();
                $params['created_date'] = date('Y-m-d H:i:s');

                $cat_id = $this->productdb->insert_category($params);
                if ($cat_id) {
                    $this->productdb->sort_category_order($cat_id, $params['cat_order']);
                    $err_msg = [
                        'msg' => 'Add Category Success.'.js_clearform(),
                        'type' => 'success'
                    ];
                } else {
                    $err_msg = [
                        'msg' => 'Add Category Failed!',
                        'type' => 'danger'
                    ];
                }
            } else {
                $err_msg = [
                    'msg' => 'Access denied - You are not authorized to access this page.',
                    'type' => 'danger'
                ];
            }
        }

        $current_path = 'master_data/category_add';
        $data = [
            'current_url' => ADMIN_URL.$current_path,
            'msg' => set_form_msg($err_msg),
            'permits' => $permits,
            'show_form' => $form_permit['show_form'],
            'title_form' => $form_permit['title_form'],
            'category' => $this->productdb->get_category($id)
        ];
        $this->_render($current_path, $data);
    }

    public function _is_unique_category_name($category_name)
    {
        $this->load->model('productdb');
        $id = $this->input->post('cat_id');
        // if duplicate name found and not belong to selected ID, return false
        $category = $this->productdb->get_category_by_name($category_name);
        if ($category && $category->cat_id !== $id) {
            $this->form_validation->set_message('_is_unique_category_name', 'The Category Name field must contain a unique value.');
            return false;
        }
        return true;
    } 

    public function _check_upload_image_cat_img()
    {
        $id = $this->input->post('cat_id');

        $allowed_mime_type_arr  = array('image/gif','image/jpeg','image/pjpeg','image/png','image/x-png');
        $mime                   = get_mime_by_extension($_FILES['cat_img']['name']);

        if($_FILES['cat_img']['size'] > 500000) {
            $this->form_validation->set_message('_check_upload_image_cat_img', 'Max file size is 500kb.');
            return false;
        }

        if(!empty($_FILES['cat_img']['name'])){
            if(in_array($mime, $allowed_mime_type_arr)){
                return true;
            }else{
                $this->form_validation->set_message('_check_upload_image_cat_img', 'Please select only gif/jpg/png file.');
                return false;
            }
        }else if($id <= 0){
            $this->form_validation->set_message('_check_upload_image_cat_img', 'Please select a file.');
            return false;
        }    
        return true;
    }
    //=== END CATEGORY

    //=== START BANNER
    public function banner()
    {
        // validasi menu dan assign title
        $submenu_code   = 'banner';
        $permits        = $this->_check_menu_access($submenu_code, 'view');
        $this->_set_title('Banner');
        $this->load->library('pagination');
        $this->load->model('bannerdb');

        //set variable
        $current_path       = 'master_data/banner';
        $current_url        = ADMIN_URL.$current_path;
        $page               = $this->input->get('page');
        $search             = set_var($this->input->get('search'), '');
        $sort_col           = set_var($this->input->get('sc'), '');
        $sort_by            = set_var($this->input->get('sb'), 'ASC');//ASC or DESC
        $xtra_var['search'] = $search;

        //set sortable col
        $allow_sort = [
            'id'            => 'ban_id',
            'name'          => 'ban_name',
            'desc'          => 'ban_desc',
            'link'          => 'ban_link',
            'nav'           => 'ban_nav',
            'order'         => 'ban_order',
            'status'        => 'ban_status',
            'start_date'    => 'start_date',
            'end_date'      => 'end_date',
            'created_date'  => 'created_date',
        ];
        
        //start query
        $url_query      = "search={$search}&sc={$sort_col}&sb={$sort_by}";
        $search_where   = " AND (ban_id LIKE ? OR ban_name LIKE ? OR ban_desc LIKE ? OR ban_link LIKE ? OR ban_nav LIKE ? OR ban_status LIKE ? OR ban_order LIKE ? ) ";
        $search_data    = [$search.'%', "%{$search}%", "%{$search}%", "%{$search}%", "%{$search}%", "%{$search}%", "%{$search}%"];
        $search_order   = sort_table_order($allow_sort, $sort_col, $sort_by);
        $all_data       = $this->bannerdb->getpaging_banner(
            $search_where,
            $search_data,
            $search_order,
            $page
        );
        
        // start pagination setting
        $config = [
            'base_url'      => $current_url.($url_query != '' ? '?'.$url_query : ''),
            'total_rows'    => $all_data['total_row'],
            'per_page'      => $all_data['per_page']
        ];

        $this->pagination->initialize($config);
        // end pagination setting

        $arr_admin          = $this->admindb->getarr_admin();
        // select data & assign variable $data
        $data = [
            'current_url'   => $current_url,
            'form_url'      => $config['base_url'],
            'page_url'      => str_replace($url_query, '', $config['base_url']),
            'xtra_var'      => $xtra_var,
            'search'        => $search,
            'permits'       => $permits,
            'arr_admin'     => $arr_admin,
            'all_data'      => $all_data['data'],
            'cst_status'    => $this->config->item('banner')['status'],
            'pagination'    => $this->pagination->create_links()
        ];
        $this->_render('master_data/banner', $data);
    }

    public function banner_add($id = 0)
    {
        //load
        $this->load->model('bannerdb');

        // validate user roles
        $submenu_code = 'banner';
        if ($id > 0) {
            $permits = $this->_check_menu_access($submenu_code, 'edit');
            $this->_set_title('Edit Banner');
        } else {
            $permits = $this->_check_menu_access($submenu_code, 'add');
            $this->_set_title('Add Banner');
        }
        $form_permit = $this->_get_form_permit($id, $permits);
        
        // validate if banner exists
        if ($id > 0) {
            $static_banner = $this->bannerdb->get_banner($id);
            if (empty($static_banner)) {
                redirect(ADMIN_URL);
            }
        }
        
        $this->load->library('form_validation');
        $this->form_validation->set_error_delimiters(PREFIX_ERROR_DELIMITER, SUFFIX_ERROR_DELIMITER);
        $this->form_validation->set_rules('ban_name', 'Banner Name', 'strip_tags|trim|required|callback__is_unique_banner_name');
        $this->form_validation->set_rules('ban_desc', 'Banner Description', 'strip_tags|trim|required');
        $this->form_validation->set_rules('ban_link', 'Banner Link', 'callback__check_link_url');
        $this->form_validation->set_rules('ban_order', 'Banner Order', 'strip_tags|trim|integer');
        $this->form_validation->set_rules('ban_url', 'Banner Image', 'callback__check_upload_image');
        $this->form_validation->set_rules('start_date', 'Start Date', 'required');
        $this->form_validation->set_rules('end_date', 'End Date', 'required|callback__end_later_than_start');

        $err_msg = [];
        if ($this->form_validation->run()) {
            //BEGIN UPLOAD IMAGE
            $image_name = '';
            if (is_uploaded_file($_FILES['ban_url']['tmp_name'])) {

                $this->load->library("google_cloud_bucket");
                $image_name = str_replace(UPLOAD_PATH, '', BANNER_IMAGE_PATH).$_FILES['ban_url']['name'];

                if(!@fopen(UPLOAD_URL.$image_name, 'r')){
                    $data = [
                        "source" => $_FILES['ban_url']['tmp_name'],
                        "name"   => $image_name
                    ];
                    
                    $this->google_cloud_bucket->upload_image($data);
                }
            }
            //END UPLOAD IMAGE

            if(empty($err_msg)){
                $start_date                = $this->input->post('start_date');
                $end_date                  = $this->input->post('end_date');
                $now_date                  = date('Y-m-d');
                $params = [
                    'ban_name'        => $this->input->post('ban_name'),
                    'ban_desc'        => $this->input->post('ban_desc'),
                    'ban_link'        => $this->input->post('ban_link'),
                    'ban_nav'         => $this->input->post('ban_nav'),
                    'ban_order'       => set_var($this->input->post('ban_order'), 1),
                    'ban_status'      => $this->config->item('banner')['status']['inactive'],
                    'start_date'      => $start_date,
                    'end_date'        => $end_date
                ];
                
                if ($now_date >= $start_date AND $now_date <= $end_date){
                    $params ['ban_status'] = $this->config->item('banner')['status']['active'];
                }

                if($image_name != ''){
                    $params['ban_url']  = $image_name;
                }
    
                if ($id > 0 && in_array('edit', $permits)) {
                    $params['updated_by']   = $this->_get_user_id();
                    $params['updated_date'] = date('Y-m-d H:i:s');
    
                    if ($this->bannerdb->update_banner($id, $params)) {
                        $this->bannerdb->sort_banner_order($id, $params['ban_order']);
                        $err_msg = [
                            'msg'   => 'Edit Banner Success',
                            'type'  => 'success'
                        ];
                    } else {
                        $err_msg = [
                            'msg'   => 'Edit Banner Failed!',
                            'type'  => 'danger'
                        ];
                    }
                } else if ($id <= 0 && in_array('add', $permits)) {
                    $params['created_by']   = $this->_get_user_id();
                    $params['created_date'] = date('Y-m-d H:i:s');
    
                    $banner_id=$this->bannerdb->insert_banner($params);
                    if ($banner_id) {
                        $this->bannerdb->sort_banner_order($banner_id, $params['ban_order']);
                        $err_msg = [
                            'msg'   => 'Add Banner Success.'.js_clearform(),
                            'type'  => 'success'
                        ];
                    } else {
                        $err_msg = [
                            'msg'   => 'Add Banner Failed!',
                            'type'  => 'danger'
                        ];
                    }
                } else {
                    $err_msg = [
                        'msg'   => 'Access denied - You are not authorized to access this page.',
                        'type'  => 'danger'
                    ];
                }
            }
        }
        
        $banner = $this->bannerdb->get_banner($id);
        $data   = [
            'current_url'   => ADMIN_URL.'master_data/banner_add',
            'msg'           => set_form_msg($err_msg),
            'permits'       => $permits,
            'show_form'     => $form_permit['show_form'],
            'title_form'    => $form_permit['title_form'],
            'static_banner' => $banner
        ];
        $this->_render('master_data/banner_add', $data);
    }
    

    public function _check_upload_image()
    {
        $id = $this->input->post('ban_id');

        $allowed_mime_type_arr  = array('image/gif','image/jpeg','image/pjpeg','image/png','image/x-png');
        $mime                   = get_mime_by_extension($_FILES['ban_url']['name']);
        if(!empty($_FILES['ban_url']['name'])){
            if(in_array($mime, $allowed_mime_type_arr)){
                return true;
            }else{
                $this->form_validation->set_message('_check_upload_image', 'Please select only gif/jpg/png file.');
                return false;
            }
        }else if($id <= 0){
            $this->form_validation->set_message('_check_upload_image', 'Please select a file.');
            return false;
        }    
        return TRUE;
    }

    public function _is_unique_banner_name($banner_name)
    {
        $id = $this->input->post('ban_id');
        // if duplicate name found and not belong to selected ID, return false
        $banner = $this->bannerdb->get_banner_custom_filter(' AND ban_id != ? AND ban_name LIKE ?', [$id, "%".$banner_name."%"]);
        if ($banner) {
            $this->form_validation->set_message('_is_unique_banner_name', 'The Banner Name field must contain a unique value.');
            return false;
        }
        return true;
    }

    public function _check_link_url($url){
        // Validate url
        if(!empty($url)){
            if (substr($url,0,7) == "http://" || substr($url,0,8) == "https://") {
                return true;
            } else {
                $this->form_validation->set_message('_check_link_url', 'The Banner link field must url type');
                return false;
            }
        }
        return true;
    }

    public function _end_later_than_start($end_date){
        $start_date = $this->input->post('start_date');
        if(!empty($end_date)){
            if ($end_date < $start_date) {
                $this->form_validation->set_message('_end_later_than_start', 'The End Date must be later than Start Date.');
                return false;       
            }
        }
        return true;
    }
    //=== END BANNER

    //=== START PAYMENT METHOD
    public function payment_method()
    {
        // validasi menu dan assign title
        $submenu_code   = 'payment_method';
        $permits        = $this->_check_menu_access($submenu_code, 'view');

        $this->_set_title('Payment Method');
        $this->load->library('pagination');
        $this->load->model('orderdb');

        //set variable
        $current_path       = 'master_data/payment_method';
        $current_url        = ADMIN_URL.$current_path;
        $page               = $this->input->get('page');
        $search             = set_var($this->input->get('search'), '');
        $sort_col           = set_var($this->input->get('sc'), '');
        $sort_by            = set_var($this->input->get('sb'), 'ASC');//ASC or DESC
        $xtra_var['search'] = $search;

        //set sortable col
        $allow_sort = [
            'id'            => 'pymtd_id',
            'name'          => 'pymtd_name',
            'code'          => 'pymtd_code',
            'label'         => 'pymtd_label',
            'desc'          => 'pymtd_desc',
            'status'        => 'pymtd_status',
            'order'         => 'pymtd_order',
            'created'       => 'created_date',
            'updated'       => 'updated_date',
        ];
        
        //start query
        $url_query      = "search={$search}&sc={$sort_col}&sb={$sort_by}";
        $search_where   = " AND (pymtd_id LIKE ? OR pymtd_name LIKE ? OR pymtd_code LIKE ? OR pymtd_label LIKE ? OR pymtd_desc LIKE ? OR pymtd_status LIKE ? OR pymtd_order LIKE ? OR created_date LIKE ? OR updated_date LIKE ?) ";
        $search_data    = [$search.'%', "%{$search}%", "%{$search}%", "%{$search}%", "%{$search}%", "%{$search}%", "%{$search}%", "%{$search}%", "%{$search}%"];
        $search_order   = sort_table_order($allow_sort, $sort_col, $sort_by);
        $all_data       = $this->orderdb->getpaging_payment_method(
            $search_where,
            $search_data,
            $search_order,
            $page
        );
        
        // start pagination setting
        $config = [
            'base_url'      => $current_url.($url_query != '' ? '?'.$url_query : ''),
            'total_rows'    => $all_data['total_row'],
            'per_page'      => $all_data['per_page']
        ];

        $this->pagination->initialize($config);
        // end pagination setting

        $arr_admin          = $this->admindb->getarr_admin();

        // select data & assign variable $data
        $data = [
            'current_url'   => $current_url,
            'form_url'      => $config['base_url'],
            'page_url'      => str_replace($url_query, '', $config['base_url']),
            'xtra_var'      => $xtra_var,
            'search'        => $search,
            'permits'       => $permits,
            'arr_admin'     => $arr_admin,
            'all_data'      => $all_data['data'],
            'cst_status'    => $this->config->item('payment_method')['status'],
            'pagination'    => $this->pagination->create_links()
        ];
        $this->_render('master_data/payment_method', $data);
    }

    public function payment_method_add($id = 0)
    {
        $this->load->model('orderdb');

        // validate user roles
        $submenu_code = 'payment_method';
        if ($id > 0) {
            $permits = $this->_check_menu_access($submenu_code, 'edit');
            $this->_set_title('Edit Payment Method');
        } else {
            $permits = $this->_check_menu_access($submenu_code, 'add');
            $this->_set_title('Add Payment Method');
        }
        $form_permit = $this->_get_form_permit($id, $permits);
        
        // validate if payment method exists
        if ($id > 0) {
            $static_pay = $this->orderdb->get_payment_method($id);
            if (empty($static_pay)) {
                redirect(ADMIN_URL);
            }
        }
        
        $this->load->library('form_validation');
        $this->form_validation->set_error_delimiters(PREFIX_ERROR_DELIMITER, SUFFIX_ERROR_DELIMITER);
        $this->form_validation->set_rules('pymtd_name', 'Payment Method Name', 'strip_tags|trim|required|callback__is_unique_method_name');
        $this->form_validation->set_rules('pymtd_label', 'Payment Method Label', 'strip_tags|trim|max_length[255]');
        $this->form_validation->set_rules('pymtd_code', 'Payment Code', 'strip_tags|trim|required|callback__is_alpha_and_space|callback__is_unique_payment_code');
        $this->form_validation->set_rules('pymtd_order', 'Payment Method Order', 'strip_tags|trim|integer');

        $err_msg = [];
        if ($this->form_validation->run()) {

            if(empty($err_msg)){
                $params = [
                    'pymtd_name'        => $this->input->post('pymtd_name'),
                    'pymtd_code'        => strtolower($this->input->post('pymtd_code')),
                    'pymtd_label'       => $this->input->post('pymtd_label'),
                    'pymtd_desc'        => $this->input->post('pymtd_desc'),
                    'pymtd_order'       => set_var($this->input->post('pymtd_order'), 1)
                ];
  
                if ($id > 0 && in_array('edit', $permits)) {
                    $params['updated_by']   = $this->_get_user_id();
                    $params['updated_date'] = date('Y-m-d H:i:s');
                    $params['pymtd_status'] = $static_pay->pymtd_status;

                    if ($this->orderdb->update_payment_method($id, $params)) {
                        $this->orderdb->sort_payment_method_order($id, $params['pymtd_order']);
                        $err_msg = [
                            'msg'   => 'Edit Payment Method Success',
                            'type'  => 'success'
                        ];
                    } else {
                        $err_msg = [
                            'msg'   => 'Edit Payment Method Failed!',
                            'type'  => 'danger'
                        ];
                    }
                } else if ($id <= 0 && in_array('add', $permits)) {
                    $params['created_by']   = $this->_get_user_id();
                    $params['created_date'] = date('Y-m-d H:i:s');
                    $params['pymtd_status'] = $this->config->item('payment_method')['status']['active'];
    
                    $pymtd_id               = $this->orderdb->insert_payment_method($params);
                    if ($pymtd_id) {
                        $this->orderdb->sort_payment_method_order($pymtd_id, $params['pymtd_order']);
                        $err_msg = [
                            'msg'   => 'Add Payment Method Success.'.js_clearform(),
                            'type'  => 'success'
                        ];
                    } else {
                        $err_msg = [
                            'msg'   => 'Add Payment Method Failed!',
                            'type'  => 'danger'
                        ];
                    }
                } else {
                    $err_msg = [
                        'msg'   => 'Access denied - You are not authorized to access this page.',
                        'type'  => 'danger'
                    ];
                }
            }
        }
        $payment_method = $this->orderdb->get_payment_method($id);
        $data   = [
            'current_url'   => ADMIN_URL.'master_data/payment_method_add',
            'msg'           => set_form_msg($err_msg),
            'permits'       => $permits,
            'show_form'     => $form_permit['show_form'],
            'title_form'    => $form_permit['title_form'],
            'static_pymtd'  => $payment_method
        ];
        $this->_render('master_data/payment_method_add', $data);
    }

    public function _is_unique_method_name($pymtd_name)
    {
        $id = $this->input->post('pymtd_id');
        // if duplicate name found and not belong to selected ID, return false
        $pymtd = $this->orderdb->get_payment_method_by_name($pymtd_name);
        if ($pymtd && $pymtd->pymtd_id !== $id) {
            $this->form_validation->set_message('_is_unique_method_name', 'The Payment Method Name field must contain a unique value.');
            return false;
        }
        return true;
    }

    public function _is_unique_payment_code($pymtd_code)
    {
        $id = $this->input->post('pymtd_id');
        // if duplicate name found and not belong to selected ID, return false
        $pymtd = $this->orderdb->get_payment_method_by_code($pymtd_code);
        if ($pymtd && $pymtd->pymtd_id !== $id) {
            $this->form_validation->set_message('_is_unique_payment_code', 'The Payment Code field must contain a unique value.');
            return false;
        }
        return true;
    }

    public function _is_alpha_and_space($pymtd_code)
    {
        $pymtd_code = $this->input->post('pymtd_code');
        if (!preg_match('/^([a-z_])+$/i', $pymtd_code)) {
            $this->form_validation->set_message('_is_alpha_and_space', 'The %s field may only contain alphabet & dash characters');
            return FALSE;
        } else {
            return TRUE;
        }
        
    }
    //=== END PAYMENT METHOD

    //>>START COURIER<<
    public function courier(){
        // validasi menu dan assign title
        $submenu_code = 'courier';
        $permits      = $this->_check_menu_access($submenu_code, 'view');
        $this->_set_title('Courier');
        $this->load->library('pagination');
        $this->load->model('courierdb');
        
        //set variable
        $current_url        = ADMIN_URL.'master_data/courier';
        $page               = $this->input->get('page');
        $search             = strtolower(set_var($this->input->get('search'), ''));
        $sort_col           = set_var($this->input->get('sc'), '');
        $sort_by            = set_var($this->input->get('sb'), 'ASC');//ASC or DESC
        $xtra_var['search'] = $search;
        $arr_admin          = $this->admindb->getarr_admin();
        
        //set sortable col
        $allow_sort = [
            'id'            => 'courier_id',
            'courier_code'  => 'courier_code',
            'courier_vendor'=> 'courier_vendor',
            'courier_desc'  => 'courier_desc',
            'courier_status'=> 'courier_status',
            'is_default'    => 'is_default',
            'created'       => 'created_date',
            'updated'       => 'updated_date'
        ];
        
        //start query
        $url_query    = "search={$search}&sc={$sort_col}&sb={$sort_by}";
        $search_where = " AND (courier_id LIKE ? OR courier_code LIKE ? OR courier_vendor LIKE ? OR courier_desc LIKE ? OR courier_status LIKE ? OR is_default LIKE ? OR created_date LIKE ? OR updated_date LIKE ?) ";
        $search_data  = [$search.'%', "%{$search}%", "%{$search}%", "%{$search}%", "%{$search}%", ($search === 'true' ? "1" : ($search === 'false' ? "0" : "")), "%{$search}%", "%{$search}%"];
        $search_order = sort_table_order($allow_sort, $sort_col, $sort_by);
        $all_data     = $this->courierdb->getpaging_courier(
            $search_where,
            $search_data,
            $search_order,
            $page
        );
        
        // start pagination setting
        $config = [
            'base_url'   => $current_url.($url_query != '' ? '?'.$url_query : ''),
            'total_rows' => $all_data['total_row'],
            'per_page'   => $all_data['per_page']
        ];
        $this->pagination->initialize($config);
        // end pagination setting

        // select data & assign variable $data
        $data = [
            'current_url'=> $current_url,
            'form_url'   => $config['base_url'],
            'page_url'   => str_replace($url_query, '', $config['base_url']),
            'xtra_var'   => $xtra_var,
            'search'     => $search,
            'permits'    => $permits,
            'arr_admin'  => $arr_admin,
            'data'       => $all_data['data'],
            'cst_status' => $this->config->item('courier')['status'],
            'pagination' => $this->pagination->create_links()
        ];
        $this->_render('master_data/courier', $data);
    }

    public function courier_add($id = 0){
        $this->load->model('courierdb');
        // validate user roles
        $submenu_code = 'courier';
        if ($id > 0) {
            $permits = $this->_check_menu_access($submenu_code, 'edit');
            $this->_set_title('Edit Courier');
        } else {
            $permits = $this->_check_menu_access($submenu_code, 'add');
            $this->_set_title('Add Courier');
        }
        $form_permit = $this->_get_form_permit($id, $permits);
        
        // validate if courier exists
        if ($id > 0) {
            $courier = $this->courierdb->get_courier($id);
            if (empty($courier)) {
                redirect(ADMIN_URL);
            }
        }

        $this->load->library('form_validation');
        $this->form_validation->set_error_delimiters(PREFIX_ERROR_DELIMITER, SUFFIX_ERROR_DELIMITER);
        $this->form_validation->set_rules('courier_code', 'Courier Code', 'strip_tags|trim|required|callback__is_unique_courier_code|callback__alpha_dash');
        $this->form_validation->set_rules('courier_vendor', 'Courier Vendor', 'strip_tags|trim|required');
        $this->form_validation->set_rules('courier_desc', 'Description', 'strip_tags|trim');

        $err_msg = [];
        if ($this->form_validation->run()) {
            if(empty($err_msg)){
                $courier_id     = $this->input->post('id');
                $is_default     = $this->input->post('is_default');
                if(!isset($is_default)){
                    $is_default = "0";
                }

                if($is_default == 1){
                    $this->courierdb->update_is_default($id, $is_default);
                }

                $params = [
                    'courier_code'   => strtolower($this->input->post('courier_code')),
                    'courier_vendor' => $this->input->post('courier_vendor'),
                    'courier_desc'   => $this->input->post('courier_desc'),
                    'is_default'     => $is_default
                ]; 

                if ($id > 0 && in_array('edit', ["edit"])) {
                    $params['updated_by']   = $this->_get_user_id();
                    $params['updated_date'] = date('Y-m-d H:i:s');
                    
                    if ($this->courierdb->update_courier($id, $params)) {
                        $err_msg = [
                            'msg'   => 'Edit Courier Success',
                            'type'  => 'success'
                        ];
                    } else {
                        $err_msg = [
                            'msg'   => 'Edit Courier Failed!',
                            'type'  => 'danger'
                        ];
                    }

                } else if ($id <= 0 && in_array('add', ["add"])) {
                    $params['courier_status'] = $this->config->item('courier')['status']['inactive'];
                    $params['created_by']     = $this->_get_user_id();
                    $params['created_date']   = date('Y-m-d H:i:s');
                    
                    if ($this->courierdb->insert_courier($id, $params)) {
                        $err_msg = [
                            'msg'   => 'Add Courier Success.'.js_clearform(),
                            'type'  => 'success'
                        ];
                    } else {
                        $err_msg = [
                            'msg'   => 'Add Courier Failed!',
                            'type'  => 'danger'
                        ];
                    }
                } else {
                    $err_msg = [
                        'msg'   => 'Access denied - You are not authorized to access this page.',
                        'type'  => 'danger'
                    ];
                }
            }
        }

       $data = [
            'current_url'   => ADMIN_URL.'master_data/courier_add',
            'msg'           => set_form_msg($err_msg),
            'permits'       => $permits,
            'show_form'     => $form_permit['show_form'],
            'title_form'    => $form_permit['title_form'],
            'cst_status'    => $this->config->item('courier')['status'],
            'courier'       => $this->courierdb->get_courier($id)
        ];
        $this->_render('master_data/courier_add', $data);
    }
    
    public function _is_unique_courier_code($code){
        $id = $this->input->post('id');
        // if duplicate name found and not belong to selected ID, return false
        $courier = $this->courierdb->get_courier_custom_filter(' AND courier_id != ? AND courier_code = ?', [$id, $code]);
        if ($courier) {
            $this->form_validation->set_message('_is_unique_courier_code', 'The Courier Code field must contain a unique value.');
            return false;
        }
        return true;
    }  
    
    public function _alpha_dash($code){
        if (!empty($code) && !preg_match('/^[a-zA-Z_]+$/', $code)) {
            $this->form_validation->set_message('_alpha_dash', 'The Courier Code field just contain alphabet and underscore.');
            return FALSE;
        }
        return TRUE;
    }
    //>>END COURIER<<

    //>>START SUBS PLAN<<
    public function subs_plan(){
        // validasi menu dan assign title
        $submenu_code       = 'subs_plan';
        $permits            = $this->_check_menu_access($submenu_code, 'view');

        $this->_set_title('Subscription Plan');
        $this->load->library('pagination');
        $this->load->model('subscriptiondb');
        
        //set variable
        $page               = $this->input->get('page');
        $search             = set_var($this->input->get('search'), '');
        $sort_col           = set_var($this->input->get('sc'), 'id');
        $sort_by            = set_var($this->input->get('sb'), 'ASC');//ASC or DESC
        $xtra_var['search'] = $search;
        $arr_admin          = $this->admindb->getarr_admin();

        //set sortable col
        $allow_sort = [
            'id'            => 'subsplan_id',
            'name'          => 'subsplan_name',
            'code'          => 'subsplan_code',
            'price'         => 'subsplan_finalprice',
            'subsplan_show' => 'subsplan_show',
            'promo'         => 'subsplan_promo',
            'duration'      => 'subsplan_duration',
            'created'       => 'created_date',
            'update'        => 'updated_date',
        ];
        
        
        //start query
        $url_query          = "search={$search}&sc={$sort_col}&sb={$sort_by}";
        $search_where       = " AND (subsplan_id = ? OR subsplan_name LIKE ? OR subsplan_code LIKE ? OR subsplan_finalprice LIKE ? OR subsplan_show LIKE ? OR subsplan_duration LIKE ? ) ";
        $search_data        = [$search, "%{$search}%", "%{$search}%", "%{$search}%", "%{$search}%", "%{$search}%"];
        $search_order       = sort_table_order($allow_sort, $sort_col, $sort_by);
        $all_data           = $this->subscriptiondb->getpaging_subs_plan(
            $search_where,
            $search_data,
            $search_order,
            $page
        );
        
        // start pagination setting
        $config = [
            'base_url'      => ADMIN_URL.'master_data/subs_plan'.($url_query != '' ? '?'.$url_query : ''),
            'total_rows'    => $all_data['total_row'],
            'per_page'      => $all_data['per_page']
        ];

        $this->pagination->initialize($config);
        // end pagination setting

        // select data & assign variable $data
        $data = [
            'current_url'   => ADMIN_URL.'master_data/subs_plan',
            'form_url'      => $config['base_url'],
            'page_url'      => str_replace($url_query, '', $config['base_url']),
            'xtra_var'      => $xtra_var,
            'search'        => $search,
            'permits'       => $permits,
            'cst_type'      => $this->config->item('promo'),
            'cst_show'      => $this->config->item('subs_plan')['subsplan_show'],
            'all_data'      => $all_data['data'],
            'arr_admin'     => $arr_admin,
            'pagination'    => $this->pagination->create_links()
        ];
        $this->_render('master_data/subs_plan', $data);

    }

    public function subs_plan_add($id = 0)
    {
        // load model
        $this->load->model('subscriptiondb');
        $this->load->model('productdb');

        // validate user roles
        $submenu_code = 'subs_plan';
        if ($id > 0) {
            $permits = $this->_check_menu_access($submenu_code, 'edit');
            $this->_set_title('Edit Subscription Plan');
        } else {
            $permits = $this->_check_menu_access($submenu_code, 'add');
            $this->_set_title('Add Subscription Plan');
        }
        $form_permit = $this->_get_form_permit($id, $permits);
        $product    = $this->productdb->getall_product();

        // validate if voucdef exists
        $subs_plan = [];
        if ($id > 0) {
            $subs_plan = $this->subscriptiondb->get_subsplan($id);
            if (empty($subs_plan)) {
                redirect(ADMIN_URL);
            }
        }
        
        $this->load->library('form_validation');
        $this->form_validation->set_error_delimiters(PREFIX_ERROR_DELIMITER, SUFFIX_ERROR_DELIMITER);
        $this->form_validation->set_rules('subsplan_name', 'Subs Plan Name', 'strip_tags|trim|required');
        $this->form_validation->set_rules('subsplan_code', 'Subs Plan Code', 'strip_tags|trim|required|min_length[3]|max_length[8]|callback__is_unique_subsplan_code|callback__alpha_numeric');
        $this->form_validation->set_rules('subsplan_baseprice', 'Base Price', 'required|is_natural');
        $this->form_validation->set_rules('subsplan_finalprice', 'Final Price', 'required|is_natural|callback__final_less_than_base');
        $this->form_validation->set_rules('subsplan_show', 'Subs Plan Show', 'required');
        $this->form_validation->set_rules('disc_nominal', 'Discount Nominal', 'required|integer');
        $this->form_validation->set_rules('disc_max', 'Discount Maximal', 'integer');
        $this->form_validation->set_rules('min_order', 'Minimal Order', 'integer');
        $this->form_validation->set_rules('item_type', 'Item Type', 'required');
        $this->form_validation->set_rules('item_list[]', 'Item List', 'callback__check_item_list');
        $this->form_validation->set_rules('disc_type', 'Discount Type', 'required');
        $this->form_validation->set_rules('image', 'Promo Image', 'callback__check_upload_image_subsplan_promo');
        $this->form_validation->set_rules('expired_day', 'Expired Day', 'strip_tags|trim|required|integer');
        $this->form_validation->set_rules('subsplan_img', 'Subs Plan Image', 'callback__check_upload_image_subsplan');
        $this->form_validation->set_rules('subsplan_img_detail', 'Subs Plan Image Detail', 'callback__check_upload_image_subsplan_detail');
        $this->form_validation->set_rules('subsplan_duration', 'Subs Plan Duration', 'strip_tags|trim|required|integer');

        $err_msg = [];
        if ($this->form_validation->run()) { 
            //BEGIN UPLOAD IMAGE
            $promo_image_name               = '';
            $subsplan_image_name            = '';
            $subsplan_image_detail_name     = '';
            if (is_uploaded_file($_FILES['image']['tmp_name'])) {

                $this->load->library("google_cloud_bucket");
                $promo_image_name       = str_replace(UPLOAD_PATH, '', SUBSPLAN_PROMO_IMAGE_PATH).$_FILES['image']['name'];

                if(!@fopen(UPLOAD_URL.$promo_image_name, 'r')){
                    $data = [
                        "source" => $_FILES['image']['tmp_name'],
                        "name"   => $promo_image_name
                    ];
                    
                    $this->google_cloud_bucket->upload_image($data);
                }
            }
            if(is_uploaded_file($_FILES['subsplan_img']['tmp_name'])) {
                
                $this->load->library("google_cloud_bucket");
                $subsplan_image_name    = str_replace(UPLOAD_PATH, '', SUBSPLAN_IMAGE_PATH).$_FILES['subsplan_img']['name'];

                if(!@fopen(UPLOAD_URL.$subsplan_image_name, 'r')){
                    $data = [
                        "source" => $_FILES['subsplan_img']['tmp_name'],
                        "name"   => $subsplan_image_name
                    ];
                    
                    $this->google_cloud_bucket->upload_image($data);
                }
            }
            if(is_uploaded_file($_FILES['subsplan_img_detail']['tmp_name'])) {
                
                $this->load->library("google_cloud_bucket");
                $subsplan_image_detail_name    = str_replace(UPLOAD_PATH, '', SUBSPLAN_IMAGE_PATH).$_FILES['subsplan_img_detail']['name'];

                if(!@fopen(UPLOAD_URL.$subsplan_image_detail_name, 'r')){
                    $data = [
                        "source" => $_FILES['subsplan_img_detail']['tmp_name'],
                        "name"   => $subsplan_image_detail_name
                    ];
                    
                    $this->google_cloud_bucket->upload_image($data);
                }
            }
            //END UPLOAD IMAGE

            if(empty($err_msg)){
                $limit_usage        = $this->config->item('subs_plan')['subsplan_promo']['limit_usage'];
                $disc_type          = $this->input->post('disc_type');
                $item_list          = $this->input->post('item_list');
                $item_type          = $this->input->post('item_type');
                $disc_nominal       = intval($this->input->post('disc_nominal'));
                $disc_max           = intval($this->input->post('disc_max'));
                $min_order          = intval($this->input->post('min_order'));
                $custom_function    = $this->input->post('custom_function');
                $free_delivery      = $this->input->post('free_delivery') == 1 ? true : false;
                $delivery_included  = $this->input->post('delivery_included') == 1 ? true : false;
                $expired_day        = $this->input->post('expired_day');

                $rules = [
                    'limit_usage'       => $limit_usage,
                    'custom_function'   => $custom_function,
                    'disc_type'         => $disc_type,
                    'disc_nominal'      => $disc_nominal,
                    'disc_max'          => $disc_max,
                    'min_order'         => $min_order,
                    'delivery_included' => $delivery_included,
                    'free_delivery'     => $free_delivery,
                    'item_type'         => $item_type,
                    'item_list'         => (in_array("0", $item_list)) ? [] : $item_list,
                    'expired_day'       => $expired_day
                ];
                
                $params = [
                    'subsplan_name'       => $this->input->post('subsplan_name'),
                    'subsplan_code'       => $this->input->post('subsplan_code'),
                    'subsplan_baseprice'  => $this->input->post('subsplan_baseprice'),
                    'subsplan_finalprice' => $this->input->post('subsplan_finalprice'),
                    'subsplan_show'       => $this->input->post('subsplan_show'),
                    'subsplan_duration'   => $this->input->post('subsplan_duration'),
                ];

                if ($subsplan_image_name != ''){
                    $params['subsplan_img']  = $subsplan_image_name;
                }

                if ($subsplan_image_detail_name != ''){
                    $params['subsplan_img_detail']  = $subsplan_image_detail_name;
                }

                if ($promo_image_name != ''){
                    $rules['image']             = $promo_image_name;
                }
                $params['subsplan_promo']       = json_encode($rules);

                if ($id > 0 && in_array('edit', $permits)) {
                    $params['updated_by']       = $this->_get_user_id();
                    $params['updated_date']     = date('Y-m-d H:i:s');
                    if($promo_image_name == ''){
                        $subs_plan                  = $this->subscriptiondb->get_subsplan($id);
                        $rules['image']             = (json_decode($subs_plan->subsplan_promo))->image;
                    }
                    $params['subsplan_promo']   = json_encode($rules);

                    if ($this->subscriptiondb->update_subs_plan($id, $params)) {
                        $err_msg = [
                            'msg'   => 'Edit Subs Plan Success',
                            'type'  => 'success'
                        ];
                    } else {
                        $err_msg = [
                            'msg'   => 'Edit Subs Plan Failed!',
                            'type'  => 'danger'
                        ];
                    }

                } else if ($id <= 0 && in_array('add', $permits)) {
                    $params['created_by']   = $this->_get_user_id();
                    $params['created_date'] = date('Y-m-d H:i:s');

                    if ($this->subscriptiondb->insert_subs_plan($params)) {
                        $err_msg = [
                            'msg'   => 'Add Subs Plan Success.'.js_clearform(),
                            'type'  => 'success'
                        ];
                    } else {
                        $err_msg = [
                            'msg'   => 'Add Subs Plan Failed!',
                            'type'  => 'danger'
                        ];
                    }
                } else {
                    $err_msg = [
                        'msg'   => 'Access denied - You are not authorized to access this page.',
                        'type'  => 'danger'
                    ];
                }
            }
        }
        $subs_plan  = $this->subscriptiondb->get_subsplan($id);
        $prm_rules  = isset($subs_plan->subsplan_promo) ? json_decode($subs_plan->subsplan_promo, true) : [];

        $data = [
            'current_url'           => ADMIN_URL.'master_data/subs_plan_add',
            'msg'                   => set_form_msg($err_msg),
            'permits'               => $permits,
            'show_form'             => $form_permit['show_form'],
            'title_form'            => $form_permit['title_form'],
            'cst_type'              => $this->config->item('promo'),
            'cst_show'              => $this->config->item('subs_plan'),
            'subs_plan'             => $subs_plan,
            'product'               => $product,
            'prm_rules'             => $prm_rules,
            'prm_rules_item_list'   => isset($prm_rules['item_list']) ? $prm_rules['item_list'] : []
        ];
        $this->_render('master_data/subs_plan_add', $data);
    }

    public function _final_less_than_base($final_price)
    {
        $base_price = $this->input->post('subsplan_baseprice');
        
        // if base price is not 0, validate final price must be less than base price
        if ($base_price > 0 &&  $final_price >= $base_price) {
            $this->form_validation->set_message('_final_less_than_base', 'The Final Price must be less than Base Price.');
            return false;       
        }
        return true;
    }

    public function _check_item_list($type){
        $cst_type   = $this->config->item('promo')['item_type'];
        $type       = $this->input->post('item_type');
        $item_list  = $this->input->post('item_list');

        if($type == $cst_type['whitelist']) {
            if(in_array("0", $item_list)) {
                $this->form_validation->set_message('_check_item_list', 'The %s can not to set No Product if you choose Whitelist type.');
                return false;   
            }
        }

        return true;
    }

    public function _check_upload_image_subsplan()
    {
        $id                     = $this->input->post('subsplan_id');
        $allowed_mime_type_arr  = array('image/gif','image/jpeg','image/pjpeg','image/png','image/x-png');
        $mime_of_subs           = get_mime_by_extension($_FILES['subsplan_img']['name']);

        if(!empty($_FILES['subsplan_img']['name'])){
            if(in_array($mime_of_subs, $allowed_mime_type_arr)){
                return true;
            }else{        
                $this->form_validation->set_message('_check_upload_image_subsplan', 'Please select only gif/jpg/png file in Subs Plan Image Field.');
                return false;
            }
        }if($id <= 0){
            $this->form_validation->set_message('_check_upload_image_subsplan', 'Please select a file in Subs Plan Image Field.');
            return false;
        }
        return true;
    }

    public function _check_upload_image_subsplan_detail()
    {
        $id                     = $this->input->post('subsplan_id');
        $allowed_mime_type_arr  = array('image/gif','image/jpeg','image/pjpeg','image/png','image/x-png');
        $mime_of_subs_detail    = get_mime_by_extension($_FILES['subsplan_img_detail']['name']);

        if(!empty($_FILES['subsplan_img_detail']['name'])){
            if(in_array($mime_of_subs_detail, $allowed_mime_type_arr)){
                return true;
            }else{
                $this->form_validation->set_message('_check_upload_image_subsplan_detail', 'Please select only gif/jpg/png file in Subs Plan Image Detail Field.');
                return false;
            }
        }if($id <= 0){
            $this->form_validation->set_message('_check_upload_image_subsplan_detail', 'Please select a file in Subs Plan Image Detail Field.');
            return false;
        }
        return true;
    }

    public function _check_upload_image_subsplan_promo()
    {
        $id                     = $this->input->post('subsplan_id');
        $allowed_mime_type_arr  = array('image/gif','image/jpeg','image/pjpeg','image/png','image/x-png');
        $mime_of_promo          = get_mime_by_extension($_FILES['image']['name']);

        if(!empty($_FILES['image']['name'])){
            if(in_array($mime_of_promo, $allowed_mime_type_arr)){
                return true;
            }else{
                $this->form_validation->set_message('_check_upload_image_subsplan_promo', 'Please select only gif/jpg/png file in Promo Image Field.');
                return false;
            }
        }if($id <= 0){
            $this->form_validation->set_message('_check_upload_image_subsplan_promo', 'Please select a file in Promo Image Field.');
            return false;
        }
        return true;
    }

    public function _is_unique_subsplan_code($code)
    {
        $this->load->model('subscriptiondb');
        $id = $this->input->post('subsplan_id');
        // if duplicate code found and not belong to selected ID, return false
        $subs_plan = $this->subscriptiondb->get_subs_plan_by_code($code);
        if ($subs_plan && $subs_plan->subsplan_id !== $id) {
            $this->form_validation->set_message('_is_unique_subsplan_code', 'The Subsplan Code field must contain a unique value.');
            return false;
        }
        return true;
    }
       
    public function _alpha_numeric($code){
        if (!empty($code) && !preg_match('/^[a-zA-Z0-9]+$/', $code)) {
            $this->form_validation->set_message('_alpha_numeric', 'The Subsplan Code field just contain alphabet and numeric.');
            return FALSE;
        }
        return TRUE;
    }
    //>>END SUBS PLAN<<

    //>>START USER DOWNLOAD<<
    public function user_download(){
        // validasi menu dan assign title
        $submenu_code = 'user_download';
        $permits      = $this->_check_menu_access($submenu_code, 'view');
        $this->_set_title('User Download');
        $this->load->library('pagination');
        $this->load->model('user_downloaddb');
        
        //set variable
        $current_url        = ADMIN_URL.'master_data/user_download';
        $page               = $this->input->get('page');
        $search             = strtolower(set_var($this->input->get('search'), ''));
        $start_date         = set_var($this->input->get('start'), date('Y-m-d', strtotime('-1 month')));
        $end_date           = set_var($this->input->get('end'), date('Y-m-d'));
        $sort_col           = set_var($this->input->get('sc'), '');
        $sort_by            = set_var($this->input->get('sb'), 'ASC');//ASC or DESC
        $xtra_var['search'] = $search;
        $xtra_var['start']  = $start_date;
        $xtra_var['end']    = $end_date;
        $arr_admin          = $this->admindb->getarr_admin();
        
        //set sortable col
        $allow_sort = [
            'id'            => 'usrd_id',
            'usrd_type'     => 'usrd_type',
            'usrd_date'     => 'usrd_date',
            'usrd_total'    => 'usrd_total',
            'created'       => 'created_date',
            'updated'       => 'updated_date'
        ];
        
        //start query
        $url_query    = "search={$search}&sc={$sort_col}&sb={$sort_by}&start={$start_date}&end={$end_date}";
        $search_where = " AND (usrd_id LIKE ? OR usrd_type LIKE ? OR usrd_total LIKE ? ) AND usrd_date >= ? AND usrd_date <= ?  ";
        $search_data  = [$search.'%', "%{$search}%", "%{$search}%",$start_date, $end_date];
        $search_order = sort_table_order($allow_sort, $sort_col, $sort_by);
        $all_data     = $this->user_downloaddb->getpaging_user_download(
            $search_where,
            $search_data,
            $search_order,
            $page
        );
        
        // start pagination setting
        $config = [
            'base_url'   => $current_url.($url_query != '' ? '?'.$url_query : ''),
            'total_rows' => $all_data['total_row'],
            'per_page'   => $all_data['per_page']
        ];
        $this->pagination->initialize($config);
        // end pagination setting

        // select data & assign variable $data
        $data = [
            'current_url'=> $current_url,
            'form_url'   => $config['base_url'],
            'page_url'   => str_replace($url_query, '', $config['base_url']),
            'xtra_var'   => $xtra_var,
            'start_date' => $start_date,
            'end_date'   => $end_date,
            'search'     => $search,
            'permits'    => $permits,
            'arr_admin'  => $arr_admin,
            'data'       => $all_data['data'],
            'cst_type'   => $this->config->item('user_download')['usrd_type'],
            'pagination' => $this->pagination->create_links()
        ];
        $this->_render('master_data/user_download', $data);
    }
    
    public function user_download_add($id = 0){
        $this->load->model('user_downloaddb');
        // validate user roles
        $submenu_code = 'user_download';
        if ($id > 0) {
            $permits = $this->_check_menu_access($submenu_code, 'edit');
            $this->_set_title('Edit User Download');
        } else {
            $permits = $this->_check_menu_access($submenu_code, 'add');
            $this->_set_title('Add User Download');
        }
        $form_permit = $this->_get_form_permit($id, $permits);
        
        // validate if user_download exists
        if ($id > 0) {
            $user_download = $this->user_downloaddb->get_user_download($id);
            if (empty($user_download)) {
                redirect(ADMIN_URL);
            }
        }
        
        $this->load->library('form_validation');
        $this->form_validation->set_error_delimiters(PREFIX_ERROR_DELIMITER, SUFFIX_ERROR_DELIMITER);
        $this->form_validation->set_rules('usrd_type', 'Type', 'strip_tags|trim|required');
        $this->form_validation->set_rules('usrd_date', 'Date', 'strip_tags|trim|required|callback__check_date');
        $this->form_validation->set_rules('usrd_total', 'Total', 'strip_tags|trim|required|integer');
        
        $err_msg = [];
        if ($this->form_validation->run()) {
            if(empty($err_msg)){

                $params = [
                    'usrd_type'   => $this->input->post('usrd_type'),
                    'usrd_date'   => $this->input->post('usrd_date'),
                    'usrd_total'  => $this->input->post('usrd_total')
                ]; 

                if ($id > 0 && in_array('edit', $permits)) {
                    $params['updated_by']   = $this->_get_user_id();
                    $params['updated_date'] = date('Y-m-d H:i:s');
                    
                    if ($this->user_downloaddb->update_user_download($id, $params)) {
                        $err_msg = [
                            'msg'   => 'Edit User Download Success',
                            'type'  => 'success'
                        ];
                    } else {
                        $err_msg = [
                            'msg'   => 'Edit User Download Failed!',
                            'type'  => 'danger'
                        ];
                    }

                } else if ($id <= 0 && in_array('add', $permits)) {
                    $params['created_by']     = $this->_get_user_id();
                    $params['created_date']   = date('Y-m-d H:i:s');
                    
                    if ($this->user_downloaddb->insert_user_download($params)) {
                        $err_msg = [
                            'msg'   => 'Add User Download Success.'.js_clearform(),
                            'type'  => 'success'
                        ];
                    } else {
                        $err_msg = [
                            'msg'   => 'Add User Download Failed!',
                            'type'  => 'danger'
                        ];
                    }
                } else {
                    $err_msg = [
                        'msg'   => 'Access denied - You are not authorized to access this page.',
                        'type'  => 'danger'
                    ];
                }
            }
        }
    
        $arr_data = $this->user_downloaddb->get_user_download($id);

        $data = [
            'current_url'   => ADMIN_URL.'master_data/user_download_add',
            'msg'           => set_form_msg($err_msg),
            'permits'       => $permits,
            'show_form'     => $form_permit['show_form'],
            'title_form'    => $form_permit['title_form'],
            'cst_type'      => $this->config->item('user_download'),
            'user_download' => $arr_data
        ];
        $this->_render('master_data/user_download_add', $data);
    }  

    public function _check_date($date){
        $type           = $this->input->post('usrd_type');
        $id             = $this->input->post('usrd_id');
        $where          = " AND usrd_date = ? AND usrd_type = ? AND usrd_id != ? ";
        $data           = [$date, $type, $id];
        $user_download  = $this->user_downloaddb->getall_user_download($where, $data);
        
        if(!empty($user_download)){
            $this->form_validation->set_message('_check_date', 'The Date field for this Type is already to used.');
            return false;
        }
        return true;
    }
    
    //>>END USER DOWNLOAD<<

    //>>START STORE IMAGE<<
    public function store_image()
    {
        // validasi menu dan assign title
        $submenu_code   = 'store_image';
        $permits        = $this->_check_menu_access($submenu_code, 'view');
        $this->_set_title('Store Image');
        $this->load->library('pagination');
        $this->load->model('storedb');

        //set variable
        $current_path       = 'master_data/store_image';
        $current_url        = ADMIN_URL.$current_path;
        $page               = $this->input->get('page');
        $search             = set_var($this->input->get('search'), '');
        $sort_col           = set_var($this->input->get('sc'), '');
        $sort_by            = set_var($this->input->get('sb'), 'ASC');//ASC or DESC
        $xtra_var['search'] = $search;

        //set sortable col
        $allow_sort = [
            'id'            => 'sti_id',
            'st_name'       => 'st.st_name',
            'sti_order'     => 'sti_order',
            'sti_status'    => 'sti_status',
            'created'       => 'created_date',
            'updated'       => 'updated_date'
        ];
        
        //start query
        $url_query      = "search={$search}&sc={$sort_col}&sb={$sort_by}";
        $search_where   = " AND (sti_id LIKE ? OR st.st_name LIKE ? OR sti_order LIKE ? OR sti_status LIKE ? ) ";
        $search_data    = [$search.'%', "%{$search}%", "%{$search}%", "%{$search}%"];
        $search_order   = sort_table_order($allow_sort, $sort_col, $sort_by);
        $all_data       = $this->storedb->getpaging_store_img(
            $search_where,
            $search_data,
            $search_order,
            $page
        );
        
        // start pagination setting
        $config = [
            'base_url'      => $current_url.($url_query != '' ? '?'.$url_query : ''),
            'total_rows'    => $all_data['total_row'],
            'per_page'      => $all_data['per_page']
        ];

        $this->pagination->initialize($config);
        // end pagination setting

        $arr_admin          = $this->admindb->getarr_admin();
        // select data & assign variable $data
        $data = [
            'current_url'   => $current_url,
            'form_url'      => $config['base_url'],
            'page_url'      => str_replace($url_query, '', $config['base_url']),
            'xtra_var'      => $xtra_var,
            'search'        => $search,
            'permits'       => $permits,
            'arr_admin'     => $arr_admin,
            'all_data'      => $all_data['data'],
            'cst_status'    => $this->config->item('store_image')['status'],
            'pagination'    => $this->pagination->create_links()
        ];
        $this->_render('master_data/store_image', $data);
    }

    public function store_image_add($id = 0)
    {
        //load
        $this->load->model('storedb');

        // validate user roles
        $submenu_code = 'store_image';
        if ($id > 0) {
            $permits = $this->_check_menu_access($submenu_code, 'edit');
            $this->_set_title('Edit Store Image');
        } else {
            $permits = $this->_check_menu_access($submenu_code, 'add');
            $this->_set_title('Add Store Image');
        }
        $form_permit = $this->_get_form_permit($id, $permits);
        
        // validate if store_image exists
        if ($id > 0) {
            $store_image = $this->storedb->get_store_img($id);
            if (empty($store_image)) {
                redirect(ADMIN_URL);
            }
        }
        
        $this->load->library('form_validation');
        $this->form_validation->set_error_delimiters(PREFIX_ERROR_DELIMITER, SUFFIX_ERROR_DELIMITER);
        $this->form_validation->set_rules('st_id', 'Store', 'strip_tags|trim|required');
        $this->form_validation->set_rules('st_name', 'Store', 'strip_tags|trim|required');
        $this->form_validation->set_rules('sti_img[]', 'Store Image', 'callback__check_upload_image_st_img');
        $this->form_validation->set_rules('sti_order', 'Store Order', 'strip_tags|trim|integer');

        $err_msg = [];
        if ($this->form_validation->run()) {
            //BEGIN UPLOAD IMAGE
            $image_name = '';
            $image = [];

            foreach($_FILES['sti_img']['tmp_name'] as $key_image => $val_image) {
                if (is_uploaded_file($val_image)) {
    
                    $this->load->library("google_cloud_bucket");
                    $image_name = str_replace(UPLOAD_PATH, '', STORE_IMG_IMAGE_PATH).$_FILES['sti_img']['name'][$key_image];
    
                    if(!@fopen(UPLOAD_URL.$image_name, 'r')){
                        $data = [
                            "source" => $val_image,
                            "name"   => $image_name
                        ];
                        
                        $this->google_cloud_bucket->upload_image($data);
                    }
                    $image[] = $image_name;
                }
                //END UPLOAD IMAGE
            }

            if(empty($err_msg)){
                $params = [
                    'st_id'         => $this->input->post('st_id'),
                    'sti_order'     => set_var($this->input->post('sti_order'), 1)
                ];
    
                if ($id > 0 && in_array('edit', $permits)) {
                    if($image_name != ''){
                        $params['sti_img']  = $image_name;
                    }
                    $params['updated_by']   = $this->_get_user_id();
                    $params['updated_date'] = date('Y-m-d H:i:s');
    
                    if ($this->storedb->update_store_img($id, $params)) {
                        $this->storedb->sort_store_img_order($id, $params['sti_order']);
                        $err_msg = [
                            'msg'   => 'Edit Store Image Success',
                            'type'  => 'success'
                        ];
                    } else {
                        $err_msg = [
                            'msg'   => 'Edit Store Image Failed!',
                            'type'  => 'danger'
                        ];
                    }
                } else if ($id <= 0 && in_array('add', $permits)) {
                    $params['created_by']   = $this->_get_user_id();
                    $params['created_date'] = date('Y-m-d H:i:s');
                    
                    if(count($image) > 0) {
                        foreach($image as $key => $value) {
                            $params['sti_img'] = $value;
                            $store_img = $this->storedb->insert_store_img($params);
                            if ($store_img) {
                                $this->storedb->sort_store_img_order($store_img, $params['sti_order']);
                                $err_msg = [
                                    'msg'   => 'Add Store Image  Success.'.js_clearform(),
                                    'type'  => 'success'
                                ];
                            } else {
                                $err_msg = [
                                    'msg'   => 'Add Store Image  Failed!',
                                    'type'  => 'danger'
                                ];
                            }
                        }
                    } else{
                        $store_img = $this->storedb->insert_store_img($params);
                        if ($store_img) {
                            $this->storedb->sort_store_img_order($store_img, $params['sti_order']);
                            $err_msg = [
                                'msg'   => 'Add Store Image  Success.'.js_clearform(),
                                'type'  => 'success'
                            ];
                        } else {
                            $err_msg = [
                                'msg'   => 'Add Store Image  Failed!',
                                'type'  => 'danger'
                            ];
                        }
                    }
                } else {
                    $err_msg = [
                        'msg'   => 'Access denied - You are not authorized to access this page.',
                        'type'  => 'danger'
                    ];
                }
            }
        }
        
        $store_img  = $this->storedb->getall_store_img(' AND sti.sti_id = ? ', [$id]);
        $data       = [
            'current_url'   => ADMIN_URL.'master_data/store_image_add',
            'msg'           => set_form_msg($err_msg),
            'permits'       => $permits,
            'show_form'     => $form_permit['show_form'],
            'title_form'    => $form_permit['title_form'],
            'store_img'     => isset($store_img[0]) ? $store_img[0] : $store_img
        ];
        $this->_render('master_data/store_image_add', $data);
    }
    
    public function _check_upload_image_st_img()
    {
        $id = $this->input->post('sti_id');

        $allowed_mime_type_arr  = array('image/gif','image/jpeg','image/pjpeg','image/png','image/x-png');
        
        if(!empty($_FILES['sti_img']['name'])){
            foreach($_FILES['sti_img']['name'] as $key_image => $val_image){
                
                if($_FILES['sti_img']['size'][$key_image] > 500000) {
                    $this->form_validation->set_message('_check_upload_image_st_img', 'Max file size is 500kb.');
                    return false;
                }

                $mime                   = get_mime_by_extension($val_image);
                if(in_array($mime, $allowed_mime_type_arr)){
                    return true;
                }else{
                    $this->form_validation->set_message('_check_upload_image_st_img', 'Please select only gif/jpg/png file.');
                    return false;
                }
            }
        }else if($id <= 0){
            $this->form_validation->set_message('_check_upload_image_st_img', 'Please select a file.');
            return false;
        }    
        return true;
    }

    //>>END STORE IMAGE<<

    //>>START BANNER CATALOGUE<<
    public function banner_catalogue()
    {
        // validasi menu dan assign title
        $submenu_code   = 'banner_catalogue';
        $permits        = $this->_check_menu_access($submenu_code, 'view');
        $this->_set_title('Banner Catalogue');
        $this->load->library('pagination');
        $this->load->model('bannerdb');

        //set variable
        $current_path       = 'master_data/banner_catalogue';
        $current_url        = ADMIN_URL.$current_path;
        $page               = $this->input->get('page');
        $search             = set_var($this->input->get('search'), '');
        $sort_col           = set_var($this->input->get('sc'), '');
        $sort_by            = set_var($this->input->get('sb'), 'ASC');//ASC or DESC
        $xtra_var['search'] = $search;

        //set sortable col
        $allow_sort = [
            'id'            => 'banc_id',
            'name'          => 'banc_name',
            'desc'          => 'banc_desc',
            'link'          => 'banc_link',
            'nav'           => 'banc_nav',
            'order'         => 'banc_order',
            'status'        => 'banc_status',
            'start_date'    => 'start_date',
            'end_date'      => 'end_date',
            'created'       => 'created_date'
        ];
        
        //start query
        $url_query      = "search={$search}&sc={$sort_col}&sb={$sort_by}";
        $search_where   = " AND (banc_id LIKE ? OR banc_name LIKE ? OR banc_desc LIKE ? OR banc_link LIKE ? OR banc_nav LIKE ? OR banc_status LIKE ? OR banc_order LIKE ? ) ";
        $search_data    = [$search.'%', "%{$search}%", "%{$search}%", "%{$search}%", "%{$search}%", "%{$search}%", "%{$search}%"];
        $search_order   = sort_table_order($allow_sort, $sort_col, $sort_by);
        $all_data       = $this->bannerdb->getpaging_banner_catalogue(
            $search_where,
            $search_data,
            $search_order,
            $page
        );
        
        // start pagination setting
        $config = [
            'base_url'      => $current_url.($url_query != '' ? '?'.$url_query : ''),
            'total_rows'    => $all_data['total_row'],
            'per_page'      => $all_data['per_page']
        ];

        $this->pagination->initialize($config);
        // end pagination setting

        $arr_admin          = $this->admindb->getarr_admin();
        // select data & assign variable $data
        $data = [
            'current_url'   => $current_url,
            'form_url'      => $config['base_url'],
            'page_url'      => str_replace($url_query, '', $config['base_url']),
            'xtra_var'      => $xtra_var,
            'search'        => $search,
            'permits'       => $permits,
            'arr_admin'     => $arr_admin,
            'all_data'      => $all_data['data'],
            'cst_status'    => $this->config->item('banner')['status'],
            'pagination'    => $this->pagination->create_links()
        ];
        $this->_render('master_data/banner_catalogue', $data);
    }

    public function banner_catalogue_add($id = 0)
    {
        //load
        $this->load->model('bannerdb');

        // validate user roles
        $submenu_code = 'banner_catalogue';
        if ($id > 0) {
            $permits = $this->_check_menu_access($submenu_code, 'edit');
            $this->_set_title('Edit Banner Catalogue');
        } else {
            $permits = $this->_check_menu_access($submenu_code, 'add');
            $this->_set_title('Add Banner Catalogue');
        }
        $form_permit = $this->_get_form_permit($id, $permits);
        
        // validate if banner exists
        if ($id > 0) {
            $banner_catalogue = $this->bannerdb->get_banner_catalogue($id);
            if (empty($banner_catalogue)) {
                redirect(ADMIN_URL);
            }
        }
        
        $this->load->library('form_validation');
        $this->form_validation->set_error_delimiters(PREFIX_ERROR_DELIMITER, SUFFIX_ERROR_DELIMITER);
        $this->form_validation->set_rules('banc_name', 'Banner Catalogue Name', 'strip_tags|trim|required|callback__is_unique_banner_catalogue_name');
        $this->form_validation->set_rules('banc_desc', 'Banner Catalogue Description', 'strip_tags|trim|required');
        $this->form_validation->set_rules('banc_link', 'Banner Catalogue Link', 'callback__check_link_url');
        $this->form_validation->set_rules('banc_order', 'Banner Catalogue Order', 'strip_tags|trim|integer');
        $this->form_validation->set_rules('banc_url', 'Banner Catalogue Image', 'callback__check_upload_image_banc');
        $this->form_validation->set_rules('start_date', 'Start Date', 'required');
        $this->form_validation->set_rules('end_date', 'End Date', 'required|callback__end_later_than_start');

        $err_msg = [];
        if ($this->form_validation->run()) {
            //BEGIN UPLOAD IMAGE
            $image_name = '';
            if (is_uploaded_file($_FILES['banc_url']['tmp_name'])) {

                $this->load->library("google_cloud_bucket");
                $image_name = str_replace(UPLOAD_PATH, '', BANNER_CATALOGUE_IMAGE_PATH).$_FILES['banc_url']['name'];

                if(!@fopen(UPLOAD_URL.$image_name, 'r')){
                    $data = [
                        "source" => $_FILES['banc_url']['tmp_name'],
                        "name"   => $image_name
                    ];
                    
                    $this->google_cloud_bucket->upload_image($data);
                }
            }
            //END UPLOAD IMAGE

            if(empty($err_msg)){
                $start_date                = $this->input->post('start_date');
                $end_date                  = $this->input->post('end_date');
                $now_date                  = date('Y-m-d');
                $params = [
                    'banc_name'        => $this->input->post('banc_name'),
                    'banc_desc'        => $this->input->post('banc_desc'),
                    'banc_link'        => $this->input->post('banc_link'),
                    'banc_nav'         => $this->input->post('banc_nav'),
                    'banc_order'       => set_var($this->input->post('banc_order'), 1),
                    'banc_status'      => $this->config->item('banner')['status']['inactive'],
                    'start_date'       => $start_date,
                    'end_date'         => $end_date
                ];
                
                if ($now_date >= $start_date AND $now_date <= $end_date){
                    $params ['banc_status'] = $this->config->item('banner')['status']['active'];
                }

                if($image_name != ''){
                    $params['banc_url']  = $image_name;
                }
    
                if ($id > 0 && in_array('edit', $permits)) {
                    $params['updated_by']   = $this->_get_user_id();
                    $params['updated_date'] = date('Y-m-d H:i:s');
    
                    if ($this->bannerdb->update_banner_catalogue($id, $params)) {
                        $this->bannerdb->sort_banner_catalogue_order($id, $params['banc_order']);
                        $err_msg = [
                            'msg'   => 'Edit Banner Catalogue Success',
                            'type'  => 'success'
                        ];
                    } else {
                        $err_msg = [
                            'msg'   => 'Edit Banner Catalogue Failed!',
                            'type'  => 'danger'
                        ];
                    }
                } else if ($id <= 0 && in_array('add', $permits)) {
                    $params['created_by']   = $this->_get_user_id();
                    $params['created_date'] = date('Y-m-d H:i:s');
    
                    $banner_id=$this->bannerdb->insert_banner_catalogue($params);
                    if ($banner_id) {
                        $this->bannerdb->sort_banner_catalogue_order($banner_id, $params['banc_order']);
                        $err_msg = [
                            'msg'   => 'Add Banner Catalogue Success.'.js_clearform(),
                            'type'  => 'success'
                        ];
                    } else {
                        $err_msg = [
                            'msg'   => 'Add Banner Catalogue Failed!',
                            'type'  => 'danger'
                        ];
                    }
                } else {
                    $err_msg = [
                        'msg'   => 'Access denied - You are not authorized to access this page.',
                        'type'  => 'danger'
                    ];
                }
            }
        }
        
        $banner_catalogue = $this->bannerdb->get_banner_catalogue($id);
        $data   = [
            'current_url'       => ADMIN_URL.'master_data/banner_catalogue_add',
            'msg'               => set_form_msg($err_msg),
            'permits'           => $permits,
            'show_form'         => $form_permit['show_form'],
            'title_form'        => $form_permit['title_form'],
            'banner_catalogue'  => $banner_catalogue
        ];
        $this->_render('master_data/banner_catalogue_add', $data);
    }
    

    public function _check_upload_image_banc()
    {
        $id = $this->input->post('banc_id');

        $allowed_mime_type_arr  = array('image/gif','image/jpeg','image/pjpeg','image/png','image/x-png');
        $mime                   = get_mime_by_extension($_FILES['banc_url']['name']);

        if($_FILES['banc_url']['size'] > 500000) {
            $this->form_validation->set_message('_check_upload_image_banc', 'Max file size is 500kb.');
            return false;
        }

        if(!empty($_FILES['banc_url']['name'])){
            if(in_array($mime, $allowed_mime_type_arr)){
                return true;
            }else{
                $this->form_validation->set_message('_check_upload_image_banc', 'Please select only gif/jpg/png file.');
                return false;
            }
        }else if($id <= 0){
            $this->form_validation->set_message('_check_upload_image_banc', 'Please select a file.');
            return false;
        }    
        return TRUE;
    }

    public function _is_unique_banner_catalogue_name($banner_name)
    {
        $id = $this->input->post('banc_id');
        // if duplicate name found and not belong to selected ID, return false
        $banner = $this->bannerdb->get_banner_catalogue_custom_filter(' AND banc_id != ? AND banc_name LIKE ?', [$id, "%".$banner_name."%"]);
        if ($banner) {
            $this->form_validation->set_message('_is_unique_banner_catalogue_name', 'The Banner Catalogue Name field must contain a unique value.');
            return false;
        }
        return true;
    }
    //>>END BANNER CATALOGUE<<

}

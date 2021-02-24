<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Product extends MY_Admin
{
    function __construct()
    {
        parent::__construct();
        $this->load->model('storedb');
        $this->load->model('productdb');
    }
    
    public function index()
    {
        show_404();
    }

    //=== START PRODUCT
    public function product()
    {
        // validasi menu dan assign title
        $submenu_code = 'product';
        $permits = $this->_check_menu_access($submenu_code, 'view');

        $this->_set_title('Products');
        $this->load->library('pagination');
        
        //set variable
        $current_path = 'product/product';
        $current_url = ADMIN_URL.$current_path;
        $page = $this->input->get('page');
        $search = set_var($this->input->get('search'), '');
        $sort_col = set_var($this->input->get('sc'), '');
        $sort_by = set_var($this->input->get('sb'), 'ASC');//ASC or DESC
        $xtra_var['search'] = $search;

        //set sortable col
        $allow_sort = [
            'id' => 'pd_id',
            'name' => 'pd_name',
            'order' => 'pd_order',
            'category' => 'cat_name',
            'desc' => 'pd_desc',
            'price' => 'pd_final_price',
            'status' => 'pd_status',
        ];
        
        //start query
        $url_query = "search={$search}&sc={$sort_col}&sb={$sort_by}";
        $search_where = " AND (pd_id LIKE ? OR pd_name LIKE ? OR cat_name LIKE ? OR pd_final_price LIKE ? OR pd_status LIKE ?) ";
        $search_data = [$search.'%', "%{$search}%", "%{$search}%", "%{$search}%", "%{$search}%"];
        $search_order = sort_table_order($allow_sort, $sort_col, $sort_by);
        $all_data = $this->productdb->getpaging_product(
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
            'data' => $all_data['data'],
            'cst_status' => $this->config->item('product')['status'],
            'pagination' => $this->pagination->create_links()
        ];
        $this->_render($current_path, $data);
    }
    
    public function product_add($id = 0)
    {
        // validate user roles
        $submenu_code = 'product';
        if ($id > 0) {
            $permits = $this->_check_menu_access($submenu_code, 'edit');
            $this->_set_title('Edit Product');
        } else {
            $permits = $this->_check_menu_access($submenu_code, 'add');
            $this->_set_title('Add Product');
        }
        $form_permit = $this->_get_form_permit($id, $permits);
        
        // validate if product exists
        if ($id > 0) {
            $product = $this->productdb->get_product($id);
            if (empty($product)) {
                redirect(ADMIN_URL);
            }
        }

        $this->load->library('form_validation');
        $this->form_validation->set_error_delimiters(PREFIX_ERROR_DELIMITER, SUFFIX_ERROR_DELIMITER);
        $this->form_validation->set_rules('pd_name', 'Product Name', 'strip_tags|trim|required|callback__is_unique_product_name');
        $this->form_validation->set_rules('cat_id', 'Category', 'required');
        $this->form_validation->set_rules('pd_base_price', 'Base Price', 'required|is_natural');
        $this->form_validation->set_rules('pd_final_price', 'Final Price', 'required|is_natural_no_zero|callback__final_less_than_base');
        $this->form_validation->set_rules('pd_desc', 'Description', 'strip_tags|trim');
        $this->form_validation->set_rules('pd_img', 'Image', 'callback__check_upload_image');
        $this->form_validation->set_rules('pd_order', 'Product Order', 'strip_tags|trim|integer');
        
        $err_msg = [];
        if ($this->form_validation->run()) {
            //BEGIN UPLOAD IMAGE
            $image_name = '';
            if (is_uploaded_file($_FILES['pd_img']['tmp_name'])) {
                $tmp_filepath = $_FILES['pd_img']['tmp_name'];
                $image_name = basename($_FILES["pd_img"]["name"]);
                if (!move_uploaded_file($tmp_filepath, PRODUCT_IMAGE_PATH. $image_name)){
                    $err_msg = [
                        'msg' => 'Cannot upload the Product Image!',
                        'type' => 'danger'
                    ];
                }
            }
            //END UPLOAD IMAGE
            if(empty($err_msg)){
                $params = [
                    'pd_name'        => $this->input->post('pd_name'),
                    'cat_id'         => $this->input->post('cat_id'),
                    'pd_base_price'  => $this->input->post('pd_base_price'),
                    'pd_final_price' => $this->input->post('pd_final_price'),
                    'pd_desc'        => $this->input->post('pd_desc'),
                    'pd_order'       => set_var($this->input->post('pd_order'), 1),
                    'pd_status'      => $this->config->item('product')['status']['active'],
                ];
                if($image_name != ''){
                    $params['pd_img']  = $image_name;
                }
                    
                if ($id > 0 && in_array('edit', $permits)) {
                    $params['updated_by'] = $this->_get_user_id();
                    $params['updated_date'] = date('Y-m-d H:i:s');

                    if ($this->productdb->update_product($id, $params)) {
                        $this->productdb->sort_product_order($id, $params['pd_order']);
                        $err_msg = [
                            'msg' => 'Edit Product Success',
                            'type' => 'success'
                        ];
                    } else {
                        $err_msg = [
                            'msg' => 'Edit Product Failed!',
                            'type' => 'danger'
                        ];
                    }

                } else if ($id <= 0 && in_array('add', $permits)) {
                    $params['created_by'] = $this->_get_user_id();
                    $params['created_date'] = date('Y-m-d H:i:s');

                    $product_id = $this->productdb->insert_product($params);
                    if ($product_id) {
                        $this->productdb->sort_product_order($product_id, $params['pd_order']);
                        $err_msg = [
                            'msg' => 'Add Product Success.'.js_clearform(),
                            'type' => 'success'
                        ];
                    } else {
                        $err_msg = [
                            'msg' => 'Add Product Failed!',
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
        }

        $current_path = 'product/product_add';
        $data = [
            'current_url' => ADMIN_URL.$current_path,
            'msg' => set_form_msg($err_msg),
            'permits' => $permits,
            'show_form' => $form_permit['show_form'],
            'title_form' => $form_permit['title_form'],
            'product' => [
                'data' => $this->productdb->get_product($id),
                'category' => $this->productdb->getall_category()
            ]
        ];
        $this->_render($current_path, $data);
    }

    public function _final_less_than_base($final_price)
    {
        $base_price = $this->input->post('pd_base_price');
        
        // if base price is not 0, validate final price must be less than base price
        if ($base_price > 0 &&  $final_price >= $base_price) {
            $this->form_validation->set_message('_final_less_than_base', 'The Final Price must be less than Base Price.');
            return false;       
        }
        return true;
    }

    public function _is_unique_product_name($product_name)
    {
        $id = $this->input->post('pd_id');
        // if duplicate name found and not belong to selected ID, return false
        $product = $this->productdb->get_product_by_name($product_name);
        if ($product && $product->pd_id !== $id) {
            $this->form_validation->set_message('_is_unique_product_name', 'The Product Name field must contain a unique value.');
            return false;
        }
        return true;
    }

    public function _check_upload_image()
    {
        $id = $this->input->post('pd_id');

        $allowed_mime_type_arr = array('image/gif','image/jpeg','image/pjpeg','image/png','image/x-png');
        $mime = get_mime_by_extension($_FILES['pd_img']['name']);

        if($_FILES['pd_img']['size'] > 500000) {
            $this->form_validation->set_message('_check_upload_image', 'Max file size is 500kb.');
            return false;
        }

        if(!empty($_FILES['pd_img']['name'])){
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
    //=== END PRODUCT

    //=== START STORE PRODUCT
    public function store_product()
    {
        // validasi menu dan assign title
        $submenu_code       = 'store_product';
        $permits            = $this->_check_menu_access($submenu_code, 'view');

        $this->_set_title('Store Products');
        $this->load->library('pagination');
        
        //set variable
        $current_path       = 'product/store_product';
        $current_url        = ADMIN_URL.$current_path;
        $page               = $this->input->get('page');
        $search             = strtolower(set_var($this->input->get('search'), ''));
        $sort_col           = set_var($this->input->get('sc'), 'stpd_id');
        $sort_by            = set_var($this->input->get('sb'), 'ASC');//ASC or DESC
        $st_id              = intval($this->input->get('st_id'));
        $xtra_var['search'] = $search;
        $xtravar['st_id']   = $st_id;

        //set sortable col
        $allow_sort = [
            'id'        => 'stpd_id',
            'store'     => 'st_name',
            'product'   => 'pd_name',
            'category'  => 'cat_name',
            'status'    => 'stpd_status',
        ];
        
        //start query
        $url_query      = "search={$search}&sc={$sort_col}&sb={$sort_by}&st_id={$st_id}";
        $search_where   = " AND (stpd_id LIKE ? OR st_name LIKE ? OR pd_name LIKE ? OR cat_name LIKE ? OR stpd_status LIKE ?) ";
        $search_data    = [$search.'%', "%{$search}%", "%{$search}%", "%{$search}%", "%{$search}%"];
        
        if(!empty($st_id)){
            $search_where .= "AND st.st_id = ?";
            array_push($search_data, $st_id);
        }

        $search_order   = sort_table_order($allow_sort, $sort_col, $sort_by);
        $all_data       = $this->productdb->getpaging_store_product(
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
            'current_url' => $current_url,
            'form_url'    => $config['base_url'],
            'page_url'    => str_replace($url_query, '', $config['base_url']),
            'xtra_var'    => $xtra_var,
            'search'      => $search,
            'permits'     => $permits,
            'data'        => $all_data['data'],
            'cst_status'  => $this->config->item('store_product'),
            'pagination'  => $this->pagination->create_links()
        ];
        $this->_render($current_path, $data);
    }

    public function store_product_add()
    {
        // validate user roles
        $submenu_code = 'store_product';
        $permits = $this->_check_menu_access($submenu_code, 'add');
        $this->_set_title('Add Store Product');
        $form_permit = $this->_get_form_permit(0, $permits);
        
        $this->load->library('form_validation');
        $this->form_validation->set_error_delimiters(PREFIX_ERROR_DELIMITER, SUFFIX_ERROR_DELIMITER);
        $this->form_validation->set_rules('st_name', 'Store', 'strip_tags|trim|required|callback__valid_store_name');
        $this->form_validation->set_rules('pd_name', 'Product', 'strip_tags|trim|required|callback__valid_product');

        $err_msg = [];
        if ($this->form_validation->run()) {
            if (in_array('add', $permits)) {
                $store = $this->storedb->get_by_name($this->input->post('st_name'));
                $product = $this->productdb->get_product_by_name($this->input->post('pd_name'));

                $params = [
                    'st_id' => $store->st_id,
                    'pd_id' => $product->pd_id,
                    'stpd_status' => $this->config->item('store_product')['status']['active'],
                    'created_by' => $this->_get_user_id(),
                    'created_date' => date('Y-m-d H:i:s'),
                ];

                if ($this->productdb->insert_store_product($params)) {
                    $err_msg = [
                        'msg' => 'Add Store Product Success.'.js_clearform(),
                        'type' => 'success'
                    ];
                } else {
                    $err_msg = [
                        'msg' => 'Add Store Product Failed!',
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
        
        $store_list = '';
        foreach ($this->storedb->getall() as $store) {
            if ($store_list === '') {
                $store_list .= '"'.$store->st_name.'"';
            } else {
                $store_list .= ',"'.$store->st_name.'"';
            }
        }

        $product_list = '';
        foreach ($this->productdb->getall_product() as $product) {
            if ($product_list === '') {
                $product_list .= '"'.$product->pd_name.'"';
            } else {
                $product_list .= ',"'.$product->pd_name.'"';
            }
        }

        $current_path = 'product/store_product_add';
        $data = [
            'current_url' => ADMIN_URL.$current_path,
            'msg' => set_form_msg($err_msg),
            'permits' => $permits,
            'show_form' => $form_permit['show_form'],
            'title_form' => $form_permit['title_form'],
            'store_list' => $store_list,
            'product_list' => $product_list,
        ];
        $this->_render($current_path, $data);
    }

    public function store_product_import()
    {
        // validate user roles
        $submenu_code   = 'store_product';
        $permits        = $this->_check_menu_access($submenu_code, 'add');
        $this->_set_title('Import Store Product');

        $form_permit    = $this->_get_form_permit(0, $permits);

        $this->load->library('form_validation');
        $this->form_validation->set_error_delimiters(PREFIX_ERROR_DELIMITER, SUFFIX_ERROR_DELIMITER);
        $this->form_validation->set_rules('st_id[]', 'Store', 'required|callback__valid_store_id', ['required' => 'At least 1 store must be selected.']);
        if($this->input->post('all_pd') == NULL){
            $this->form_validation->set_rules('pd_id[]', 'Product', 'required|callback__valid_product_id', ['required' => 'At least 1 product must be selected.']);
        }

        $err_msg = [];
        if ($this->form_validation->run()) {
            $product_list   = $this->productdb->getall_product();
            $store_id       = $this->input->post('st_id');
            $product_id     = $this->input->post('pd_id');

            if (in_array('add', $permits)) {
                $params = [
                    'st_id'         => $store_id,
                    'updated_by'    => $this->_get_user_id()
                ];
                $all_pd         = $this->input->post('all_pd');
                if($all_pd == "1"){
                    foreach ($product_list as $product){
                        $params['pd_status'][]  = $product->pd_status;
                        $params['pd_id'][]      = $product->pd_id;
                    }
                }else{
                    $params['pd_id']  = $product_id;
                    //get status product yg dipilih
                    foreach ($product_id as $value_product){
                        $pd                     = $this->productdb->get_product($value_product);
                        $params['pd_status'][]  = $pd->pd_status;
                    }
                }

                //tambah data ketika data yang diinput belum ada
                $import   = $this->productdb->import_store_product_n_bulk($params, $this->_get_user_id());
                if($import){
                    $err_msg = [
                        'msg' => 'Bulk Store Product Success',
                        'type' => 'success'
                    ];
                }else{
                    $err_msg = [
                        'msg' => 'Bulk Store Product Failed!',
                        'type' => 'danger'
                    ];
                }
            }else {
                $err_msg = [
                    'msg'       => 'Access denied - You are not authorized to access this page.',
                    'type'      => 'danger'
                ];
            }
        }

        $store          = $this->storedb->getall();
        $product        = $this->productdb->getall_product();
        $data = [
            'current_url'       => ADMIN_URL.'product/store_product_import',
            'msg'               => set_form_msg($err_msg),
            'permits'           => $permits,
            'show_form'         => $form_permit['show_form'],
            'title_form'        => $form_permit['title_form'],
            'pd'                => $this->input->post('all_pd'),
            'pd_id'             => isset($product_id) ? $product_id : [],
            'st_id'             => isset($store_id) ? $store_id : [],
            'store_list'        => $store,
            'product_list'      => $product
        ];
        $this->_render('product/store_product_import', $data);
    } 

    public function store_product_update_perstore()
    {
        // validate admin roles
        $submenu_code   = 'store_product';
        $access         = $this->_check_menu_access($submenu_code, 'bulk_process', false, false);
        if (!$access) {
            exit('Access denied - You are not authorized to access this page.');
        }

        $this->load->library('form_validation');
        $this->form_validation->set_error_delimiters(PREFIX_ERROR_DELIMITER, SUFFIX_ERROR_DELIMITER);
        $this->form_validation->set_rules('st_id', 'Store', 'strip_tags|trim|required|callback__valid_store_id');
        $this->form_validation->set_rules('st_name', 'Store', 'strip_tags|trim|required');
        $this->form_validation->set_rules('stpd_status', 'Status', 'strip_tags|trim|required');
        if($this->input->post('cat_id[]') == NULL){
            $this->form_validation->set_rules('pd_id[]', 'Product', 'required|callback__valid_product_id', ['required' => 'At least 1 product must be selected.']);
        }

        $err_msg        = [];
        $product        = [];
        $pd_id          = $this->input->post('pd_id');
        $st_id          = $this->input->post('st_id');
        $st_name        = $this->input->post('st_name');
        $cat_id         = $this->input->post('cat_id');
        if ($this->form_validation->run()) {
            $params = [
                'st_id'         => [$this->input->post('st_id')],
                'pd_id'         => $pd_id,
                'stpd_status'   => $this->input->post('stpd_status'),
                'updated_by'    => $this->_get_user_id()
            ];

            if($cat_id != NULL){
                if(empty($params['pd_id'])){
                    $params['pd_id']    = [];
                }
                //input perkategori
                foreach($cat_id as $value){
                    $product     = $this->productdb->getall_product(' AND cat.cat_id = ? ', array($value));
                    foreach($product as $val){
                        array_push($params['pd_id'], $val->pd_id);
                    }
                } 
            }
                
            //tambah data ketika data yang diinput belum ada
            $import   = $this->productdb->import_store_product_n_bulk($params, $this->_get_user_id());
            if($import){
                $err_msg = [
                    'msg' => 'Bulk Store Product Success',
                    'type' => 'success'
                ];
            }else{
                $err_msg = [
                    'msg' => 'Bulk Store Product Failed!',
                    'type' => 'danger'
                ];
            }
        }

        //product perkategori
        $category       = $this->productdb->getall_category();
        foreach($category as $key => $value){
            $product = $this->productdb->getall_product(' AND cat.cat_id = ? ', array($value->cat_id) );
            //kalau ada product baru di munculin.
            if(! empty($product)){
                $list_matrix[$key]['cat_id']   = $value->cat_id;
                $list_matrix[$key]['cat_name'] = $value->cat_name;
                $list_matrix[$key]['product']  = $product;
            }
        }

        $data = [
            'current_url'   => ADMIN_URL.'product/store_product_update_perstore',
            'msg'           => set_form_msg($err_msg),
            'cst_status'    => $this->config->item('store_product')['status'],
            'pd_id'         => isset($pd_id) ? $pd_id : [],
            'st_id'         => isset($st_id) ? $st_id : '',
            'st_name'       => isset($st_name) ? $st_name : '',
            'cat_id'        => isset($cat_id) ? $cat_id : [],
            'list_matrix'   => $list_matrix
        ];
        $this->_render('product/store_product_update_perstore', $data);
    }

    public function store_product_update_perproduct()
    {
        // validate admin roles
        $submenu_code   = 'store_product';
        $access         = $this->_check_menu_access($submenu_code, 'bulk_process', false, false);
        if (!$access) {
            exit('Access denied - You are not authorized to access this page.');
        }

        $this->load->library('form_validation');
        $this->form_validation->set_error_delimiters(PREFIX_ERROR_DELIMITER, SUFFIX_ERROR_DELIMITER);
        $this->form_validation->set_rules('pd_id', 'Product', 'strip_tags|trim|required|callback__valid_product_id');
        $this->form_validation->set_rules('pd_name', 'Product', 'strip_tags|trim|required');
        $this->form_validation->set_rules('stpd_status', 'Status', 'strip_tags|trim|required');
        if($this->input->post('all_st') == NULL){
            if($this->input->post('all_ste') != NULL){
                $this->form_validation->set_rules('st_id[]', 'Store', 'required|callback__valid_store_id|callback__empty_store_id', ['required' => 'At least 1 store must be selected.']);
            }else if($this->input->post('all_ste') == NULL){
                $this->form_validation->set_rules('all_ste', 'Store', 'required', ['required' => 'At least 1 store must be checked.']);
            }
        }
        
        $err_msg = [];
        $store        = $this->storedb->getall();
        $st_id        = $this->input->post('st_id');
        $pd_id        = $this->input->post('pd_id');
        $pd_name      = $this->input->post('pd_name');
        if ($this->form_validation->run()) {
            $params = [
                'pd_id'         => [$this->input->post('pd_id')],
                'st_id_ex'      => $st_id,
                'stpd_status'   => $this->input->post('stpd_status'),
                'updated_by'    => $this->_get_user_id()
            ];

            foreach ($store as $st_id){
                $params['st_id'][]  = $st_id->st_id;
            }
                
            //tambah data ketika data yang diinput belum ada
            $import   = $this->productdb->import_store_product_n_bulk($params, $this->_get_user_id());
            if($import){
                $err_msg = [
                    'msg' => 'Bulk Store Product Success',
                    'type' => 'success'
                ];
            }else{
                $err_msg = [
                    'msg' => 'Bulk Store Product Failed!',
                    'type' => 'danger'
                ];
            }
        }

        $data = [
            'current_url'   => ADMIN_URL.'product/store_product_update_perproduct',
            'msg'           => set_form_msg($err_msg),
            'cst_status'    => $this->config->item('store_product')['status'],
            'st'            => $this->input->post('all_st'),
            'ste'           => $this->input->post('all_ste'),
            'st_id'         => isset($st_id) ? $st_id : [],
            'pd_id'         => isset($pd_id) ? $pd_id : '',
            'pd_name'       => isset($pd_name) ? $pd_name : '',
            'store_list'    => $store
        ];
        $this->_render('product/store_product_update_perproduct', $data);
    }

    function _valid_store_name($store_name)
    {
        // validate if store exists in db
        if (!$this->storedb->get_by_name($store_name)) {
            $this->form_validation->set_message('_valid_store_name', 'Invalid Store Name.');
            return false;       
        }
        return true;
    }

    function _valid_store_id($store_id)
    {
        // validate if store exists in db
        if(!empty($product_id)){
            if (!$this->storedb->get($store_id)) {
                $this->form_validation->set_message('_valid_store_id', 'Invalid Store ID.');
                return false;       
            }
        }
        return true;
    }

    function _empty_store_id($store_id_list)
    {
        // validate if store exists in db
        if(!empty($store_id_list)){
            $store_id_list     = $this->input->post('st_id');
            $store             = $this->storedb->getall();
            if (count($store) == count($store_id_list)){
                $this->form_validation->set_message('_empty_store_id', 'At least 1 store must be selected to import.');
                return false;       
            }
        }
        return true;
    }

    function _valid_product_id($product_id)
    {
        // validate if product exists in db
        if(!empty($product_id)){
            if (!$this->productdb->get_product($product_id)) {
                $this->form_validation->set_message('_valid_product_id', 'Invalid Product ID.');
                return false;       
            }
        }
        return true;
    }

    function _valid_product($product_name)
    {
        // validate if product exist in db
        $product = $this->productdb->get_product_by_name($product_name);
        if (!$product) {
            $this->form_validation->set_message('_valid_product', 'Invalid Product Name.');
            return false;
        }

        // validate if store product already exists in selected store
        $store = $this->storedb->get_by_name($this->input->post('st_name'));
        $store_product = $this->productdb->get_store_product_custom_filter(" AND st_id = ? AND pd_id = ?", [$store->st_id, $product->pd_id]);

        if ($store_product) {
            $this->form_validation->set_message('_valid_product', 'Store Product already exists.');
            return false; 
        }

        return true;
    }

    //=== END STORE PRODUCT

    //=== START PRODUCT COGS
    public function product_cogs()
    {
        // validasi menu dan assign title
        $submenu_code = 'product_cogs';
        $permits      = $this->_check_menu_access($submenu_code, 'view');

        $this->_set_title('Products COGS');
        $this->load->library('pagination');
        
        //set variable
        $current_url        = ADMIN_URL.'product/product_cogs';
        $page               = $this->input->get('page');
        $search             = set_var($this->input->get('search'), '');
        $sort_col           = set_var($this->input->get('sc'), '');
        $sort_by            = set_var($this->input->get('sb'), 'ASC');//ASC or DESC
        $xtra_var['search'] = $search;

        //set sortable col
        $allow_sort = [
            'pdcogs_id'     => 'pdcogs_id',
            'pd_name'       => 'pd_name',
            'pdcogs_price'  => 'pdcogs_price',
            'cogs_date'     => 'cogs_date',
            'created'       => 'created_date',
            'updated'       => 'updated_date'
        ];
        
        //start query
        $url_query          = "search={$search}&sc={$sort_col}&sb={$sort_by}";
        $search_where       = " AND (pd_cogs.pdcogs_id LIKE ? OR pd.pd_name LIKE ? OR pd_cogs.pdcogs_price LIKE ? OR pd_cogs.cogs_date LIKE ? OR pd_cogs.created_date LIKE ? OR pd_cogs.updated_date LIKE ?) ";
        $search_data        = [$search.'%', "%{$search}%", "%{$search}%", "%{$search}%", "%{$search}%", "%{$search}%"];
        $search_order       = sort_table_order($allow_sort, $sort_col, $sort_by);
        $all_data           = $this->productdb->getpaging_product_cogs(
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
            'data'          => $all_data['data'],
            'pagination'    => $this->pagination->create_links()
        ];
        $this->_render('product/product_cogs', $data);
    }
    
    public function product_cogs_add($id = 0)
    {
        //load
        $this->load->helper('date');

        // validate user roles
        $submenu_code = 'product_cogs';
        if ($id > 0) {
            $permits = $this->_check_menu_access($submenu_code, 'edit');
            $this->_set_title('Edit Product COGS');
        } else {
            $permits = $this->_check_menu_access($submenu_code, 'add');
            $this->_set_title('Add Product COGS');
        }
        $form_permit = $this->_get_form_permit($id, $permits);
        
        // validate if product exists
        if ($id > 0) {
            $product_cogs = $this->productdb->get_product_cogs($id);
            if (empty($product_cogs)) {
                redirect(ADMIN_URL);
            }
        }

        $this->load->library('form_validation');
        $this->form_validation->set_error_delimiters(PREFIX_ERROR_DELIMITER, SUFFIX_ERROR_DELIMITER);
        $this->form_validation->set_rules('pd_id', 'Product ID', 'required');
        $this->form_validation->set_rules('pdcogs_price', 'COGS', 'strip_tags|trim|required|is_natural');
        if ($id <= 0 && in_array('add', $permits)) {
            $this->form_validation->set_rules('start_date', 'Start Date', 'required');
            $this->form_validation->set_rules('end_date', 'End Date', 'required|callback__end_later_than_start');
        }

        $err_msg = [];
        if ($this->form_validation->run()) {

            if(empty($err_msg)){
                $params = [
                    'pd_id'          => $this->input->post('pd_id'),
                    'pdcogs_price'   => $this->input->post('pdcogs_price')
                ];

                if ($id > 0 && in_array('edit', $permits)) {
                    $params['updated_by']   = $this->_get_user_id();
                    $params['updated_date'] = date('Y-m-d H:i:s');
                    $product_cogs           = $this->productdb->update_product_cogs($id, $params);

                    if ($product_cogs) {
                        $err_msg = [
                            'msg'   => 'Edit Product COGS Success',
                            'type'  => 'success'
                        ];
                    } else {
                        $err_msg = [
                            'msg'   => 'Edit Product COGS Failed!',
                            'type'  => 'danger'
                        ];
                    }

                } else if ($id <= 0 && in_array('add', $permits)) {
                    $start_date             = $this->input->post('start_date');
                    $end_date               = $this->input->post('end_date');
                    $where                  = " AND pd_cogs.cogs_date >= ? AND pd_cogs.cogs_date <= ? AND pd_cogs.pd_id = ?";
                    $cogs_date              = date_range($start_date, $end_date);
                    $params['created_by']   = $this->_get_user_id();
                    $params['created_date'] = date('Y-m-d H:i:s');
                    $data                   = [
                        'start_date'        => $start_date,
                        'end_date'          => $end_date,
                        'pd_id'             => $params['pd_id']
                    ];
                    $product_cogs           = $this->productdb->getall_product_cogs($where, $data);
                    
                    if(!empty($product_cogs)){
                        $this->productdb->delete_product_cogs_perdate($data);
                    }

                    foreach ($cogs_date as $date)
                    {
                        $params['cogs_date']    = $date;
                        $product_cogs           = $this->productdb->insert_product_cogs($params); 
                    }

                    if ($product_cogs) {
                        $err_msg = [
                            'msg'   => 'Add Product COGS Success.'.js_clearform(),
                            'type'  => 'success'
                        ];
                    } else {
                        $err_msg = [
                            'msg'   => 'Add Product COGS Failed!',
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
            'current_url'   => ADMIN_URL.'product/product_cogs_add',
            'msg'           => set_form_msg($err_msg),
            'permits'       => $permits,
            'show_form'     => $form_permit['show_form'],
            'title_form'    => $form_permit['title_form'],
            'data'          => $this->productdb->get_product_cogs($id),
            'product'       => $this->productdb->getall_product()
        ];

        $this->_render('product/product_cogs_add', $data);
    }

    public function _end_later_than_start($end_date){
        $start_date = $this->input->post('start_date');
        if(!empty($end_date)){
            // if base price is not 0, validate final price must be less than base price
            if ($end_date <= $start_date) {
                $this->form_validation->set_message('_end_later_than_start', 'The End Date must be later than Start Date.');
                return false;       
            }
        }
        return true;
    }

    public function _cogs_date_check($cogs_date){
        $product_cogs = $this->productdb->get_cogs_date();
        foreach($product_cogs as $date){
            if($date->cogs_date == $cogs_date){
                $this->form_validation->set_message('_cogs_date_check', 'COGS date already to used.');
                return false;
            }
        }
        return true;
    }

    //=== END PRODUCT COGS
}

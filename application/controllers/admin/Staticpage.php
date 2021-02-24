<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Staticpage extends MY_Admin
{
    function __construct()
    {
        parent::__construct();

        $this->load->model('staticdb');
        $this->load->model('productdb');
    }
    
    public function index()
    {
        show_404();
    }

    //=== START FAQ
    public function faq()
    {
        // validasi menu dan assign title
        $submenu_code = 'static_faq';
        $permits = $this->_check_menu_access($submenu_code, 'view');

        $this->_set_title('FAQ');
        $this->load->library('pagination');
        
        //set variable
        $current_path = 'staticpage/faq';
        $current_url = ADMIN_URL.$current_path;
        $page = $this->input->get('page');
        $search = set_var($this->input->get('search'), '');
        $sort_col = set_var($this->input->get('sc'), 'order');
        $sort_by = set_var($this->input->get('sb'), 'ASC');//ASC or DESC
        $xtra_var['search'] = $search;

        //set sortable col
        $allow_sort = [
            'order' => 'faq_order',
            'id' => 'faq_id',
            'question' => 'faq_question',
            'answer' => 'faq_answer'
        ];
        
        //start query
        $url_query = "search={$search}&sc={$sort_col}&sb={$sort_by}";
        $search_where = " AND (faq_id LIKE ? OR faq_question LIKE ?) ";
        $search_data = [$search.'%', "%{$search}%", ];
        $search_order = sort_table_order($allow_sort, $sort_col, $sort_by);
        $all_data = $this->staticdb->getpaging_faq(
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
    
    public function faq_add($id = 0)
    {
        // validate user roles
        $submenu_code = 'static_faq';
        if ($id > 0) {
            $permits = $this->_check_menu_access($submenu_code, 'edit');
            $this->_set_title('Edit FAQ');
        } else {
            $permits = $this->_check_menu_access($submenu_code, 'add');
            $this->_set_title('Add FAQ');
        }
        $form_permit = $this->_get_form_permit($id, $permits);
        
        // validate if category exists
        if ($id > 0) {
            $static_faq = $this->staticdb->get_faq($id);
            if (empty($static_faq)) {
                redirect(ADMIN_URL);
            }
        }
        
        $this->load->library('form_validation');
        $this->form_validation->set_error_delimiters(PREFIX_ERROR_DELIMITER, SUFFIX_ERROR_DELIMITER);
        $this->form_validation->set_rules('faq_question', 'FAQ question', 'strip_tags|trim|required');
        $this->form_validation->set_rules('faq_answer', 'FAQ answer', 'strip_tags|trim|required');
        $this->form_validation->set_rules('faq_order', 'FAQ order', 'strip_tags|trim|required');
        
        $err_msg = [];
        if ($this->form_validation->run()) {
            $params = [
                'faq_question' => $this->input->post('faq_question'),
                'faq_answer' => $this->input->post('faq_answer'),
                'faq_order' => $this->input->post('faq_order')
            ];

            if ($id > 0 && in_array('edit', $permits)) {
                $params['updated_by'] = $this->_get_user_id();
                $params['updated_date'] = date('Y-m-d H:i:s');

                if ($this->staticdb->update_faq($id, $params)) {
                    $err_msg = [
                        'msg' => 'Edit FAQ Success',
                        'type' => 'success'
                    ];
                } else {
                    $err_msg = [
                        'msg' => 'Edit FAQ Failed!',
                        'type' => 'danger'
                    ];
                }
            } else if ($id <= 0 && in_array('add', $permits)) {
                $params['created_by'] = $this->_get_user_id();
                $params['created_date'] = date('Y-m-d H:i:s');

                if ($this->staticdb->insert_faq($params)) {
                    $err_msg = [
                        'msg' => 'Add FAQ Success.'.js_clearform(),
                        'type' => 'success'
                    ];
                } else {
                    $err_msg = [
                        'msg' => 'Add FAQ Failed!',
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

        $current_path = 'staticpage/faq_add';
        $data = [
            'current_url' => ADMIN_URL.$current_path,
            'msg' => set_form_msg($err_msg),
            'permits' => $permits,
            'show_form' => $form_permit['show_form'],
            'title_form' => $form_permit['title_form'],
            'static_faq' => $this->staticdb->get_faq($id)
        ];
        $this->_render($current_path, $data);
    }

    //>>START STATIC IMAGE<<
    public function static_image(){
        // validasi menu dan assign title
        $submenu_code = 'static_image';
        $permits      = $this->_check_menu_access($submenu_code, 'view');

        $this->_set_title('Static Image');
        $this->load->library('pagination');

        //set variable
        $current_url        = ADMIN_URL.'staticpage/static_image';
        $page               = $this->input->get('page');
        $search             = set_var($this->input->get('search'), '');
        $sort_col           = set_var($this->input->get('sc'), '');
        $sort_by            = set_var($this->input->get('sb'), 'ASC');//ASC or DESC
        $xtra_var['search'] = $search;
        $arr_admin          = $this->admindb->getarr_admin();

        //set sortable col
        $allow_sort = [
            'stat_id'       => 'stat_id',
            'stat_code'     => 'stat_code',
            'stat_title'    => 'stat_title',
            'created'       => 'created_date',
            'updated'       => 'updated_date'
        ];

        //start query
        $url_query          = "search={$search}&sc={$sort_col}&sb={$sort_by}";
        $search_where       = " AND (stat_id LIKE ? OR stat_code LIKE ? OR stat_title LIKE ? OR created_by LIKE ? OR created_date LIKE ? OR updated_by LIKE ? OR updated_date LIKE ?) ";
        $search_data        = [$search.'%', "%{$search}%", "%{$search}%", "%{$search}%" , "%{$search}%", "%{$search}%", "%{$search}%"];
        $search_order       = sort_table_order($allow_sort, $sort_col, $sort_by);
        $all_data           = $this->staticdb->getpaging_static_image(
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

        // select data & assign variable $data
        $data = [
            'current_url'   => $current_url,
            'form_url'      => $config['base_url'],
            'page_url'      => str_replace($url_query, '', $config['base_url']),
            'xtra_var'      => $xtra_var,
            'search'        => $search,
            'permits'       => $permits,
            'all_data'      => $all_data['data'],
            'pagination'    => $this->pagination->create_links(),
            'arr_admin'     => $arr_admin
        ];
        
        $this->_render('staticpage/static_image', $data);
    }

    public function static_image_add($id = 0){
        // validate user roles
        $submenu_code = 'static_image';
        if ($id > 0) {
            $permits = $this->_check_menu_access($submenu_code, 'edit');
            $this->_set_title('Edit Static Image');
        } else {
            $permits = $this->_check_menu_access($submenu_code, 'add');
            $this->_set_title('Add Static Image');
        }
        $form_permit = $this->_get_form_permit($id, $permits);

        // validate if category exists
        if ($id > 0) {
            $static_image = $this->staticdb->get_static_image($id);
            if (empty($static_image)) {
                redirect(ADMIN_URL);
            }
        }

        $this->load->library('form_validation');
        $this->form_validation->set_error_delimiters(PREFIX_ERROR_DELIMITER, SUFFIX_ERROR_DELIMITER);
        $this->form_validation->set_rules('stat_code', 'Static Code', 'strip_tags|trim|required|callback__is_unique_static_code|callback__alpha_dash');
        $this->form_validation->set_rules('stat_title', 'Static Title', 'strip_tags|trim|required');
        $this->form_validation->set_rules('stat_img', 'Image', 'callback__check_upload_image');

        $err_msg = [];
        if ($this->form_validation->run()) {
            //BEGIN UPLOAD IMAGE
            $image_name = '';
            if (is_uploaded_file($_FILES['stat_img']['tmp_name'])) {
                $this->load->library("google_cloud_bucket");
                $image_name = str_replace(UPLOAD_PATH, '', STATIC_IMAGE_PATH).$_FILES['stat_img']['name'];
                if(!@fopen(UPLOAD_URL.$image_name, 'r')){
                    $data = [
                        "source" => $_FILES['stat_img']['tmp_name'],
                        "name"   => $image_name
                    ];
                    $this->google_cloud_bucket->upload_image($data);
                }
            }
            //END UPLOAD IMAGE
            if(empty($err_msg)){
            $params = [
                'stat_code'     => strtoupper($this->input->post('stat_code')),
                'stat_title'   => $this->input->post('stat_title')
            ];
            if($image_name != ''){
                $params['stat_img']  = $image_name;
            }

            if ($id > 0 && in_array('edit', $permits)) {
                $params['updated_by'] = $this->_get_user_id();
                $params['updated_date'] = date('Y-m-d H:i:s');
                if ($this->staticdb->update_static_image($id, $params)) {
                    $err_msg = [
                        'msg' => 'Edit Static Image Success',
                        'type' => 'success'
                    ];
                }
                if ($this->staticdb->update_static_image($id, $params)) {
                    $err_msg = [
                        'msg' => 'Edit Static Image Success',
                        'type' => 'success'
                    ];
                } else {
                    $err_msg = [
                        'msg' => 'Edit Static Image Failed!',
                        'type' => 'danger'
                    ];
                }
            } else if ($id <= 0 && in_array('add', $permits)) {
                $params['created_by'] = $this->_get_user_id();
                $params['created_date'] = date('Y-m-d H:i:s');

                if ($this->staticdb->insert_static_image($params)) {
                    $err_msg = [
                        'msg' => 'Add Static Image Success.'.js_clearform(),
                        'type' => 'success'
                    ];
                } else {
                    $err_msg = [
                        'msg' => 'Add Static Image Failed!',
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
        $current_path = 'staticpage/static_image_add';
        $data = [
            'current_url' => ADMIN_URL.$current_path,
            'msg' => set_form_msg($err_msg),
            'permits' => $permits,
            'show_form' => $form_permit['show_form'],
            'title_form' => $form_permit['title_form'],
            'static_image' => $this->staticdb->get_static_image($id)
        ];
        $this->_render('staticpage/static_image_add', $data);
    }

    public function _is_unique_static_code($code){
        $id = $this->input->post('stat_id');
        // if duplicate name found and not belong to selected ID, return false
        $static_image = $this->staticdb->get_static_image_by_code($code);
        if ($static_image && $static_image->stat_id !== $id) {
            $this->form_validation->set_message('_is_unique_static_code', 'The Static Image Code field must contain a unique value.');
            return false;
        }
        return true;
    }

    public function _alpha_dash($code){
        if (!empty($code) && !preg_match('/^[a-zA-Z_]+$/', $code)) {
            $this->form_validation->set_message('_alpha_dash', 'The Static Image Code field just contain alphabet and underscore.');
            return FALSE;
        }
        return TRUE;
    }

    public function _check_upload_image(){
        $id = $this->input->post('stat_id');
        $allowed_mime_type_arr = array('image/gif','image/jpeg','image/pjpeg','image/png','image/x-png');
        $mime                  = get_mime_by_extension($_FILES['stat_img']['name']);

        if(!empty($_FILES['stat_img']['name'])){
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

    //>>END STATIC IMAGE<<
}
?>
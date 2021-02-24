<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Bank extends MY_Admin
{
    function __construct()
    {
        parent::__construct();

        $this->load->model('bankdb');
    }

    //=== START BANK
    public function index()
    {
        // validasi menu dan assign title
        $submenu_code = 'bank';
        $permits = $this->_check_menu_access($submenu_code, 'view');

        $this->_set_title('Bank');
        $this->load->library('pagination');
        
        //set variable
        $page = $this->input->get('page');
        $search = set_var($this->input->get('search'), '');
        $sort_col = set_var($this->input->get('sc'), 'id');
        $sort_by = set_var($this->input->get('sb'), 'ASC');//ASC or DESC
        $xtra_var['search'] = $search;

        //set sortable col
        $allow_sort = [
            'id' => 'bank_id',
            'code' => 'bank_code',
            'name' => 'bank_name'
        ];
        
        //start query
        $url_query = "search={$search}&sc={$sort_col}&sb={$sort_by}";
        $search_where = " AND (bank_id LIKE ? OR bank_code LIKE ? OR bank_name LIKE ? ) ";
        $search_data = [$search.'%', "%{$search}%", "%{$search}%"];
        $search_order = sort_table_order($allow_sort, $sort_col, $sort_by);
        $all_data = $this->bankdb->getpaging_bank(
            $search_where,
            $search_data,
            $search_order,
            $page
        );
        
        // start pagination setting
        $config = [
            'base_url' => ADMIN_URL.'bank'.($url_query != '' ? '?'.$url_query : ''),
            'total_rows' => $all_data['total_row'],
            'per_page' => $all_data['per_page']
        ];

        $this->pagination->initialize($config);
        // end pagination setting

        // select data & assign variable $data
        $data = [
            'form_url' => $config['base_url'],
            'page_url' => str_replace($url_query, '', $config['base_url']),
            'xtra_var' => $xtra_var,
            'search' => $search,
            'permits' => $permits,
            'all_data' => $all_data['data'],
            'pagination' => $this->pagination->create_links()
        ];
        $this->_render('bank/bank', $data);
    }

    public function bank_add($id = 0)
    {
        // validate user roles
        $submenu_code = 'bank';
        if ($id > 0) {
            $permits = $this->_check_menu_access($submenu_code, 'edit');
            $this->_set_title('Edit Bank');
        } else {
            $permits = $this->_check_menu_access($submenu_code, 'add');
            $this->_set_title('Add Bank');
        }
        $form_permit = $this->_get_form_permit($id, $permits);
        
        // validate if category exists
        if ($id > 0) {
            $static_bank = $this->bankdb->get_bank($id);
            if (empty($static_bank)) {
                redirect(ADMIN_URL);
            }
        }
        
        $this->load->library('form_validation');
        $this->form_validation->set_error_delimiters(PREFIX_ERROR_DELIMITER, SUFFIX_ERROR_DELIMITER);
        $this->form_validation->set_rules('bank_code', 'Bank Code', 'strip_tags|trim|required|callback__is_unique_bank_code');
        $this->form_validation->set_rules('bank_name', 'Bank Name', 'strip_tags|trim|required|callback__is_unique_bank_name');
        $this->form_validation->set_rules('bank_guidances', 'Bank Guidances', 'strip_tags|trim|required|callback__is_json');
        $this->form_validation->set_rules('bank_img', 'Bank Image', 'callback__check_upload_image'); 

        $err_msg = [];
        if ($this->form_validation->run()) {
            //BEGIN UPLOAD IMAGE
            $image_name = '';
            if (is_uploaded_file($_FILES['bank_img']['tmp_name'])) {
                // your code here
                $this->load->library("google_cloud_bucket");
                $image_name = str_replace(UPLOAD_PATH, '', BANK_IMAGE_PATH).$_FILES['bank_img']['name'];

                if(!@fopen(UPLOAD_URL.$image_name, 'r')){
                    $data = [
                        "source" => $_FILES['bank_img']['tmp_name'],
                        "name"   => $image_name
                    ];
                    
                    $this->google_cloud_bucket->upload_image($data);
                }
            }
            //END UPLOAD IMAGE

            if(empty($err_msg)){
                $params = [
                    'bank_code' => $this->input->post('bank_code'),
                    'bank_name' => $this->input->post('bank_name'),
                    'bank_guidances' => $this->input->post('bank_guidances')
                ];

                if($image_name != ''){
                    $params['bank_img']  = $image_name;
                }
    
                if ($id > 0 && in_array('edit', $permits)) {
                    $params['updated_by'] = $this->_get_user_id();
                    $params['updated_date'] = date('Y-m-d H:i:s');
    
                    if ($this->bankdb->update_bank($id, $params)) {
                        $err_msg = [
                            'msg' => 'Edit Bank Success',
                            'type' => 'success'
                        ];
                    } else {
                        $err_msg = [
                            'msg' => 'Edit Bank Failed!',
                            'type' => 'danger'
                        ];
                    }
                } else if ($id <= 0 && in_array('add', $permits)) {
                    $params['created_by'] = $this->_get_user_id();
                    $params['created_date'] = date('Y-m-d H:i:s');
    
                    if ($this->bankdb->insert_bank($params)) {
                        $err_msg = [
                            'msg' => 'Add Bank Success.'.js_clearform(),
                            'type' => 'success'
                        ];
                    } else {
                        $err_msg = [
                            'msg' => 'Add Bank Failed!',
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
        $static_bank = $this->bankdb->get_bank($id);
        $data = [
            'current_url' => ADMIN_URL.'bank/bank_add',
            'msg' => set_form_msg($err_msg),
            'permits' => $permits,
            'show_form' => $form_permit['show_form'],
            'title_form' => $form_permit['title_form'],
            'static_bank' => $static_bank
        ];
        $this->_render('bank/bank_add', $data);
    }
    //=== END BANK

    public function _check_upload_image()
    {
        $id = $this->input->post('bank_id');
        $allowed_mime_type_arr = array('image/gif','image/jpeg','image/pjpeg','image/png','image/x-png');
        $mime = get_mime_by_extension($_FILES['bank_img']['name']);
        if(!empty($_FILES['bank_img']['name'])){
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

    public function _is_json()
    {
        $guidances = json_decode($this->input->post('bank_guidances'), true);
        if($guidances == null){
            $this->form_validation->set_message('_is_json', 'Content must be a json type');
            return FALSE;
        }
        return TRUE;
    }

    public function _is_unique_bank_code($bank_code)
    {
        $id = $this->input->post('bank_id');
        // if duplicate name found and not belong to selected ID, return false
        $bank = $this->bankdb->get_bank_custom_filter(' AND bank_id != ? AND bank_code = ?', [$id, $bank_code]);
        if ($bank) {
            $this->form_validation->set_message('_is_unique_bank_code', 'The Bank Code field must contain a unique value.');
            return false;
        }
        return true;
    }
    
    public function _is_unique_bank_name($bank_name)
    {
        $id = $this->input->post('bank_id');
        // if duplicate name found and not belong to selected ID, return false
        $bank = $this->bankdb->get_bank_custom_filter(' AND bank_id != ? AND bank_name LIKE ?', [$id, "%".$bank_name."%"]);
        if ($bank) {
            $this->form_validation->set_message('_is_unique_bank_name', 'The Bank Name field must contain a unique value.');
            return false;
        }
        return true;
    }

}    
?>
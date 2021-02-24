<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Appversion extends MY_Admin
{
    function __construct()
    {
        parent::__construct();

        $this->load->model('appversiondb');
    }

    //=== START APP_VERSION
    public function index()
    {
        // validasi menu dan assign title
        $submenu_code = 'app_version';
        $permits = $this->_check_menu_access($submenu_code, 'view');

        $this->_set_title('App Version');
        $this->load->library('pagination');
        
        //set variable
        $current_url = ADMIN_URL.'appversion/appversion';
        $page = $this->input->get('page');
        $search = set_var($this->input->get('search'), '');
        $sort_col = set_var($this->input->get('sc'), 'ver_code');
        $sort_by = set_var($this->input->get('sb'), 'DESC');//ASC or DESC
        $xtra_var['search'] = $search;

        //set sortable col
        $allow_sort = [
            'id'        => 'ver_id',
            'code'      => 'ver_code',
            'platform'  => 'ver_platform',
            'status'    => 'ver_status',
            'by'        => 'created_by',
            'created'   => 'created_date',
            'upby'      => 'updated_by',
            'updated'   => 'updated_date',
        ];

        $arr_admin      = $this->admindb->getarr_admin();
        //start query
        $url_query = "search={$search}&sc={$sort_col}&sb={$sort_by}";
        $search_where = " AND (ver_id LIKE ? OR ver_code LIKE ? OR ver_platform LIKE ? OR ver_status LIKE ? OR created_by LIKE ? OR updated_by LIKE ?) ";
        $search_data = [$search.'%', "%{$search}%", "%{$search}%", "%{$search}%", "%{$search}%", "%{$search}%"];
        $search_order = sort_table_order($allow_sort, $sort_col, $sort_by);
        $all_data = $this->appversiondb->getpaging_app_version(
            $search_where,
            $search_data,
            $search_order,
            $page
        );
        
        // start pagination setting
        $config = [
            'base_url' => ADMIN_URL.'appversion'.($url_query != '' ? '?'.$url_query : ''),
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
            'arr_admin' => $arr_admin,
            'pagination' => $this->pagination->create_links()
        ];
        $this->_render('appversion/appversion', $data);
    }

    public function appversion_add($id = 0)
    { 
        // validate app_version roles
        $submenu_code = 'app_version';
        if ($id > 0) {
            $permits = $this->_check_menu_access($submenu_code, 'edit');
            $this->_set_title('Edit App Version');
        } else {
            $permits = $this->_check_menu_access($submenu_code, 'add');
            $this->_set_title('Add App Version');
        }
        $form_permit = $this->_get_form_permit($id, $permits);
        
        // validate if app_version exists
        if ($id > 0) {
            $static_app_version = $this->appversiondb->get_app_version($id);
            if (empty($static_app_version)) {
                redirect(ADMIN_URL);
            }
        }
        
        //set form validation
        $this->load->library('form_validation');
        $this->form_validation->set_error_delimiters(PREFIX_ERROR_DELIMITER, SUFFIX_ERROR_DELIMITER);
        $this->form_validation->set_rules('ver_code', 'Version Code', 'strip_tags|trim|required|callback__is_unique_ver_code');
        $this->form_validation->set_rules('ver_platform', 'Version Platform', 'strip_tags|trim|required|callback__is_unique_ver_platform');
        $this->form_validation->set_rules('ver_status', 'Version Status', 'strip_tags|trim|required'); 

        //run form validation
        $err_msg = [];
        if ($this->form_validation->run()) {
            $params = [
                'ver_code' => $this->input->post('ver_code'),
                'ver_platform' => $this->input->post('ver_platform'),
                'ver_status' => $this->input->post('ver_status')
            ];

            if ($id > 0 && in_array('edit', $permits)) {
                $params['updated_by'] = $this->_get_user_id();
                $params['updated_date'] = date('Y-m-d H:i:s');

                if ($this->appversiondb->update_app_version($id, $params)) {
                    $err_msg = [
                        'msg' => 'Edit App Version Success',
                        'type' => 'success'
                    ];
                } else {
                    $err_msg = [
                        'msg' => 'Edit App Version Failed!',
                        'type' => 'danger'
                    ];
                }
            } else if ($id <= 0 && in_array('add', $permits)) {
                $params['created_by'] = $this->_get_user_id();
                $params['created_date'] = date('Y-m-d H:i:s');

                if ($this->appversiondb->insert_app_version($params)) {
                    $err_msg = [
                        'msg' => 'Add App Version Success.'.js_clearform(),
                        'type' => 'success'
                    ];
                } else {
                    $err_msg = [
                        'msg' => 'Add App Version Failed!',
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
        $static_app_version = $this->appversiondb->get_app_version($id);

        $data = [
            'current_url' => ADMIN_URL.'appversion/appversion_add',
            'msg' => set_form_msg($err_msg),
            'permits' => $permits,
            'show_form' => $form_permit['show_form'],
            'title_form' => $form_permit['title_form'],
            'cst_version' => $this->config->item('appversion'),
            'appversion' => $static_app_version
        ];
        
        $this->_render('appversion/appversion_add', $data);
    }

    public function _is_unique_ver_code($ver_code)
    {
        $ver_platform = $this->input->post('ver_platform');
        $id = $this->input->post('ver_id');
        // if duplicate name found and not belong to selected ver_code, return false
        $appversion = $this->appversiondb->get_version_by_code_platform($ver_code, $ver_platform);
        if ($appversion && $appversion->ver_id !== $id) {
            $this->form_validation->set_message('_is_unique_ver_code', 'The App Version Code field must contain a unique value.');
            return false;
        }
        return true;
    }

    public function _is_unique_ver_platform($ver_platform)
    {
        $ver_code = $this->input->post('ver_code');
        $id = $this->input->post('ver_id');
        // if duplicate name found and not belong to selected ver_code, return false
        $appversion = $this->appversiondb->get_version_by_code_platform($ver_code, $ver_platform);
        if ($appversion && $appversion->ver_id !== $id) {
            $this->form_validation->set_message('_is_unique_ver_platform', 'The App Version Platform field must contain a unique value.');
            return false;
        }
        return true;
    }
    //=== END APP_VERSION 

}    
?>
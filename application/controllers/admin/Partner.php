<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Partner extends MY_Admin
{
    function __construct()
    {
        parent::__construct();
        
        $this->load->model('partnerdb');
    }
    

    // START PROMO
    public function index()
    {
        // validasi menu dan assign title
        $submenu_code = 'partner';
        $permits = $this->_check_menu_access($submenu_code, 'view');

        $this->_set_title('Partner');
        $this->load->library('pagination');
        
        //set variable
        $page = $this->input->get('page');
        $search = set_var($this->input->get('search'), '');
        $sort_col = set_var($this->input->get('sc'), 'id');
        $sort_by = set_var($this->input->get('sb'), 'ASC');//ASC or DESC
        $xtra_var['search'] = $search;

        //set sortable col
        $allow_sort = [
            'id'            => 'ptr_id',
            'name'          => 'ptr_name',
            'code'          => 'ptr_code',
            'token'         => 'ptr_token',
            'desc'          => 'ptr_desc',
            'created_by'    => 'created_by',
            'created_date'  => 'created_date',
            'updated_by'    => 'updated_by',
            'updated_date'  => 'updated_date'
        ];
        
        $arr_admin      = $this->admindb->getarr_admin();
        
        //start query
        $url_query = "search={$search}&sc={$sort_col}&sb={$sort_by}";
        $search_where = " AND (ptr_id LIKE ? OR ptr_name LIKE ? OR ptr_code LIKE ? OR ptr_token LIKE ? OR ptr_desc LIKE ? OR created_by LIKE ? OR created_date LIKE ? OR updated_by LIKE ? OR updated_date LIKE ?)  ";
        $search_data = [$search.'%', "%{$search}%", "%{$search}%", "%{$search}%", "%{$search}%", "%{$search}%", "%{$search}%", "%{$search}%", "%{$search}%"];
        $search_order = sort_table_order($allow_sort, $sort_col, $sort_by);
        $all_data = $this->partnerdb->getpaging_partner(
            $search_where,
            $search_data,
            $search_order,
            $page
        );
        
        // start pagination setting
        $config = [
            'base_url' => ADMIN_URL.'partner'.($url_query != '' ? '?'.$url_query : ''),
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
            'cst_status' => $this->config->item('partner')['status'],
            'all_data' => $all_data['data'],
            'arr_admin' => $arr_admin,
            'pagination' => $this->pagination->create_links()
        ];
        $this->_render('partner/partner', $data);
    }

    public function _is_unique_partner_code($code){
        $id = $this->input->post('ptr_id');
        // if duplicate name found and not belong to selected ID, return false
        $partner = $this->partnerdb->get_partner_by_code($code);
        if ($partner && $partner->ptr_id !== $id) {
            $this->form_validation->set_message('_is_unique_partner_code', 'The Partner code field must contain a unique value.');
            return false;
        }
        return true;
    }

    public function partner_add($id = 0)
    {
        // validate user roles
        $submenu_code = 'partner';
        if ($id > 0) {
            $permits = $this->_check_menu_access($submenu_code, 'edit');
            $this->_set_title('Edit Partner');
        } else {
            $permits = $this->_check_menu_access($submenu_code, 'add');
            $this->_set_title('Add Partner');
        }
        $form_permit = $this->_get_form_permit($id, $permits);
        
        // validate if category exists
        if ($id > 0) {
            $partner = $this->partnerdb->get_partner($id);
            if (empty($partner)) {
                redirect(ADMIN_URL);
            }
        }
        
        $this->load->library('form_validation');
        $this->form_validation->set_error_delimiters(PREFIX_ERROR_DELIMITER, SUFFIX_ERROR_DELIMITER);
        $this->form_validation->set_rules('ptr_name', 'Partner name', 'strip_tags|trim|required');
        $this->form_validation->set_rules('ptr_code', 'Partner code', 'strip_tags|trim|required|callback__is_unique_partner_code');
        $this->form_validation->set_rules('ptr_token', 'Partner token', 'strip_tags|trim|required');
        $this->form_validation->set_rules('ptr_desc', 'Partner description', 'strip_tags|trim');
        
        $err_msg = [];
        if ($this->form_validation->run()) {
            $params = [
                'ptr_name' => $this->input->post('ptr_name'),
                'ptr_code' => strtoupper($this->input->post('ptr_code')),
                'ptr_token' => $this->input->post('ptr_token'),
                'ptr_desc' => $this->input->post('ptr_desc')
            ];

            if ($id > 0 && in_array('edit', $permits)) {
                $params['updated_by'] = $this->_get_user_id();
                $params['updated_date'] = date('Y-m-d H:i:s');

                if ($this->partnerdb->update_partner($id, $params)) {
                    $err_msg = [
                        'msg' => 'Edit Partner Success',
                        'type' => 'success'
                    ];
                } else {
                    $err_msg = [
                        'msg' => 'Edit Partner Failed!',
                        'type' => 'danger'
                    ];
                }
            } else if ($id <= 0 && in_array('add', $permits)) {
                $params['created_by'] = $this->_get_user_id();
                $params['created_date'] = date('Y-m-d H:i:s');

                if ($this->partnerdb->insert_partner($params)) {
                    $err_msg = [
                        'msg' => 'Add Partner Success.'.js_clearform(),
                        'type' => 'success'
                    ];
                } else {
                    $err_msg = [
                        'msg' => 'Add Partner Failed!',
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

        $current_path = 'partner/partner_add';
        $data = [
            'current_url' => ADMIN_URL.$current_path,
            'msg' => set_form_msg($err_msg),
            'permits' => $permits,
            'show_form' => $form_permit['show_form'],
            'title_form' => $form_permit['title_form'],
            'partner' => $this->partnerdb->get_partner($id)
        ];
        $this->_render($current_path, $data);
    }

    public function partner_promo($id = 0)
    {
        $this->load->model('admindb');

        $submenu_code = 'partner';
        $permits = $this->_check_menu_access($submenu_code, 'view');
        $form_permit = $this->_get_form_permit($id, $permits);

        $this->_set_title('Partner Promo');
        $this->load->library('pagination');

        //set variable
        $page = $this->input->get('page');
        $search = set_var($this->input->get('search'), '');
        $sort_col = set_var($this->input->get('sc'), 'id');
        $sort_by = set_var($this->input->get('sb'), 'ASC');//ASC or DESC
        $xtra_var['search'] = $search;

        $arr_admin      = $this->admindb->getarr_admin();

        //set sortable col
        $allow_sort = [
            'id' => 'prm.prm_id',
            'name' => 'prm.prm_name',
            'code' => 'prm.prm_custom_code',
            'start' => 'prm.prm_start',
            'type' => 'prm.prm_type',
            'status' => 'prm.prm_status',
            'by' => 'prm.created_by',
            'date' => 'prm.created_date'
        ];

        // validate partner
        $partner = $this->partnerdb->get_partner($id);
        if (!$partner) {
            redirect(ADMIN_URL);
        }
        
        //start query
        $url_query = "sc={$sort_col}&sb={$sort_by}";
        $search_where = " AND ptrpm.ptr_id = ? ";
        $search_order = sort_table_order($allow_sort, $sort_col, $sort_by);
        $all_data = $this->partnerdb->getpaging_partner_promo(
            $search_where,
            [$id],
            $search_order,
            $page
        );

        // start pagination setting
        $config = [
            'base_url' => ADMIN_URL.'partner/partner_promo/'.$id.($url_query != '' ? '?'.$url_query : ''),
            'total_rows' => $all_data['total_row'],
            'per_page' => $all_data['per_page']
        ];

        $this->pagination->initialize($config);
        // end pagination setting

        $data = [
            'current_url' => ADMIN_URL.'partner/partner_promo',
            'form_url' => $config['base_url'],
            'page_url' => str_replace($url_query, '', $config['base_url']),
            'permits' => $permits,
            'xtra_var' => $xtra_var,
            'cst_status' => $this->config->item('promo')['status'],
            'partner' => $partner,
            'show_form' => $form_permit['show_form'],
            'title_form' => $form_permit['title_form'],
            'all_data' => $all_data['data'],
            'arr_admin' => $arr_admin,
            'pagination' => $this->pagination->create_links()
        ];
        $this->_render('partner/partner_promo', $data);
    }

    public function promo_add($id = 0)
    {
        $this->load->model('promodb');

        // validate user roles
        $submenu_code = 'partner';
        $permits = $this->_check_menu_access($submenu_code, 'add');
        $this->_set_title('Add Promo Partner');
        $form_permit = $this->_get_form_permit(0, $permits);
        
        $partner = $this->partnerdb->get_partner($id);
        if (!$partner) {
            redirect(ADMIN_URL);
        }

        $where = " AND prm_status = ? AND prm_custom_code NOT LIKE ? AND prm_custom_code NOT LIKE ?";
        $data = ['active', 'REG%', 'REF%'];
        $promo = $this->promodb->getall_promo($where, $data);
        
        $this->load->library('form_validation');
        $this->form_validation->set_error_delimiters(PREFIX_ERROR_DELIMITER, SUFFIX_ERROR_DELIMITER);
        $this->form_validation->set_rules('prm_id', 'Promo', 'required|integer');
        $err_msg = [];
        if ($this->form_validation->run()) {
            $params = [
                'ptr_id' => $id,
                'prm_id' => $this->input->post('prm_id'),
                'created_date' => date('Y-m-d H:i:s')
            ];

            if ($this->partnerdb->insert_partner_promo($params)) {
                $err_msg = [
                    'msg' => 'Add Partner Promo Success.'.js_clearform(),
                    'type' => 'success'
                ];
            } else {
                $err_msg = [
                    'msg' => 'Add Partner Promo Failed!',
                    'type' => 'danger'
                ];
            }
        }

        $data = [
            'current_url' => ADMIN_URL.'partner/promo_add',
            'msg' => set_form_msg($err_msg),
            'permits' => $permits,
            'show_form' => $form_permit['show_form'],
            'title_form' => $form_permit['title_form'],
            'promo' => $promo,
            'partner' => $partner
        ];
        $this->_render('partner/promo_add', $data);
    }
    // END PROMO
}

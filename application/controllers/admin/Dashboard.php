<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends MY_Admin {

    function __construct()
	{
        parent::__construct();        
	}
    
    public function index(){

        $this->load->model('reportdb');

        //SET VARIABLE
        $product_url                = ADMIN_URL.'product/store_product';
        $cst_status                 = $this->config->item('order')['status'];
        $cst_delivery_type          = $this->config->item('order')['delivery_type'];
        $cst_stpd_status            = $this->config->item('store_product')['storepd_status'];

        //ADDITIONAL FILTER
        $data_total             = ["date" => '%'.date('Y-m-d').'%', "status" => $cst_status['completed'], "stpd_status" => $cst_stpd_status['out_of_stock']];

        //CEK USER ID AND STORE ID
        $user = $this->admindb->get_admin($this->_get_user_id());
        
        if($user->st_id > 0) {
            $data_total["st_id"]    = $user->st_id;
        }

        $data_total_paid        = $data_total;
        $data_total_pickup      = $data_total;
        $data_total_delivery    = $data_total;
        $data_total_paid["status"]              = $cst_status['paid'];
        $data_total_paid["delivery_type"]       = $cst_delivery_type['delivery'];
        $data_total_pickup["status"]            = $cst_status['ready_for_pickup'];
        $data_total_pickup["delivery_type"]     = $cst_delivery_type['pickup'];
        $data_total_delivery["status"]          = $cst_status['on_delivery'];
        $data_total_delivery["delivery_type"]   = $cst_delivery_type['delivery'];

        //START QUERY
        $total_order                = $this->reportdb->get_total_order($data_total); 
        $total_paid                 = $this->reportdb->get_total_order($data_total_paid);
        $pickup_uncompleted         = $this->reportdb->get_total_order($data_total_pickup);
        $delivery_uncompleted       = $this->reportdb->get_total_order($data_total_delivery);
        $list_store_out_of_stock    = $this->reportdb->get_store_out_of_stock($data_total);

        // SELECT DATA & ASSIGN VARIABLE $DATA
        $data['product_url']             = $product_url;
        $data['total_order']             = $total_order->total;
        $data['total_paid']              = $total_paid->total;
        $data['total_pickup']            = $pickup_uncompleted->total;
        $data['total_delivery']          = $delivery_uncompleted->total;
        $data['list_store_out_of_stock'] = $list_store_out_of_stock;
        
        $this->_render('home/home', $data);
    }
    
}

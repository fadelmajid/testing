<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Home extends MY_Admin {

    function __construct()
	{
		parent::__construct();
        
	}
    
    public function noaccess()
    {
        $submenu_id = $this->input->get('sub');
        $this->_set_title('No Access!');
        
        $submenu = $this->admindb->get_submenu($submenu_id);
        if(empty($submenu)){
            $data['msg'] = 'Anda tidak memiliki akses ke menu tersebut';
        }else{
            $data['msg'] = 'Anda tidak memiliki akses ke menu '. $submenu->menu_name.' > '. $submenu->submenu_name .'';
        }
        
        $this->_render('home/noaccess', $data);
    }
}

<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Welcome extends MY_Admin {

    function __construct()
	{
		parent::__construct();
        
	}
    
	public function index()
	{
		$data = array();
		redirect(BASE_URL.'dashboard');
	}
}

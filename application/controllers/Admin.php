<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin extends CI_Controller {

	
	function __construct(){

		parent::__construct();
		
		$this->load->model('Admin_model');

	}//end of function


	public function index()
	{		
		
		$data = array();
		
		$results = $this->Admin_model->get_all_field_info();
		
		$data['results'] = $results;

		$this->load->view('admin_home_view', $data);		

	}//end of function
	
	
	
	public function edit($fid){
		
		$data = array();
		
		$this->load->view('edit_field_view', $data);
		
	}//end of function
	
	
}
?>

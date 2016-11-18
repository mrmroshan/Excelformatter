<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Templates extends CI_Controller {

	
	function __construct(){

		parent::__construct();
		$config['upload_path']          = './uploads/';
        $config['allowed_types']        = 'xls|xlsx|csv';
        //$config['max_size']             = 1000;
        //$config['max_width']            = 1024;
        //$config['max_height']           = 768;
		$this->load->library('upload', $config);

		//load our new PHPExcel library
		$this->load->library('excel');
		$this->load->model('ExcelFormatter_model');

	}//end of function


		
	public function index(){

		$data = array();

		for ($i = 1 ; $i<=40; $i++){
			$data['all_columns'][] = array('col'.$i => 'Column'.$i);
		}

		$this->load->view('template_index_view', $data);
	}//end of function
	
}
?>

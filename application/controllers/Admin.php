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



	public function edit($fid=0){


		$data = array();

		$results = $this->Admin_model->get_field_info($fid);

		$post = $this->input->post();

		if(!empty($post)){
				
			//echo '<pre>';var_dump($post);exit;
				
			$data = array();
				
			$data['FIELD_ID'] = $fid;
			$data['FIELD_LABEL'] = $post['FIELD_LABEL'];
			$data['FIELD_INDEX'] = $post['FIELD_INDEX'];
			$data['MAXCHARS'] = $post['MAXCHARS'];
			$data['SOAP_FIELD'] = $post['SOAP_FIELD'];
			$data['DATATYPE'] = $post['DATATYPE'];
			$data['REGXPATTERN'] = $post['REGXPATTERN'];
			$data['MAPPED_COL_NAMES'] = $post['MAPPED_COL_NAMES'];
			$data['REQUIRED'] = (!empty($post['REQUIRED']))?1:0;
			$data['CATEGORY_ID'] = 1;
			$data['STATUS'] = (!empty($post['STATUS']))?1:0;
				
			$update_status = $this->Admin_model->update_field($data);
				
			$results = $this->Admin_model->get_field_info($fid);
				
		}//end if

		if(!empty($results)){
				
			$data['field_info'] = $results[0];
				
		}else{
				
			$this->session->set_flashdata(
					'error',
					'No data found!');
			redirect('admin/index', 'refresh');
				
		}

		$this->load->view('edit_field_view', $data);

	}//end of function


}
?>

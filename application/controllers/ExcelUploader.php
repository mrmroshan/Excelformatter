<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * This is the controller which does the wizard type template creation and data mapping
 * for excel sheets
 * 
 * @author Roshan.Ruzik
 * @created on 22-11-2016
 *
 */
class ExcelUploader extends CI_Controller {

	private $debug = true;
	private $selected_col_list = null;
	//private $up_excel_sheet_data = array();
	//private $up_excel_sheet_cols = array();
		
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
	
	public function unset_sessions(){
		
		$array_items = array('excel_data_array', 'template_col_list_array','up_file_col_list_array');		
		$this->session->unset_userdata($array_items);
	}

	private function check_sessions($step){
		
		if(empty($this->session->userdata('template_col_list_array')) and 
				empty($this->session->userdata('excel_data_array'))and 
				empty($this->session->userdata('up_file_col_list_array'))){
			
				$this->session->set_flashdata(
						'error',
						'Session Expired! Please start over again.');
				redirect('ExcelUploader/index', 'refresh');
				
			
		}
		
		switch($step){
			case 'first':
				break;
			case 'second':
				if(empty($this->session->userdata('template_col_list_array'))){
					
					$this->session->set_flashdata(
							'error',
							'Please select the template fields first before 
							prceeding with other steps!');
					redirect('ExcelUploader/index', 'refresh');
				}
				break;
			case 'third':
				
				if(empty($this->session->userdata('excel_data_array'))){
						
					$this->session->set_flashdata(
							'error',
							'Please upload your Excel file first before
							prceeding with other steps!');
										
					redirect('ExcelUploader/upload_excel_file', 'refresh');
				}
				break;
			case 'four':
				break;
					
		}//end switch
		
	}//end of function

	/**
	 * Index()
	 * 
	 * This function lists the column listings to prepare the template column list
	 */	
	public function index(){
		
		//$this->upload_excel_file();
		redirect('ExcelUploader/wizard', 'refresh');
		
	}//end of function 
	
	
	public function creat_template(){
		
		$data = array();
		$data['all_columns'] = array(

			'Srl.No' => 'Srl.No',		
			'Bill'=> 'Bill',	
			'Cutomer A/c#' => 'Cutomer A/c#',
			'Pickup Number' => 'Pickup Number',
			'Cnee Name(100)' => 'Cnee Name(100)',
			'Company Name(100)' => 'Company Name(100)',
			'Valid ID' => 'Valid ID',
			'Address(250)'=> 'Address(250)',
			'USER_ID'=>'USER_ID',
			'Desciption of Goods(100)'=>'Desciption of Goods(100)',
			'Pcs'=>'Pcs',
			'Wt'=>'Wt',
			'Calling'=>'Calling',
			'ROUNDTRIP'=>'ROUNDTRIP',
			'COD'=>'COD',
			'COD Currency'=>'COD Currency',
			'Cost of Goods'=>'Cost of Goods',
			'COG Currency'=>'COG Currency',
			'jcs no' => 'jcs no',	
			'Ref1(50)' => 'Ref1(50)',
			'Ref2(50)' => 'Ref2(50)', 			
			'Cnee Country Name'=>'Cnee Country Name',	
			'Cnee Country Code'=> 'Cnee Country Code',	
			'Cnee Province Name'=>'Cnee Province Name',	
			'Cnee Province Code'=>'Cnee Province Code',	
			'Cnee City Name'=>'Cnee City Name',	
			'Cnee City Code'=>'Cnee City Code',	
			'Cnee Area'=>'Cnee Area',	
			'Cnee Area Code'=>'Cnee Area Code',	
			'Cnee Pin Code'=>'Cnee Pin Code',	
			'Email1(100)'=>'Email1(100)',	
			'Email2(100)'=>'Email2(100)',	
			'Service Name'=>'Service Name',	
			'Service ID'=>'Service ID',				
			'Description2(100)'=>'Description2(100)',				
			'Del_Date'=>'Del_Date',	
			'Del_Time'=>'Del_Time',	
			'Note1(250)'=>'Note1(250)',	
			'Note2(250)'=>'Note2(250)',	
			'Note3(250)'=> 'Note3(250)',	
			'Note4(250)'=> 'Note4(250)',	
			'Note5(250)'=>'Note5(250)',	
			'Note6(250)'=>'Note6(250)',	
			'Autodialer/TelMobile(Phone1)'=>'Autodialer/TelMobile(Phone1)',	
			'(TelHome)Phone2'=>'(TelHome)Phone2',	
			'(WhatsApp)Phone3'=>'(TelHome)Phone2',
			'(TelWork)Phone4'=>'(TelWork)Phone4',	
			'Phone5'=>'Phone5',	
			'Phone6'=>'Phone6',	
			'Source Station'=>'Source Station',	
			'Destination Station'=>'Destination Station', 	
			'INSURED'=>'INSURED',	
			'SHIPMENT NAME'=>'SHIPMENT NAME',	
			'SHIPMENT CODE'=>'SHIPMENT CODE'		
			);

			$data['required_columns'] = array(
				'AIRWAY'=>'AIRWAY',
			);

		$post = $this->input->post();
		
		if(!empty($post['selected_col_list'])){
				
			//$this->selected_col_list = $post['selected_col_list'];
			
			$col_list_str = urldecode($post['selected_col_list']);
			
			$col_list_array = explode('---',$col_list_str);
			
			array_pop ($col_list_array);//remove last element
			
			$i = 0;
			
			$template_col_list = array();
			
			foreach($col_list_array as $col){
				
				$template_col_list[] = $col;
				
			}//end foreach
					
			$this->set_template_col_list_in_session($template_col_list);
			
			if($this->debug)log_message('debug','index():$template_col_list :'. print_r($template_col_list,true));
			
			$this->upload_excel_file();
				
		}else{
			
			$this->load->view('template_index_view', $data);
		}
		
	}//end of function

	
	public function wizard(){
		
		$post_data = $this->input->post();
		$step = (!empty($post_data['step']))?$post_data['step']:'upload';
			
		switch($step){
			case "template_col_select":
				$this->index();
				break;
			case "upload":
				$this->upload_excel_file();
				break;
			case 'mapping':
				//redirect('ExcelUploader/upload_excel_file', 'refresh');
				//$this->map_fields();
				$this->map_field_dropdowns();
				break;
			case 'validate':
				$this->validate_data();
				break;
						
				
		}//endswitch
		
		
	}//end of function
	
	
	/**
	 * upload_excel()
	 * 
	 * This function will upload the excel file to the system.
	 * Then prepares its column listing in an array. 
	 */
	public function upload_excel_file(){
				
		$file_data = array();
		
		$file_data['error'] = null;
		
		$file_element = (!empty($_FILES['userfile']['name']))?$_FILES['userfile']:null;	
		
						
		if(!empty($file_element)){
			
					
			$file_data = $this->upload();
			
			if(!array_key_exists('error',$file_data)){
				
				$this->extract_excel_file_data($file_data['upload_data']);
				
				if($this->debug)log_message('debug','upload_excel_file():$file_data:'. print_r($file_data,true));
				
				//$this->map_fields();
				$this->map_field_dropdowns();
				
				
			}else{
				
				$err_msg = trim($file_data['error'],'<p>');
				
				$err_msg = trim($err_msg,'</p>');
				
				$this->session->set_flashdata('error',$err_msg);
			}
			
		}else{			
			
			//$this->session->set_flashdata('error',"Please upload");	
			$this->load->view('file_upload_view', $file_data);			
		}	
		
	}//end of function
	
	
	
	
	public function map_fields(){
	
		//$this->check_sessions('third');
	
		$data = array();
	
		$up_file_col_list = $this->get_uploaded_file_col_list();
	
		$data['up_file_col_list'] = $up_file_col_list;
	
		if($this->debug)log_message('debug','map_fields():$up_file_col_list:'. print_r($up_file_col_list,true));
	
		$all_field_list = $this->get_all_fields();
	
		$data['all_field_list'] = $all_field_list;
	
		if($this->debug)log_message('debug','map_fields():$all_field_list:'. print_r($all_field_list,true));
				
		$this->load->view('map_fields_view', $data);
	
	}//end of function
	
	public function map_field_dropdowns(){
	
		$data = array();
	
		$up_file_col_list = $this->get_uploaded_file_col_list();
	
		$data['up_file_col_list'] = $up_file_col_list;
	
		if($this->debug)log_message('debug','map_field_dropdowns():$up_file_col_list:'. print_r($up_file_col_list,true));
	
		$all_field_list_array = $this->get_all_fields_array();
	
		$data['all_field_list_array'] = $all_field_list_array;
	
		if($this->debug)log_message('debug','map_fields():$all_field_list_array:'. print_r($all_field_list_array,true));
	
		$this->load->view('map_fields_combo_view', $data);
	
	}//end of function
	
	

	public function validate_data(){
		
		$post_data = $this->input->post();
		foreach($post_data as $field){
			$$field[] = $field;
		}
		$this->dump_data($field);
		
	}//end of function
	
	
	
	
	public function preview_data(){
		
		//$this->check_sessions('third');
		
		$post_data = $this->input->post();
		
		$data = array();
		
		if(!empty($post_data['template_col_list'])){
				
			$template_col_list = $post_data['template_col_list'];
				
			$up_file_new_col_list_order = $post_data['up_file_col_list'];
				
			if($this->debug)log_message('debug','map_fields():$up_file_new_col_list_order:'. print_r($up_file_new_col_list_order,true));
				
			$new_up_col_index_array = $this->get_clean_col_list_array($up_file_new_col_list_order);
				
			if($this->debug)log_message('debug','map_fields():$new_up_col_index_array:'. print_r($new_up_col_index_array,true));
				
			$original_up_file_data = $this->get_excel_file_data_from_session();
				
			$new_data_array = array();				
				
			foreach($original_up_file_data as $rows=>$cols){
				
				foreach($new_up_col_index_array as $new_index){
					
					$new_data_array[$rows][]=$original_up_file_data[$rows][$new_index];
					
				}//end foreach
			}
			//var_dump($new_data_array);
			
			$data['new_data_array'] = $new_data_array;
			
			$data['original_up_file_data'] = $original_up_file_data;			
			
			$this->load->view('preview_view', $data);
		}//end if
		
		
	}//end of function
	
	
	/**
	 * upload()
	 * 
	 * This function uploads the given file and store in the system
	 * 
	 * @return array file data
	 */
	private function upload(){	
	
		$upload_feedback = $this->upload->do_upload('userfile');
	
		//var_dump($this->upload->data()); //check for file mime type
	
		if(!$upload_feedback ){
	
			$data = array('error'=> $this->upload->display_errors());
			$this->session->set_flashdata(
					'error',
					$this->upload->display_errors());
	
		}else{
	
			$data = array('upload_data' => $this->upload->data());
		}
	
		return $data;
		 
	}//end of function
	
	
	/**
	 * extract_excel_file_data()
	 *
	 * This function extracts all data in the excel file
	 *
	 * @param unknown $excel_file
	 */
	private function extract_excel_file_data($excel_file){
	
		$excel_data_array = array();
	
		// Identify the type of $inputFileName
		$inputFileType = PHPExcel_IOFactory::identify($excel_file['full_path']);
	
		// Create a new Reader of the type that has been identified
		$objReader = PHPExcel_IOFactory::createReader($inputFileType);
	
		// Load $inputFileName to a PHPExcel Object
		$objPHPExcel = $objReader->load($excel_file['full_path']);
	
		$objReader->setReadDataOnly(TRUE);
	
		$objWorksheet = $objPHPExcel->getActiveSheet();
	
		// Get the highest row and column numbers referenced in the worksheet
		$highestRow = $objWorksheet->getHighestRow(); // e.g. 10
	
		$highestColumn = $objWorksheet->getHighestColumn(); // e.g 'F'
	
		$highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn); // e.g. 5
	
		for ($row = 1; $row <= $highestRow; ++$row) {
				
			for ($col = 0; $col <= $highestColumnIndex; ++$col) {
	
				$cell_value =  $objWorksheet->getCellByColumnAndRow($col, $row)->getValue() ;
					
				$excel_data_array[$row][$col] = $cell_value;
					
			}//end for each
				
		}//end foreach
	
		$this->set_excel_file_data_in_session($excel_data_array);
	
		if($this->debug)log_message('debug','extract_excel_file_data():$excel_data_array :'. print_r($excel_data_array,true));
	
		$this->set_uploaded_file_col_list($excel_data_array);
	
	}//end of function
	
	
	/**
	 * get_clean_col_list_array()
	 *
	 * This function returns clean array of collist when supplied with a
	 * column list string with '---' delimeter
	 *
	 * @param string $col_list_str
	 * @return array $clean_col_list_array
	 */
	private function get_clean_col_list_array($col_list_str){
	
		$clean_col_list_array = explode('---',$col_list_str);
			
		array_pop ($clean_col_list_array);//remove last element
	
		return $clean_col_list_array;
	
	}//end of function
	
	
	
	private function get_all_fields_array(){
		
		$fields = array(
				'AWB_INFO' => array(
						'Srl.No' => 'Srl.No',
						'AIRWAY Bill'=> 'AIRWAY Bill'),
				'CUSTOMER_INFO' => array(
						'Cutomer A/c#' => 'Cutomer A/c#',
						'Pickup Number' => 'Pickup Number',
						'jcs no' => 'jcs no',
				),
				'REFERENCE_INFO'=>array(
						'Ref1' => 'Ref1',
						'Ref2' => 'Ref2',
				),
				'CONSIGNEE_INFO'=>array(
						'Cnee Name' => 'Cnee Name',
						'Company Name' => 'Company Name',
						'Cnee Country Name'=>'Cnee Country Name',
						'Cnee Country Code'=> 'Cnee Country Code',
						'Cnee Province Name'=>'Cnee Province Name',
						'Cnee Province Code'=>'Cnee Province Code',
						'Cnee City Name'=>'Cnee City Name',
						'Cnee City Code'=>'Cnee City Code',
						'Cnee Area'=>'Cnee Area',
						'Cnee Area Code'=>'Cnee Area Code',
						'Cnee Pin Code'=>'Cnee Pin Code',
						'Email1'=>'Email1',
						'Email2'=>'Email2',
						'Valid ID' => 'Valid ID',
						'Address'=> 'Address',
				),
				'SERVICE_INFO'=>array(
						'Service Name'=>'Service Name',
						'Service ID'=>'Service ID',
						'SHIPMENT NAME'=>'SHIPMENT NAME',
						'SHIPMENT CODE'=>'SHIPMENT CODE',
						'supplier Code'=>'supplier Code',
				),
				'PACKAGE_INFO'=>array(
						'Description2'=>'Description2',
						'Desciption of Goods'=>'Desciption of Goods',
						'Pcs'=>'Pcs',
						'Wt'=>'Wt',
						'Calling'=>'Calling',
						'ROUNDTRIP'=>'ROUNDTRIP',
						'Del_Date'=>'Del_Date',
						'Del_Time'=>'Del_Time',
						'INSURED'=>'INSURED',
				),
				'STATION_INFO'=>array(
						'USER_ID'=>'USER_ID',
						'Source Station'=>'Source Station',
						'Destination Station'=>'Destination Station',
				),
				'NOTE_INFO'=>array(
						'Note1'=>'Note1',
						'Note2'=>'Note2',
						'Note3'=> 'Note3',
						'Note4'=> 'Note4',
						'Note5'=>'Note5',
						'Note6'=>'Note6',
				),
				'CALL_INFO'=>array(
						'Autodialer/TelMobile(Phone1)'=>'Autodialer/TelMobile(Phone1)',
						'(TelHome)Phone2'=>'(TelHome)Phone2',
						'(WhatsApp)Phone3'=>'(TelHome)Phone2',
						'(TelWork)Phone4'=>'(TelWork)Phone4',
						'Phone5'=>'Phone5',
						'Phone6'=>'Phone6',
				),
				'COD_INFO'=>array(
						'COD'=>'COD',
						'COD Currency'=>'COD Currency',
						'Cost of Goods'=>'Cost of Goods',
						'COG Currency'=>'COG Currency',
				)				
		);
		
		return $fields;
		
	}//end of function
	
	
	
	private function get_all_fields(){
		
		$fields = array(		
				'Srl.No' => 'Srl.No',
				'Bill'=> 'Bill',
				'Cutomer A/c#' => 'Cutomer A/c#',
				'Pickup Number' => 'Pickup Number',
				'Cnee Name(100)' => 'Cnee Name(100)',
				'Company Name(100)' => 'Company Name(100)',
				'Valid ID' => 'Valid ID',
				'Address(250)'=> 'Address(250)',
				'USER_ID'=>'USER_ID',
				'Desciption of Goods(100)'=>'Desciption of Goods(100)',
				'Pcs'=>'Pcs',
				'Wt'=>'Wt',
				'Calling'=>'Calling',
				'ROUNDTRIP'=>'ROUNDTRIP',
				'COD'=>'COD',
				'COD Currency'=>'COD Currency',
				'Cost of Goods'=>'Cost of Goods',
				'COG Currency'=>'COG Currency',
				'jcs no' => 'jcs no',
				'Ref1(50)' => 'Ref1(50)',
				'Ref2(50)' => 'Ref2(50)',
				'Cnee Country Name'=>'Cnee Country Name',
				'Cnee Country Code'=> 'Cnee Country Code',
				'Cnee Province Name'=>'Cnee Province Name',
				'Cnee Province Code'=>'Cnee Province Code',
				'Cnee City Name'=>'Cnee City Name',
				'Cnee City Code'=>'Cnee City Code',
				'Cnee Area'=>'Cnee Area',
				'Cnee Area Code'=>'Cnee Area Code',
				'Cnee Pin Code'=>'Cnee Pin Code',
				'Email1(100)'=>'Email1(100)',
				'Email2(100)'=>'Email2(100)',
				'Service Name'=>'Service Name',
				'Service ID'=>'Service ID',
				'Description2(100)'=>'Description2(100)',
				'Del_Date'=>'Del_Date',
				'Del_Time'=>'Del_Time',
				'Note1(250)'=>'Note1(250)',
				'Note2(250)'=>'Note2(250)',
				'Note3(250)'=> 'Note3(250)',
				'Note4(250)'=> 'Note4(250)',
				'Note5(250)'=>'Note5(250)',
				'Note6(250)'=>'Note6(250)',
				'Autodialer/TelMobile(Phone1)'=>'Autodialer/TelMobile(Phone1)',
				'(TelHome)Phone2'=>'(TelHome)Phone2',
				'(WhatsApp)Phone3'=>'(TelHome)Phone2',
				'(TelWork)Phone4'=>'(TelWork)Phone4',
				'Phone5'=>'Phone5',
				'Phone6'=>'Phone6',
				'Source Station'=>'Source Station',
				'Destination Station'=>'Destination Station',
				'INSURED'=>'INSURED',
				'SHIPMENT NAME'=>'SHIPMENT NAME',
				'SHIPMENT CODE'=>'SHIPMENT CODE'
		);
		return $fields;
		
	}//end of function
	
	/**
	 * get_template_col_list_from_session()
	 * 
	 * This function gets template column listing from session
	 * 
	 * @return array $template_col_list
	 */
	private function get_template_col_list_from_session(){
		
		$template_col_list = $this->session->userdata('template_col_list_array');
		
		if($this->debug)log_message('debug','get_template_col_list(): $template_col_list :'. print_r($template_col_list,true));
		
		return $template_col_list;
		
	}//end of function
	
	
	/**
	 * get_excel_file_data_from_session()
	 *
	 * This function gets uploaded excel file data from alrady stored session
	 *
	 * @return array $excel_data
	 */
	private function get_excel_file_data_from_session(){
	
		$excel_data = $this->session->userdata('excel_data_array');
	
		if($this->debug)log_message('debug','get_excel_file_data_from_session(): $excel_data :'. print_r($excel_data,true));
	
		return $excel_data;
			
	}//end of function
	
	
	/**
	 * get_uploaded_file_col_list()
	 *
	 * This function returns uploaded file col listing from the session
	 *
	 * @return array collist
	 */
	private function get_uploaded_file_col_list(){
	
		$collist = $this->session->userdata('up_file_col_list_array');
	
		if($this->debug)log_message('debug','get_uploaded_file_col_list():$collist :'. print_r($collist,true));
	
		return $collist;
	
	}//end of function
	
		
	
	/**
	 * set_excel_file_data_in_session()
	 * 
	 * This function stores given uploaded excel fild data in session
	 *  
	 * @param array $excel_data_array
	 */
	private function set_excel_file_data_in_session($excel_data_array){
		
		if($this->debug)log_message('debug','set_excel_file_data_in_session(): 	$excel_data_array :'. print_r($excel_data_array,true));
		
		$this->session->set_userdata('excel_data_array',$excel_data_array);
				
	}//end of function
	
	
	/**
	 * set_template_col_list_in_session()
	 *
	 * This function sets template column listing in a session
	 *
	 * @param array $template_col_list
	 */
	private function set_template_col_list_in_session($template_col_list){
	
		$this->session->set_userdata('template_col_list_array',$template_col_list);
	
		if($this->debug)log_message('debug','set_template_col_list_in_session(): $$template_col_list :'. print_r($template_col_list,true));
	
	}//end of function
	
		
	
	/**
	 * set_uploaded_file_col_list()
	 * 
	 * This function gets column listing of uploaded excel file from
	 * stored shession
	 * 
	 * return array collist
	 */
	private function set_uploaded_file_col_list($excel_data_array){
		
		$collist = array();
		
		foreach($excel_data_array[1] as $cols){
			
			$collist[] = $cols;
			
		}//endforeach
		
		if(empty(end($collist))){
			
			array_pop($collist);
		}
		
		if($this->debug)log_message('debug','set_uploaded_file_col_list():$collist :'. print_r($collist,true));
		
		$this->session->set_userdata('up_file_col_list_array',$collist);		
		
	}//end of function
	
	
	private function dump_data($data){
		
		echo '<pre>';
		var_dump($data);
		exit;
	}
	
	
	
	public function create_template(){

			$get = $this->input->get();

			//load our new PHPExcel library
			$this->load->library('excel');
			//activate worksheet number 1
			$this->excel->setActiveSheetIndex(0);
			//name the worksheet
			$this->excel->getActiveSheet()->setTitle('Data Upload Template');
			//set cell A1 content with some text
				
			if(!empty($get['col_list'])){

				//$col_list_array = json_decode( urldecode($get['col_list']) );
				$col_list_str = urldecode($get['col_list']);

				$col_list_array = explode('---',$col_list_str);
				
				array_pop ($col_list_array);//remove last element
				//var_dump($col_list_array);exit;
				$i = 0;

				foreach($col_list_array as $col){

					//$this->excel->getActiveSheet()->setCellValue($i.'1', $col);
					$this->excel->getActiveSheet()->setCellValueByColumnAndRow($i, 1, $col);
					$i++;
				}//endforeach

				$filename='data_upload_template.xls'; //save our workbook as this file name
				header('Content-Type: application/vnd.ms-excel'); //mime type
				header('Content-Disposition: attachment;filename="'.$filename.'"'); //tell browser what's the file name
				header('Cache-Control: max-age=0'); //no cache

				//save it to Excel5 format (excel 2003 .XLS file), change this to 'Excel2007' 
				//(and adjust the filename extension, also the header mime type)
				//if you want to save it as .XLSX Excel 2007 format
				$objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5'); 

				//force user to download the Excel file without writing it to server's HD
				$objWriter->save('php://output');

			}//end if	
			 redirect('ExcelUploader/index', 'refresh');	

	}//end of function
	
}
?>
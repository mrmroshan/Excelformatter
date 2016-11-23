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
class Templates extends CI_Controller {

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


	/**
	 * Index()
	 * 
	 * This function lists the column listings to prepare the template column list
	 */	
	public function index(){
		
		$data = array();
		$data['all_columns'] = array(

			'Srl.No' => 'Srl.No',
			'AIRWAY'=>'AIRWAY',
			'Bill'=> 'Bill',	
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
			'COG Currency'=>'COG Currency'
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
				
			$this->extract_excel_file_data($file_data['upload_data']);
			
			if($this->debug)log_message('debug','upload_excel_file():$file_data:'. print_r($file_data,true));
			
			$this->map_fields();
			
		}else{	
				
			$this->load->view('file_upload_view', $file_data);
			
		}
		
	}//end of function
	
	
	private function map_fields(){
		
		$data = array();
		
		$up_file_col_list = $this->get_uploaded_file_col_list();
		
		$data['up_file_col_list'] = $up_file_col_list;
		
		if($this->debug)log_message('debug','map_fields():$up_file_col_list:'. print_r($up_file_col_list,true));
				
		$template_col_list = $this->get_template_col_list_from_session();
		
		$data['template_col_list'] = $template_col_list;
		
		if($this->debug)log_message('debug','map_fields():$template_col_list:'. print_r($template_col_list,true));
		
			
		$this->load->view('map_fields_view', $data);
		
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
	
		}else{
	
			$data = array('upload_data' => $this->upload->data());
		}
	
		return $data;
		 
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
		
		if($this->debug)log_message('debug','set_uploaded_file_col_list():$collist :'. print_r($collist,true));
		
		$this->session->set_userdata('up_file_col_list_array',$collist);		
		
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
			 redirect('Templates/index', 'refresh');	

	}//end of function
	
}
?>

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
	private $excel_sheet_data = array();
		
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
			
			$temp_col_list = array();
			
			foreach($col_list_array as $col){
				
				$temp_col_list[] = $col;
				
			}//end foreach
					
			
			$this->session->set_userdata('template_col_list_array',$temp_col_list);
			
			if($this->debug)log_message('debug','Temp session col list :'. print_r($temp_col_list,true));
			
			$this->upload_excel();
				
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
	public function upload_excel(){
		
		$file_data = array();
		
		$file_data['error'] = null;
		
		$file_element = (!empty($_FILES['userfile']['name']))?$_FILES['userfile']:null;		
	
		if(!empty($file_element)){
		
			$file_data = $this->upload();
				
			$this->extract_excel_file_data($file_data['upload_data']);
			
			if($this->debug)log_message('debug','File details :'. print_r($file_data,true));
			
			$this->map_fields();
			
		}else{	
				
			$this->load->view('file_upload_view', $file_data);
			
		}
		
	}//end of function
	
	
	public function map_fields(){
		
		$data = array();
		
		//echo '<pre>';
		//var_dump($this->session->userdata('excel_data_array'));
		
		$this->load->view('map_fields_view', $data);
		
	}//end of function
	
	
	
	
	/**
	 * upload()
	 * 
	 * This function uploads the given file and store in the system
	 * 
	 * @return array file data
	 */
	public function upload(){	
	
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
	 * extract_excel_file_data()
	 * 
	 * This function extracts all data in the excel file
	 * 
	 * @param unknown $excel_file
	 */
	public function extract_excel_file_data($excel_file){
	
		/**  Identify the type of $inputFileName  **/
		$inputFileType = PHPExcel_IOFactory::identify($excel_file['full_path']);
		/**  Create a new Reader of the type that has been identified  **/
		$objReader = PHPExcel_IOFactory::createReader($inputFileType);
		/**  Load $inputFileName to a PHPExcel Object  **/
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
				
				//if(!empty($cell_value)){
					
					$this->excel_sheet_data[$row][$col] = $cell_value;					
					
				//}
			}//end for each
		}//end foreach
		
		$excel_data_array = $this->excel_sheet_data;
			
		$this->session->set_userdata('excel_data_array',$excel_data_array);
		
		if($this->debug)log_message('debug','Excel Data :'. print_r($this->excel_sheet_data,true));
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

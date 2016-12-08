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
		//$this->load->library('SOAPClient_lib');
		$this->load->model('ExcelUploader_model');
		
		

	}//end of function
	
	public function unset_sessions(){
		
		$array_items = array('excel_data_array', 'template_col_list_array','up_file_col_list_array');		
		$this->session->unset_userdata($array_items);
		session_destroy();
	}

	private function check_sessions(){
		
		if(empty($this->session->userdata('template_col_list_array')) and 
				empty($this->session->userdata('excel_data_array'))and 
				empty($this->session->userdata('up_file_col_list_array'))){
			
				$this->session->set_flashdata(
						'error',
						'Session Expired! Please start over again.');
				redirect('ExcelUploader/index', 'refresh');
				
			
		}
		$step='';
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
		
		redirect('ExcelUploader/wizard', 'refresh');
		
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
			case 'preview_uploaded_data':
				$this->preview_data_grid();
				//$this->prepare_mapped_data_array();
				break;
			case 'validate_grid':
				$this->preview_data_grid();
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
		
	
	
	public function map_field_dropdowns(){
	
		$this->check_sessions();
		
		$data = array();
	
		$up_file_col_list = $this->get_uploaded_file_col_list();		
	
		$data['up_file_col_list'] = $up_file_col_list;
	
		if($this->debug)log_message('debug','map_field_dropdowns():$up_file_col_list:'. print_r($up_file_col_list,true));
	
		$all_field_list_array = $this->get_all_fields_array();
	
		$data['all_field_list_array'] = $all_field_list_array;
	
		if($this->debug)log_message('debug','map_fields():$all_field_list_array:'. print_r($all_field_list_array,true));
	
		$this->load->view('map_fields_combo_view', $data);
	
	}//end of function
	
	
	
	
	/**
	 * preview_data_grid()
	 * 
	 * This method previews uploaded data in a grid after mapping appropriate collumns with
	 * master column template(master field list)
	 * 
	 */
	public function preview_data_grid(){
		
		$this->check_sessions();
		
		$mapped_data_array = null;
		
		$all_fields_list = $this->get_all_fields_single_array();
		
		$post_data = $this->input->post();
		
		if(isset($post_data['btnUploadGrid'])){
			
			//$this->dump_data($post_data);
			$grid_array = $post_data['grid'];
			
			$previous_grid_data_array = $this->get_mapped_data_array_from_session();
			
			//assign submitted grid data to previous grid data array
		
			foreach($grid_array as $row => $cols){
				
				foreach($cols as $k=>$v){
					
					$previous_grid_data_array[$row][$k] = $grid_array[$row][$k];
				}				
			}
			
			$mapped_data_array = $previous_grid_data_array;
			
			$this->set_mapped_data_array_in_session($mapped_data_array);
			
		}else{			
			
			$mapped_cols_array = $this->prepare_mapped_cols_array();
			
			$mapped_data_array = $this->prepare_mapped_data_array();
			
		}//end if post
		
		//$this->dump_data($mapped_data_array);
		
		$data['mapped_data_array'] = $this->validate_uploaded_data_array($mapped_data_array);
			
		//$data['original_up_file_data'] = $original_up_file_data;
		
		$data['all_fields_list'] = $all_fields_list;
			
		$this->load->view('preview_grid_view', $data);
		
	}//end of function
	
	

	
	private function prepare_mapped_data_array(){
		
		// THE KEY part!!!
		
		$debug = false;
		
		$mapped_data_array = array();
		
		$mapped_col_count = 0;
		
		$original_col_count = 0 ;
		
		$original_up_file_data = $this->get_excel_file_data_from_session();
		
		//$this->dump_data($original_up_file_data);
		
		$mapped_cols_array = $this->get_mapped_cols_array();
		
		//$this->dump_data($mapped_cols_array);
		
		$mapped_data_array = array();
		
		foreach($original_up_file_data as $rows=>$cols){
			
			//$this->dump_data($cols);
							
			foreach($mapped_cols_array as $index=>$col){
				
				//$this->dump_data($mapped_cols_array);
				
				if(!empty($col) && $col !== ''){
		
					//remove hidden controller chars
						
					$formatted_cell_data = preg_replace(
							'/[\x00-\x09\x0B\x0C\x0E-\x1F\x7F]/', 
							'', 
							$original_up_file_data[$rows][$col]);
						
					$formatted_cell_data = $string = preg_replace(
							'/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F-\x9F]/u', 
							'', 
							$formatted_cell_data);
						
					$formatted_cell_data = preg_replace(
							'/[\x00-\x1F\x7F-\x9F]/u', 
							'', 
							$formatted_cell_data);
						
					$formatted_cell_data = $this->rip_tags($formatted_cell_data);
					
					$mapped_data_array[$rows][$index] = $formatted_cell_data;
					
					if($debug){
						
						$mapped_data_array[$rows][$index] = $formatted_cell_data."<br>ColInx:$col-FildInx:$index";
							
					}
		
				}else{
						
					$mapped_data_array[$rows][$index] = '';	
					
					if($debug){
						
						$mapped_data_array[$rows][$index] = "<br>ExColInx:$col-FildInx:$index";
					}
				}		
									
			}//end foreach	
	
		}//end foreach
		
		//$this->dump_data($mapped_data_array);
		
		$this->set_mapped_data_array_in_session($mapped_data_array);
		
		return $mapped_data_array;
		
	}//end of function
	
	
	
	
	private function set_mapped_data_array_in_session($mapped_data_array){
		
		$this->session->unset_userdata('mapped_data_array');
		
		$this->session->set_userdata('mapped_data_array',$mapped_data_array);
		
	}//end of function
	
	
	
		
	private function prepare_mapped_cols_array(){
		
		//get all select values and prepare mapped colls array
		
		$post_data = $this->input->post();
			
		$mapped_cols_array = array();
		
		foreach($post_data as $field=>$value){
			
			$field_id_array = array();
			
			if( $value != 'Next' &&  $value !== 'preview_uploaded_data' && $field != ""){
								
				$field_id_array = explode("-",$field); // array(2) { [0]=> string(1) "2" [1]=> string(11) "Airway_Bill" }
				
				//var_dump($field_id_array);exit;
				
				$$field = $value;	//make element name as variable.
				
				$mapped_cols_array[$field_id_array[0]] = $$field;
			}
		}
		
		//$this->dump_data($mapped_cols_array);
		
		$this->session->unset_userdata('mapped_cols_array');
		
		$this->session->set_userdata('mapped_cols_array',$mapped_cols_array);
				
		return $mapped_cols_array;
		
	}//end of function
	
	
	
	
	/**
	 * get_mapped_col_info()
	 * 
	 * This function gets corresponding table field info from previously mapped array and returns
	 * field info as an array. Returns array if field is found. Else returns '0' if not found
	 * 
	 * @param int $data_col_index
	 * @return mix []|string
	 */
	private function get_mapped_col_info($data_col_index){
		
		//var_dump($data_col_index);exit;
		
		//first prepare all table fileds list into one array
		
		$all_master_field_single_array = array();
		
		$all_field_list_multy_array = $this->get_all_fields_array();	
		
		//$this->dump_data($all_field_list_multy_array);
		
		//make it a single multy array
		
		foreach($all_field_list_multy_array as $categories){
		
			foreach($categories as $category=>$field){
		
				$all_master_field_single_array[$field['FIELD_ID']] = $field;
			}
		
		}//end foreach
		
		//$this->dump_data($all_master_field_single_array);
		
		//get column-table field mapped array to identify appropriate mapped table field
		
		$mapped_col_field_array = $this->get_mapped_cols_array();
		
		//$this->dump_data($mapped_col_field_array);
		
		//now get corresponding table field info
		
		$field_index = $mapped_col_field_array[$data_col_index];
		
		//$field_index = array_search($data_col_index, $mapped_col_field_array); 
		
		//var_dump($field_index);exit;
		
		//if(array_key_exists($field_index, $all_master_field_single_array)){
			
			return $all_master_field_single_array[$data_col_index];//[$data_col_index];//
			
		//}else{
			
		//	return '0';
		//}
		
	}//end of function
	
	
	
	
	
	
	private function validate_uploaded_data_array($data_array){
		
		$debug = true;
		
		//$this->dump_data($data_array);
		
		$all_field_list_multy_array = $this->get_all_fields_array();				
			
		$row_no = 1;
		
		$new_data_array = array();
		
		$flagd_col_names = array();
		
		$flagd_empty_col_names = array();
		
		$invalid_data_col_names= array();
		
		foreach($data_array as $datarows=>$datacolls){
			
			//$this->dump_data($datacolls);
			
			$data_col_index = 0;//for each row 
			
			$empty_cell_count = 0;
			
			foreach($datacolls as $col_index => $datacell){
				
				//$this->dump_data($datacell);
				
				$regexpattern = null;
				
				$regx_result = null;
				
				$mapped_col_info = null;
				
				$debug_data = null;
				
				$chk_string = $datacell; //make a copy for processing
				
				$chk_string = $this->rip_tags($chk_string);//strip all html tags in data
				
				$mapped_col_info = $this->get_mapped_col_info($col_index);
				
				if($row_no >= 2){	
					
					//$this->dump_data($datacolls);					
					
					//$this->dump_data($mapped_col_info);
					
					if(is_array($mapped_col_info)){
						
					
						$fieldName = $mapped_col_info['FIELD_LABEL'];
							
						$regexpattern = $mapped_col_info['REGXPATTERN'];
							
						$maxchars = (int)$mapped_col_info['MAXCHARS'];
							
						$is_empty = $this->is_empty_cell($chk_string);
							
						$is_exceeded = $this->is_exceeded($chk_string,$maxchars);
						
						$regx_result= preg_match("/".$regexpattern."/" , $chk_string );
							
						if($debug){
								
							$debug_data = " <br>fieldName:$fieldName
							<br>is_empty:$is_empty
							<br>is_exeeded:$is_exceeded
							<br>max_char:$maxchars
							<br>char len: ".strlen($chk_string).
							"<br>RegX pattern:$regexpattern
							<br>RegX result: $regx_result
							<br>DataColInx:$col_index";
						}//end debug
							
						if($is_exceeded == 'Y'){
						
							$flagd_col_names[$mapped_col_info['FIELD_INDEX']] = array(
									'label'=>$mapped_col_info['FIELD_LABEL'],
									'limit'=>$mapped_col_info['MAXCHARS']);
								
							$datacell = '<div class="err_exceed_limit">'.
									'<textarea name="grid['.$row_no.']['.$col_index.']" cols="10" rows="2">'.
									$chk_string.'</textarea></div>';
						
						}else{
						
							if($mapped_col_info['REQUIRED']==1){
									
								if($is_empty == 'Y'){
										
									$flagd_empty_col_names[$mapped_col_info['FIELD_INDEX']] = $mapped_col_info['FIELD_LABEL'];
										
									$chk_string = $this->rip_tags($chk_string);
										
									$datacell = '<div class="err_empty">
													<textarea name="grid['.$row_no.']['.$col_index.']" cols="10" rows="2">'.
																			$chk_string.'</textarea></div>';
																				
									$empty_cell_count++;
								}//end if
									
							}else{
									
								$chk_string = $this->rip_tags($chk_string);
									
								if($is_empty == 'N' && strlen( $chk_string) >0){
						
									if(!empty($regexpattern)){
											
										if($regx_result === 0 ){
												
											$invalid_data_col_names[$mapped_col_info['FIELD_INDEX']] = $mapped_col_info['FIELD_LABEL'];
												
											$datacell = '<div class="err_invalid_data">
															<textarea name="grid['.$row_no.']['.$col_index.']" cols="10" rows="2">'.
															$chk_string.
															'</textarea></div>';
						
										}//end if
									}//end if
						
								}//end if
									
							}//end if
						
						}//end if required
							
						$new_data_array[$row_no][$col_index] = $datacell.$debug_data;
											
					}else{//end if mapped col info
					
						//$this->dump_data($mapped_col_info);
						
						$new_data_array[$row_no][$col_index] = $datacell.$debug_data;
					}
					
				}else{
					
					//first row shows excel sheets column headers
					
					$new_data_array[$row_no][$col_index] = $datacell;
					
				}
				
				$data_col_index++;
				
			}//end foreach
			
			$row_no++;
		}//end foreach	
	
		
		//Error messages all in one
		$err_msg = null;
	
		//set errro messages for required field empty strings
		
		if(!empty($flagd_empty_col_names)){
			
			$fields_str = null;
			
			foreach($flagd_empty_col_names as $col){
			
				$fields_str .= $col.', ';
			}
				
			$fields_str = rtrim($fields_str,', ');
				
			//$this->session->set_flashdata(
			//		'error',
			//		"Following fields data connot be empty.<br><br>$fields_str");	
			
			$err_msg = "Following column(s) data connot be empty.<br><br><b>$fields_str</b><br><br>";
			
		}//end if 
		
		//set error message for limit exceeds
		
		if(!empty($flagd_col_names)){
			
			$fields_str = null;
			
			foreach($flagd_col_names as $index => $col_info){				
				
				$fields_str .= $col_info['label'].' max char limit is '.$col_info['limit'].', ';				
			}
			
			$fields_str = rtrim($fields_str,', ');
			
			//$this->session->set_flashdata(
			//		'error',
			//		"Following fields contains character limit exceeded data in the cells.<br><br>$fields_str");
			
			$err_msg .= "Following column(s) contains data which is exceeded char limit.<br><br><b>$fields_str</b><br><br>";
			
		}
		
		if(!empty($invalid_data_col_names)){
			
			$fields_str = null;
				
			foreach($invalid_data_col_names as $index => $col_info){
			
				$fields_str .= $col_info.', ';
			}
				
			$fields_str = rtrim($fields_str,', ');				
			
			$err_msg .= "Following column(s) contains invalid data cells.<br><br><b>$fields_str</b><br>";
				
		}
		
		if(!empty($err_msg)){
			
			$this->session->set_flashdata('error',$err_msg);
		
		}
		
		if($empty_cell_count > 500){
			
			$err_msg .= "<br><b>This file contains too many empty values for required column(s) 
													Please edit the file and then re upload</b>";
			
			$this->session->set_flashdata('error',$err_msg);
							
			//redirect('ExcelUploader/index', 'Location');
			
		}
		
		
		return $new_data_array;
		
	}//end function
	
	
	
	
	
	private function is_empty_cell($data){
		
		$data = $this->rip_tags($data);
		
		if($data === ""){
			
			return 'Y';
		
		}else{
			
			return 'N';
		
		}
		
	}//end of function
	
	
	
	
	/**
	 * rip_tags()
	 * 
	 * hhttp://php.net/manual/en/function.strip-tags.php#110280
	 * 
	 * @param unknown $string
	 * @return string
	 */
	private function rip_tags($string) {
	
		// ----- remove HTML TAGs -----
		$string = preg_replace ('/<[^>]*>/', ' ', $string);
	
		// ----- remove control characters -----
		$string = str_replace("\r", '', $string);    // --- replace with empty space
		
		$string = str_replace("\n", ' ', $string);   // --- replace with space
		
		$string = str_replace("\t", ' ', $string);   // --- replace with space
	
		// ----- remove multiple spaces -----
		$string = trim(preg_replace('/ {2,}/', ' ', $string));
	
		return $string;
	
	}
	
	/**
	 * is_exceeded()
	 * 
	 * This function checks supplied text exeeds given max charactor limit 
	 * and return true or false
	 * 
	 * @param string $str
	 * @param unknown $max
	 * @return string
	 */
	private function is_exceeded($str,$max){
		
		$str = $this->rip_tags($str);
		
		$len = strlen($str);
		
		$result = 'N';
		
		if((int)$len > (int)$max){
			
			$result =  'Y';
			
		}else{
			
			$result = 'N';
			
		}
		
		return $result;
		
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
	
		
		//remove empty rows 
		
		foreach($excel_data_array as $rows=>$cols){
			
			$empty_cell_count = 0;
			
			foreach($cols as $cell){
				
				if(empty($cell)) $empty_cell_count++;
			}
			
			//echo $empty_cell_count.$rows."<br>";
			
			if($empty_cell_count == count($cols)){
				
				//array_splice($excel_data_array,$rows-1);
				unset($excel_data_array[$rows]);
				
			}
		}//end foreach
		
		
		//$this->dump_data($excel_data_array);
		
		$this->set_excel_file_data_in_session($excel_data_array);
	
		if($this->debug)log_message('debug','extract_excel_file_data():$excel_data_array :'. print_r($excel_data_array,true));
	
		$this->set_uploaded_file_col_list($excel_data_array);
	
	}//end of function
	
	
	
	
	/**
	 * get_mapped_cols_array()
	 * 
	 * This function returns mapped cols array from session
	 * 
	 * @return array mapped_cols_array
	 */
	private function get_mapped_cols_array(){
		
		$mapped_cols_array = $this->session->userdata('mapped_cols_array');
		
		return $mapped_cols_array;
		
	}//end of function
	
	
	
	
	/**
	 * get_mapped_data_array_from_session()
	 * 
	 * This function gets mapped data array from session then returns
	 * 
	 * @return array $mapped_data_array
	 */
	private function get_mapped_data_array_from_session(){
		
		$mapped_data_array = $this->session->userdata('mapped_data_array');
		
		return $mapped_data_array;
		
	}//end of function
	
	

	
	/**
	 * get_all_fields_single_array()
	 * 
	 * This function returns all field lists as a single array from multi dimentional 
	 * field list array
	 * 
	 * @return array $field_list_single_array
	 */
	private function get_all_fields_single_array(){
		
		$all_field_list_multy_array = $this->get_all_fields_array();
		
		$field_list_single_array = null;
	
		foreach($all_field_list_multy_array as $categories){
		
			foreach($categories as $field){				
				
				$field_list_single_array[$field['FIELD_ID']] = $field['FIELD_LABEL'];				
			}
		}
		
		//$this->dump_data($field_list_single_array);
		
		return $field_list_single_array;
		
	}//end of function
		
	
	
	
	private function get_all_fields_array(){
	
		$fields_dataset=$this->ExcelUploader_model->get_all_field_info();	
		
		$fields = array();
		
		//$this->dump_data($fields_dataset);
		
		foreach($fields_dataset as $row)
		{
			$fields[$row['CAT_LABEL']][] = array(
									'FIELD_ID' => $row['FIELD_ID'],
									'CATEGORY_ID' => $row['CATEGORY_ID'],
									'FIELD_INDEX' => $row['FIELD_INDEX'],
									'FIELD_LABEL' => $row['FIELD_LABEL'],
									'REQUIRED' => $row['REQUIRED'],
									'REGXPATTERN' => $row['REGXPATTERN'],
									'MAXCHARS' => $row['MAXCHARS'],
									'PRECHKFIELDS' => $row['PRECHKFIELDS'],
									'SOAP_FIELD' => $row['SOAP_FIELD']
											);
		}
		//$this->dump_data($fields);
		
		return $fields;		
	
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
		
		$this->session->unset_userdata('excel_data_array');
		
		$this->session->set_userdata('excel_data_array',$excel_data_array);
				
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
		
		//$this->dump_data($collist);
		
		if($this->debug)log_message('debug','set_uploaded_file_col_list():$collist :'. print_r($collist,true));
		
		$this->session->unset_userdata('up_file_col_list_array');
		
		$this->session->set_userdata('up_file_col_list_array',$collist);		
		
	}//end of function
	
	
	
	
	private function dump_data($data){
		
		echo '<pre>';
		var_dump($data);
		exit;
	}
	
	
	public function upload_shipment(){
		
		$CLIENTINFO = array(
						'CodeStation'=>'KWI',
						'Password'=>'shr',
						'ShipperAccount'=>'Test7474',
						'UserName'=>'shareefsh'
				);
		
		$BATCHSHIPMENTS[] = array(									
								'Address'=>'kuwait city',
								'AirwayBillNo'=>'',
								'COD' => '100',
								'CODCurrencyCode'=>'KWD',
								'COGCurrencyCode' => 'KWD',
								'Calling'=> '',
								'Company'=>'home',
								'ConsigneeAreaCode'=>'AREA71',
								'ConsigneeCityCode'=>'CITY24051',
								'ConsigneeCountryCode'=>'KWT',
								'ConsigneeName'=>'Roshan uploader',
								'ConsigneePincode'=>'123',
								'ConsigneeProvinceCode'=>'KW',
								'CostOfGoods'=>'5000',
								'DeliveryDate'=>'2016-11-11',
								'DeliveryTime'=>'00:00-02:00',
								'Description1'=>'This is a test upload 1 ',
								'Description2'=>'desc 2 from upload',
								'DestinationStation'=>'KWI',
								'Email1'=>'test@mail.com',
								'Email2'=>'test@mail.com',
								'Insured'=>'Y',
								'JCSNo'=>'',
								'Note1'=>'test1',
								'Note2'=>'test1',
								'Note3'=>'test1',
								'Note4'=>'test1',
								'Note5'=>'test1',
								'Note6'=>'test1',
								'Phone5'=>'1111111',
								'Phone6'=>'2222',
								'PickupNumber'=>'44',
								'Pieces'=>'1',
								'Reference1'=>'test1',
								'Reference2'=>'test1',
								'RequestSequence'=>'1',
								'RoundTrip'=>'N',
								'ServiceCode'=>'SRV6',
								'ShipmentTypeCode'=>'SHPT1',
								'SourceStation'=>'KWI',
								'SupplierCode'=>'',
								'TelHomePhone2'=>'123456',
								'TelMobilePhone1'=>'123456',
								'TelWorkPhone4'=>'12345678',
								'ValidID'=>'123456',
								'Weight'=>'12',
								'WhatsAppPhone3'=>'123456'						
								);		
		
		$parameters1 =array(				
				'CLIENTINFO'=>$CLIENTINFO,
				'BatchShpt'=>$BATCHSHIPMENTS	
				);
		
		try {
		
			$client = new SoapClient("http://172.53.1.34:8080/APIService/PostaWebClient.svc?wsdl",
					array('trace' => 1));
			
			$result = $client->BatchShipments3($parameters1);
			
			//echo "<pre>REQUEST:\n" . htmlentities($client->__getLastRequest()) . "\n";
			//echo "<pre>Response:\n" . htmlentities($client->__getLastResponse()) . "\n";
			
			$shipment_upload_responce = $result->BatchShipmentsResult->SHIPMENT_INFO;
			
			if(count($shipment_upload_responce)>1){
					
				foreach($shipment_upload_responce as $result){
			
					$response_msg = $result->ResponseMessage;
					$error_msg = $result->ErrorMessage;
					$RequestSequence = $result->RequestSequence;
					$connote = $result->Connote;
			
					echo "<hr><br>responce msg: $response_msg, error msg: $error_msg, requence seq: $RequestSequence, connote: $connote";
				}
					
			}else{				
				
				//var_dump($shipment_upload_responce);
				/*object(stdClass)#111 (4) { ["Connote"]=> string(12) "100000352763" ["ErrorMessage"]=> string(14) "PICKUPNOTFOUND"
				 * ["RequestSequence"]=> string(1) "1"["ResponseMessage"]=> string(6) "FAILED" }*/
				
				$response_msg = $shipment_upload_responce->ResponseMessage;
					
				$error_msg = $shipment_upload_responce->ErrorMessage;
					
				$RequestSequence = $shipment_upload_responce->RequestSequence;
					
				$connote = $shipment_upload_responce->Connote;
					
				echo "<hr><br>responce msg: $response_msg, error msg: $error_msg, requence seq: $RequestSequence, connote: $connote";					
			}
		
		} catch (SoapFault $fault) {
			
			//trigger_error("SOAP Fault: (faultcode: {$fault->faultcode}, faultstring: {$fault->faultstring})", E_USER_ERROR);
			echo 'Error! '."SOAP Fault: (faultcode: {$fault->faultcode}, faultstring: {$fault->faultstring})";
		}
		
		

		/*
		require_once APPPATH."/third_party/SOAP/lib/nusoap.php";
		$client = new nusoap_client(
				'http://172.53.1.34:8080/APIService/PostaWebClient.svc?wsdl', 
				'wsdl'				
				);
		
		$err = $client->getError();		
		if ($err) {
			echo '<h2>Constructor error</h2><pre>' . $err . '</pre>';
			echo '<h2>Debug</h2><pre>' . htmlspecialchars($client->getDebug(), ENT_QUOTES) . '</pre>';
			exit();
		}		
		$client->setUseCurl($useCURL);			
		$client->soap_defencoding = 'UTF-8';				
		$result = $client->call('BatchShipments',  array('parameters'=>$parameters1), '', '', false, false);
		if ($client->fault) {
			echo '<h2>Fault (Expect - The request contains an invalid SOAP body)</h2><pre>'; print_r($result); echo '</pre>';
		} else {
			$err = $client->getError();
			if ($err) {
				echo '<h2>Error</h2><pre>' . $err . '</pre>';
			} else {
				echo '<h2>Result</h2><pre>'; print_r($result); echo '</pre>';
			}
		}	
		echo '<h2>Request</h2><pre>' . htmlspecialchars($client->request, ENT_QUOTES) . '</pre>';
		echo '<h2>Response</h2><pre>' . htmlspecialchars($client->response, ENT_QUOTES) . '</pre>';
		echo '<h2>Debug</h2><pre>' . htmlspecialchars($client->getDebug(), ENT_QUOTES) . '</pre>';
		*/
		
		
		
		
	}//end of funtion
	
	
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

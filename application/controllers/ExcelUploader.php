<?php
defined('BASEPATH') OR exit('No direct script access allowed');

ini_set('max_execution_time', 300);

/**
 * This is the controller which does the wizard type template creation and data mapping
 * for excel sheets
 *
 * @author Roshan.Ruzik
 * @created on 22-11-2016
 *
 */
class ExcelUploader extends CI_Controller {

	private $debug = false;

	private $selected_col_list = null;

	private $ACCOUNTNO = null;

	private $USERNAME = null;

	private $PASSWORD = null;

	private $CODESTATION = null;

	/**
	 * __construct()
	 *
	 * Controller construct
	 *
	 */
	function __construct(){

		parent::__construct();

		//$this->check_auth();

		ini_set('memory_limit','2048M');

		$config['upload_path']          = './uploads/';

		$config['allowed_types']        = 'xls|xlsx|csv';

		//$config['max_size']             = 1000;

		//$config['max_width']            = 1024;

		//$config['max_height']           = 768;

		$this->load->library('upload', $config);

		$this->load->library('excel');

		//$this->load->library('SOAPClient_lib');

		$this->load->model('ExcelUploader_model');

	}//end of function



	private function check_auth(){

		if(empty($this->session->userdata('CODESTATION')) and
				empty($this->session->userdata('ACCOUNTNO'))and
				empty($this->session->userdata('USERNAME')) and
				empty($this->session->userdata('PASSWORD'))){
						
					//redirect('ExcelUploader/index', 'refresh');
					redirect('ExcelUploader/index?login_data='.$this->session->userdata('login_data'));
						
		}else{
				
			return true;
		}

	}//END OF FUNCTION


	/**
	 * unset_sessions()
	 *
	 * This function is used for unsetting all app sessions for testing
	 *
	 */
	public function unset_sessions(){

		$array_items = array('excel_data_array',
				'template_col_list_array',
				'up_file_col_list_array');

		$this->session->unset_userdata($array_items);

		session_destroy();

	}//end of function



	private function check_sessions(){

		if( empty($this->session->userdata('template_col_list_array')) and
				empty($this->session->userdata('excel_data_array'))and
				empty($this->session->userdata('up_file_col_list_array'))){

					$this->session->set_flashdata(
							'error',
							'Session Expired! Please start over again.');
						
					//redirect('ExcelUploader/index', 'refresh');
					redirect('ExcelUploader/index?login_data='.$this->session->userdata('login_data'));
						
		}
			
	}//end of function


	/**
	 * Index()
	 *
	 * This function lists the column listings to prepare the template column list
	 */
	public function index(){

		//KWI:Test7474:shareefsh:shr

		//http://localhost:8080/shipment_uploader/index.php/ExcelUploader/?login_data=S1dJOlRlc3Q3NDc0OnNoYXJlZWZzaDpzaHI=

		$enc_login_data = $this->input->get('login_data');
		
		$login_data = base64_decode($enc_login_data);

		if(!empty($login_data)){

			$login_data_array = explode(':',$login_data);

			$this->session->set_userdata('CODESTATION',$login_data_array[0]);

			$this->session->set_userdata('ACCOUNTNO',$login_data_array[1]);

			$this->session->set_userdata('USERNAME',$login_data_array[2]);

			$this->session->set_userdata('PASSWORD',$login_data_array[3]);

			$this->session->set_userdata('login_data',$enc_login_data);

			redirect('ExcelUploader/wizard', 'refresh');

		}else{

			$this->load->view("unauthorized_access");
		}

	}//end of function



	/**
	 * wizard()
	 *
	 * This function acts as a work flow controlelr for this app.
	 * It basically calls relevent function on a button click
	 *
	 */
	public function wizard(){

		$this->check_auth();

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

				$this->map_field_dropdowns();

				break;

			case 'preview_uploaded_data':

				$this->preview_data_grid();
					
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

				$excel_read = $this->extract_excel_file_data($file_data['upload_data']);
				
				//now delete uploaded file from disk

				@delete_files($file_data['upload_data']['file_path']);

				if($this->debug)log_message('debug','upload_excel_file():$file_data:'. print_r($file_data,true));

				if($excel_read){
					
					$this->session->unset_userdata('error');
					
					$this->map_field_dropdowns();
					
				}else{
					
					$this->session->set_flashdata(
							'error',
							'Uploaded file contains empty row at the top or empty column at the beginning.'.
							' Please remove them as shown at the bottom images and re upload the file');					
					
					$this->load->view("file_upload_view");				
					
				}

			}else{

				$err_msg = trim($file_data['error'],'<p>');

				$err_msg = trim($err_msg,'</p>');

				$this->session->set_flashdata('error',$err_msg);

				$this->load->view('file_upload_view', $file_data);
			}
				
		}else{
				
			//$this->session->set_flashdata('error',"Please upload");
				
			$this->load->view('file_upload_view', $file_data);
		}

	}//end of function

	
	

	/**
	 * map_field_dropdowns()
	 *
	 * This function calls table column and excel file column mapping page
	 *
	 */
	public function map_field_dropdowns(){

		$this->check_sessions();

		$data = array();

		$up_file_col_list = $this->get_uploaded_file_col_list();

		$data['up_file_col_list'] = $up_file_col_list;
		
		// this is to auto select element values which are previously mapped.
		
		$data['mapped_form_elements'] = $this->get_mapped_form_element_from_session();

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
		
		$this->set_mapped_form_element_session($post_data);

		if(isset($post_data['btnUploadGrid'])){
				
			//check if any corrected data is sent back to the server. If found update data arrray and
			//proceed to display on the grid after validation. If not then proceed to upload with stroed
			//sesshion data
				
			if(isset($post_data['grid'])){

				$this->session->unset_userdata('error');

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

				$mapped_data_array = $this->get_mapped_data_array_from_session();

				//$this->upload_shipment($mapped_data_array);

				$this->export_shipment_page();

				return;
			}
				
		}else{
				
			$mapped_cols_array = $this->prepare_mapped_cols_array();
				
			$mapped_data_array = $this->prepare_mapped_data_array();
				
		}//end if post

		$data['mapped_data_array'] = $this->validate_uploaded_data_array($mapped_data_array);

		$data['all_fields_list'] = $all_fields_list;
			
		$this->load->view('preview_grid_view', $data);

	}//end of function

	
	
	
	private function set_mapped_form_element_session($mapped_form_element){
		
		$this->session->unset_userdata('mapped_form_element');
		
		$this->session->set_userdata('mapped_form_element',$mapped_form_element);
		
	}//end of function

	
	
	private function get_mapped_form_element_from_session(){
	
		return $this->session->userdata('mapped_form_element');
	
	}//end of function
	
	
	
	

	/**
	 * prepare_mapped_data_array()
	 *
	 * This is the most important function in this app. It mapps and creates
	 * an array which is based on previously mapped field-column array
	 *
	 * @return string
	 */
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

				if( $col !== ''){

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



	/**
	 * set_mapped_data_array_in_session()
	 *
	 * This function stores mappd data array in session
	 *
	 * @param unknown $mapped_data_array
	 */
	private function set_mapped_data_array_in_session($mapped_data_array){

		$this->session->unset_userdata('mapped_data_array');

		$this->session->set_userdata('mapped_data_array',$mapped_data_array);

	}//end of function



	/**
	 * prepare_mapped_cols_array()
	 *
	 * This function prepares mapped column-fields array
	 *
	 * @return array
	 */
	private function prepare_mapped_cols_array(){

		//get all select values and prepare mapped colls array

		$post_data = $this->input->post();
			
		$mapped_cols_array = array();

		$mapped_field_name = null;

		foreach($post_data as $field=>$value){
				
			$field_id_array = array();
				
			if( $value != 'Next' &&  $value !== 'preview_uploaded_data' && $field != ""){

				$field_id_array = explode("-",$field); // array(2) { [0]=> string(1) "2" [1]=> string(11) "Airway_Bill" }

				if($value !== ''){
						
					$this->store_mapped_fields($field_id_array,$value);
				}

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
	 * store_mapped_fields()
	 *
	 * This function stores excel column names to be mapped automatically
	 * with field name when mapping page is opend.
	 *
	 */
	private function store_mapped_fields($field_data,$column_index){

		$field_id = $field_data[0];

		$column_list_array = $this->get_uploaded_file_col_list();

		//$mapped_col_name = str_replace("_", " ", $field_data[1]);

		$mapped_col_name = $column_list_array[$column_index];

		$existing_fields_list_str = $this->get_mapped_col_info($field_id);

		$existing_fields_list_array = explode("|", $existing_fields_list_str['MAPPED_COL_NAMES']);

		if(empty(end($existing_fields_list_array))){
				
			array_pop($existing_fields_list_array);
		}

		//check if its already existing

		if(in_array($mapped_col_name,$existing_fields_list_array)){
				
			return;
		}

		//$this->dump_data($existing_fields_list_array);

		if(!empty($existing_fields_list_str['MAPPED_COL_NAMES'])){
				
			$existing_fields_list_str = $existing_fields_list_str['MAPPED_COL_NAMES'] .'|'.trim($mapped_col_name);
				
		}else{
				
			$existing_fields_list_str = trim($mapped_col_name);
		}

		$status = $this->ExcelUploader_model->update_entry(
				array('MAPPED_COL_NAMES' => $existing_fields_list_str,
						'FIELD_ID' => $field_id)
				);
			
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

		//first prepare all table fileds list into one array

		$all_master_field_single_array = array();

		$all_field_list_multy_array = $this->get_all_fields_array();

		//make it a single multy array

		foreach($all_field_list_multy_array as $categories){

			foreach($categories as $category=>$field){

				$all_master_field_single_array[$field['FIELD_ID']] = $field;
			}

		}//end foreach

		//get column-table field mapped array to identify appropriate mapped table field

		$mapped_col_field_array = $this->get_mapped_cols_array();

		//now get corresponding table field info

		$field_index = $mapped_col_field_array[$data_col_index];
			
		return $all_master_field_single_array[$data_col_index];

	}//end of function



	/**
	 * get_mapped_col_info_by_soap_field()
	 *
	 * This function gets table field info based on given SOAP field name
	 *
	 * @param string $soap_field
	 * @return string[]
	 */
	private function get_mapped_col_info_by_soap_field($soap_field){
			
		//first prepare all table fileds list into one array

		$all_master_field_single_array = array();

		$all_field_list_multy_array = $this->get_all_fields_array();

		//make it a single multy array

		foreach($all_field_list_multy_array as $categories){

			foreach($categories as $category=>$field){

				$all_master_field_single_array[$field['FIELD_ID']] = $field;
			}

		}//end foreach

		foreach($all_master_field_single_array as $field_id => $field_infos){
				
			//var_dump($field_infos);exit;
				
			foreach($field_infos as $field_name=>$field_value){

				if($field_name=='SOAP_FIELD' && $field_value == $soap_field){
						
					return $field_infos;

				}else if($soap_field == 'SOAP_error'){
						
					return array('FIELD_ID'=>'SOAP_error');
				}
			}
		}//end of function

	}//end of function



	/**
	 * validate_uploaded_data_array()
	 *
	 * This function validates and prepares error messages to be shown to end user.
	 * If any error is found it also enables users to enter or correct data by providing
	 * edit fields
	 *
	 * @param array $data_array
	 * @return string
	 */
	private function validate_uploaded_data_array($data_array){

		$debug = false;
			
		$row_no = 1;

		$new_data_array = array();

		$flagd_col_names = array();

		$flagd_empty_col_names = array();

		$invalid_data_col_names= array();

		foreach($data_array as $datarows=>$datacolls){
				
			$empty_cell_count = 0;
				
			foreach($datacolls as $col_index => $datacell){

				$regexpattern = null;

				$regx_result = null;

				$mapped_col_info = null;

				$debug_data = null;

				$chk_string = $datacell; //make a copy for processing

				$chk_string = $this->rip_tags($chk_string);//strip all html tags in data

				$mapped_col_info = $this->get_mapped_col_info($col_index);

				if($row_no >= 2){
						
					if(is_array($mapped_col_info)){

							
						$fieldName = $mapped_col_info['FIELD_LABEL'];
							
						$regexpattern = $mapped_col_info['REGXPATTERN'];
							
						$maxchars = (int)$mapped_col_info['MAXCHARS'];
							
						$is_empty = $this->is_empty_cell($chk_string);
							
						$is_exceeded = $this->is_exceeded($chk_string,$maxchars);

						//$pattern = '/' . preg_quote($regexpattern, '/') . '/';

						$regx_result= @preg_match("/".$regexpattern."/" , $chk_string );
							
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
														
													if(!empty($regexpattern)){
															
														if($regx_result == 0 ){

															$chk_string = $this->rip_tags($chk_string);
																
															$invalid_data_col_names[$mapped_col_info['FIELD_INDEX']] = $mapped_col_info['FIELD_LABEL'];

															$datacell = '<div class="err_invalid_data">
														<textarea name="grid['.$row_no.']['.$col_index.']" cols="10" rows="2">'.
														$chk_string.
														'</textarea></div>';
															
														}//end if
															
													}//end if

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
				
			$err_msg = "Following column(s) data connot be empty.<br><br><b>$fields_str</b><br><br>";
				
		}//end if

		//set error message for limit exceeds

		if(!empty($flagd_col_names)){
				
			$fields_str = null;
				
			foreach($flagd_col_names as $index => $col_info){

				$fields_str .= $col_info['label'].' max char limit is '.$col_info['limit'].', ';
			}
				
			$fields_str = rtrim($fields_str,', ');
				
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
				
		}

		return $new_data_array;

	}//end function



	/**
	 * is_empty_cell()
	 *
	 * This function checks for empty string and return 'Y' if found else 'N'
	 *
	 * @param string $data
	 * @return string
	 */
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
	 * @param string $string
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

				//$cell_value =  $objWorksheet->getCellByColumnAndRow($col, $row)->getValue() ;
				$cell_value =  $objWorksheet->getCellByColumnAndRow($col, $row)->getFormattedValue() ;
					
				$excel_data_array[$row][$col] = $cell_value;
				//$cell->getFormattedValue()
					
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

		//check if first row is empty or not
		
		if(empty($excel_data_array[1])){
		
			return false;
			
		}//end if
		
		$this->set_uploaded_file_col_list($excel_data_array);
		
		return true;

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

				if($field['STATUS']=== '1'){

					$field_list_single_array[$field['FIELD_ID']] = $field['FIELD_LABEL'];
						
				}
			}
		}

		//$this->dump_data($field_list_single_array);

		return $field_list_single_array;

	}//end of function



	/**
	 * get_all_fields_array()
	 *
	 * This function returns all table fields information as a associative array
	 *
	 * @return assosiative array
	 */
	private function get_all_fields_array(){

		$fields_dataset=$this->ExcelUploader_model->get_all_field_info();

		$fields = array();

		foreach($fields_dataset as $row)
		{
			if($row['STATUS'] == '1' ){

				$fields[$row['CAT_LABEL']][] = array(
						'FIELD_ID' => $row['FIELD_ID'],
						'CATEGORY_ID' => $row['CATEGORY_ID'],
						'FIELD_INDEX' => $row['FIELD_INDEX'],
						'FIELD_LABEL' => $row['FIELD_LABEL'],
						'REQUIRED' => $row['REQUIRED'],
						'REGXPATTERN' => $row['REGXPATTERN'],
						'MAXCHARS' => $row['MAXCHARS'],
						'PRECHKFIELDS' => $row['PRECHKFIELDS'],
						'SOAP_FIELD' => $row['SOAP_FIELD'],
						'DATATYPE' => $row['DATATYPE'],
						'MAPPED_COL_NAMES' => $row['MAPPED_COL_NAMES'],
						'STATUS'=> $row['STATUS']
				);
			}
				
		}

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
			
			if($this->debug)log_message('debug','set_uploaded_file_col_list():$collist :'. print_r($collist,true));
			
			$this->session->unset_userdata('up_file_col_list_array');
			
			$this->session->set_userdata('up_file_col_list_array',$collist);
				

	}//end of function



	/**
	 * export_shipment_page()
	 *
	 * This function opens export shipment page and makes AJAX calls to
	 * upload shipment details as 10 batch requests
	 *
	 */
	public function export_shipment_page(){

		//remove the first element, since its only column names

		$data_array = $this->get_mapped_data_array_from_session();

		array_shift($data_array);

		$json_data_array = json_encode($data_array);

		$req_no = round(count($data_array)/10);

		$data['json_data_array'] = $json_data_array;

		$data['req_no'] = ($req_no==0)?1:$req_no;

		$data['tot_rows'] = count($data_array);

		$all_fields_list = $this->get_all_fields_single_array();

		$data['all_fields_list'] = $all_fields_list;

		$this->load->view('export_shipment_view', $data);

	}//end of function



	/**
	 * ajax_create_shipment()
	 *
	 * This function execute batch shipment uploads using SOAP client when
	 * a request is received. For each request this function prepares batchshipment
	 * array using stored session excel data
	 *
	 */
	public function ajax_create_shipment(){

		$debug = false;

		$data = array('debug_data'=>'');

		$sequence = $this->input->get("sequence");

		$first = ((int)$sequence*10)-9;

		$last =  ((int)$sequence* 10);

		$data_array = $this->get_mapped_data_array_from_session();

		$fields_count = 0;

		$soap_request = null;
			
		$soap_response =  null;

		//remove first column list element

		array_shift($data_array);

		$offset = $first-1;
			
		$batch_data = array_slice($data_array,$offset,10);

		//echo 'SEQUENCE:'.$sequence." first:".$first." last:".$last;
			
		$i = 0;

		foreach($batch_data as $datarows=>$datacolls){

			$fields = array();

			foreach($datacolls as $col_index => $datacell){

				$mapped_col_info = $this->get_mapped_col_info($col_index);
					
				if($mapped_col_info['DATATYPE'] == 'DATE'){

					if($datacell !== ''){

						$datacell = date("Y-m-d", strtotime($datacell));
					}else {
						$datacell = '0001-01-01';
					}
						
				}else if($mapped_col_info['DATATYPE'] =='FLOAT'){
					
					if($datacell == '' || empty($datacell)){
						
						$datacell = 0.00 ;
						
					}else{
						
						$datacell = floatval($datacell);
					}
					
				}else if($mapped_col_info['DATATYPE'] =='INT'){
					
					if($datacell == '' || empty($datacell)){
						
						$datacell = 0 ;
						
					}else{
						
						$datacell = intval($datacell);
					}
				}

				$fields[trim($mapped_col_info['SOAP_FIELD'])] = $datacell;

				$fields_count++;
					
			}//end foreach

			ksort($fields);

			$BATCHSHIPMENTS[$i] = $fields;

			$i++;

		}//end foreach

		//in result table of view file has 2 more additional columns so add extra 2

		$fields_count += 2;

		//var_dump($BATCHSHIPMENTS);

		$CODESTATION = $this->session->userdata('CODESTATION');

		$ACCOUNTNO = $this->session->userdata('ACCOUNTNO');

		$USERNAME = $this->session->userdata('USERNAME');

		$PASSWORD = $this->session->userdata('PASSWORD');

		$CLIENTINFO = array(
				'CodeStation'=>$CODESTATION,
				'Password'=> $PASSWORD,
				'ShipperAccount'=>$ACCOUNTNO,
				'UserName'=>$USERNAME
		);
			
		$parameters =array(
				'CLIENTINFO'=>$CLIENTINFO,
				'BatchShpt'=>$BATCHSHIPMENTS
		);

		//MAKE SOUP CLIENT

		$context = stream_context_create(array(
				'ssl' => array(
						'verify_peer' => false,
						'verify_peer_name' => false,
						'allow_self_signed' => true
				),
				'http'=>array(
						'user_agent' => 'PHPSoapClient'
				)
		));

		$client = new SoapClient(SOAP_URL,
				array(
						'stream_context' => $context,
						'trace' => 1,
						'soap_version'   => SOAP_1_1,
						'style' => SOAP_DOCUMENT,
						'encoding' => SOAP_LITERAL,
						'cache_wsdl' => WSDL_CACHE_NONE
				));

		try {
				
			$result = $client->BatchShipments($parameters);
				
			$soap_request = $client->__getLastRequest();
				
			$soap_response =  $client->__getLastResponse();

			if($debug) $data['debug_data'] .=  "<pre>REQUEST:<br><textarea>" .$soap_request . "</textarea><br>";

			if($debug)$data['debug_data'] .=  "<br><pre>Response:<br><textarea>" . $soap_response . "</textarea>";//htmlentities(

			$shipment_upload_responce = $result->BatchShipmentsResult->SHIPMENT_INFO;

			if(count($shipment_upload_responce)>1){

				$m = 0;//to align with $BATCHSHIPMENTS array sequence

				foreach($shipment_upload_responce as $result){

					$response_msg = $result->ResponseMessage;
						
					$error_msg = $result->ErrorMessage;
						
					$RequestSequence = $result->RequestSequence;
						
					$connote = $result->Connote;

					if($debug) $data['debug_data'] .= "<hr><br>
					responce msg: $response_msg,
					error msg: $error_msg,
					request seq: $RequestSequence,
					connote: $connote";
						
					$batch_data[$m]= $this->soap_response_incorporater(
							$result,
							$batch_data[$m],
							$m);

					$m++;
						
				}//end foreach
					
			}else{
					
				$response_msg = $shipment_upload_responce->ResponseMessage;
					
				$error_msg = $shipment_upload_responce->ErrorMessage;
					
				$RequestSequence = $shipment_upload_responce->RequestSequence;
					
				$connote = $shipment_upload_responce->Connote;
					
				if($debug) $data['debug_data'] .= "<hr><br>
				responce msg: $response_msg,
				error msg: $error_msg,
				request seq: $RequestSequence,
				connote: $connote";

				$m = 0;

				$batch_data[$m] = $this->soap_response_incorporater(
						$shipment_upload_responce,
						$batch_data[$m],
						$m);
					
			}//end if

			$this->set_after_SOAP_response_data_array($batch_data);
				
		} catch (SoapFault $fault) {

			//trigger_error("SOAP Fault: (faultcode: {$fault->faultcode}, faultstring: {$fault->faultstring})", E_USER_ERROR);
				
			if($debug)$data['debug_data'] .= 'Error! '."SOAP Fault: (faultcode: {$fault->faultcode}, faultstring: {$fault->faultstring})";
				
			//in case if try block fails it does not get request data from inside the
			//try block so make requests data  from catch section.
				
			$soap_request = $client->__getLastRequest();

			$soap_response =  $client->__getLastResponse();
				
			$shipment_upload_responce = $soap_response;
				
			if (!is_array($shipment_upload_responce) && stripos($shipment_upload_responce, "validation errors") !== false) {

				if($debug){
						
					echo '<tr><td  colspan="'.$fields_count.'">Error! One or more mandatary SOAP fields data are missing!<textarea>'.$soap_request.'</textarea></td></tr>';
						
				}else{
						
					echo '<tr><td  colspan="'.$fields_count.'">Error! One or more mandatary SOAP fields data are missing!</td></tr>';
				}

			}//end if is_array()
				
			exit;

		}//end catch

		if(!$debug) $data['debug_data']= null;
			
		$soap_responce_data = $this->get_after_SOAP_response_data_array();

		if(!empty($soap_responce_data) && is_array($soap_responce_data)){

			echo $this->prepare_table_html($soap_responce_data,$sequence,$data['debug_data']);

		}else{
				
			if($debug){

				echo '<tr><td  colspan="'.$fields_count.'">Error!<textarea>'.$soap_request.'</textarea></td></tr>';

			}else{
					
				echo '<tr><td  colspan="'.$fields_count.'">Error!</td></tr>';
			}
		}

	}//end of function



	/**
	 * prepare_table_html()
	 *
	 * This function gets submitted soap requests array data and then prepares
	 * html table <tr> elements with values to be displayed in the grid via
	 * AJAX response.
	 *
	 * @param unknown $data_array
	 * @return string
	 */
	private function prepare_table_html($data_array,$sequence,$debug_data){

		$field_info = null;

		$mapped_cols_array = $this->get_mapped_cols_array();

		$tbody = '<tr>';

		$i=((int)$sequence*10)-9;

		foreach($data_array as $rows){

			//$tbody .= '<td>'.$i.'</td>';
				
			foreach($rows as $field_id=>$value){
					
				$tbody .= "<td>".$value."</td>";
			}
				
			if(!empty($debug_data)) $tbody.="<td>$debug_data</td>";
				
			$tbody .= '</tr>';
				
			$i++;
				
		}//end foreach

		return $tbody;

	}//end of function


	/**
	 * soap_response_incorporater()
	 *
	 * This function gets SOAP responses and incorporate them with existing mapped excel data
	 * array.
	 *
	 * @param array $soap_response
	 * @param array $data_row - excel data
	 * @param int $arr_sequence
	 * @return array
	 */
	private function soap_response_incorporater($soap_response,$data_row,$arr_sequence){


		//$data_array = $this->get_mapped_data_array_from_session();

		//var_dump($data_row);exit;

		$response_msg = $soap_response->ResponseMessage;

		$error_msg = $soap_response->ErrorMessage;

		$RequestSequence = $soap_response->RequestSequence;

		$connote = $soap_response->Connote;

		$msg = "";
		
		if($response_msg === 'FAILED'){
				
			$msg = $this->get_error($error_msg);
				
			$data_row['SOAP_error']= '<div class="soap_error">FAIL</div>';
			
			$data_row['new_connote'] = $msg;
				
			$this->set_fail_shipment_row($data_row);
				
		}else if($response_msg === 'INTERNAL ERROR'){
				
			$data_row['SOAP_error']= '<div class="soap_error">FAIL</div>';
			
			$data_row['new_connote'] = 'Could not upload record. Internal Server Error<BR>'.$response_msg;
				
			$this->set_fail_shipment_row($data_row);
				
		}else if($response_msg === 'SUCCESS'){
				
			$data_row['SOAP_error']= '<div class="soap_success">'.$response_msg.'</div>';
				
			$data_row['new_connote'] = $connote;
				
			$this->set_success_shipment_row($data_row);
				
		}//end if

		return $data_row;

	}//end of function



	private function set_success_shipment_row($shipment_array){

		$shipment_data = $this->get_success_shipment_row();

		$shipment_data[] = $shipment_array;

		$this->session->set_userdata('success_shipments',$shipment_data);

	}//end of function



	private function set_fail_shipment_row($shipment_array){
			
		$fail_shipment_data = $this->get_fail_shipment_row();

		$fail_shipment_data[] = $shipment_array;

		$this->session->set_userdata('fail_shipments',$fail_shipment_data);

	}//end of function



	private function get_fail_shipment_row(){

		return $this->session->userdata("fail_shipments");
	}


	private function get_success_shipment_row(){

		return $this->session->userdata("success_shipments");
	}


	public function get_failed_shipments(){

		$faild_shipments_array = $this->get_fail_shipment_row();

		$this->create_excel_file($faild_shipments_array,'fail');

	}//end of function



	public function get_success_shipments(){

		$success_shipments_array = $this->get_success_shipment_row();

		$this->create_excel_file($success_shipments_array,'success');

	}//end of function

	

	/**
	 * get_error()
	 *
	 * This function gets error codes from SOAP response and prepares readable
	 * error messages.
	 *
	 * @param string $error_code
	 * @return string
	 */
	private function get_error($error_code){

		$msg = null;

		$debug = true;
		/*
		switch($error_code){
				
			case "Airwaybill Already Exist":

				$msg = "Airwaybill Already Exist";

				break;
					
			case "PICKUPNOTFOUND":

				$msg = "PICKUP number is not found.";

				break;

			case "JCSNOPICKUP":
					
				$msg = "Either JCS or PICKUP number is incorrect.";
					
				break;

			case"Invalid ConsigneeProvinceCode":

				$msg = "Consignee Provice Code is incorrect";

				break;

			case "Invalid ConsigneeCountryCode":

				$msg = "Consignee Country Code is incorrect";

				break;

			case "The ServiceCode field is required":

				$msg = "Service Code is required";

				break;

			case "Invalid CodeService":

				$msg = "Service Code is incorrect";

				break;


		}//end switch
		*/

		if($debug) $msg.= $error_code;

		return $msg;

	}//end of function



	/**
	 * set_after_SOAP_response_data_array()
	 *
	 * This function stores Excel data array after SOAP requst is made and received
	 * responses.
	 *
	 * @param array $data_array
	 */
	private function set_after_SOAP_response_data_array($data_array){

		$this->session->unset_userdata('after_SOAP_data_array');

		$this->session->set_userdata('after_SOAP_data_array',$data_array);

	}//end of function



	/**
	 * get_after_SOAP_response_data_array()
	 *
	 * This function gets the data array which is stored after SOAP response
	 *
	 * @return array
	 */
	private function get_after_SOAP_response_data_array(){

		$data_array = $this->session->userdata('after_SOAP_data_array');

		return $data_array;
			
	}//end of function



	/**
	 * searcharray()
	 *
	 * This function searches for a key in the array,
	 * then if found it returns its index
	 *
	 * @param string $value
	 * @param string $key
	 * @param array $array
	 * @return int|NULL
	 */
	private function searcharray($value, $key, $array) {

		foreach ($array as $k => $val) {
				
			if ($val[$key] == $value) {

				return $k;
			}
		}

		return null;

	}//end of function



	//depricated
	/**
	 * upload_shipment()
	 *
	 * This function maks SOAP array from server side directly.
	 *
	 * @param unknown $data_array
	 */
	public function upload_shipment($data_array){

		$data['debug_data'] = null;

		//$this->dump_data($data_array);

		$i = 0;

		foreach($data_array as $datarows=>$datacolls){
				
			//$BATCHSHIPMENTS['BATCHSHIPMENTS'] = array();
				
			$fields = array();
				
			foreach($datacolls as $col_index => $datacell){

				$mapped_col_info = $this->get_mapped_col_info($col_index);

				//$this->dump_data($mapped_col_info);

				//if($i>0) {
				if($mapped_col_info['DATATYPE'] == 'DATE'){

					$datacell = date("Y-m-d", strtotime($datacell));

					//$this->dump_data($datacell);
				}
				$fields[trim($mapped_col_info['SOAP_FIELD'])] = $datacell;
					
				//}

			}//end foreach
				
			ksort($fields);

			$BATCHSHIPMENTS[$i] = $fields;
				
			$i++;
				
		}//end foreach

		array_shift($BATCHSHIPMENTS);//remove first elemtn and keys from the array

		//$this->dump_data($BATCHSHIPMENTS);


		$CLIENTINFO = array(
				'CodeStation'=>$this->CODESTATION,
				'Password'=> $this->PASSWORD,
				'ShipperAccount'=>$this->ACCOUNTNO,
				'UserName'=>$this->USERNAME
		);
			
		$parameters1 =array(
				'CLIENTINFO'=>$CLIENTINFO,
				'BatchShpt'=>$BATCHSHIPMENTS
		);

		//$this->dump_data($parameters1);

		try {
				
			//
			//
			$client = new SoapClient(SOAP_URL,
					array('trace' => 1,
							'soap_version'   => SOAP_1_1,
							'style' => SOAP_DOCUMENT,
							'encoding' => SOAP_LITERAL,
							'cache_wsdl' => WSDL_CACHE_NONE
					));
				
			$result = $client->BatchShipments($parameters1);
				
			$data['debug_data'] .=  "<pre>REQUEST:\n" . htmlentities($client->__getLastRequest()) . "\n";
				
			$data['debug_data'] .=  "<br><pre>Response:\n" . htmlentities($client->__getLastResponse()) . "\n";//htmlentities(
				
			$shipment_upload_responce = $result->BatchShipmentsResult->SHIPMENT_INFO;
				
			if(count($shipment_upload_responce)>1){
					
				foreach($shipment_upload_responce as $result){
						
					$response_msg = $result->ResponseMessage;
					$error_msg = $result->ErrorMessage;
					$RequestSequence = $result->RequestSequence;
					$connote = $result->Connote;
						
					$data['debug_data'] .= "<hr><br>
					responce msg: $response_msg,
					error msg: $error_msg,
					requence seq: $RequestSequence,
					connote: $connote";
				}
					
			}else{

				//var_dump($shipment_upload_responce);
				/*object(stdClass)#111 (4) { ["Connote"]=> string(12) "100000352763"
				 * ["ErrorMessage"]=> string(14) "PICKUPNOTFOUND"
				 * ["RequestSequence"]=> string(1) "1"["ResponseMessage"]=> string(6) "FAILED" }*/

				$response_msg = $shipment_upload_responce->ResponseMessage;
					
				$error_msg = $shipment_upload_responce->ErrorMessage;
					
				$RequestSequence = $shipment_upload_responce->RequestSequence;
					
				$connote = $shipment_upload_responce->Connote;
					
				$data['debug_data'] .= "<hr><br>
				responce msg: $response_msg,
				error msg: $error_msg,
				requence seq: $RequestSequence,
				connote: $connote";
			}

		} catch (SoapFault $fault) {
				
			//trigger_error("SOAP Fault: (faultcode: {$fault->faultcode}, faultstring: {$fault->faultstring})", E_USER_ERROR);
			$data['debug_data'] .= 'Error! '."SOAP Fault: (faultcode: {$fault->faultcode}, faultstring: {$fault->faultstring})";
		}

		$this->load->view('soap_request_responce_view', $data);

	}//end of funtion


	
	
	/**
	 * test_data()
	 * 
	 */
	private function test_data(){

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
				'PickupNumber'=>'R20016/1234',
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

	}//end of test data


	
	

	private function create_excel_file($data_array,$type){

		//var_dump($data_array);exit;
		
		$new_array = array();
		
		$cols_array = array();
					
		//load our new PHPExcel library
			
		$this->load->library('excel');
			
		//activate worksheet number 1
			
		$this->excel->setActiveSheetIndex(0);
			
		//name the worksheet
			
		$this->excel->getActiveSheet()->setTitle('Data Upload Template');
			
		//get uploaded file columns array
		
		$up_file_col_array = $this->get_uploaded_file_col_list();
				
		$mapped_col_array = $this->get_mapped_cols_array();				
		
		foreach($mapped_col_array as $field_id => $col_id){
			
			//get field info 
			
			$field_info = $this->get_mapped_col_info($field_id);
			
			//get column info			
			
			$cols_array[] = $field_info['FIELD_LABEL'];					
			
		}//end foreach
		
		 array_push($new_array, $cols_array);	
		
		 $cols_array2 = array();
		 
		foreach($mapped_col_array as $field_id => $col_id){				
			
			//get column info
		
			$cols_array2[] = (!empty($up_file_col_array[$col_id]))?$up_file_col_array[$col_id]:null;				
				
		}//end foreach	
		
		array_push($new_array, $cols_array2);
		
		foreach($data_array as $row){
			
			$new_array[] = $row;
		}
		
		//var_dump($new_array);exit;
		
		$row_no = 1;

		foreach($new_array as $row=>$cols){

			$col_idx = 0;

			foreach($cols as $data){
					
				//$this->excel->getActiveSheet()->setCellValue($i.'1', $col);
					
				$data = $this->rip_tags($data);
					
				$this->excel->getActiveSheet()->setCellValueByColumnAndRow($col_idx, $row_no, $data);
					
				$col_idx++;
			}

			$row_no++;

		}//endforeach

		if($type =='success'){

			$filename = 'successful_shipment.xls';

		}else{

			$filename = 'failed_shipment.xls';
		}
			
		header('Content-Type: application/vnd.ms-excel'); //mime type
			
		header('Content-Disposition: attachment;filename="'.$filename.'"'); //tell browser what's the file name
			
		header('Cache-Control: max-age=0'); //no cache

		//save it to Excel5 format (excel 2003 .XLS file), change this to 'Excel2007'
		//(and adjust the filename extension, also the header mime type)
		//if you want to save it as .XLSX Excel 2007 format
			
		$objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');

		//force user to download the Excel file without writing it to server's HD
			
		$objWriter->save('php://output');
			
		redirect('ExcelUploader/index?login_data='.$this->session->userdata('login_data'));

	}//end of function

}//end of the class
?>

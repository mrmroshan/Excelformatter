<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Home extends CI_Controller {

	
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


	public function index()
	{		
		
		$file_data = array();

		$file_data['error'] = null;
		
		$file_element = (!empty($_FILES['userfile']))?$_FILES['userfile']:false;
		

		if(!empty($file_element)){

			$file_data = $this->upload();
			
			$this->extract_excel_file_data($file_data['upload_data']);
			
		}//end if

		$this->load->view('home_view', $file_data);		

	}//end of function

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
		
		echo '<table>' . "\n";
		for ($row = 1; $row <= $highestRow; ++$row) {
		    echo '<tr>' . PHP_EOL;
		    for ($col = 0; $col <= $highestColumnIndex; ++$col) {

		        echo '<td>' . $objWorksheet->getCellByColumnAndRow($col, $row)->getValue() . '</td>' . PHP_EOL;
		    }
		    echo '</tr>' . PHP_EOL;
		}
		echo '</table>' . PHP_EOL;
		

	}//end of function

	public function save_in_db($excel_data){


	}//end of function

	
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
	

	public function column_setup(){
		$this->load->view('column_setup');
	}//end of function




	public function test_2(){		
		
				
		/**  Identify the type of $inputFileName  **/
		$inputFileType = PHPExcel_IOFactory::identify($inputFileName);
		/**  Create a new Reader of the type that has been identified  **/
		$objReader = PHPExcel_IOFactory::createReader($inputFileType);
		/**  Load $inputFileName to a PHPExcel Object  **/
		$objPHPExcel = $objReader->load("application/controllers/test1.xlsx");

		//$objReader = PHPExcel_IOFactory::createReader('Excel2007');
		
		$objReader->setReadDataOnly(TRUE);
		//$objPHPExcel = $objReader->load("application/controllers/test1.xlsx");
		
		$objWorksheet = $objPHPExcel->getActiveSheet();

		// Get the highest row and column numbers referenced in the worksheet
		$highestRow = $objWorksheet->getHighestRow(); // e.g. 10
		$highestColumn = $objWorksheet->getHighestColumn(); // e.g 'F'
		$highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn); // e.g. 5
		
		echo '<table>' . "\n";
		for ($row = 1; $row <= $highestRow; ++$row) {
		    echo '<tr>' . PHP_EOL;
		    for ($col = 0; $col <= $highestColumnIndex; ++$col) {

		        echo '<td>' . $objWorksheet->getCellByColumnAndRow($col, $row)->getValue() . '</td>' . PHP_EOL;
		    }
		    echo '</tr>' . PHP_EOL;
		}
		echo '</table>' . PHP_EOL;
	}//end of function




	public function excel_test(){
		
		//load our new PHPExcel library
		$this->load->library('excel');
		//activate worksheet number 1
		$this->excel->setActiveSheetIndex(0);
		//name the worksheet
		$this->excel->getActiveSheet()->setTitle('test worksheet');
		//set cell A1 content with some text
		$this->excel->getActiveSheet()->setCellValue('A1', 'This is just some text value');
		//change the font size
		$this->excel->getActiveSheet()->getStyle('A1')->getFont()->setSize(20);
		//make the font become bold
		$this->excel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
		//merge cell A1 until D1
		$this->excel->getActiveSheet()->mergeCells('A1:D1');
		//set aligment to center for that merged cell (A1 to D1)
		$this->excel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		
		$filename='just_some_random_name.xls'; //save our workbook as this file name
		header('Content-Type: application/vnd.ms-excel'); //mime type
		header('Content-Disposition: attachment;filename="'.$filename.'"'); //tell browser what's the file name
		header('Cache-Control: max-age=0'); //no cache
		//save it to Excel5 format (excel 2003 .XLS file), change this to 'Excel2007' (and adjust the filename extension, also the header mime type)
		//if you want to save it as .XLSX Excel 2007 format
		$objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5'); 
		//force user to download the Excel file without writing it to server's HD
		$objWriter->save('php://output');
	}//end of function
	


	
}
?>

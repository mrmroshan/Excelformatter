<?php defined('BASEPATH') OR exit('No direct script access allowed');
include("header.php")
?>

<div class="container-fluid">
  <div class="col-md-12 offset4">
    
    <div class="panel panel-default">
    <div class="panel-heading">
      <h2 class="panel-title">Upload File</h2>
    </div>
    <div class="panel-body">
    <?php 
    if(!empty ($this->session->flashdata('error'))){
    	include 'error_msg.php';
	}
    ?>    
      <h3>
      Please upload your Excel file to proceed
      </h3>
      <hr>
      
      <br>
      
      <div class="row">
      <div class="col-md-8 col-md-offset-4" >
      
	    
        <?php //echo (!empty($error))?$error:null;?>

        <?php echo form_open_multipart('ExcelUploader/wizard');?>

        <input type="file" name="userfile" size="20" value ="" class ="btn btn-primary lg" />
        
        <?php if (!empty($upload_data)){?>
        <br>
        	Your file has been uploaded!
        <?php } ?>
        <br /> 
        
    </div>
        
    </div>
      <div class="row">
      <div class="col-md-12">
      <div class="alert alert-info">
    	<strong>Please follow the instructions given below.</strong>     	
  	  </div>
      </div><!-- end col -->
      </div><!-- end row -->
      
      
      <div class="row">
      <div class="col-md-6">
      <div class="thumbnail">
	      
	        <img src="<?php echo base_url("public/imgs/correct.png")?>" style="width:80%">
	        <div class="caption">
	          <p>Here is an exampe file of a correct file format. </p>
	        </div>  
      </div>
      </div>
      <div class="col-md-6">
      <div class="thumbnail">	      
	        <img src="<?php echo base_url("public/imgs/incorrect.png")?>" style="width:90%">
	        <div class="caption">
	          <p>Make sure your Excel file contains no empty values on first row and first column. </p>
	        </div>  
      </div>
      </div>
      </div>
      

	  
      

    <!-- <form action="<?php echo site_url('ExcelUploader/wizard')?>" method="post">-->
    <input type="hidden" name="step" id="step" value="upload">
    <input type ="submit" class ="btn btn-primary btn-lg pull-right" value ="Next" id="btnNext">
    </form>
          
    </div>
     
    </div>

  </div>

</div><!-- /.container -->

<?php include ("footer.php")?>
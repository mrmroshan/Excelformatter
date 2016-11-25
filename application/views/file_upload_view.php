<?php defined('BASEPATH') OR exit('No direct script access allowed');
include("header.php")
?>

<div class="container">
  <div class="col-md-12 offset4">
    
    <div class="panel panel-default">
    <div class="panel-heading">
      <h2 class="panel-title">Step 1</h2>
    </div>
    <div class="panel-body">
    <?php 
    if(!empty ($this->session->flashdata('error'))){
    	include 'error_msg.php';
	}
    ?>    
      <h3 style="text-align:center">Please upload your Excel file to list all columns in the spread sheet</h3>
      <br>
      <div class="row">
      <div class="col-md-6 col-md-offset-3" >
      <div class="row">
      	<div class="panel panel-default">
	    <div class="panel-heading">
	      <h2 class="panel-title">Upload Excel File</h2>
	      
	    </div>
	    <div class="panel-body ">
	      
        <?php //echo (!empty($error))?$error:null;?>

        <?php echo form_open_multipart('ExcelUploader/wizard');?>

        <input type="file" name="userfile" size="20" value ="" class ="btn btn-primary lg" />
        
        <?php if (!empty($upload_data)){?>
        <br>
        	Your file has been uploaded!
        <?php } ?>
        <br /><br />
        <!-- 
        <input type ="submit" class ="btn btn-primary lg" value ="Upload File">
      	</form>
        -->
        </div><!-- end of panel body -->
        </div><!-- end of panel -->       
        </div><!-- end of col md 6 -->
        
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
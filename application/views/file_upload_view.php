<?php defined('BASEPATH') OR exit('No direct script access allowed');
include("header.php")
?>

<div class="container">
  <div class="span4 offset4">
    
    <div class="panel panel-default">
    <div class="panel-heading">
      <h2 class="panel-title">Step 2</h2>
    </div>
    <div class="panel-body">
      <p>Please upload your Excel file to list all columns in the spread sheet</p>
      
      <div class="col-md-6 col-md-offset-3" >
      <div class="row">
      	<div class="panel panel-default">
	    <div class="panel-heading">
	      <h2 class="panel-title">Step 2</h2>
	    </div>
	    <div class="panel-body">
	        
        <?php echo (!empty($error))?$error:null;?>

        <?php echo form_open_multipart('templates/upload_excel');?>

        <input type="file" name="userfile" size="20" value ="" />
        
        <?php if (!empty($upload_data)){?>
        <br>
        	Your file has been uploaded!
        <?php } ?>
        <br /><br />
        
        <!-- <input type ="submit" class ="btn btn-primary lg" value ="Upload File">-->
        
        </div><!-- end of panel body -->
        </div><!-- end of panel -->       
        </div><!-- end of col md 6 -->
        
    
	  <input type ="submit" class ="btn btn-primary btn-lg pull-right" value ="Next" id="btnNext">
      </form>
   
    </div>
    </div>
    </div>
  </div>

</div><!-- /.container -->

<?php include ("footer.php")?>
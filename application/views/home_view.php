<?php defined('BASEPATH') OR exit('No direct script access allowed');
include("header.php")
?>

<div class="container">
  <div class="span4 offset4">
    
    <div class="panel panel-default">
    <div class="panel-heading">
      <h3 class="panel-title">Upload Excel File</h3>
    </div>
    <div class="panel-body">
      <p>Please upload your Excel file to proceed</p>
      
      <hr>
      
        <?php echo (!empty($error))?$error:null;?>

        <?php echo form_open_multipart('home/index');?>

        <input type="file" name="userfile" size="20" value ="" />
        
        <br /><br />
        
        <input type ="submit" class ="btn btn-primary lg" value ="Upload File">
      </form>

      <ul>
        <?php if (!empty($upload_data)):?>
        <?php foreach ($upload_data as $item => $value):?>
        <li><?php echo $item;?>: <?php echo $value;?></li>
        <?php endforeach; ?>
      <?php endif?>
      </ul>

   
    </div>
    </div>
  </div>

</div><!-- /.container -->

<?php include ("footer.php")?>
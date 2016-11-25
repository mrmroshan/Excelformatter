<?php defined('BASEPATH') OR exit('No direct script access allowed');
include("header.php")
?>

<div class="container">
	<div class="row">
		<div class="col-md-12">
    
	    <div class="panel panel-default">
	    <div class="panel-heading">
	      <h2 class="panel-title">Step 1</h2>
	    </div>
	    <div class="panel-body">
	    <?php 
	    if(!empty ($this->session->flashdata('error'))){include 'error_msg.php';}?>    
	      <h3 style="text-align:center">Please select matching column names from the dropdowns to map with fields names</h3>
	      <br>
	      <form class="form-horizontal">
	      <?php foreach($all_field_list_array as $category=>$fields){?>
	      <div class="row">
	      <div class="col-md-10 col-md-offset-1" >
	      <div class="row">
	      	<div class="panel panel-default">
		    <div class="panel-heading">
		      <h2 class="panel-title"><?php echo str_replace('_'," ",$category)?></h2>		      
		    </div>
		    <div class="panel-body ">
		    
  
		    
		   	<?php foreach($fields as $k =>$v ){?>
		   	<div class="form-group">
    			<label for="<?php echo $k;?>" class="col-sm-2 control-label"><?php echo $k;?></label>
    				<div class="col-sm-10">
      				<select class="dropdown-toggle form-control" name="" id="up_file_fields_<?php echo $k?>">
					    <option value="">Please select a field</option>
					    <?php 
					    foreach($up_file_col_list as $col_list){
					    	echo '<option value="'.$col_list.'">'.$col_list.'</option>';
					    }
					    ?>
					   		    
					 </select>   
				</div><!--/col-sm-10 -->
  			</div><!--/form-group -->	   		
				
		   	<?php }?>  	       
	        </div><!-- end of panel body -->
	        </div><!-- end of panel -->       
	        </div><!-- end of col md 6 -->	
	        	          
	    </div><!-- /col-md-10 -->     
	    </div><!-- /row -->
	    <?php }//endforeach?>
	    
	     <input type="hidden" name="step" id="step" value="upload">
	    <input type ="submit" class ="btn btn-primary btn-lg pull-right" value ="Next" id="btnNext">
	    </form>
  	</div><!-- /col-md-12 -->	
	</div><!-- /row -->
</div><!-- /.container -->
<script type="text/javascript">

$(document).ready(function() {
	  //$(".js-example-basic-single").select2();
	  $('select').select2({
		  placeholder: "Select a field",
		  allowClear: true
		});
});

</script>
<?php include ("footer.php")?>
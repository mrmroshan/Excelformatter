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
	      <form class="form-horizontal" method="post" action="<?php echo site_url('ExcelUploader/wizard')?>">
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
    			<label for="<?php echo $k;?>" class="col-sm-4 control-label"><?php echo $k;?></label>
    				<div class="col-sm-8">
      				<select class="dropdown-toggle form-control" style="width: 50%" name="<?php echo $k?>" id="<?php echo $k?>">
					    <option value="">Please select a field</option>
					    <?php 					    
					   
					    $i = 0;
					    foreach($up_file_col_list as $col_list){
					    	
					    	if( strpos( strtoupper(trim($k)), strtoupper(trim($col_list)) ) !== false ) {
					    		echo '<option value="'.$i.'" selected="selected">'.$col_list.'</option>';
					    	}else{
					    		echo '<option value="'.$i.'">'.$col_list.'</option>';
					    	}
					    	$i++;
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
	    
	    <input type="hidden" name="step" id="step" value="validate">
	    <input type ="submit" class ="btn btn-primary btn-lg pull-right" name="submit" value ="Next" id="btnNext">
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
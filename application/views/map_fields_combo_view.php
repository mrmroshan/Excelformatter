<?php defined('BASEPATH') OR exit('No direct script access allowed');
include("header.php")
?>

<div class="container-fluid">
	<div class="row">
		<div class="col-md-12">
    
	    <div class="panel panel-default">
	    <div class="panel-heading">
	      <h2 class="panel-title">Map Columns</h2>
	    </div>
	    <div class="panel-body">
	       <h3>Map columns with labels</h3>
	      <p>Please select matching column names from the dropdowns to map with labels.<br>
	      Labels which are in red color are mandatory fields.
	      <br>
	      After mapping the fields please click on <b>'Next'</b> button at the bottom to proceed to next page to verify data.<br>
	      If you have mistakenly uploaded a wrong file, click on <b>'Previous'</b> button to go to file upload page.
	       
	      </p>
	      <hr>
	      <br>
	      <?php if(!empty ($this->session->flashdata('error'))){include 'error_msg.php';}?>  
	      <div id="msg"></div>	    
	      <form id = "mapfields" class="form-horizontal" method="post" action="<?php echo site_url('ExcelUploader/wizard')?>" onsubmit="">
	     	
	      <?php 
	      
	      //var_dump($mapped_form_elements);
	      	      
	      //echo '<pre>';var_dump($all_field_list_array);exit;
	      
	      foreach($all_field_list_array as $category=>$fields){?>
	      
		      <div class="row">
		     		    
			   	<?php 
			   	
			   	$items_per_col = ((count($fields)/3)%10 != 0 )? round(count($fields)/3):(count($fields)/3);
			  	
			   	$field_count = 1;
			   	
			   	foreach($fields as $k1){
			   	
			   		if($field_count == 1 || 
			   				$field_count == ($items_per_col+1) || 
			   				$field_count == (($items_per_col * 2)+1)){
			   			
			   			echo '<div class="col-md-4">';
			   			
			   		}
			   		
			   		?>
			   			   		
				   	<div class="form-group">
		    			<label for="<?php echo $k1['FIELD_LABEL'];?>" class="col-sm-5 control-label">
		    			<?php echo ($k1['REQUIRED'] ==1)? '<font style="color:red;">'.$k1['FIELD_LABEL'].'</font>':$k1['FIELD_LABEL'];?>
		    			
		    			<?php if($k1['REQUIRED'] == 1){?><font style="color:red;">*</font><?php }?>
		    			
		    			<?php $element_name = $k1['FIELD_ID'].'-'.str_replace(" ","_", $k1['FIELD_LABEL']);?>
		    			
		    			</label>
		    				
		    				<div class="col-sm-7">
		      				<select class="dropdown-toggle form-control" 
		      					style="width: 80%" 
		      					name="<?php echo $element_name;?>" 
		      					id="<?php echo str_replace(" ","_", $k1['FIELD_LABEL'])?>">
							    
							    
							    <?php 					    
							   /*
							    $i = 0;
							    foreach($up_file_col_list as $col_list){
							    	
							    	$mapped_fields_array = explode("|", $k1['MAPPED_COL_NAMES']);
							    	//var_dump($mapped_fields_array);exit;
							    	
							    	if( in_array( trim($col_list),$mapped_fields_array ) ) {
							    		echo '<option value="'.$i.'" selected="selected">'.$col_list.'</option>';
							    	}else{
							    		echo '<option value="'.$i.'"  >'.$col_list.'</option>';
							    	}
							    	
							    	$i++;
							    }		
							    */
							    
							    $i = 0;
							    
							    foreach($up_file_col_list as $col_list){					    	
							    		
							    	if( $mapped_form_elements[$element_name] == $i ) {
							    									    		
							    		echo '<option value="'.$i.'" selected="selected">'.$col_list.'</option>';
							    		
							    	}else if ($mapped_form_elements[$element_name] != '' ) {
							    		
							    		echo '<option value="'.$i.'"  >'.$col_list.'</option>';
							    		
							    	}else{
							    		
							    		echo '<option value="" selected="selected">Please select an excel column name</option>';
							    		
							    		echo '<option value="'.$i.'"  >'.$col_list.'</option>';
							    		
							    		
							    	}
							    	
							    	$i++;
							    }
							    
							    ?>							   		    
							 </select>   
						</div><!--/col-sm-10 -->
		  			</div><!--/form-group -->	   		
					
				   	<?php 
				   		
				   				   	
				   		$field_count++;
				   		if($field_count == ($items_per_col+1) || 
				   				$field_count == (($items_per_col * 2 )+ 1) || 
				   				($field_count > ($items_per_col * 2 ) && $field_count < ($items_per_col * 2)) ){
				   			echo '</div>';
				   		}
				   	}
				   	?>  	           
		        	          
		    </div><!-- /col-md-10 -->     
		    </div><!-- /row -->
	    <?php }//endforeach?>
	    
	     
	    
	    <input type="hidden" name="step" id="step" value="preview_uploaded_data">
	    <input type ="submit" class ="btn btn-primary btn-lg pull-right" name="submit" value ="Next" id="btnNext">
	    </form>
	    <form action="<?php echo site_url('ExcelUploader/wizard')?>" method="post">
     	<input type="hidden" name="step" id="step" value="upload">
     	<button type ="submit" class ="btn btn-primary btn-lg"  id="btnPrevious">Previous</button>
     	</form> 
	    
  	</div><!-- /col-md-12 -->	
	</div><!-- /row -->
</div><!-- /.container -->
<script type="text/javascript">

var debug = false;

$(document).ready(function() {	

	 var required_fields=[];	 
	 
	 <?php 
	 //get required fields array for validation	 
	 
	 foreach($all_field_list_array as $category=>$fields){
	 	
		foreach($fields as $k1){
			
			if($k1['REQUIRED'] == 1){				
				?>				
				required_fields.push( '<?php echo  str_replace(" ","_", $k1['FIELD_LABEL']);?>');				
				<?php 
			}			
		}//end foreach		
	 }//end foreach
	 
	 ?>
	
	  $('select').select2({
		  
		  placeholder: "Select a field",
		  allowClear: true
		  
		});
			  
	  $( "#mapfields" ).submit(function( event ) {

		 var empty_fields_list = "";
		  
		 //event.preventDefault(); 
		 
		 if(debug) console.log('Form submit called');	 
		  		  
		  $("select").each(function(){		  
			  
		        if(debug)console.log(
				        'Type:' + this.type + 
				        '  Name:' + this.name + 
				        '  Value:'+ this.value);				
			});		
			
		 if(debug)console.log('REQUIRED FIELDS:'+required_fields);	 
		  
		 for (var i = 0; i < required_fields.length; i++) {
			  
		      if(debug)console.log(required_fields[i]);
		      
		      var value = $("#"+required_fields[i]).val();
		      
		      if (value == ""){
			      
				empty_fields_list += required_fields[i]+", " 
				
			   }      
			   
		  }//end for
		  
		  console.log(empty_fields_list);
		  
		  if(empty_fields_list !=""){
			  
			  	show_error('Please map following required fields to proceed..<br>'+empty_fields_list,'error')
				
				return false;
		  }else{
				return true;
		  }			
		  
		});//end submit
		
});//end ready

function show_error(msg ,type)
{
	
	var error = '<div class="alert alert-warning alert-dismissible" role="alert">'+
	'<button type="button" class="close" data-dismiss="alert" aria-label="Close">'+
	'<span aria-hidden="true">&times;</span></button>'+
	'<strong>Error!</strong> '+ msg +
	'</div>';
	$('#msg').html(error);
	
}

</script>
<?php include ("footer.php")?>
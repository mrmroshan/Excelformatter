<?php defined('BASEPATH') OR exit('No direct script access allowed');
include("header.php")
?>
<style>
.err_exceed_limit{border: solid pink 5px;height:50px;}
.err_empty{border: solid red 5px;width:100px;height:50px;}
.err_invalid_data{border: solid black 5px;width:100px;height:50px;}
textarea{width:100%; height:100%;}
td{min-width: 100px;}
</style>
<div class="container-fluid">
<div class="row">
  <div class="col-md-12">
    
    <div class="panel panel-default">
    <div class="panel-heading">
      <h2 class="panel-title">Verify Uploaded Data</h2>
    </div>
    <div class="panel-body">
           
       	<h3>Verify mapped data</h3>

		Please verify both mapped column name and label name on the grid.<br>
		If any column is mistakenly mapped with wrong column, then you can always go back to previous page by clicking on <b>'Previous'</b> button at 
		the bottom and redo the mapping.<br>
		To upload current mapped data, then press <b>'Upload Grid'</b> button at the bottom

          
          
            
        <hr>       
         <?php if(!empty ($this->session->flashdata('error'))){include 'error_msg.php';}?>  
		<div style="overflow:scroll;height:auto;;max-height:600px;width:100%"><!-- table wrapper -->
		<form method="post" action="<?php echo site_url("ExcelUploader/wizard")?>">
			<table class="table table-bordered table table-hover">		    
		    <?php 
		     //echo '<pre>';var_dump($mapped_data_array);exit;
		     echo '<thead>';
		     echo '<tr class="info">';
		     echo '<th>Excel Row #</th>';		     
		     foreach($all_fields_list as $index=>$field){
		     	
		     	echo '<th ">'.$field.'</th>';
		     	
		     }//end foreach
		     
		     echo '</tr>';
		     echo '</thead>';
		     
		     $i=1;
		     echo '<tbody>';
		     foreach($mapped_data_array as $rows){
		     	
		     	echo ($i==1)? '<tr class="active">':'<tr>';
		     	echo '<td>'.$i.'</td>';
			     foreach($rows as $col){
			     	
			     	
				    echo "<td>".$col."</td>";
				    
				 }
				 
				 echo '</tr>';
				 echo '</tbody>';				 
				 $i++;			
		     }//end foreach	      
		    
		    ?>		     
		   
		  </table>
		  		  
		</div><!--/ table wrapper -->
		<br>
		<input type="hidden" name="step" id="step" value="validate_grid">
        <button type ="submit" class ="btn btn-primary btn-lg pull-right"  id="btnUploadGrid" name="btnUploadGrid" value="upload_grid">Upload Grid</button>
	  </form>
	  
      <form action="<?php echo site_url('ExcelUploader/wizard')?>" method="post">
      <input type="hidden" name="step" id="step" value="mapping">
      <button type ="submit" class ="btn btn-primary btn-lg"  id="btnPrevious">Previous</button>
      </form> 
       
    </div>
    </div>
    </div>
  </div>
</div>
</div><!-- /.container -->

<script>
$(document).ready(function() {	
	
	$("div.err_empty , div.err_invalid_data , div.err_exceed_limit").closest( "tr" ).css( "background-color", "orange" );
	
});

</script>

<?php include ("footer.php")?>
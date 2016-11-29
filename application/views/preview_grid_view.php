<?php defined('BASEPATH') OR exit('No direct script access allowed');
include("header.php")
?>
<style>
.err_exceed_limit{background-color: pink;}
.err_empty{border: solid red 5px;width:100px;height:50px;}
</style>
<div class="container">
<div class="row">
  <div class="col-md-12">
    
    <div class="panel panel-default">
    <div class="panel-heading">
      <h2 class="panel-title">Step 3</h2>
    </div>
    <div class="panel-body">
    <h3>Please confirm the changes</h3>
            
        <hr>       
         <?php if(!empty ($this->session->flashdata('error'))){include 'error_msg.php';}?>  
		<div style="overflow:scroll;height:600px;width:100%"><!-- table wrapper -->
		
			<table class="table table-bordered table table-hover">		    
		    <?php 
		    //echo '<pre>';var_dump($all_fields_list);exit;
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
		     foreach($new_data_array as $rows){
		     	
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
		  <br>
		</div><!--/ table wrapper -->
		<br>
      <!-- <form method="post" action="<?php echo site_url("ExcelUploader/export_data")?>" onsubmit=""> -->
	  <button type ="submit" class ="btn btn-primary btn-lg pull-right"  id="btnNext">Export Data</button>
	  
      <form action="<?php echo site_url('ExcelUploader/wizard')?>" method="post">
      <input type="hidden" name="step" id="step" value="mapping">
      <button type ="submit" class ="btn btn-primary btn-lg"  id="btnPrevious">Previous</button>
      </form> 
      <!-- </form>-->
      
   
    </div>
    </div>
    </div>
  </div>
</div>
</div><!-- /.container -->

<?php include ("footer.php")?>
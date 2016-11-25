<?php defined('BASEPATH') OR exit('No direct script access allowed');
include("header.php")
?>

<div class="container">
<div class="row">
  <div class="col-md-12">
    
    <div class="panel panel-default">
    <div class="panel-heading">
      <h2 class="panel-title">Step 4</h2>
    </div>
    <div class="panel-body">
    <h2>Please confirm the changes</h2>
    <br>
      <h3>Original Excel Sheet Data</h3>        
		<div style="overflow: auto"><!-- table wrapper -->
		
			<table class="table table-bordered">
		    <!-- <thead>
		      <tr>
		        <th>Firstname</th>
		        <th>Lastname</th>
		        <th>Email</th>
		      </tr>
		    </thead>-->
		    <tbody>
		    <?php 
		    $i=0;
		    foreach($original_up_file_data as $rows){
		    	
		    	echo '<tr>';
		    	
		    	foreach($rows as $col){
		    		
		    		echo "<td>".$col."</td>";
		    	}		       
		      	echo '</tr>';
		      	
		      	$i++;
		      	
		      	if($i == 3) break;
		     }
		    ?>		     
		    </tbody>
		  </table>
		</div><!--/ table wrapper -->
       		       
        
        <hr>
        
        <h3>New Excel Sheet Data</h3>        
		<div style="overflow: auto"><!-- table wrapper -->
		
			<table class="table table-bordered">
		    <!-- <thead>
		      <tr>
		        <th>Firstname</th>
		        <th>Lastname</th>
		        <th>Email</th>
		      </tr>
		    </thead>-->
		    <tbody>
		    <?php 
		    $i=0;
		    foreach($new_data_array as $rows){
		    	echo '<tr>';
		    	foreach($rows as $col){
		    		echo "<td>".$col."</td>";
		    	}		       
		      	echo '</tr>';
		      	$i++;
		      	if($i == 3) break;
		     }
		    ?>		     
		    </tbody>
		  </table>
		</div><!--/ table wrapper -->
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
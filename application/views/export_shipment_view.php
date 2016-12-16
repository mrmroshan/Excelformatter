<?php defined('BASEPATH') OR exit('No direct script access allowed');
include("header.php")
?>
<style>
.err_exceed_limit{border: solid pink 5px;height:50px;}
.err_empty{border: solid red 5px;width:100px;height:50px;}
.err_invalid_data{border: solid black 5px;width:100px;height:50px;}
textarea{width:100%; height:100%;}
td{min-width: 100px;}
.ui-progressbar {
    position: relative;
  }
  .progress-label {
    position: absolute;
    left: 50%;
    top: 4px;
    font-weight: bold;
    text-shadow: 1px 1px 0 #fff;
  }
</style>

<div class="container">
<div class="row">
  <div class="col-md-12">
    
    <div class="panel panel-default">
    <div class="panel-heading">
      <h2 class="panel-title">Uploading...</h2>
    </div>
    <div class="panel-body">
    <h3>Upload Shipments</h3>
            
        <hr>
        <div id="progressbar"><div class="progress-label">Loading...</div></div>
        <div id="div1"></div>
        <br>
        
         <?php if(!empty ($this->session->flashdata('error'))){include 'error_msg.php';}?>  
		<div style="overflow:scroll;height:600px;width:100%"><!-- table wrapper -->
		 
		<!-- <form method="post" action="<?php echo site_url("ExcelUploader/wizard")?>">-->
			<table class="table table-bordered table table-hover" id="data_table">		    
		    <?php 
		     //echo '<pre>';var_dump($mapped_data_array);exit;
		     echo '<thead>';
		     echo '<tr class="info">';
		     echo '<th>Excel Row #</th>';		     
		     foreach($all_fields_list as $index=>$field){
		     	
		     	echo '<th>'.$field.'</th>';
		     	
		     }//end foreach
		     
		     echo '<th>Error Description</th>';
		     echo '</tr>';		     
		     echo '</thead>';
		     echo '<tbody>';
		     /*
		     $i=1;
		     
		     foreach($mapped_data_array as $rows){
		     	
		     	echo ($i==1)? '<tr class="active">':'<tr>';
		     	echo '<td>'.$i.'</td>';
			     foreach($rows as $col){			     	
			     	
				    echo "<td>".$col."</td>";
				    
				 }
				 
				 echo '</tr>';
				 			 
				 $i++;			
		     }//end foreach	      
		    */
		    ?>		     
		  </tbody>
		  </table>		  
		  		  
		</div><!--/ table wrapper -->
		<br>
		<input type="hidden" name="step" id="step" value="validate_grid">
        <button type ="submit" class ="btn btn-primary btn-lg pull-right"  id="btnUploadGrid" name="btnUploadGrid" value="upload_grid">Upload Grid</button>
	  <!-- </form>-->
	  
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
var debug = true;

$(document).ready(function() {	
	
	$("div.err_empty , div.err_invalid_data , div.err_exceed_limit").closest( "tr" ).css( "background-color", "orange" );

	var json_data = <?php echo $json_data_array;?>

	var req_no = <?php echo $req_no?>

	var tot_rows = <?php echo $tot_rows;?>

	var percentage = 0;

	if(debug) console.log('array count:'+ json_data.length);

   
    var progressbar = $( "#progressbar" );
    var progressLabel = $( ".progress-label" );

	progressbar.progressbar({
	    value: false,
	    change: function() {
	      progressLabel.text( progressbar.progressbar( "value" ) + "%" );
	    },
	    complete: function() {
	      progressLabel.text( "Complete!" );
	    }
	  });

	function progress(percentage) {
	    var val = progressbar.progressbar( "value" ) || 0;
	
	    progressbar.progressbar( "value", percentage );
	
	    if ( val < 99 ) {
	      //setTimeout( progress, 80 );
		      //progress;
	    }
	  }

	progressbar.progressbar( "value", percentage )
	

	var i = 0;
	
	//progress(0);
		
	for(var n=1; n <= req_no; n++){
		
		if(debug)console.log( 'req_no:'+ n);

		$.ajax({	
			  
			  url: "<?php echo site_url('ExcelUploader/ajax_create_shipment?sequence=')?>"+n,

			  async: true,
			   
			  success: function(result){
				  var div = $(".ui-progressbar-value");
				  div.css.width = percentage;

				//$("#div1").html(result);		
				        
			        if(debug)console.log('result:'+ result);
			        
			        if(debug)console.log('TotRows:'+tot_rows);
			        
			        percentage = ((n * 10 )/tot_rows)*100;
			        
			        if(debug)console.log('percentage:'+ percentage);

			        //if(percentage<=100){
				      //progressbar.progressbar( "value", percentage );
				        	progress(percentage);
				        	//$("#data_table").html(percentage+'%');

				        	$("#data_table").find('tbody').append(result);
			        //}
		        	
		    },
		    complete:function(result){
			//update(percentage);
		    }});
		
		
	}//end foreach
	
function update(){
	$("#div1").html(percentage+'%');
}
	
	
	/*
	$( function() {
	    var progressbar = $( "#progressbar" ),
	      progressLabel = $( ".progress-label" );
	 
	    progressbar.progressbar({
	      value: false,
	      change: function() {
	        progressLabel.text( progressbar.progressbar( "value" ) + "%" );
	      },
	      complete: function() {
	        progressLabel.text( "Complete!" );
	      }
	    });
	 
	    function progress() {
	      var val = progressbar.progressbar( "value" ) || 0;
	 
	      progressbar.progressbar( "value", val + 2 );
	 
	      if ( val < 99 ) {
	        setTimeout( progress, 80 );
	      }
	    }
	 
	    setTimeout( progress, 2000 );
	  } );
	*/

});

</script>

<?php include ("footer.php")?>
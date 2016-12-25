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
  
  /* Start by setting display:none to make this hidden.
   Then we position it in relation to the viewport window
   with position:fixed. Width, height, top and left speak
   for themselves. Background we set to 80% white with
   our animation centered, and no-repeating */
.modal {
    display:    none;
    position:   fixed;
    z-index:    1000;
    top:        50px;
    left:       0;
    height:     100%;
    width:      100%;
    background: rgba( 255, 255, 255, .8 ) 
                url('http://i.stack.imgur.com/FhHRx.gif') 
                50% 50% 
                no-repeat;
}

/* When the body has the loading class, we turn
   the scrollbar off with overflow:hidden */
body.loading {
    overflow: hidden;   
}

/* Anytime the body has the loading class, our
   modal element will be visible */
body.loading .modal {
    display: block;
}
  
</style>

<div class="container-fluid">
<div class="row">
  <div class="col-md-12">
    
    <div class="panel panel-default">
    <div class="panel-heading">
      <h2 class="panel-title">Admin</h2>
    </div>
    <div class="panel-body">
    <?php 
    //echo '<pre>';var_dump($results);
    /*
     *  [0]=>
  array(14) {
    ["FIELD_ID"]=>
    string(2) "55"
    ["CATEGORY_ID"]=>
    string(1) "1"
    ["FIELD_INDEX"]=>
    string(1) "0"
    ["FIELD_LABEL"]=>
    string(6) "SRL No"
    ["REQUIRED"]=>
    string(1) "1"
    ["REGXPATTERN"]=>
    string(7) "^[0-9]$"
    ["MAXCHARS"]=>
    string(2) "10"
    ["PRECHKFIELDS"]=>
    string(0) ""
    ["SOAP_FIELD"]=>
    string(15) "RequestSequence"
    ["DATATYPE"]=>
    string(3) "INT"
    ["MAPPED_COL_NAMES"]=>
    string(13) "Srl No|Srl.No"
    ["ID"]=>
    string(1) "1"
    ["CATEGORY"]=>
    string(3) "AWB"
    ["CAT_LABEL"]=>
    string(8) "AWB INFO"
  }*/
    ?>
    <h3></h3>
            
        <hr>
   
        <div id="msg_div"></div>
        <br>
        
         <?php if(!empty ($this->session->flashdata('error'))){include 'error_msg.php';}?>  
		<div style="overflow:scroll;height:600px;width:100%"><!-- table wrapper -->
		 
		<table class="table table-bordered table table-hover" id="data_table">	
			<thead>
		    <tr class="info">
		    <th>Id</th>		   			    		   
		    <th>Name</th>
		    <th>Data type</th>
		    <th>Required?</th>
		    <th>Order</th>
		    <th>Char Limit</th>
		    <th>Mapped SOAP Tag</th>		    
		    <th>Auto Mapping Column Names</th>
		    <th>Regex Pattern</th>
		    <th>Status</th>
		    
		    </tr>		     
		    </thead>
		    <tbody>	
		    <?php 
		    foreach($results as $row){
		    	echo '<tr>';
		    	echo '<td>'.$row['FIELD_ID'].'</td>';
		    	echo '<td><a href="'.site_url('admin/edit/'.$row['FIELD_ID']).'">'.$row['FIELD_LABEL'].'</a></td>';
		    	echo '<td>'.$row['DATATYPE'].'</td>';
		    	echo '<td>'.(($row['REQUIRED']==1)?'YES':'NO').'</td>';
		    	echo '<td>'.$row['FIELD_INDEX'].'</td>';
		    	echo '<td>'.$row['MAXCHARS'].'</td>';
		    	echo '<td>'.$row['SOAP_FIELD'].'</td>';		    	
		    	echo '<td>',$row['MAPPED_COL_NAMES'].'</td>';
		    	echo '<td>'.$row['REGXPATTERN'].'</td>';
		    	echo '<td>'.(($row['STATUS']==1)?'A':'I').'</td>';
		    }
		    ?>     
	  		</tbody>
	  </table>		  
	  		  
	</div><!--/ table wrapper -->
	<br>

    </div>
    </div>
    </div>
  </div>
</div>
</div><!-- /.container -->

<div class="modal"><!-- Place at bottom of page --></div>

<script>
var debug = true;

$(document).ready(function() {	
	
	
});

function set_error(text){

	var err_html =  '<div class="alert alert-warning alert-dismissible" role="alert">'+
	 '<button type="button" class="close" data-dismiss="alert" aria-label="Close">'+
	 '<span aria-hidden="true">&times;</span></button>'+
	 '<strong>Error!</strong> '+
	 text +
	 '</div>';

	 return err_html;
}
</script>

<?php include ("footer.php")?>
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
      <h2 class="panel-title">Upload Shipments.</h2>
    </div>
    <div class="panel-body">
    <h3>Upload Shipments Results</h3>            
     <hr>
   
        <div id="msg_div"></div>
        <br>
        
         <?php if(!empty ($this->session->flashdata('error'))){include 'error_msg.php';}?>  
		<div style="overflow:scroll;height:600px;width:100%"><!-- table wrapper -->
		 
		<table class="table table-bordered table table-hover" id="data_table">	
			<thead>
		    <tr class="info">
		    <th>Excel Row #</th>	    
		    <?php 		    
		     foreach($all_fields_list as $index=>$field){
		     	
		     	echo '<th>'.$field.'</th>';
		     	
		     }//end foreach	 
		    ?>	
		    <th>Upload Results</th>
		    </tr>		     
		    </thead>
		    <tbody>	     
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
	
	var req_no = <?php echo $req_no?>

	var tot_rows = <?php echo $tot_rows;?>

	if(debug)console.log('TotRows:'+tot_rows);
    
	var percentage = 0;

	if(debug) console.log('total reqs:'+ req_no);
	
	for(var n=1; n <= req_no; n++){		

		$.ajax({	
			  
			  url: "<?php echo site_url('ExcelUploader/ajax_create_shipment?sequence=')?>"+n,

			  async: true,
			   
			  success: function(result){

				  	if(debug)console.log( 'req_no:'+ n);
				 						        
			        if(debug)console.log('result:'+ result);			        
			        
			       	$("#data_table").find('tbody').append(result);			        	
	       
		    },		    
		    error:function(jqXHR, textStatus, errorThrown){
			    
				$("#div1").html(
				'<p>status code: '+jqXHR.status+
				'</p><p>errorThrown: ' + 
				errorThrown + '</p><p>jqXHR.responseText:</p><div>'+
				jqXHR.responseText + '</div>');
				
                console.log('jqXHR:');
                console.log(jqXHR);
                console.log('statusCode:'+jqXHR.status);
                console.log('textStatus:'+jqXHR.statusText);
                console.log('responseText:'+jqXHR.responseText);
                console.log(errorThrown);				
			},
			complete:function(result){
			
		    },
		});	
		
	}//end for

});



$body = $("body");

$(document).on({
    ajaxStart: function() { $body.addClass("loading");    },

    ajaxStop: function() { 
        
        $body.removeClass("loading");

        var has_error = $( "div" ).hasClass( "soap_error" )
       
        if(has_error) {
              
        	 var error = set_error("Following rows which are highlited in orange color, " +
                	 "has some issues. Please check last column 'Upload Results' to see the descriptions.");

        	 $("#msg_div").html(error);
        }

        $("div.err_empty , div.err_invalid_data , div.err_exceed_limit, div.soap_error")
    	.closest( "tr" )
    	.css( "background-color", "orange" );

        $("div.soap_success").closest('tr').css('background-color','yellowgreen');
                
    }    
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
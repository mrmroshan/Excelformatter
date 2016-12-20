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
  <div class="col-md-8 col-md-offset-2">
    
    <div class="panel panel-default">
    <div class="panel-heading">
      <h2 class="panel-title">Admin</h2>
    </div>
    <div class="panel-body">
    
    <h3>Edit Field Information</h3>
            
        <hr>
   
        <div id="msg_div"></div>
        <br>
        
         <?php if(!empty ($this->session->flashdata('error'))){include 'error_msg.php';}?>  
		
		<form>
			<div class="form-group row">
			  <label for="FIELD_LABEL" class="col-md-3 col-form-label">FIELD LABEL</label>
			  <div class="col-md-9">
			    <input class="form-control" type="text" value="" id="FIELD_LABEL" name ="FIELD_LABEL">
			  </div>
			</div>
			<div class="form-group row">
			  <label for="FIELD_INDEX" class="col-md-3 col-form-label">FIELD INDEX</label>
			  <div class="col-xs-3">
			    <input class="form-control" type="text" value="" id="FIELD_INDEX" name="FIELD_INDEX">
			  </div>
			</div>
			<div class="form-group row">
			  <label for="REQUIRED" class="col-md-3 col-form-label">REQUIRED?</label>
			  <div class="col-md-9">
			      <input type="checkbox" class="form-check-input" id="REQUIRED" name="REQUIRED">
			  </div>
			</div>		
			
			<div class="form-group row">
			  <label for="example-url-input" class="col-md-3 col-form-label">MAXCHARS</label>
			  <div class="col-xs-3">
			    <input class="form-control" type="text" value="" id="MAXCHARS" name="MAXCHARS">
			  </div>
			</div>
			
			<div class="form-group row">
			  <label for="SOAP_FIELD" class="col-md-3 col-form-label">SOAP FIELD</label>
			  <div class="col-xs-3">
			    <input class="form-control" type="text" value="" id="SOAP_FIELD" name="SOAP_FIELD">
			  </div>
			</div>
			
			<div class="form-group row">
		    <label for="DATATYPE" class="col-md-3 col-form-label">DATATYPE</label>
		    <div class="col-xs-3">
		    <select  class="form-control" id="DATATYPE" name="DATATYPE"> 
		      <option value="STRING">STRING</option>
		      <option value="INT">INTEGER</option>
		      <option value="FLOAT">FLOAT</option>
		      <option value="DATE">DATE</option>
		    </select>
		  	</div>
		  	</div>
			
			
			<div class="form-group row">
			  <label for="REGXPATTERN" class="col-md-3 col-form-label">REGXPATTERN</label>
			  <div class="col-md-9">
			    <textarea class="form-control" id="REGXPATTERN" name="REGXPATTERN"></textarea>
			  </div>
			</div>
			<div class="form-group row">
			  <label for="MAPPED_COL_NAMES" class="col-md-3 col-form-label">MAPPED COLLUMN NAMES</label>
			  <div class="col-md-9">
			    <textarea class="form-control" id="MAPPED_COL_NAMES"></textarea>
			  </div>
			</div>
			
			
		  <button type="submit" class="btn btn-primary">Submit</button>
		</form>
	  		  
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
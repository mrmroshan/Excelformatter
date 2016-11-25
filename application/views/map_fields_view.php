<?php defined('BASEPATH') OR exit('No direct script access allowed');
include("header.php")
?>
<style type="text/css">
.required{
    border: 1px solid red;
    background: #cc5b5b;
    color: black}
</style>
<div class="container">
  <div class="span4 offset4">
    
    <div class="panel panel-default">
    <div class="panel-heading">
      <h2 class="panel-title">Step 2</h2>
    </div>
    <div class="panel-body">
      <p>Map the columns  </p>
  
    <div class="row">
      <div class="col-md-6" >

         <div class="panel panel-default">
          <div class="panel-heading">
            <h3 class="panel-title">Field List</h3>
          </div>
          <div class="panel-body">
            
        <ul id="sortable1" class="connectedSortable">
          <?php 
            foreach ($all_field_list as $k => $v){
                echo '<li class="ui-state-highlight required" id="'.$k.'">'.$v.'</li>';              
            }
          ?>
         
        </ul>
      </div>

      </div>
    </div><!--/col-md-6-->

     <div class="col-md-6" >

        <div class="panel panel-default">
          <div class="panel-heading">
            <h3 class="panel-title">Uploaded File Columns List</h3>            
          </div>
          <div class="panel-body">

        <ul id="sortable2" class="connectedSortable" >
           <?php 
           $i = 0;
           
           foreach ($up_file_col_list as $k => $v){
           	
                echo '<li class="ui-state-default" id ="'.$i.'" >'.
                $v.
                " <a href=\"javascript:void(0)\" class=\"clearitem pull-right\">X</a>".
                '</li>';  
                $i++;
            }//end foreach
          ?>
       
        </ul>
      </div><!-- end of panel body -->
	  </div><!-- end of panel -->
	  <form method="post" action="<?php echo site_url("templates/preview_data")?>" onsubmit="">
	  <input type="hidden" name="template_col_list" id="template_col_list" value="">
	  <input type="hidden" name="up_file_col_list" id="up_file_col_list" value="">
	  
    </div><!--/col-md-6-->
     
    </div><!--/row-->
  
    <input type="hidden" name="step" id="step" value="preview">
    <input type ="submit" class ="btn btn-primary btn-lg pull-right" value ="Next" id="btnNext">
    </form>
    
    <form action="<?php echo site_url('templates/wizard')?>" method="post">
    <input type="hidden" name="step" id="step" value="upload">
    <button type ="submit" class ="btn btn-primary btn-lg"  id="btnPrevious">Previous</button>
     </form> 
      
  </div><!--/panelbody-->

</div><!-- /.container -->

<script type="text/javascript">

	var debug = true
	
    $( document ).ready(function() {  
   
      $( "#sortable1, #sortable2" ).sortable({
        	connectWith: ".connectedSortable",
        	cancel: ".required"
      }).disableSelection();

      $( "#btnNext" ).click(function() {
        
        var template_col_list = "";
        $( "#sortable1 li" ).each(function( index ) {

            template_col_list += $( this ).text() + '---'
            
          
        });//end each

        if(debug)console.log( "template_col_list: " + template_col_list );        
        $("#template_col_list").val(template_col_list);
        
        var up_file_col_list_order = "";
        $( "#sortable2 li" ).each(function( index ) {
                 
        	up_file_col_list_order += $(this).attr('id') + '---'
        	if(debug)console.log( "element id  " + $(this).attr('id') )
          
        });//end each
        
        if(debug)console.log( "up_file_col_list_order: " + up_file_col_list_order );
		$("#up_file_col_list").val(up_file_col_list_order);
		
      });//end of click()     
	
  	});
	
  	$('#sortable2').on('click', '.clearitem', function() {
    	 $(this).closest('li').remove();
    });
	    	
	 function remove_element(element){
			$(element).fadeOut(300, function() { 
				$(this).remove(); 
		});
	  }
	  	    
</script>
 
<?php include ("footer.php")?>
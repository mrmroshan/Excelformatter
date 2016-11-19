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
      <h3 class="panel-title">Column Setup</h3>
    </div>
    <div class="panel-body">
      <p>Please select the columns from the list and arrange the position order you need </p>
  
    <div class="row">
      <div class="col-md-6" >

         <div class="panel panel-default">
          <div class="panel-heading">
            <h3 class="panel-title">All Columns</h3>
          </div>
          <div class="panel-body">
            
        <ul id="sortable1" class="connectedSortable">
          <?php 
            foreach ($all_columns as $k => $v){
                echo '<li class="ui-state-default" id="'.$k.'">'.$v.'</li>';
              
            }
          ?>
          
        </ul>
      </div>

      </div>
    </div><!--/col-md-6-->

     <div class="col-md-6" >

        <div class="panel panel-default">
          <div class="panel-heading">
            <h3 class="panel-title">Template Columns</h3>
            <br>
            <input type ="button" class ="btn btn-primary btn-sm" value ="Download Excel Template" id="btnDL">
          </div>
          <div class="panel-body">

        <ul id="sortable2" class="connectedSortable" >
           <?php 
            foreach ($required_columns as $k => $v){
                echo '<li class="ui-state-highlight required" id ="'.$k.'">'.$v.'</li>';              
            }
          ?>
        
        </ul>
      </div>

      </div>
    </div><!--/col-md-6-->
    
    </div><!--/row-->
  </div><!--/panelbody-->

</div><!-- /.container -->

<script type="text/javascript">

    $( document ).ready(function() {  
   
      $( "#sortable1, #sortable2" ).sortable({
        connectWith: ".connectedSortable",
        cancel: ".required"
      }).disableSelection();

      $( "#btnDL" ).click(function() {
        
        var col_list = [];
        $( "#sortable2 li" ).each(function( index ) {
          //console.log( index + ": " + $( this ).text() );
           col_list.push({
              index: index,
              name: $( this ).text()           
            });
          
        });//end each
         console.log( "col_list[]: " + col_list );
      });
    

  });
    
</script>
 
<?php include ("footer.php")?>
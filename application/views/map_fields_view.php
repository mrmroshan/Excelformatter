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
      <h2 class="panel-title">Step 3</h2>
    </div>
    <div class="panel-body">
      <p>Map the columns  </p>
  
    <div class="row">
      <div class="col-md-6" >

         <div class="panel panel-default">
          <div class="panel-heading">
            <h3 class="panel-title">Template Columns</h3>
          </div>
          <div class="panel-body">
            
        <ul id="sortable1" class="connectedSortable">
          <?php 
            //foreach ($all_columns as $k => $v){
              //  echo '<li class="ui-state-default" id="'.$k.'">'.$v.'</li>';
              
            //}
          ?>
          <li class="ui-state-default" id="">test1</li>
        </ul>
      </div>

      </div>
    </div><!--/col-md-6-->

     <div class="col-md-6" >

        <div class="panel panel-default">
          <div class="panel-heading">
            <h3 class="panel-title">Template Columns</h3>            
          </div>
          <div class="panel-body">

        <ul id="sortable2" class="connectedSortable" >
           <?php 
            //foreach ($required_columns as $k => $v){
                //echo '<li class="ui-state-highlight required" id ="'.$k.'">'.$v.'</li>';              
            //}
          ?>
        <li class="ui-state-default" id="">test2</li>
        </ul>
      </div><!-- end of panel body -->
	  </div><!-- end of panel -->
	  <form method="post" action="<?php echo site_url("templates/index")?>">
	  <!-- <input type ="button" class ="btn btn-primary btn-sm" value ="Download Excel Template" id="btnDL">-->
	  <input type="hidden" name="selected_col_list" id="selected_col_list" value="">
	  <input type ="submit" class ="btn btn-primary btn-lg pull-right" value ="Next" id="btnNext">
       </form>
    </div><!--/col-md-6-->
    
    </div><!--/row-->
  </div><!--/panelbody-->

</div><!-- /.container -->

<script type="text/javascript">

	var debug = false
	
    $( document ).ready(function() {  
   
      $( "#sortable1, #sortable2" ).sortable({
        connectWith: ".connectedSortable",
        cancel: ".required"
      }).disableSelection();

      $( "#btnNext" ).click(function() {
        
        var col_list = [];
        var col_list_str = "";
        $( "#sortable2 li" ).each(function( index ) {
          //console.log( index + ": " + $( this ).text() );
           col_list.push({
              index: index,
              name: $( this ).text()           
            });
           col_list_str += $( this ).text() + '---'
          
        });//end each
        
        if(debug)console.log( "col_list_str: " + col_list_str );
		$("#selected_col_list").val(col_list_str);
	
        
        /*
          var url = '<?php echo site_url('templates/create_template?col_list=')?>'+encodeURIComponent(col_list_str);
          console.log('url:'+url)
          window.location = url;
          */
          
        /*
        $.ajax({
          url: "<?php echo site_url('templates/create_template')?>", 
          data: { col_list: col_list} ,
          success: function(result){
            //console.log("result:"+result);
            window.location = '<?php echo site_url('templates/create_template')?>';
          }
        });
      */

      });//end of click()
    

  });
    
</script>
 
<?php include ("footer.php")?>
<?php defined('BASEPATH') OR exit('No direct script access allowed');
include("header.php")
?>

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
          <?php //var_dump($all_columns)?>

        <ul id="sortable1" class="connectedSortable">
          <?php 
            foreach ($all_columns as $k => $v){
              foreach($v as $k1 => $v1){
                echo '<li class="ui-state-default">'.$v1.'</li>';
              }
            }
          ?>

          <li class="ui-state-default">Item 1</li>
          <li class="ui-state-default">Item 2</li>
          <li class="ui-state-default">Item 3</li>
          <li class="ui-state-default">Item 4</li>
          <li class="ui-state-default">Item 5</li>
        </ul>

      </div>
      <div class="col-md-6" >
        <ul id="sortable2" class="connectedSortable">
          <li class="ui-state-highlight">Item 1</li>
          <li class="ui-state-highlight">Item 2</li>
          <li class="ui-state-highlight">Item 3</li>
          <li class="ui-state-highlight">Item 4</li>
          <li class="ui-state-highlight">Item 5</li>
        </ul>
      </div>
    </div>


    </div>
    </div>
  </div>

</div><!-- /.container -->

 
<?php include ("footer.php")?>
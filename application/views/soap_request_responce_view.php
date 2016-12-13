<?php defined('BASEPATH') OR exit('No direct script access allowed');
include("header.php")
?>

<div class="container">
  <div class="span4 offset4">
    
    <div class="panel panel-default">
    <div class="panel-heading">
      <h3 class="panel-title">SOAP output</h3>
    </div>
    <div class="panel-body">
      <p></p>
      
        <?php var_dump($output);?> 
    </div>
    </div>
  </div>

</div><!-- /.container -->

<?php include ("footer.php")?>
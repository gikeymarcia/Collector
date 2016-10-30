<?php

  require '../../initiateTool.php';


  ob_end_clean();
  header('Content-type: text/plain; charset=utf-8');

  $web_address  = $_GET['web_address'];
  $filename     = $_GET['filename'];
  
  
  file_put_contents("Toolboxes/User/$filename.txt",$web_address);  

  
  

?>
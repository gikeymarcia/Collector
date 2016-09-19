<?php

  require '../../initiateTool.php';


//  ob_end_clean();
  header('Content-type: text/plain; charset=utf-8');

  $file         = $_GET['file'];
  $gui_content  = $_GET['gui_content'];
  $js_content   = $_GET['js_content'];
  $old_name     = $_GET['old_name'];
  $new_name     = $_GET['new_name'];
  $manuscript    = $_GET['manuscript_content'];
  
  
  file_put_contents("Analyses/$new_name"."_gui_script.txt",$gui_content);  
  file_put_contents("Analyses/$new_name"."_js_script.txt",$js_content); 
  file_put_contents("Analyses/$new_name"."_manuscript.html",$manuscript); 

  
  if($old_name != $new_name){
    // code for deleting old file
  }  

?>
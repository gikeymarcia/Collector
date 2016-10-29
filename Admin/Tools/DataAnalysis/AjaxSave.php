<?php

  require '../../initiateTool.php';


//  ob_end_clean();
  header('Content-type: text/plain; charset=utf-8');

  $filename         = $_POST['filename'];
  $filetext         = $_POST['filetext'];
  
  if ($filename !== '' AND preg_match('/[^a-zA-Z0-9._ -]/', $filename) === 0) {
    file_put_contents("Analyses/$filename.txt",$filetext);
  
    file_put_contents("hello.txt",json_encode($_POST));
    
  }
  
  
  
?>
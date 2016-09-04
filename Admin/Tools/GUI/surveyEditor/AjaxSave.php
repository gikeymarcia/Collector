<?php

  require '../../../initiateTool.php';
  require '../guiFunctions.php';


  ob_end_clean();
  header('Content-type: text/plain; charset=utf-8');

  $file     = $_GET['file'];
  $content  = $_GET['content'];

  
  $stimTableArray = json_decode($content, true);                            // converting json from POST into array table data into array
  writeHoT($file,$stimTableArray);   // saving array into HoT format

?>
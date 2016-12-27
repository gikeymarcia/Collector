<?php

// security checks here!!

require '../../initiateTool.php';
ob_end_clean();


  $filename = $_POST['filename'];

  $csv_data = fsDataType_CSV::read($filename);
  $raw_data = array();
  $raw_data[] = array_keys($csv_data[0]);
  
  foreach ($csv_data as $row) {
    $raw_data[] = array_values($row);
  }
  
  echo json_encode($raw_data);

?>
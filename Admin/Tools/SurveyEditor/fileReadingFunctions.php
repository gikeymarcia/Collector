<?php

function read_csv_raw($file) {
  $data = array();
  $file_resource = fopen($file, 'r');
  
  while ($line = fgetcsv($file_resource)) {
    $data[] = $line;
  }
  fclose($file_resource);
  return $data;
}

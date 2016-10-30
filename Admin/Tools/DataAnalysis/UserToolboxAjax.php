<?php

require '../../initiateTool.php';
ob_end_clean();

if (!isset($_GET['filename'])) exit;

header('Content-Type: text/plain; charset=utf-8');

$filename = $_GET['filename'];

if ($filename !== '' AND preg_match('/[^a-zA-Z0-9._ -]/', $filename) === 0) {
  $filepath = "Toolboxes/User/$filename.txt";
  
  if (is_file($filepath)) {
    $url = file_get_contents($filepath);
    echo $url;
  } else {
      echo 'bad path';
  }
} else {
    echo 'bad request';
    
}
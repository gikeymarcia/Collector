<?php

require '../../initiateTool.php';
ob_end_clean();


header('Content-Type: text/plain; charset=utf-8');

if(isset($_POST['web_address']) && isset($_POST['filename'])){
  $web_address  = $_POST['web_address'];
  $filename     = $_POST['filename'];

  if(!is_dir('Toolboxes/User/')){
    mkdir('Toolboxes/User/',0777,true);
  }
  if ($filename !== '' AND preg_match('/[^a-zA-Z0-9._ -]/', $filename) === 0){
    file_put_contents("Toolboxes/User/$filename.txt",$web_address);  
  }
  
  echo "success";
} else {
  if (!isset($_GET['filename'])) exit;
  
}

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
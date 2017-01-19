<?php

require '../../../initiateTool.php';
ob_end_clean(); // no need to transmit useless data
ini_set('html_errors', false);

header('Content-type: text/plain; charset=utf-8');

ob_start(); // start new output buffer to catch error messages

if (!isset($_POST['file'], $_POST['data'], $_POST['filetype'])) {
    exit('Missing filename or data or filetype');
}

if(strpos($_POST['file'], '\\') !==false || strpos($_POST['file'], '.') !==false){
  exit("Bad filename provided");
};

$filename = $_POST['file'];
$data     = $_POST['data'];

if($_POST['filetype'] == "Stimuli"){
  $filetype = "Stimuli";
} elseif ($_POST['filetype'] == "Procedure"){
  $filetype = "Procedure";
} else {
  exit ("invalid filetype submitted");
}



$data  = $_POST['data'];

$valid_experiments = get_Collector_experiments($_FILES);

if(!in_array($_POST['exp_name'],$valid_experiments)){
  exit ("not valid experiment name");
} 
$exp_name = $_POST['exp_name'];


$system_map_values = array(
    'Current Experiment' => "Demo", //$exp_name,
    $filetype           => "test.csv"//$filename
);

$_FILES->write($filetype, $data, $system_map_values);


?>
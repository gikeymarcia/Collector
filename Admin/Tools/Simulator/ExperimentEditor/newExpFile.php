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

$headers = array_shift($_POST['data']);
$data    = array();

foreach ($_POST['data'] as $line) {
    $data[] = array_combine($headers, $line);
}

$valid_experiments = get_Collector_experiments($FILE_SYS);

if(!in_array($_POST['exp_name'],$valid_experiments)){
  exit ("not valid experiment name");
} 
$exp_name = $_POST['exp_name'];


$system_map_values = array(
    'Current Experiment' => $exp_name,
    $filetype            => "$filename.csv"
);

$FILE_SYS->overwrite($filetype, $data, $system_map_values);

echo "New $filetype file created: $filename";
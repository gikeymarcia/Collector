<?php

require '../../initiateTool.php';
ob_end_clean(); // no need to transmit useless data
ini_set('html_errors', false);
require 'fileReadingFunctions.php';
require_once ('../guiFunctions.php');


header('Content-type: text/plain; charset=utf-8');

ob_start(); // start new output buffer to catch error messages

if (!isset($_GET['sheet'])) exit;

$sheet_info = explode('/', $_GET['sheet']);

$survey_name = array_shift($sheet_info);
//$exp_file = implode('/', $sheet_info);

$surveys = getCsvsInDir($FILE_SYS->get_path('Common')."/Surveys");

if (!in_array($survey_name, $surveys)) {
    exit("bad request: '$survey_name' is not an existing experiment");
}

//$FILE_SYS->set_default('Current Experiment', $survey_name);


$sheet_path = $FILE_SYS->get_path('Common')."/Surveys/$survey_name";



/* -- can delete - right?


if ($exp_file === 'Conditions.csv') {
    $sheet_path = $FILE_SYS->get_path('Conditions');
} else {
    $sheet_type = null;
    $filename   = '';
    
    if (substr($exp_file, 0, 8) === 'Stimuli/') {
        $sheet_type = 'Stimuli';
        $filename   = substr($exp_file, 8);
    } elseif (substr($exp_file, 0, 10) === 'Procedure/') {
        $sheet_type = 'Procedure';
        $filename   = substr($exp_file, 10);
    }
    
    if ($sheet_type === null
        || $filename === ''
        || preg_match('/[^a-zA-Z0-9._ -]/', $filename) !== 0
    ) {
        exit('bad file request');
    }
    
    $sheet_dir  = $FILE_SYS->get_path("$sheet_type Dir");
    $sheet_path = "$sheet_dir/$filename";
}
 */
 
 
if (!is_file($sheet_path)) {
    exit("error: file does not exist: '$sheet_path'");
}

$sheet_data = read_csv_raw($sheet_path);

if (error_get_last() === null) {
    ob_end_clean();
    echo 'success: ';
    echo json_encode($sheet_data);
} else {
    echo 'unknown error: ' . ob_get_clean();
}

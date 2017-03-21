<?php

require '../../../initiateTool.php';
ob_end_clean(); // no need to transmit useless data
ini_set('html_errors', false);
require 'fileReadingFunctions.php';

header('Content-type: text/plain; charset=utf-8');

ob_start(); // start new output buffer to catch error messages

if (!isset($_GET['sheet'])) exit;

$sheet_info = explode('/', $_GET['sheet']);

$exp_name = array_shift($sheet_info);
$exp_file = implode('/', $sheet_info);

$experiments = get_Collector_experiments($FILE_SYS);

if (!in_array($exp_name, $experiments)) {
    exit("bad request: '$exp_name' is not an existing experiment");
}

$FILE_SYS->set_default('Current Experiment', $exp_name);

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

if (!is_file($sheet_path)) {
    exit("error: file does not exist: '$sheet_path'");
}


$sheet_data = read_csv_raw($sheet_path);

function clean_string($string) { // by Wayne Weibel at http://stackoverflow.com/questions/1176904/php-how-to-remove-all-non-printable-characters-in-a-string/20766625#20766625
  $s = trim($string);
  $s = iconv("UTF-8", "UTF-8//IGNORE", $s); // drop all non utf-8 characters

  // this is some bad utf-8 byte sequence that makes mysql complain - control and formatting i think
  $s = preg_replace('/(?>[\x00-\x1F]|\xC2[\x80-\x9F]|\xE2[\x80-\x8F]{2}|\xE2\x80[\xA4-\xA8]|\xE2\x81[\x9F-\xAF])/', ' ', $s);

  $s = preg_replace('/\s+/', ' ', $s); // reduce all multiple whitespace to a single space

  return $s;
}

if (error_get_last() === null) {
    ob_end_clean();
    echo 'success: ';
    
    // remove bad utf-8 characters from each cell
      // loop through 
      foreach($sheet_data as &$row){
          foreach($row as &$cell){
              $cell = clean_string($cell);
          }
      }
    
//    $clean_sheet_data = clean_string($sheet_data);
    echo json_encode($sheet_data);    
    file_put_contents("inspecting_json.txt",json_last_error_msg().print_r($sheet_data,true)); // remove after debugging
} else {
    echo 'unknown error: ' . ob_get_clean();
}

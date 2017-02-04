<?php

require '../../initiateTool.php';
ob_end_clean(); // no need to transmit useless data

require 'fileReadingFunctions.php';
require_once ('../guiFunctions.php');


if (!isset($_POST['file'], $_POST['data'])) {
    exit('Missing filename or data');
}

$file_path = strtr($_POST['file'], '\\', '/');

if (strpos($file_path, '..') !== false) {
    exit('Bad file path provided, illegal character sequence.');
}

$file_path_parts = explode('/', $file_path);

$survey_name = $_POST["survey_name"];


$survey = $file_path_parts[0];
$surveys = getCsvsInDir($FILE_SYS->get_path('Common')."/Surveys");



if (!in_array($survey, $surveys)) {
    exit('Bad file path provided, experiment "' . $survey . '" invalid.');
}

$data = json_decode($_POST['data'], true);

if (!is_array($data)) {
    exit('Bad data provided, not in array format');
}

foreach ($data as $row) {
    if (!is_array($row)) {
        exit('Bad data provided, data rows not in array format');
    }
}

$dir = dirname($file_path);

if (!is_dir($dir)) mkdir($dir, 0777, true); // this seems wrong, but redundant



if($survey_name !== $file_path){
  // code for renaming file
  
  $file_full_path = $FILE_SYS->get_path('Surveys') . '/' . $survey_name;
  $delete_path = $FILE_SYS->get_path('Surveys') . '/' . $file_path;
  $delete_original = TRUE;
  //new name
  //delete original file?
} else {
  $file_full_path = $FILE_SYS->get_path('Surveys') . '/' . $file_path;
  
  $delete_original = FALSE;

}

$file_resource = fopen($file_full_path, 'w');

foreach ($data as $row) {
    fputcsv($file_resource, $row);
}

fclose($file_resource);

if($delete_original == TRUE){
  unlink($delete_path);
}

echo '<b>Success!</b> File saved';

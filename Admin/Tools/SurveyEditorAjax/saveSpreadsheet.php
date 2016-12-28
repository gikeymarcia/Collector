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

$survey = $file_path_parts[0];
$surveys = getCsvsInDir($_FILES->get_path('Common')."/Surveys");

echo $survey;
print_r ($surveys);

if (!in_array($survey, $surveys)) {
    exit('Bad file path provided, experiment "' . $survey . '" invalid.');
}

/* if (count($file_path_parts) > 2) {
    if (    $file_path_parts[1] !== 'Procedure'
        AND $file_path_parts[1] !== 'Stimuli'
    ) {
        exit('Bad file path provided, subfolder "' . $file_path_parts[1] . '" invalid');
    }
} elseif ($file_path_parts[1] !== 'Conditions.csv') {
    exit('Bad file path provided, filename besides Conditions.csv without subfolder is not allowed.');
} */

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

if (!is_dir($dir)) mkdir($dir, 0777, true);

$file_full_path = $_FILES->get_path('Surveys') . '/' . $file_path;

$file_resource = fopen($file_full_path, 'w');

foreach ($data as $row) {
    fputcsv($file_resource, $row);
}

fclose($file_resource);

echo '<b>Success!</b> File saved';

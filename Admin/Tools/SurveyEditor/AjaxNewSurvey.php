<?php

require '../../initiateTool.php';
ob_end_clean(); // no need to transmit useless data
ini_set('html_errors', false);

header('Content-type: text/plain; charset=utf-8');

ob_start(); // start new output buffer to catch error messages

if (!isset($_POST['new_name'])) exit;

$new_name = $_POST['new_name'];

if ($new_name === '' OR preg_match('/[^a-zA-Z0-9._ -]/', $new_name) !== 0) {
    exit('error: invalid experiment name: "' . $new_name . '"');
}

$new_name = str_ireplace(".csv","",$new_name);

$temp_split = explode(".",$new_name);
if(count($temp_split) > 1){
  exit("illegal filetype");
}

$new_name = $new_name.".csv";

// copy a .csv file rather than use a json decode - right???


//$default_files = json_decode(file_get_contents('default_new_experiment.json'), true);

$exp_root_path = $FILE_SYS->get_path('Surveys');
$template_survey = file_get_contents("$exp_root_path/SurveyDemo.csv");
file_put_contents("$exp_root_path/$new_name",$template_survey);
echo "success:$new_name";

/*
$exp_dir = "$exp_root_path/$new_name";
echo $exp_dir;

if (is_dir($exp_dir)) {
    exit('error: experiment already exists'); // experiment already exists
}

mkdir($exp_dir, 0777, true);

function write_csv($path, $data) {
    $file_resource = fopen($path, 'w');
    
    foreach ($data as $line) {
        fputcsv($file_resource, $line);
    }
    
    fclose($file_resource);
}

function write_array_to_dir($dir, $array) {
    foreach ($array as $name => $contents) {
        $entry_path = "$dir/$name";
        
        if (strtolower(substr($name, -4)) === '.csv') {
            write_csv($entry_path, $contents);
        } elseif (is_array($contents)) {
            mkdir($entry_path, 0777, true);
            write_array_to_dir($entry_path, $contents);
        } else {
            file_put_contents($entry_path, $contents);
        }
    }
}

write_array_to_dir($exp_dir, $default_files);

if (error_get_last() === null) {
    echo 'success';
} else {
    $errors = ob_get_clean();
    echo 'error: PHP error: ' . $errors;
}
*/
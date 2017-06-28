<?php

require '../../Code/initiateCollector.php';
ob_end_clean(); // no need to transmit useless data
ini_set('html_errors', false);

$file             = $_POST['file'];


//check the filename is a csv

if ($file === '' OR preg_match('/[^a-zA-Z0-9._ -?]/', $file) !== 0) {
    exit('error: invalid file name: "' . $file . '"');
} else {
    if (strtolower(substr($file, -4)) === '.csv') {
        // that is fine
    } else {
        $file = "$file.csv";        
    }
}

$survey_dir = $FILE_SYS-> get_path("Surveys");

if(strpos($file,"http") !== false){
    $file_location = "$file";    
} else {
    $file_location =  "$survey_dir/$file";    
}

$file_contents = file_get_contents ($file_location);

echo $file_contents;

?>
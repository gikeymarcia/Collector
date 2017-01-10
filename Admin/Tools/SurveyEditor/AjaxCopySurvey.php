<?php

require '../../initiateTool.php';
ob_end_clean(); // no need to transmit useless data
ini_set('html_errors', false);

$root_path = $_FILES->get_path('Surveys');


//safety checks


$old_survey = $_POST['old_survey'];
$new_name   = $_POST['new_name'];

$new_name = str_ireplace(".csv","",$new_name);

$this_survey_contents = file_get_contents("$root_path/$old_survey");
file_put_contents("$root_path/$new_name.csv",$this_survey_contents);  



echo "$old_survey has been copied to $new_name.csv";


?>
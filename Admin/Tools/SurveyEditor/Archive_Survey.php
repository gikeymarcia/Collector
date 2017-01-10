<?php

$archived_survey = $_POST['archived_survey'];

if(file_exists("archived_files.txt") == false){
  file_put_contents("archived_files.txt",$archived_survey);
} else {
  $archived_files = file_get_contents("archived_files.txt");
  $archived_files += ",$archived_survey";
  file_put_contents("archived_files.txt",$archived_files);
  
}
  
?>
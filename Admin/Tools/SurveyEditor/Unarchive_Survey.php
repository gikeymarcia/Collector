<?php

  $archived_survey = $_POST['archived_survey'];

  $archived_files = file_get_contents("archived_files.txt");
  
  $archived_files = str_ireplace(",".$archived_survey,"",$archived_files);
  
  file_put_contents("archived_files.txt",$archived_files);    
  
?>
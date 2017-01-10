<?php

  require '../../initiateTool.php';
  ob_end_clean(); // no need to transmit useless data

  require 'fileReadingFunctions.php';
  require_once ('../guiFunctions.php');


  //security checks

  
  $deleted_survey = $_POST['deleted_survey'];

  $file_full_path  = $_FILES->get_path('Surveys') . '/' . $deleted_survey;

  unlink($file_full_path);

?>
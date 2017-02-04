<?php

require '../../../initiateTool.php';
ob_end_clean(); // no need to transmit useless data
ini_set('html_errors', false);

if (!isset($_POST['file'], $_POST['data'])) {
    exit('Missing filename or data');
}

$file_path = strtr($_POST['file'], '\\', '/');

if (strpos($file_path, '..') !== false) {
    exit('Bad file path provided, illegal character sequence.');
}

$file_path_parts = explode('/', $file_path);

$trial_type = $file_path_parts[0];
//$trialTypes = get_all_trial_type_data($FILE_SYS); // get Collector trial types

$custom_trial_types = $FILE_SYS->read('Custom Trial Types');
$default_trial_types = $FILE_SYS->read('Trial Types');
$trial_types = array_merge($custom_trial_types,$default_trial_types);

$default_trial_types_path = $FILE_SYS->get_path('Trial Types');
$custom_trial_types_path = $FILE_SYS->get_path('Custom Trial Types');


if (!in_array($trial_type, $trial_types)) {
  // creating custom folder for new trial type
  if(strpos($trial_type,".") !== false){
    exit('Bad file path provided, trial type "' . $trial_type . '" invalid.');
  } else {
    mkdir("$custom_trial_types_path/$trial_type",0777,true);    
  }
}
    

if (count($file_path_parts) > 2) {
    
  exit ("invalid path given");
    
} else  {
    if (    $file_path_parts[1] !== 'template'
      AND $file_path_parts[1] !== 'scoring'
      AND $file_path_parts[1] !== 'prepare_inputs'
  ) {
      exit('Bad file path provided, subfolder "' . $file_path_parts[1] . '" invalid');
  }
}

  

  // check if the directory is already in the custom one
  $filetypes = ['template.html','scoring.js','prepareInputs.js'];
  if(!in_array($trial_type,$custom_trial_types)){
    if(!is_dir("$custom_trial_types_path/$trial_type")){
      mkdir("$custom_trial_types_path/$trial_type",0777,true);
    }
    foreach($filetypes as $filetype){
      $this_file_path = "$default_trial_types_path/$trial_type/$filetype";
      if(file_exists($this_file_path)){
        $this_destination_path = "$custom_trial_types_path/$trial_type/$filetype";
        copy($this_file_path,$this_destination_path);
      }
    }
  }
  switch ($file_path_parts[1]){
    case "template":
      $save_file_path = "$custom_trial_types_path/$trial_type/template.html";
      break;
    case "scoring":
      $save_file_path = "$custom_trial_types_path/$trial_type/scoring.js";
      break;
    case "prepare_inputs":
      $save_file_path = "$custom_trial_types_path/$trial_type/prepareInputs.js";
      break;
    default:
      echo "Unknown problem";
  }
    
  file_put_contents($save_file_path,$_POST['data']);

echo '<b>Success!</b> Trial type file saved';

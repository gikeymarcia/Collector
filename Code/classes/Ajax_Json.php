<?php 

require '../../Admin/initiateTool.php';
ob_end_clean(); // no need to transmit useless data
ini_set('html_errors', false);


$file             = $_POST['file'];
$data             = $_POST['data'];
$experiment_name  = $_POST['experiment_name'];
$read_write       = $_POST['read_write'];

//check the filename is a json
if ($file === '' OR preg_match('/[^a-zA-Z0-9._ -]/', $file) !== 0) {
    exit('error: invalid json name: "' . $file . '"');
} else {
  if (strtolower(substr($file, -5)) === '.json') {
    // that is good
  } else {
    $file_exploded=explode(".",$file);
    if(count($file_exploded)>1){
      exit("error: invalid file type submitted"); // might be a bit harsh, maybe automatically rename
    } else {
      $file = "$file.json";
    }
  }
}

$data_dir = $FILE_SYS-> get_path("Data");

$file_location =  "$data_dir/$experiment_name-Data/jsons/$file";

if($read_write == "Read"){
  echo file_get_contents($file_location);
} else {
  $valid_json = true; // need to change this
  if( $read_write == "Write"){
    //check the file contents is a json to create $valid_json

    if($valid_json == true){
      file_put_contents ($file_location,$data);      
    } else {
      exit ("Not a valid json you were trying to write");
    }
  } else {
    exit("Not clear whether you were trying to read or write a file.");
  }
}


//echo ("$file,$data,$experiment_name,$read_write");



?>
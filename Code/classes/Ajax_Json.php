<?php 


require '../initiateCollector.php';
ob_end_clean(); // no need to transmit useless data
ini_set('html_errors', false);

$file             = $_POST['file'];
$data             = $_POST['data'];
$read_write_list  = strtolower($_POST['read_write_list']);
$json_app         = strtolower($_POST['json_app']);

//check the filename is a json

if($json_app == "app"){
    // do checks here
    
    $file = str_ireplace(".html","",$file);
    $file = "$file.html";
    
} else {
    
    if ($file === '' OR preg_match('/[^a-zA-Z0-9._ -]/', $file) !== 0) {
        exit('error: invalid file name: "' . $file . '"');
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

}


if($json_app == "json"){
    $file_directory = $FILE_SYS-> get_path("Jsons");
    $file_location  = "$file_directory/$file";    
}

if($json_app == "app"){
    $file_directory = $FILE_SYS-> get_path("Apps");
    $file_location  = "$file_directory/$file";    
} 

if($read_write_list == "read"){
    echo file_get_contents($file_location);
} 
if($read_write_list == "write"){
    file_put_contents($file_location,$data);
    echo $file;
}
if($read_write_list == "list"){
    $files = glob("$file_directory/*");
    foreach($files as &$file){
        $filename = explode("/",$file);
        $file = $filename[count($filename) -1];        
    }
    echo json_encode($files);
}


//echo ("$file,$data,$experiment_name,$read_write");



?>
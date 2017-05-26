<?php

    
    $file_url = $_POST['fileurl'];
    $file_split = explode('/',$file_url);
    $pp = $file_split[count($file_split)-2];
    $experiment = $file_split[count($file_split)-3];
    
    //
    
    $this_basename =  basename($file_url);
    
    
    header('Content-Type: application/octet-stream');
    header("Content-Transfer-Encoding: Binary"); 
    
    header("Content-disposition: attachment; filename='".$experiment."_".$pp."_".$this_basename."'"); 
    readfile($file_url); // do the double-download-dance (dirty but worky)
    

?>
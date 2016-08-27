<?php

require '../../../initiateTool.php';
require '../guiFunctions.php';
ob_end_clean();
header('Content-type: text/plain; charset=utf-8');

$file = $_GET['file'];
$content = $_GET['content'];
$file_location = $_GET['location'];

// use this frame for a security check - make sure that file exists in Original folder 

/*
$stimFiles = getExperimentCsvs('Stimuli',   $_PATH);
$procFiles = getExperimentCsvs('Procedure', $_PATH);

if (!in_array($file, $stimFiles) && !in_array($file, $procFiles)) {
    exit('[]');
}
*/

file_put_contents($file_location.$file,$content);

?>
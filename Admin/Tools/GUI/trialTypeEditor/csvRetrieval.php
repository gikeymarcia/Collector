<?php

require '../../../initiateTool.php';
require '../guiFunctions.php';
ob_end_clean();
header('Content-type: text/plain; charset=utf-8');

$file = $_GET['file'];

$stimFiles = getExperimentCsvs('Stimuli',   $_PATH);
$procFiles = getExperimentCsvs('Procedure', $_PATH);

if (!in_array($file, $stimFiles) && !in_array($file, $procFiles)) {
    exit('[]');
}

$fileData = getFromFile($file, false);

$rawData = array(array_keys($fileData[0]));

foreach ($fileData as $row) {
    $rawData[] = array_values($row);
}

echo json_encode($rawData);

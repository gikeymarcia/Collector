<?php
require __DIR__ . '/../Code/initiateCollector.php';
require __DIR__ . '/toolsFunctions.php';

//verifyLogin($_SETTINGS->password);
if(!isset($_SESSION['user_email'])){
    $redirect_page = $FILE_SYS->get_path("index");
    header("Location:$redirect_page");
}


#### Create Aliases
$tool = determineCurrentTool();

if ($tool === false) {
    $_DATA =& $_SESSION['admin']['_DATA'];
} else {
    $_DATA =& $_SESSION['admin']['tools'][$tool]['_DATA'];
}

if (!isset($_DATA['_PATH'])) $_DATA['_PATH'] = new FileSystem();

$_PATH = $_DATA['_PATH'];


#### Write wrapper HTML for tool
ob_start(function($buffer) {
    return $buffer . '</body></html>';
});

writeToolsHtmlHead($_PATH, $tool);
writeToolsNavBar($_PATH, $tool);


unset($tool);

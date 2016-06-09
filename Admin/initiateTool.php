<?php
require __DIR__ . '/../Code/initiateCollector.php';
require __DIR__ . '/toolsFunctions.php';

verifyLogin($_SETTINGS->password);


#### Create Aliases
$tool = determineCurrentTool();

if ($tool === false) {
    $_DATA =& $_SESSION['admin']['_DATA'];
} else {
    $_DATA =& $_SESSION['admin']['tools'][$tool]['_DATA'];
}

if (!isset($_DATA['_PATH'])) $_DATA['_PATH'] = new Pathfinder();

$_PATH = $_DATA['_PATH'];


#### Write wrapper HTML for tool
writeToolsHtmlHead($_PATH, $tool);
writeToolsNavBar($_PATH, $tool);

ob_start(function($buffer) {
    return $buffer . '</body></html>';
});


unset($tool);

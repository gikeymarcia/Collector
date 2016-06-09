<?php
require __DIR__ . '/../Code/initiateCollector.php';
require __DIR__ . '/toolsFunctions.php';

ob_start(function($buffer) {
    return $buffer . '</body></html>';
});


#### Verify Logged In
// check if login has expired
if (isset($_SESSION['admin']['login'])) {
    if (time() > $_SESSION['admin']['login']) {
        unset($_SESSION['admin']['login']);
    }
}

// check if we have logged in
if (!isset($_SESSION['admin']['login'])) {
    // haven't logged in, run password script
    require __DIR__ . '/Login/Control.php';
}


#### Create Aliases
$tool = determineCurrentTool();

if ($tool === false) {
    $_DATA =& $_SESSION['admin']['_DATA'];
} else {
    $_DATA =& $_SESSION['admin']['tools'][$tool]['_DATA'];
}

if (!isset($_DATA['_PATH'])) $_DATA['_PATH'] = new Pathfinder();

$_PATH = $_DATA['_PATH'];


#### Create Opening HTMl
$title = $tool ? $tool : 'Collector - Admin Menu';
require $_PATH->get('Header');

$rootUrl = $_PATH->get('root', 'url');

$adminStyle = "$rootUrl/Admin/adminStyle.css";
echo "<link rel='stylesheet' href='$adminStyle'>";

$adminJS = "$rootUrl/Admin/adminScript.js";
echo "<script src='$adminJS'></script>";

require __DIR__ . '/navBar.php';


#### cleanup
unset($title, $adminStyle, $adminJS, $tool, $rootUrl);

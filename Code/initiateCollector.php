<?php
error_reporting(E_ALL);

require_once __DIR__.'/customFunctions.php';

// configure and register autoloader
Collector_prepare_autoloader();

// start session
Collector_session_start();

// load file locations
if (!isset($_SESSION['_FILES'])) $_SESSION['_FILES'] = new FileSystem();
$FILE_SYS = $_SESSION['_FILES'];

// load settings
$_SESSION['settings'] = new Collector\Settings($FILE_SYS);
$_SETTINGS = $_SESSION['settings'];

if ($_SETTINGS->password === null) {
    require $FILE_SYS->get_path("Set Password");
}

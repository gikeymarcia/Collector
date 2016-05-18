<?php

// configure and register autoloader
require __DIR__ . '/vendor/Kint/Kint.class.php';
require __DIR__ . '/classes/Autoloader.php';
$autoloader = new Collector\Autoloader();
$autoloader->register();
$autoloader->add('Collector', __DIR__.'/classes');
$autoloader->add('adamblake\Parse', __DIR__.'/vendor/adamblake/Parse');
$autoloader->add('phpbrowscap', __DIR__.'/vendor/phpbrowscap');

// start session
session_start();
error_reporting(E_ALL);

// load file locations
$_PATH = new Collector\Pathfinder($_SESSION['Pathfinder']);

// check if they switched Collectors
// (e.g., went from 'MG/Collector/Code/Done.php' to 'TK/Collector/Code/Done.php')
$currentCollector = $_PATH->get('root', 'url');
if (!isset($_SESSION['Current Collector'])
    ||  $_SESSION['Current Collector'] !== $currentCollector
) {
    $_SESSION = array();
    $_SESSION['Current Collector'] = $currentCollector;
    $_PATH = new Collector\Pathfinder($_SESSION['Pathfinder']);

    // if inside Code/ redirect to index
    if ($_PATH->inDir('Code') && !$_PATH->atLocation('Login')) {
        header('Location: '.$_PATH->get('index'));
        exit;
    }
}
unset($currentCollector);

// load settings
if (isset($_SESSION['settings'])) {
    $_SETTINGS = &$_SESSION['settings'];
    $_SETTINGS->upToDate($_PATH);
} else {
    $_SESSION['settings'] = new Collector\Settings(
        $_PATH->get('Common Settings'),
        $_PATH->get('Experiment Settings'),
        $_PATH->get('Password')
    );
    $_SETTINGS = &$_SESSION['settings'];
}

if ($_SETTINGS->password === null) {
    $noPass = true;
    require $_PATH->get("Set Password");
    if ($noPass === true) {
        exit;
    }
}

// if experiment has been loaded (after login) set the variable
if (isset($_SESSION['_EXPT'])) {
    $_EXPT = $_SESSION['_EXPT'];
    $_TRIAL = $_EXPT->getTrial();
}

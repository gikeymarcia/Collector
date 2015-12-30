<?php
/**
 * Autoloader function for Collector.
 * 
 * @param string $className The class to load.
 */
function autoClassLoader($className)
{
    $root = '';
    $ancestors = 0;
    while (!is_dir("{$root}Code/classes") && ($ancestors < 3)) {
        $root .= '../';
        ++$ancestors;
    }
    $loc = "{$root}Code/classes/$className.php";
    if (is_file($loc)) {
        require $loc;
    } else {
        var_dump(scandir(dirName($loc)));
        echo "Object $className could not be found";
    }
}
spl_autoload_register('autoClassLoader');

// start the session and error reporting
session_start();
error_reporting(-1);

// load file locations
$_PATH = new Pathfinder($_SESSION['Pathfinder']);

// load custom functions and parse
require $_PATH->get('Custom Functions');

// check if they switched Collectors 
// (e.g., went from 'MG/Collector/Code/Done.php' to 'TK/Collector/Code/Done.php')
$currentCollector = $_PATH->get('root', 'url');
if (!isset($_SESSION['Current Collector'])
    ||  $_SESSION['Current Collector'] !== $currentCollector
) {
    $_SESSION = array();
    $_SESSION['Current Collector'] = $currentCollector;
    $_PATH = new Pathfinder($_SESSION['Pathfinder']);

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
    $_SETTINGS->up_to_date($_PATH);
} else {
    $_SESSION['settings'] = new Settings(
        $_PATH->get('Common Settings'),
        $_PATH->get('Experiment Settings'),
        $_PATH->get('Password')
    );
    $_SETTINGS = &$_SESSION['settings'];
}

// if experiment has been loaded (after login) set the variable
if (isset($_SESSION['_EXPT'])) {
    $_EXPT = $_SESSION['_EXPT'];
}

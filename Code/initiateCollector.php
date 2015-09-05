<?php
    // start the session and error reporting
    session_start();
    error_reporting(-1);
    
    // load file locations
    require 'pathfinder.class.php';
    $_PATH = new Pathfinder();
    
    // load custom functions and parse
    require $_PATH->get('Custom Functions');
    require $_PATH->get('Parse');
    
    
    // check for logging in directly from somewhere else
    /* save this for after Mikey's changes to login.php
    if (isset($_GET['Username'], $_GET['Experiment']) AND !$_PATH->atLocation('Login')) {
        $_SESSION = array();
        
        $redirect  = $_PATH->get('Login');
        $redirect .= '?';
        $redirect .= 'Username='   . urlencode($_GET['Username']);
        $redirect .= '&';
        $redirect .= 'Experiment=' . urlencode($_GET['Experiment']);
        $redirect .= '&';
        $redirect .= 'Condition=Auto';
        
        header('Location: ' . $redirect);
        exit;
    }
    */
    
    
    
    // check if they switched Collectors (e.g., went from 'MG/Collector/Code/Done.php' to 'TK/Collector/Code/Done.php')
    $currentCollector = $_PATH->get('root', 'url');
    
    if (    !isset($_SESSION['Current Collector'])
        OR  $_SESSION['Current Collector'] !== $currentCollector
    ) {
        $_SESSION = array();
        $_SESSION['Current Collector'] = $currentCollector;
        
        // if inside Code/ redirect to index
        if (     $_PATH->inDir('Code')
            AND !$_PATH->atLocation('Login')
        ) {
            header('Location: ' . $_PATH->get('index'));
            exit;
        }
    }
    
    unset($currentCollector);
    
    
    
    // check if new experiment is being started (i.e., they are at Experiments/{some exp}/index.php)
    if ($_PATH->inDir('Experiments')) {
        $currentExp = explode('/', $_SERVER['SCRIPT_NAME']);    // get something like array(... , "Collector", "Experiments", "Demo", "index.php")
        $currentExp = $currentExp[count($currentExp)-2];        // take directory name (from above example, take "Demo")
        
        $_PATH->setDefault('Current Experiment', $currentExp);
    }
    
    unset($currentExp);
    
    
    
    // load settings
    $_CONFIG = Parse::fromConfig($_PATH->get('Common Config'), true);
    
    if ($_PATH->getDefault('Current Experiment') !== null) {
        $newSettings = Parse::fromConfig($_PATH->get('Experiment Config'));
        foreach ($newSettings as $settingName => $setting) {
            $_CONFIG->$settingName = $setting;
        }
        unset($newSettings, $settingName, $setting);
    }

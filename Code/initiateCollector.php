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
    
    $_CONFIG = Parse::fromConfig($_PATH->get('Config'), true);

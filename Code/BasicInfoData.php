<?php
/*  Collector
    A program for running experiments on the web
    Copyright 2012-2015 Mikey Garcia & Nate Kornell
 */
    require 'initiateCollector.php';

    $data = array( 'Username' => $_SESSION['Username'],
                   'ID'       => $_SESSION['ID']              )
          + $_POST;

    // write user demographics data to demographics file
    arrayToLine($data, $_FILES->demographics);
    
    if ($_CONFIG->run_instructions) {
        $next = 'instructions.php';
    } else {
        $next = 'experiment.php';
    }
    
    header("Location: $next");
    exit;

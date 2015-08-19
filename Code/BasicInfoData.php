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
    arrayToLine($data, $_PATH->demographics_data);
    
    if ($_CONFIG->run_instructions) {
        $next = $_PATH->get('Instructions Page');
    } else {
        $next = $_PATH->get('Experiment Page');
    }
    
    header("Location: $next");
    exit;

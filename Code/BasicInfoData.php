<?php
/*  Collector
    A program for running experiments on the web
    Copyright 2012-2014 Mikey Garcia & Nate Kornell
 */
    ini_set('auto_detect_line_endings', true);              // fixes problems reading files saved on mac
    session_start();                                        // start the session at the top of each page
    if ($_SESSION['Debug'] == FALSE) {
        error_reporting(0);
    }
    require 'CustomFunctions.php';                          // Loads all of my custom PHP functions
    require 'fileLocations.php';                            // sends file to the right place

    $data = array( 'Username' => $_SESSION['Username'], 'ID' => $_SESSION['ID'] ) + $_POST;
    // write user demographics data to demographics file
    arrayToLine($data, $up.$dataF.$_SESSION['DataSubFolder'].$extraDataF.$demographicsFileName.$outExt);
    
    header("Location: instructions.php");
    exit;
?>
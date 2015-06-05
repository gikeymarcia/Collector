<?php
/*  Collector
    A program for running experiments on the web
 */
    require 'initiateCollector.php';
    
    $data = array(
         'Username' => $_SESSION['Username'],
         'ID'         => $_SESSION['ID'],
         'Date'     => date('c'),
    );
    $data += $_POST;
    arrayToLine($data, $instructPath);
    header('Location: experiment.php');
    exit;
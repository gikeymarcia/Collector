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
    arrayToLine($data, $_PATH->get('Instructions Data'));
    header('Location: ' . $_PATH->get('Experiment Page'));
    exit;

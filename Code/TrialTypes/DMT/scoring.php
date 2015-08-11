<?php
/*  Collector
    A program for running experiments on the web
    Copyright 2012-2014 Mikey Garcia & Nate Kornell
 */
    $data = $_POST;
    
    $dataTypes = array('Choice', 'ChoiceCode', 'Score', 'dmtRT');
    $trialData = array();
    
    foreach ($dataTypes as $col) {
        $trialData[$col] = explode(',', $_POST[$col]);
    }
    
    $rounds = count($trialData[ $dataTypes[0] ]);
    
    for ($i=0; $i<$rounds; ++$i) {
        $extraData = array('DMT_Round' => $i+1);
        
        foreach ($dataTypes as $col) {
            $extraData['DMT_'  .$col] = $trialData[$col][$i];
        }
        
        recordTrial($extraData, false, false);
    }

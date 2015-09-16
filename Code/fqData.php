<?php
/*  Collector
    A program for running experiments on the web
    Copyright 2012-2015 Mikey Garcia & Nate Kornell
 */
    require 'initiateCollector.php';


    // setting up aliases for later use
    $allFQs =& $_SESSION['FinalQs'];
    $pos    =& $_SESSION['FQpos'];
    $FQ     =& $allFQs[$pos];                                               // all info about current final question
    
    $readablePos = $pos -1;
    
    
    // capture data
    $formData = isset($_POST['formData']) ? $_POST['formData'] : '';        // it wouldn't be set, if they left all checkboxes blank
    
    $data = array(  'Username'  =>  $_SESSION['Username'],
                    'ID'        =>  $_SESSION['ID'],
                    'Trial'     =>  $readablePos,
                    'Question'  =>  $FQ['Question'],
                    'Type'      =>  $FQ['Type'],
                    'RT'        =>  $_POST['RT']  );
    
    if (is_array($formData)) {
        foreach ($formData as $resp) {
            $data['Response'] = $resp;
            arrayToLine($data, $_PATH->final_questions_data);
        }
    } else {
        $data['Response'] = $formData;
        arrayToLine($data, $_PATH->final_questions_data);
    }
    
    
    $pos++;                                                             // advance position counter
    
    if (!isset($allFQs[$pos])) {                                        // if there isn't a question coming up
        header("Location: " . $_PATH->get('Done'));                                       // send to done.php
        exit;                                                               // don't run the code below
    }
    
    
    header("Location: " . $_PATH->get('Final Questions Page'));                             // send back to FinalQuestions.php before any HTML is sent
    exit;

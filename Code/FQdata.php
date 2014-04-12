<?php
/*  Collector
    A program for running experiments on the web
    Copyright 2012-2014 Mikey Garcia & Nate Kornell
 */
    require 'CustomFunctions.php';                      // Load custom PHP functions
    initiateCollector();
    require 'fileLocations.php';                        // sends file to the right place


    // setting up aliases for later use
    $allFQs =& $_SESSION['FinalQs'];
    $pos    =& $_SESSION['FQpos'];
    $FQ     =& $allFQs[$pos];                           // all info about current final question
    
    $readablePos = $pos -1;
    
    
    // capture data
    $formData = isset($_POST['formData']) ? $_POST['formData'] : '';            // it wouldn't be set, if they left all checkboxes blank

    
    $data = array(  'Username'  =>  $_SESSION['Username'],
                    'ID'        =>  $_SESSION['ID'],
                    'Trial'     =>  $readablePos,
                    'Question'  =>  $FQ['Question'],
                    'Type'      =>  $FQ['Type'],
                    'RT'        =>  $_POST['RT']
                  );

    if( is_array($formData) ) {
        foreach( $formData as $resp ) {
            $data['Response'] = $resp;
            arrayToLine( $data, $finalQuestionsDataCompleteFileName );
        }
    } else {
        $data['Response'] = $formData;
        arrayToLine( $data, $finalQuestionsDataCompleteFileName );
    }


    // advance counter before sending back to final questions
    $pos++;
    
    // these two lines redirect the page to FinalQuestions.php before any HTML is sent
    header("Location: FinalQuestions.php");
    exit;
?>
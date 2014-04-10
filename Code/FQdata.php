<?php
/*  Collector
    A program for running experiments on the web
    Copyright 2012-2014 Mikey Garcia & Nate Kornell
 */
    require 'CustomFunctions.php';                      // Load custom PHP functions
    require 'fileLocations.php';                        // sends file to the right place
    initiateCollector();


    // setting up aliases for later use
    $allFQs =& $_SESSION['FinalQs'];
    $pos    =& $_SESSION['FQpos'];
    $FQ     =& $allFQs[$pos];                           // all info about current final question
    
    $readablePos = $pos -1;
    
    
    // capture data
    $formData = isset($_POST['formData']) ? $_POST['formData'] : '';            // it wouldn't be set, if they left all checkboxes blank


    $fileName = $up.$dataF.$_SESSION['DataSubFolder'].$extraDataF.$finalQuestionsDataFileName.$outExt;
    
    $data = array(  'Username'  =>  $_SESSION['Username'],
                    'ID'        =>  $_SESSION['ID'],
                    'Trial'     =>  $readablePos,
                    'Question'  =>  $FQ['Question'],
                    'Type'      =>  $FQ['Type'],
                    'RT'        =>  $_POST['RT']
                  );

    if( is_array($_POST['formData']) ) {
        foreach( $_POST['formData'] as $resp ) {
            $data['Response'] = $resp;
            arrayToLine( $data, $fileName );
        }
    } else {
        $data['Response'] = $_POST['formData'];
        arrayToLine( $data, $fileName );
    }


    // advance counter before sending back to final questions
    $pos++;
    
    // these two lines redirect the page to FinalQuestions.php before any HTML is sent
    header("Location: FinalQuestions.php");
    exit;
?>
<?php
/*  Collector
    A program for running experiments on the web
    Copyright 2012-2015 Mikey Garcia & Nate Kornell
 */
    
    // to make sure that everything is recorded, just throw all of POST into
    // $data, which will eventually be recorded into the output file.
    // To create new columns, simply assign the new data you want to record
    // to a new key in $data.
    // For example,    $data[ 'New Column' ] = "Hello";    would make a new
    // column titled "New Column" in the output, and every row for this trial
    // type would have the value "Hello".
    $data = $_POST;
    
    $answerClean   = trim(strtolower($answer));
    $response      = $_POST['Response'];
    $responseClean = substr( $answerClean, 0, 2 ) . trim(strtolower($response));
    $Acc           = NULL;
    $Acc2          = NULL;
    
    // store the "complete" answer here, which is both the two-letter cue and
    // the rest of the word that they typed in.
    $data[ 'ResponseComplete' ] = $responseClean;
    
    #### Calculating and saving accuracy for trials with user input
    similar_text($responseClean, $answerClean, $Acc);                   // determine text similarity and store as $Acc
    similar_text($responseClean, trim(strtolower($response)), $Acc2);
    $Acc = max($Acc, $Acc2);
    $data['Accuracy'] = $Acc;
    
    #### Scoring and saving scores
    if ($Acc == 100) {                          // strict scoring
        $data['strictAcc'] = 1;
    } else {
        $data['strictAcc'] = 0;
    }
    
    if ($Acc >= $_CONFIG->lenient_criteria) {             // lenient scoring
        $data['lenientAcc'] = 1;
    } else {
        $data['lenientAcc'] = 0;
    }

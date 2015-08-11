<?php
/*  Collector
    A program for running experiments on the web
    Copyright 2012-2015 Mikey Garcia & Nate Kornell
 */
    $data = $_POST;
    
    
    #### Code that saves and scores information when user input is given
    if (isset($_POST['Response'])) {                                        // if there is a response given then do scoring
        
        ### cleaning up response and answer (for later comparisons)
        $response      = $_POST['Response'];
        $response      = trim(strtolower($response));
        $correctAns    = explode('|', $answer);                             // if there is a range of answers, just use the first one for scoring
        $correctAns    = array_shift($correctAns);
        $correctAns    = trim(strtolower($correctAns));
        $Acc           = NULL;
        
        #### Calculating and saving accuracy for trials with user input
        similar_text($response, $correctAns, $Acc);                   // determine text similarity and store as $Acc
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
    }

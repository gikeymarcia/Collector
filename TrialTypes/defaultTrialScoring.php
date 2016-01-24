<?php
/*  Collector
    A program for running experiments on the web
    Copyright 2012-2016 Mikey Garcia & Nate Kornell
 */
    $data = $_POST;
    /*
     * Q: Why are we using $data instead of setting values directly into $_EXPT->responses?
     * 
     * A: $data holds all scoring information and once scoring is complete $data is merged
     *    into $_EXPT->responses[$currentPos-1]
     *    
     *    This is done so when scoring a post trial all data will be prepended with the 
     *    correct post# (e.g., $data['RT'] would be merged as $data['post#_RT] iF scoring 
     *    is happening for a 'Post 1 Trial Type')
     */

    #### Code that saves and scores information when user input is given
    if (isset($_POST['Response'])) {                                        // if there is a response given then do scoring

        ### cleaning up response and answer (for later comparisons)
        $response = $_POST['Response'];
        $response = trim(strtolower($response));
        $correctAns = trim(strtolower($answer));
        $Acc = null;

        #### Calculating and saving accuracy for trials with user input
        similar_text($response, $correctAns, $Acc);                   // determine text similarity and store as $Acc
        $data['Accuracy'] = $Acc;

        #### Scoring and saving scores
        if ($Acc == 100) {                          // strict scoring
            $data['strictAcc'] = 1;
        } else {
            $data['strictAcc'] = 0;
        }

        if ($Acc >= $_SETTINGS->lenient_criteria) {             // lenient scoring
            $data['lenientAcc'] = 1;
        } else {
            $data['lenientAcc'] = 0;
        }
    }

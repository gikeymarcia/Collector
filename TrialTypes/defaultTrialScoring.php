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

    // Calculate and save accuracy for trials with user input (i.e. trials with 
    // a 'Response').
    if (isset($data['Response'])) {
        // determine text similarity and store to data['Accuracy']
        // trim the strings and convert to lowercase before comparison
        similar_text(
            trim(strtolower($data['Response'])), 
            trim(strtolower($_EXPT->get('answer'))),
            $data['Accuracy']
        );

        // store strict score
        $data['strictAcc'] = $data['Accuracy'] == 100 ? 1 : 0;

        // store lenient score
        $data['lenientAcc'] = $data['Accuracy'] >= $_SETTINGS->lenient_criteria ? 1 : 0;
    }

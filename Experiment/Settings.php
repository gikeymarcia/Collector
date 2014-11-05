<?php
/*  Collector
    A program for running experiments on the web
    Copyright 2012-2014 Mikey Garcia & Nate Kornell
 */
    #### setting experiment variables ####
    $experimentName   = 'Collector';            // Recorded in datafile and can be useful
    $loginCounterName = '1.txt';                // Change to restart condition cycling
    $doDemographics   = FALSE;                  // Can be TRUE or FALSE
    $doInstructions   = TRUE;
    $loginCounterName = '1.csv';                // Change to restart condition cycling
    $nextExperiment   = FALSE;                  // to link use format 'www.cogfog.com/Generic/' do not forget the www and the ending '/'

    // debugging functionality
    $checkAllFiles = TRUE;                      // if `TRUE`, all cues in all stimuli files will be checked for existence before the experiment
    $checkCurrentFiles = FALSE;                 // if `TRUE`, with each login, the cues for just that session will be checked for file existence
    $debugName = '';                            // create a password here to enable the use of the debug name when logging in
    $debugMode = FALSE;                         // Can be `TRUE` or `FALSE` (without ticks)
    $debugTime = 1;                             // trial length (in seconds) when in debug mode, if set to '' then timing will be normal in debug mode
    $trialDiagnostics = FALSE;                  // show trial diagnostics? `TRUE` or `FALSE`
    $stopAtLogin = FALSE;                       // show diagnostic information once login process is complete
    $stopForErrors = TRUE;                      // stop experiment progression if errors are found at login? `TRUE` or `FALSE`

    //mTurk Mode
    $mTurkMode    = FALSE;                      // turn on mTurkMode? `TRUE` or `FALSE` (without ticks)
    $verification = 'Shinebox';                 // code that shows on done.php
    $checkElig    = FALSE;                      // use files in eligibility/ folder to check past participation (mTurkMode must be on to use this)
    $blacklist    = FALSE;                      // when true, the same IP cannot participate twice
    $whitelist    = array('::1', 'other-ip');   // The IPs in this array will be allowed to participate more than once
                                                // ::1 is the default IPv6 loopback -- leave it in so that the check will pass when working locally

    // index.php (Starting Page)
    $showConditionSelector = TRUE;              // Show (TRUE) or hide (FALSE) the condition selector at login?
    $welcome        = 'Welcome to the experiment!';
    $expDescription = '<p> This experiment will run for approximately 25 minutes.  Your goal is to learn some information</p>';
    $askForLogin    = '<p> Please enter your ucla email address</p>';

    
    // scoring settings
    $lenientCriteria = 75;                      // determines the % match required to count an answer as 1(correct) or 0(incorrect)

    // trial settings
    $MultiChoiceButtons    = array( 'Cat1', 'Cat2', 'Cat3', 'Cat4', 'Cat6', 'Cat6', 'Cat7', 'Cat 8', 'Cat9', 'Cat10', 'Cat11', 'Cat 12');
    $MCitemsPerRow = 4;                         // sets how many items per row when using MCpic trials (use values 1-4; anything bigger causes problems which require css changes
    
    // done.php
    $experimenterEmail = 'youremail@yourdomain.com';
    
    // getdata
    $getdataPassword = '';                        // to enable getdata, enter a string other than ''
?>
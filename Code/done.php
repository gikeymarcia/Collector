<?php
/*  Collector
    A program for running experiments on the web
    Copyright 2012-2015 Mikey Garcia & Nate Kornell
 */
    require 'initiateCollector.php';
    
    // if someone skipped to done.php without doing all trials
    if ((array_key_exists('finishedTrials', $_SESSION) == FALSE)
        OR ($_SESSION['finishedTrials'] != TRUE)
    ) {
        header("Location: http://www.youtube.com/watch?v=oHg5SJYRHA0");            // rick roll people trying to skip to done.php
        exit;
    }
    
    
    // turn off error reporting for debug mode
    if (array_key_exists('Debug', $_SESSION)) {
        if ($_SESSION['Debug'] == FALSE) {
            error_reporting(0);
        }
    }
    
    
    // Set the page message
    if ($nextExperiment == FALSE) {
        $title   = 'Done!';
        $message = '<h2>Thank you for your participation!</h2>'
                 .  '<p>If you have any questions about the experiment please email '
                 .      '<a href="mailto:' . $experimenterEmail . '?Subject=Comments%20on%20' . $experimentName . '" target="_top">' . $experimenterEmail . '</a>'
                 .  '</p>';
        if ($mTurkMode == TRUE) {
            $message .= '<h3>Your verification code is: ' . $verification . '-' . $_SESSION['ID'] .'</h3>';
        }
    } else {
        $title    = 'Quick Break';
        $message  = '<h2>Experiment will resume in 5 seconds.</h2>';
        $nextLink = 'http://' . $nextExperiment;
        $username = $_SESSION['Debug'] ? $debugName . ' ' . $_SESSION['Username'] : $_SESSION['Username'];
        echo '<meta http-equiv="refresh" content="5; url=' . $nextLink . 'Code/login.php?Username='
            . urlencode($username) . '&Condition=Auto&ID=' . $_SESSION['ID'] . '">';
    }
    
    
    if (isset($_SESSION['finishedTrials'])
        AND (!isset($_SESSION['alreadyDone']))
        ) {
        // calculate total duration of experiment session
        $duration = time() - strtotime($_SESSION['Start Time']);
        $durationFormatted = $duration;
        $hours   = floor($durationFormatted/3600);
        $minutes = floor( ($durationFormatted - $hours*3600)/60);
        $seconds = $durationFormatted - $hours*3600 - $minutes*60;
        if ($hours   < 10 ) { $hours   = '0' . $hours;   }
        if ($minutes < 10 ) { $minutes = '0' . $minutes; }
        if ($seconds < 10 ) { $seconds = '0' . $seconds; }
        $durationFormatted = $hours . ':' . $minutes . ':' . $seconds;
        
        
        #### Record info about the person ending the experiment to status finish file
        $data = array(
                        'Username'              => $_SESSION['Username'],
                        'ID'                    => $_SESSION['ID'],
                        'Date'                  => date('c'),
                        'Duration'              => $duration,
                        
                        'Duration_Formatted'    => $durationFormatted,
                        'Session'               => $_SESSION['Session'],
                        'Condition_Number'      => $_SESSION['Condition']['Number'],
                        );
        arrayToLine($data, $statusEndPath);
        
        
        ######## Save the $_SESSION array as a JSON string
        $ExpOverFlag = $_SESSION['Trials'][ ($_SESSION['Position']) ]['Procedure']['Item'];
        // if you haven't finished all sessions yet
        if ($ExpOverFlag != 'ExperimentFinished') {           
            $_SESSION['Position']++;                        // increment counter so next session will begin after the *NewSession* (if multisession)
            $_SESSION['Session']++;                         // increment session # so next login will be correctly labeled as the next session
            $_SESSION['ID'] = rand_string();                // generate a new ID (for next login)
            $_SESSION['finishedTrials'] = FALSE;            // will stop them from skipping to done.php during next session
            $_SESSION['LastFinish'] = time();
        }
        
        $jsonSession = json_encode($_SESSION);              // encode the entire $_SESSION array as a json string
        
        $jsonDIR  = $_rootF . $dataF . $dataSubFolder . $jsonF;
        $jsonPath = $jsonDIR . $_SESSION['Username'] . '.json';
        
        if (!is_dir($jsonDIR)) {                            // make the folder if it doesn't exist
            mkdir($jsonDIR, 0777, true);
        }
        $jsonHandle = fopen($jsonPath, 'w');                // open the file for writing, zero out any previous data
        fwrite($jsonHandle, $jsonSession);                  // write the current state of $_SESSION
        fclose($jsonHandle);
        #######
    }
    
    
    $_SESSION = array();                        // clear out all session info
    session_destroy();                          // destroy the session so it doesn't interfere with any future experiments
    
    require $_codeF . 'Header.php';
?>
    <form id="content">
        <?php echo $message; ?>
    </form>
    
    <style>
        #content { width: 500px; }
        #content { text-rendering: optimizeLegibility; }
    </style>
<?php
    require $_codeF . 'Footer.php';
?>
<?php
    if (!isset($_GET['trialType']) ) {
        echo 'Please select a trial type. . . .';
        exit;
    }
    session_start();
    $_SESSION = array();
    $_SESSION['Debug'] = FALSE;     // this just messes with timing
    
    include 'scanTrialTypes.php';
    $_SESSION['Trial Types'] = $trialTypes;
    
    $_SESSION['Username']   = 'TrialTester';
    $_SESSION['ID']         = 'TrialTester';
    $_SESSION['Position']   = 1;
    $_SESSION['PostNumber'] = 0;
    $_SESSION['Condition']  = array(
        'Number'                => 1,
        'Stimuli'               => 'test',
        'Procedure'             => 'test',
        'Condition Description' => 'testing trial types',
    );
    
    $_SESSION['Trials'][1] = array(
        'Stimuli' => array(
            'Cue'     => $_GET['Cue'],
            'Answer'   => $_GET['Answer'],
            'Shuffle' => 'off'
        ),
        'Procedure' => array(
            'Item' => '2',
            'Timing' => 'user',
            'Shuffle' => 'off',
            'Trial Type' => $_GET['trialType'],
            'Text' => $_GET['Text'],
        ),
        'Response' => array()
    );
    
    session_write_close();
    
    include 'trial.php';
    
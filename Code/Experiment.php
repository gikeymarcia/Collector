<?php
/*  Collector
    A program for running experiments on the web
    Copyright 2012-2015 Mikey Garcia & Nate Kornell
 */

    require 'initiateCollector.php';

    // this only happens once, so that refreshing the page doesn't do anything, and recording a new line of data is the only way to update the timestamp
    if (!isset($_SESSION['Timestamp'])) {
        $_SESSION['Timestamp'] = microtime(true);
    }
    
    
    function recordTrial($extraData = array(), $exitIfDone = true, $advancePosition = true) {
        global $_CONFIG, $_PATH;
        #### setting up aliases (for later use)
        $currentPos   =& $_SESSION['Position'];
        $currentTrial =& $_SESSION['Trials'][$currentPos];

        #### Calculating time difference from current to last trial
        $oldTime = $_SESSION['Timestamp'];
        $_SESSION['Timestamp'] = microtime(true);
        $timeDif = $_SESSION['Timestamp'] - $oldTime;
        
        #### Writing to data file
        $data = array(  'Username'              =>  $_SESSION['Username'],
                        'ID'                    =>  $_SESSION['ID'],
                        'ExperimentName'        =>  $_CONFIG->experiment_name,
                        'Session'               =>  $_SESSION['Session'],
                        'Trial'                 =>  $_SESSION['Position'],
                        'Date'                  =>  date("c"),
                        'TimeDif'               =>  $timeDif,
                        'Condition Number'      =>  $_SESSION['Condition']['Number'],
                        'Stimuli File'          =>  $_SESSION['Condition']['Stimuli'],
                        'Order File'            =>  $_SESSION['Condition']['Procedure'],
                        'Condition Description' =>  $_SESSION['Condition']['Condition Description'],
                        'Condition Notes'       =>  $_SESSION['Condition']['Condition Notes']
                      );
        foreach ($currentTrial as $category => $array) {
            $data += AddPrefixToArray($category . '*', $array);
        }
        
        if (!is_array($extraData)) {
            $extraData = array($extraData);
        }
        foreach ($extraData as $header => $datum) {
            $data[$header] = $datum;
        }
        
        $writtenArray = arrayToLine($data, $_PATH->get('Experiment Output'));                                       // write data line to the file
        ###########################################

        // progresses the trial counter
        if ($advancePosition) {
            $currentPos++;
            $_SESSION['PostNumber'] = 0;
        }

        // are we done with the experiment? if so, send to finalQuestions.php
        if ($exitIfDone) {
            $item = $_SESSION['Trials'][$currentPos]['Procedure']['Item'];
            if ($item == 'ExperimentFinished') {
                $_SESSION['finishedTrials'] = true;             // stops people from skipping to the end
                header('Location: ' . $_PATH->get('Final Questions Page'));
                exit;
            }
        }
        return $writtenArray;
    }


    // setting up easier to use and read aliases(shortcuts) of $_SESSION data
    $condition      =& $_SESSION['Condition'];
    
    $currentPos     =& $_SESSION['Position'];
    $currentPost    =& $_SESSION['PostNumber'];
    $currentTrial   =& $_SESSION['Trials'][$currentPos];
    
    $currentStimuli =  $currentTrial['Stimuli'];
    createAliases($currentStimuli);
    
    // this will also create aliases of any columns that apply to the current trial (filtering out "post X" prefixes when necessary)
    // currentProcedure becomes an array of all columns matched for this trial, using their original column names
    $currentProcedure = ExtractTrial($currentTrial['Procedure'], $currentPost);
    
    if (!isset($trialType)) {
        $error = array(
            'Error*Missing_Trial_Type' => 'Post ' . $_SESSION['PostNumber']
        );
        recordTrial($error);
        header('Location: ' . $_PATH->get('Experiment Page'));
        exit;
    }
    
    $trialType = strtolower($trialType);
    
    $trialFiles = getTrialTypeFiles($trialType);
    if (isset($trialFiles['script'])) {
        $addedScripts = array($trialFiles['script']);
    }
    if (isset($trialFiles['style'])) {
        $addedStyles  = array($trialFiles['style']);
    }
    
    if (!isset($item)) {
        $item = $currentTrial['Procedure']['Item'];
    }
    
    if ($currentPost < 1) {
        $prefix = '';
    } else {
        $prefix = 'Post' . ' '  . $currentPost . ' ';
    }
    
    if (isset($currentTrial['Procedure'][$prefix . 'Text'])) {
        $text =& $currentTrial['Procedure'][$prefix . 'Text'];
        $text =  str_ireplace(array('$cue', '$answer'), array($cue, $answer), $text);
    }
    
    // if there is another item coming up then set it as $nextTrail
    if (array_key_exists($currentPos+1, $_SESSION['Trials'])) {
        $nextTrial =& $_SESSION['Trials'][$currentPos+1];
    } else {
        $nextTrial = false;
    }
    
    // variables I'll need and/or set in trialTiming() function
    $timingReported = strtolower($maxTime);         // get value from 'Max Time' column
    $formClass = '';
    $maxTime   = '';
    if (!isset($minTime)) {
        $minTime = 'not set';
    }
    
    
    ob_start();
    
    #### Presenting different trial types ####
    $postTo    = $_PATH->get('Experiment Page');
    $trialFail = false;                                    // this will be used to show diagnostic information when a specific trial isn't working
    
    $title = 'Experiment';
    $_dataController = 'experiment';
    $_dataAction = $trialType;
    
    if (isset($trialFiles['helper'])) include $trialFiles['helper'];
    
    /*
     * Whenever experiment.php finds $_POST data, it will try to store that data
     * immediately, rather than simply holding it through the trial.
     * This is done by either storing the data in $currentTrial['Response'].
     * If the main trial and all post trials are completed, the data will
     * be written using the recordTrial() function.
     */
    if ($_POST !== array()) {
        if ($currentPost === 0) {
            $keyMod = '';
        } else {
            $keyMod = 'post' . $currentPost . '_';
        }
        $findingKeys = false;
        require $trialFiles['scoring'];
        if (!isset($data)) { $data = $_POST; }
        #### merging $data into $currentTrial['Response]
        $currentTrial['Response'] = placeData($data, $currentTrial['Response'], $keyMod);
        
        $notTrials   = array('off', 'no', '', 'n/a');
        $finishedRow = true;
        ++$currentPost;
        
        while (isset($currentTrial['Procedure']['Post ' . $currentPost . ' Trial Type'])) {
            $nextTrialType = strtolower($currentTrial['Procedure']['Post ' . $currentPost . ' Trial Type']);
            if (!in_array($nextTrialType, $notTrials)) {
                $finishedRow = false;
                break;
            }
            ++$currentPost;
        }
        
        if ($finishedRow) {
            recordTrial();
        }
        
        header('Location: ' . $_PATH->get('Experiment Page'));
        exit;
    }


    // if we hit a *NewSession* then the experiment is over (this means that we don't ask FinalQuestions until the last session of the experiment)
    if(strtolower($item) == '*newsession*') {
        $_SESSION['finishedTrials'] = true;
        header('Location: ' . $_PATH->get('Done'));
        exit;
    }
    
    require $_PATH->get('Header');
    
?>

    <!-- trial content -->
    <?php
        if ($trialFiles['display']):
            trialTiming();                                   // find out what kind of class name to give the up-coming form
            ?><form class="<?php echo $formClass; ?> invisible" action="<?php echo $postTo; ?>" method="post" id="content">
                  <?php include $trialFiles['display'] ?>
                  <input id="RT"       name="RT"      type="hidden"  value="-1"/>
                  <input id="RTfirst"  name="RTfirst" type="hidden"  value="-1"/>
                  <input id="RTlast"   name="RTlast"  type="hidden"  value="-1"/>
                  <input id="Focus"    name="Focus"   type="hidden"  value="-1"/>
              </form><?php
        else: ?>
            <h2>Could not find the following trial type: <strong><?php echo $trialType; ?></strong></h2>
            <p>Check your procedure file to make sure everything is in order. All information about this trial is displayed below.</p>

            <!-- default trial is always user timing so you can click 'Done' and progress through the experiment -->
            <div class="precache">
                <form name="UserTiming" class="UserTiming" action="<?php echo $postTo; ?>" method="post">
                    <input id="RT"     name="RT"      type="hidden" value="RT"       />
                    <input id="Focus"  name="Focus"   type="hidden" value="notSet"   />
                    <input class="button" id="FormSubmitButton" type="submit"   value="Done" />
                </form>
            </div>
    <?php
            $trialFail = true;
            $maxTime = 'user';
        endif;
    ?>

    <!-- hidden field that JQuery/JavaScript uses to check the timing to $postTo -->
    <div id="maxTime"   class="hidden"> <?php echo $maxTime; ?> </div>
    <div id="minTime"   class="hidden"> <?php echo $minTime; ?> </div>

     <?php
        #### Diagnostics ####
        if (($_CONFIG->trial_diagnostics == true)
            OR ($trialFail == true)
        ) {
            // clean the arrays used so that they output strings, not code
            $clean_session      = arrayCleaner($_SESSION);
            $clean_currentTrial = arrayCleaner($currentTrial);
            echo '<div class=diagnostics>'
                .    '<h2>Diagnostic information</h2>'
                .    '<ul>'
                .        '<li> Condition #: '              . $clean_session['Condition']['Number']                . '</li>'
                .        '<li> Condition Stimuli File:'    . $clean_session['Condition']['Stimuli']               . '</li>'
                .        '<li> Condition Procedure File: ' . $clean_session['Condition']['Procedure']             . '</li>'
                .        '<li> Condition description: '    . $clean_session['Condition']['Condition Description'] . '</li>'
                .    '</ul>'
                .    '<ul>'
                .        '<li> Trial Number: '         . $currentPos                                  . '</li>'
                .        '<li> Trial Type: '           . $trialType                                   . '</li>'
                .        '<li> Trial max time: '       . $clean_currentTrial['Procedure']['Max Time'] . '</li>'
                .        '<li> Trial Time (seconds): ' . $maxTime                                     . '</li>'
                .    '</ul>'
                .    '<ul>'
                .        '<li> Cue: '    . show($cue)    . '</li>'
                .        '<li> Answer: ' . show($answer) . '</li>'
                .    '</ul>';
            readable($currentTrial,         "Information loaded about the current trial");
            readable($_SESSION['Trials'],   "Information loaded about the entire experiment");
            echo '</div>';
        }
    ?>

<!-- Pre-Cache Next trial -->
<div class="precachenext">
    <?php
    if ($nextTrial) {
        $nextCues    = explode('|', $nextTrial['Stimuli']['Cue']);
        $nextAnswers = explode('|', $nextTrial['Stimuli']['Answer']);
        $allNext = array_merge($nextCues, $nextAnswers);
        foreach ($allNext as $next) {
            echo show($next);
        }
    }
    ?>
</div>
    
<?php
    require $_PATH->get('Footer');
    
    ob_end_flush();

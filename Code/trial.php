<?php
/*  Collector
    A program for running experiments on the web
    Copyright 2012-2014 Mikey Garcia & Nate Kornell
 */

    require 'initiateCollector.php';
    
    // this only happens once, so that refreshing the page doesn't do anything, and recording a new line of data is the only way to update the timestamp
    if (!isset($_SESSION['Timestamp'])) {
        $_SESSION['Timestamp'] = microtime(TRUE);
    }
    
    
    
    function recordTrial($extraData = array(), $exitIfDone = TRUE, $advancePosition = TRUE) {

        #### setting up aliases (for later use)
        $currentPos   =& $_SESSION['Position'];
        $currentTrial =& $_SESSION['Trials'][$currentPos];
        
        global $experimentName;


        #### Calculating time difference from current to last trial
        $oldTime = $_SESSION['Timestamp'];
        $_SESSION['Timestamp'] = microtime(TRUE);
        $timeDif = $_SESSION['Timestamp'] - $oldTime;
        
        
        #### Writing to data file
        $data = array(  'Username'              =>  $_SESSION['Username'],
                        'ID'                    =>  $_SESSION['ID'],
                        'ExperimentName'        =>  $experimentName,
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
        
        $writtenArray = arrayToLine($data, $_SESSION['Output File']);                                       // write data line to the file
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
                $_SESSION['finishedTrials'] = TRUE;             // stops people from skipping to the end
                header("Location: FinalQuestions.php");
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
    
    if (!isset($trialType))
    {
        $error = array(
            'Error*Missing_Trial_Type' => 'Post ' . $_SESSION['PostNumber']
        );
        recordTrial();
        header('Location: trial.php');
        exit;
    }
    
    $trialType = strtolower($trialType);
    
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
        $nextTrial = FALSE;
    }
    
    // variables I'll need and/or set in trialTiming() function
    $timingReported = strtolower(trim( $timing ));
    $formClass    = '';
    $time        = '';
    if( !isset( $minTime ) ) {
        $minTime    = 'not set';
    }
    
    
    ob_start();
    

    #### Presenting different trial types ####
    $expFiles  = $up.$expFiles;                            // setting relative path to experiments folder for trials launched from this page
    $postTo    = 'trial.php';
    $trialFail = FALSE;                                    // this will be used to show diagnostic information when a specific trial isn't working
    $trialFile = $_SESSION['Trial Types'][ $trialType ]['trial'];
    
    
    $title = 'Trial';
    $_dataController = 'trial';
    $_dataAction = $trialType;
    
    
    /*
     * Whenever Trial.php finds $_POST data, it will try to store that data
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
        $findingKeys = FALSE;
        require $_SESSION['Trial Types'][$trialType]['scoring'];
        if (!isset($data)) { $data = $_POST; }
        #### merging $data into $currentTrial['Response]
        $currentTrial['Response'] = placeData($data, $currentTrial['Response'], $keyMod);
        
        $notTrials   = array('off', 'no', '', 'n/a');
        $finishedRow = TRUE;
        ++$currentPost;
        
        while (isset($currentTrial['Procedure']['Post ' . $currentPost . ' Trial Type'])) {
            $nextTrialType = strtolower($currentTrial['Procedure']['Post ' . $currentPost . ' Trial Type']);
            if (!in_array($nextTrialType, $notTrials)) {
                $finishedRow = FALSE;
                break;
            }
            ++$currentPost;
        }
        
        if ($finishedRow) {
            recordTrial();
        }
        
        header('Location: trial.php');
        exit;
    }


    // if we hit a *NewSession* then the experiment is over (this means that we don't ask FinalQuestions until the last session of the experiment)
    if(strtolower($item) == '*newsession*') {
        $_SESSION['finishedTrials'] = TRUE;
        header("Location: done.php");
        exit;
    }
    
    // skip to done.php if some has logged in who has already completed all parts of the experiment
    if ($item == 'ExperimentFinished') {
        header('Location: done.php');
        exit;
    }
    
    require $_codeF . 'Header.php';
    
?>

    <!-- trial content -->
    <?php
        if ($trialFile):
			ob_start();                                      // start an output buffer, so we can include the file, without outputting it quite yet
			include $trialFile;                              // include the file early, so that it can set default values, like default timing
			$trialContents = ob_get_clean();                 // end the buffer without outputting, but store the contents for later use
			
			#### if you have other default settings you want written in the trial type files, you can work with them here
			
			if (!isset($compTime)) {                         // if the trial type doesn't have a default timing, create a general default here
				$compTime = 'user';                          // if the trial should be computer-timed, a user timed trial will be immediately obvious
			}
			trialTiming();                                   // find out what kind of class name to give the up-coming form
			
			#### if you want to edit the trial contents before they are outputted, you can mess with them as a string right here
			
		    ?><form class="<?php echo $formClass; ?> collector-form invisible" action="<?php echo $postTo; ?>" method="post">
                  <?= $trialContents ?>
                  <input class="hidden"  id="RT"     name="RT"       type="text" value="RT"       />
                  <input class="hidden"  id="RTkey"  name="RTkey"    type="text" value="no press" />
                  <input class="hidden"  id="RTlast" name="RTlast"   type="text" value="no press" />
			  </form><?php
        else: ?>
            <h2>Could not find the following trial type: <strong><?php echo $trialType; ?></strong></h2>
            <p>Check your procedure file to make sure everything is in order. All information about this trial is displayed below.</p>

            <!-- default trial is always user timing so you can click 'Done' and progress through the experiment -->
            <div class="precache">
                <form name="UserTiming" class="UserTiming" action="<?php echo $postTo; ?>" method="post">
                    <input class="hidden" id="RT" name="RT"     type="text"     value=""  />
                    <input class="button" id="FormSubmitButton" type="submit"   value="Done" />
                </form>
            </div>
    <?php
        $trialFail = TRUE;
        $time = 'user';
        endif;
    ?>

    <!-- hidden field that JQuery/JavaScript uses to check the timing to $postTo -->
    <div id="Time"      class="hidden"> <?php echo $time; ?>    </div>
    <div id="minTime"   class="hidden"> <?php echo $minTime; ?> </div>

    <!-- placeholders for a debug function that shows timer values -->
    <div id="showTimer" class="hidden">
        <div> Start (ms):   <span id="start">   </span> </div>
        <div> Current (ms): <span id="current"> </span> </div>
        <div> Timer (ms):   <span id="dif">     </span> </div>
    </div>

     <?php
        #### Diagnostics ####
        if (($trialDiagnostics == TRUE)
            OR ($trialFail == TRUE)
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
                .        '<li> Trial Number: '         . $currentPos                                . '</li>'
                .        '<li> Trial Type: '           . $trialType                                 . '</li>'
                .        '<li> Trial timing: '         . $clean_currentTrial['Procedure']['Timing'] . '</li>'
                .        '<li> Trial Time (seconds): ' . $time                                      . '</li>'
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
    require $_codeF . 'Footer.php';
    
    ob_end_flush();

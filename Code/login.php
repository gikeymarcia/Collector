<?php
/*  Collector
    A program for running experiments on the web
 */
    require 'initiateCollector.php';
    
    #### Reset session so it doesn't contain any information from a previous login attempt
    $_SESSION = array();
    $_SESSION['state'] = 'init';


    #### initiate the object that finds files for us
    $_PATH = new Pathfinder($_SESSION['Pathfinder']);
    require $_PATH->get('Shuffle Functions');               // load shuffle functions we will use later


    #### Establish which experiment is active
    $_SESSION['Current Collector'] = $_PATH->get('root', 'url');
    $current = empty($_GET['CurrentExp']) ? '' : $_GET['CurrentExp'];       // if no experiment is set then set current to empty string
    if (in_array($current, getCollectorExperiments()) ) {
        $_PATH->setDefault('Current Experiment', $_GET['CurrentExp']);      // tell pathfinder the current experiment
    } else {
        header('Location: ' . $_PATH->get('root'));                         // send back to index
        exit;
    }
    

    #### load settings from common AND from current experiment folder
    $_SETTINGS = getCollectorSettings();


    #### login objects
    $errors = new ErrorController();

    $user   = new User(
        $_GET['Username'],
        $errors
    );
    $user->feedPathfinder($_PATH);
    
    $debug = new DebugController(           // sets $_SESSION['Debug'] value
        $user->getUsername(), 
        $_SETTINGS->debug_name,
        $_SETTINGS->debug_mode
    );
    $debug->feedPathfinder($_PATH);         // change data recording directory if debug mode is on
    $debug->toSession();                    // sets $_SESSION['Debug'] to a bool

    $cond = new ConditionController(
        $_PATH->get('Conditions'),
        $_PATH->get('Counter'),
        $errors,
        $_SETTINGS->hide_flagged_conditions
    );
    $cond->setSelected($_GET['Condition']);
    
    
    // is this user ineligible to participate in the experiment?
    if ($_SETTINGS->check_elig == true
    ) {
        include $_PATH->get('check');
    }
    

    #### Dealing with people returning to the experiment
    $revisit = new ReturnVisitController(
        $_PATH->get('json'), 
        $_PATH->get('Done'),
        $_PATH->get('Experiment Page')
    );

    if ($revisit->isReturning()) {
        if ($revisit->isDone()) {
            $revisit->reloadToDone();
        }
        if ($revisit->isTimeToReturn()) {                     // updating lots of things with info from previous login
            $revisit->reloadToExperiment($_PATH, $user);            
        } else {
            echo $revisit->getTimeProblem();
            exit;
        }
    }

    // stop people who are specifically trying to return that we don't know about
    if (!empty($_GET['returning'])) {
        echo "We could not find the next part of the experiment for " . $user->getUsername();
        exit;
    }

    #### Set user's condition
    $cond->assignCondition();
    $_PATH->setDefault('Condition Index', $cond->getAssignedIndex());
    // modify paths based on assigned condition

    $procedure = new Procedure(
        $_PATH->get('Procedure Dir'),
        $cond->allProc(),
        $errors
    );
    $stimuli = new Stimuli(
        $_PATH->get('Stimuli Dir'),
        $cond->allStim(),
        $errors
    );

    $status = new StatusController();
    $status->updateUser(
        $user->getUsername(),
        $user->getID(),
        $user->getOutputFile(),
        $user->getSession()
    );
    $status->setConditionInfo(
        $cond->get()
    );
    $status->setPaths(
        $_PATH->get('Status Begin Data'),
        $_PATH->get('Status End Data')
    );
    $status->writeBegin();
    $_SESSION['Status'] = serialize($status);

    // check if procedure and stimuli files have unique column names
    $procedure->checkOverlap( $stimuli->getKeys(true) );

    $procedure->shuffle();
    $stimuli->shuffle();
    
    
    #### Trial Validation
    require $_PATH->get('Trial Validator Require');
    
    ######## Feed stuff to login #######
    $_SESSION['Username']   = $user->getUsername();
    $_SESSION['ID']         = $user->getID();
    $_SESSION['Session']    = $user->getSession();
    $_SESSION['Start Time'] = time();
    
    // access stimuli, procedure, and condition arrays using $_EXPT->[name]
    $_EXPT = new Experiment(
                $stimuli->getShuffled(),
                $procedure->getShuffled(),
                $cond->get()
            );
    $_SESSION['_EXPT'] = $_EXPT;
    
    ####################################


    if ($errors->arePresent()) {
        echo $errors;
        echo "<div>
                Oops, something has gone wrong. Email the experimenter at <b>$_SETTINGS->experimenter_email</b><br>
                <button type='button' onClick='window.location.reload(true);'>Click here to refresh</button>
              </div>";
        exit;
    } else {
        $_SESSION['state'] = 'exp';
        $experiment = $_PATH->get("Experiment Page");
        header("Location: $experiment");
        exit;
    }

exit;
                        #             #
                       ###           ###
                      #####         #####
                     #######       #######
                    #########     #########
                   ###########   ###########
                 ############## ##############
                ################################
    
    #### Find all of the columns that hold trial types (including 'Post# Trial Type's)
    // $trialTypeColumns = array();                                                                // Each position will have the column name of a trial type column
    // $proc = GetFromFile($_FILES->proc_files.'/' . $_SESSION['Condition']['Procedure'], false); // load procedure file without padding
    // foreach ($proc[0] as $col => $val) {                                    // check all column names
    //     if (substr($col, -10) == 'Trial Type') {                           // if ends with 'trial type'
    //         if ($col == 'Trial Type') {                                        // and is trial type
    //             $trialTypeColumns[0] = $col;                                            // save it
    //         } elseif (substr($col, 0, 5) == 'Post ') {                          // if it starts with 'post'
    //             $cleanCol = trim(substr($col, 5, -10));                            // drop the 'post' and 'trial type'
    //             if (is_numeric($cleanCol)) {                                            // if what remains is a # (e.g., '15')
    //                 $correctTitle = 'Post' . ' ' . (int)$cleanCol . ' ' . 'Trial Type';
    //                 if ($col !== $correctTitle) {
    //                     $errors['Count']++;
    //                     $errors['Details'][] = 'Column "' . $col . '" in ' . $_SESSION['Condition']['Procedure']
    //                                          . ' needs to have exactly one space before and after the number (e.g., ' . $correctTitle . ').';
    //                 } else {
    //                     $trialTypeColumns[$cleanCol] = $col;                                    // set $trialTypeColumns[15] to this column name
    //                 }
    //             } else {                                                                // if not, it should have been a #
    //                 $errors['Count']++;
    //                 $errors['Details'][] = 'Column "' . $column . '" in ' . $_SESSION['Condition']['Procedure']
    //                                      . ' needs to be numbered (e.g., "Post <b>1</b> Trial Type").';
    //             }
    //         }
    //     }
    // }
    
    
    
    // #### checking that all Post levels have the needed columns
    // $needed = array('Max Time');                                                          // if we need more cols in the future they can be added here
    // foreach ($needed as $need) {
    //     foreach ($trialTypeColumns as $number => $colName) {                                // check all trial type levels we found
    //         if ($number == 0) {
    //             continue;                                                                   // we already checked the non-post level elsewhere in the code
    //         }
    //         if (!isset($proc[0]['Post' . ' '  . $number . ' ' . $need])) {                  // if the associated needed row doesn't exist (e.g., 'Post 1 Max Time')
    //             $errors['Count']++;
    //             $errors['Details'][] = 'Post level ' . $number . ' is missing a "' . $need . '" column (i.e., add a column called 
    //                                         "Post ' . $number . ' ' . $need . '" to the file "' . $_SESSION['Condition']['Procedure'] . '" to fix this error).
    //                                         <br>
    //                                         <em> If you are not using Post level ' . $number . ' you can safely delete the column 
    //                                         "Post ' . $number . ' Trial Type" and this will also solve this error.</em>';
    //         }
    //     }
    // }
    // unset($proc);
    
    
    
    // #### Checking stimuli files for correct image/media path and filenames
    // if (($_SETTINGS->check_all_files == true)
    //     OR ($_SETTINGS->check_current_files == true)
    // ) {
    //     $stimuliFiles = array();
    //     if ($_SETTINGS->check_all_files == true) {
    //         $stimPath = $_FILES->stim_files.'/';
    //         foreach ($Conditions as $row => $cells) {
    //             $stimuliFiles[] = $stimPath . $Conditions[$row]['Stimuli'];
    //         }
    //     } else {
    //         $stimuliFiles[] = $_FILES->stim_files.'/' . $_SESSION['Condition']['Stimuli'];
    //     }
        
    //     foreach ($stimuliFiles as $fileName) {
    //         $temp = GetFromFile($fileName);
    //         foreach ($temp as $i => $row) {
    //             if ($i < 2) { continue; }                   // skip padding rows
    //             if (show($row['Cue'], true, true) !== $row['Cue']) {
    //                 // show() detects a file extension like .png, and will use FileExists to check that it exists
    //                 // but it will always return a string, for cases where you are showing regular text
    //                 // using FileExists, we can see if a cue detected as an image by show() is a file that actually exists
    //                 if (FileExists('../Experiment/' . $row['Cue']) === false ) {
    //                     $errors['Count']++;
    //                     $errors['Details'][] = 'Image or audio file "../Experiment/' . $row['Cue'] . '" not found for row '
    //                                          . $i . ' in Stimuli File "' . basename($fileName) . '".';
    //                 }
    //             }
    //         }
    //     }
    // }
    
    
    
    // #### Check that we can find files for all trials in use (also finds custom scoring files)
    // $procedure  = GetFromFile($_FILES->proc_files.'/' . $_SESSION['Condition']['Procedure']);
    // $trialTypes = array();                                                  // we will make a list of all found trial types, and what level they will be used at
    // $notTrials  = array('off'   => true,                                    // if the 'Trial Type' value is one of these then it isn't a trial
    //                     'no'    => true,
    //                     ''      => true,
    //                     'n/a'   => true  );
    // foreach ($procedure as $i => $row) {                                    // go through all rows of procedure
    //     if ($row === 0)                                 { continue; }       // skip padding
    //     if (strtolower($row['Item']) === '*newsession*')   { continue; }    // skip multisession markers
    //     foreach ($trialTypeColumns as $postNumber => $column) {             // check all trial levels
    //         $thisTrialType = strtolower($row[$column]);                     // which trial is being used at this level (e.g, Study, Test, etc.)
    //         if (isset($notTrials[$thisTrialType])) {                        // skip turned off trials (off, no, n/a, '')
    //             continue;
    //         }
    //         $trialFiles = getTrialTypeFiles($thisTrialType);
    //         $trialTypes[$thisTrialType]['files'] = $trialFiles;
    //         $trialTypes[$thisTrialType]['levels'][$postNumber] = true;      // make note what levels each trial type are used at (e.g., Study is a regular AND a Post 1 trial)
    //         if ($trialFiles === false) {
    //             $procName = pathinfo($_FILES->proc_files .'/'. $_SESSION['Condition']['Procedure'], PATHINFO_FILENAME);
    //             $errors['Count']++;
    //             $errors['Details'][] = 
    //                 'The trial type '. $row[$column] .' for row '. $i .' in '
    //               . 'the procedure file '. $procName .' has no folder in '
    //               . 'either the "'. $_FILES->custom_trial_types .'" folder or '
    //               . 'the "'. $_FILES->trial_types .'" folder.';
    //         }
    //     }
    // }
    
    // unset($procedure);
    ##### END Error Checking Code #################################################
    
    
    
    
    
    ###############################################################################
    #### Preparing The Experiment #################################################
    ###############################################################################
    // Setting up all the ['Response'] keys that will be needed during the experiment
    // Also checks scoring files if that trial type lists some required columns
    // $proc = GetFromFile($_FILES->proc_files.'/' . $_SESSION['Condition']['Procedure'], false); // load procedure file without padding
    // $allColumnsNeeded = array();
    // $allColumnsOutput = array();
    // foreach ($trialTypes as $type => $info) {
    //     if (isset($info['files']['helper'])) {
    //         $neededColumns = array();
    //         $outputColumns = array();
    //         include $info['files']['helper'];
    //         $trialTypes[$type]['neededColumns'] = $neededColumns;
    //         $trialTypes[$type]['outputColumns'] = $outputColumns;
    //         if (isset($info['levels'][0])) {
    //             $allColumnsNeeded += array_flip($neededColumns);
    //             $allColumnsOutput += array_flip($outputColumns);
    //         }
    //     }
    // }
    // foreach ($trialTypes as $type => $info) {
    //     foreach ($info['levels'] as $postNumber => $null) {
    //         if ($postNumber === 0) continue;
    //         foreach ($info['neededColumns'] as $column) {
    //             $column = 'Post ' . $postNumber . ' ' . $column;
    //             $allColumnsNeeded[$column] = true;
    //         }
    //         foreach ($info['outputColumns'] as $column) {
    //             $column = 'post'  . $postNumber . '_' . $column;
    //             $allColumnsOutput[$column] = true;
    //         }
    //     }
    // }
    // foreach ($allColumnsOutput as &$column) {
    //     $column = null;
    // }
    // unset($column);
    
    
    // foreach (array_keys($allColumnsNeeded) as $column) {
    //     $errors = keyCheck($proc, $column, $errors, $_SESSION['Condition']['Procedure']);
    // }
    
    // include 'shuffleFunctions.php';
    #### Create $_SESSION['Trials'] 
    #### Load all Stimuli and Procedure info for this participant's condition then combine to create the experiment
    // load stimuli for this condition then block shuffle
    // $cleanStimuli = GetFromFile($_FILES->stim_files.'/' . $_SESSION['Condition']['Stimuli']);
    // $stimuli = multiLevelShuffle($cleanStimuli);
    // $stimuli = shuffle2dArray($stimuli, $_SETTINGS->stop_at_login);
    // $_SESSION['Stimuli'] = $stimuli;
    
    // load and block shuffle procedure for this condition
    // $cleanProcedure = GetFromFile($_FILES->proc_files.'/' . $_SESSION['Condition']['Procedure']);
    
    // $addColumns = array('Text');
    // foreach ($addColumns as $add) {
    //     foreach ($trialTypeColumns as $number => $colName) {                                // check all trial type levels we found
    //         if ($number == 0) {
    //             $prefix = '';
    //         } else {
    //             $prefix = 'Post' . ' ' . $number . ' ';
    //         }
    //         $column = $prefix . $add;
    //         addColumn($cleanProcedure, $column);                // this will only add columns if they don't already exist; nothing is overwritten
    //     }
    // }
    
    // $procedure = multiLevelShuffle($cleanProcedure);
    // $procedure = shuffle2dArray($procedure, $_SETTINGS->stop_at_login);
    
    // $_SESSION['Procedure'] = $procedure;
    
    // Load entire experiment into $Trials[1-X] where X is the number of trials
    // $Trials = array(0=> 0);
    // $procedureLength = count($procedure);
    // for ($count=2; $count<$procedureLength; $count++) {
    //     // $Trials[$count-1] = makeTrial($procedure[$count]['Item']);
    //     $items = rangeToArray($procedure[$count]['Item']);
    //     $stim = array();
    //     foreach ($items as $item) {
    //         if (isset($stimuli[$item]) and is_array($stimuli[$item])) {
    //             foreach ($stimuli[$item] as $column => $value) {
    //                 $stim[$column][] = $value;
    //             }
    //         }
    //     }
    //     if ($stim === array()) {
    //         foreach ($stimuli[2] as $column => $value) {
    //             $stim[$column][] = '';
    //         }
    //     }
    //     foreach ($stim as $column => $valueArray) {
    //         $Trials[$count-1]['Stimuli'][$column] = implode('|', $valueArray);
    //     }
    //     // $Trials[$count-1]['Stimuli']    = $stimuli[ ($procedure[$count]['Item']) ];         // adding 'Stimuli', as an array, to each position of $Trials
    //     $Trials[$count-1]['Procedure']  = $procedure[$count];                               // adding 'Procedure', as an array, to each position of $Trials
    //     $Trials[$count-1]['Response']   = $allColumnsOutput;
        
    //     // on trials with no Stimuli info (e.g., freerecall) keep the same Stimuli structure but fill with 'n/a' values
    //     // I need a consistent Trial structure to do all of the automatic output creation I do later on
    //     if ($Trials[$count-1]['Stimuli'] == NULL) {
    //         $stim       =& $Trials[$count-1]['Stimuli'];
    //         $stim       =  $stimuli[2];
    //         $stimKey    =  array_keys($stim);
    //         $empty      =  array_fill_keys($stimKey, 'n/a');
    //         $Trials[$count-1]['Stimuli'] = $empty;
    //     }
    //     if ($count == ($procedureLength-1)) {                               // when the last trial has been loaded
    //         $Trials[$count] = cleanTrial($Trials[$count-1]);                    // return a copy of the last trial without any values in it
    //         $Trials[$count]['Procedure']['Item'] = 'ExperimentFinished';        // add this flag so we know when participants are done with all sessions
    //     }
    // }
    
    
    
    #### Establishing $_SESSION['Trials'] as the place where all experiment trials are stored
    // $Trials also contains trials for other sessions but experiment.php sends to done.php once a *NewSession* shows up
    // $_SESSION['Trials']     = $Trials;
    // $_SESSION['Position']   = 1;
    // $_SESSION['PostNumber'] = 0;
    
    
    
    #### Figuring out what the output filename will be
    // $outputFile = ComputeString($_SETTINGS->output_file_name) . $_SETTINGS->output_file_ext;
    $_SESSION['Output File'] = "{$_FILES->raw_output}/{$outputFile}";
    $_SESSION['Start Time']  = date('c');
    
    
    ###############################################################################
    #### END Preparing The Experiment #############################################
    ###############################################################################
    
    
    
   
    
    
    #### Send participant to next phase of experiment (demographics or instructions)
    if ($_SETTINGS->run_demographics == true) {
        $link = 'BasicInfo.php';
    } elseif ($_SETTINGS->run_instructions) {
        $link = 'instructions.php';
    } else {
        $link = 'experiment.php';
    }
    
    
    if ($_SETTINGS->stop_at_login == true) {             // if things are going wrong this info will help you figure out when the program broke
        // Readable($_SESSION['Condition'],    'Condition information');
        // Readable($stimuli,                  'Stimuli file in use ('   . $_FILES->stim_files.'/' . $_SESSION['Condition']['Stimuli']   . ')');
        // Readable($procedure,                'Procedure file in use (' . $_FILES->proc_files.'/' . $_SESSION['Condition']['Procedure'] . ')');
        // Readable($trialTypeColumns,         'Levels of trial types being used');
        // Readable($trialTypes,  'All info about trial types used in experiment');
        // Readable($_SESSION['Trials'],       '$_SESSION["Trials"] array');
        // // for checking that shuffling is working as planned
        // echo '<h1> Stimuli before/after</h1>';
        // echo '<div class="before">';
        //           display2darray($cleanStimuli);
        // echo '</div>';
        // echo '<div class="after">';
        //           display2darray($stimuli);
        // echo '</div>';
        
        // echo '<div class="sectionBreak">';
        // echo '<h1>Procedure before/after</h1>';
        // echo '</div>';
        // echo '<div class="before">';
        //           display2darray($cleanProcedure);
        // echo '</div>';
        // echo '<div class="after">';
        //           display2darray($procedure);
        // echo '</div>';

        // echo '<form action ="' . $link . '" method="get">'
        //        . '<button class="collectorButton">Press here to continue to experiment</button>'
        //    . '</form>';
    }
    else {
        echo '<form id="loadingForm" action="' . $link . '" method="get"> </form>';
    }
        
    require $_FILES->code . '/Footer.php';
?>

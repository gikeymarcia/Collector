<?php
/*  Collector
    A program for running experiments on the web
 */
    require 'initiateCollector.php';
    
    $_SESSION = array();                                    // reset session so it doesn't contain any information from a previous login attempt
    // $_SESSION['OutputDelimiter'] = $_CONFIG->delimiter; // hard-coded now
    $_SESSION['Debug'] = $_CONFIG->debug_mode;
    
    $_PATH->loadDefault('Current Data', $_CONFIG->experiment_name . '-Data');
    
    $title = 'Preparing the Experiment';
    require $_PATH->get('Header');
    
    
    #### Grabbing username and condition from $_GET
    // cleaning characters that wouldn't write to a filename
    $bad_username = filter_input(INPUT_GET, 'Username', FILTER_SANITIZE_EMAIL);
    $username = str_replace(array( '/', '\\', '?', '%', '*', ':', '|', '"', '<', '>' ), '', $bad_username );
    $selectedCondition = filter_input(INPUT_GET, 'Condition', FILTER_SANITIZE_STRING);
    
    
    
    #### Setting a unique ID for this login
    if (!isset($_SESSION['ID'])) {
        if (isset($_GET['ID'])) {                                           // if the ID is in the URL
            $_SESSION['ID'] = $_GET['ID'];                                      // use it as the unique ID
        } else {
            $_SESSION['ID'] = rand_string();                                // otherwise, make a new ID
        }
    }
    
    
    
    #### Check for debug mode
    if ((strlen($_CONFIG->debug_name) > 0)
        AND (substr($username, 0, strlen($_CONFIG->debug_name)) === $_CONFIG->debug_name)
    ) {
        // logged in as debug
        $_SESSION['Debug'] = true;
        $username = trim(substr($username, strlen($_CONFIG->debug_name)));
        if ($username === '') { $username = $_SESSION['ID']; }
    }
    if ($_SESSION['Debug'] === true) {
        // debug mode is definitely on, make sure data is sectioned off
        $_PATH->loadDefault('Current Data', $_PATH->getDefault('Current Data') . '/' . 'Debug');
    }
    
    
    
    #### Checking info about this username
    $_SESSION['Username'] = $username;                                      // set Username
    
    // is the username long enough (> 3 characters)
    if ((strlen($username) < 3)
        AND (!$_SESSION['Debug'])
    ) {
        echo '<h1> Error: Login username must be 3 characters or longer</h1>'
           . '<h2>Click <a href="' . $_PATH->get('index', 'url') . '">here</a> to enter a valid username</h2>';
        exit;
    }
    
    // is this user ineligible to participate in the experiment?
    if (($_CONFIG->check_elig == true)
        AND ($_CONFIG->mTurk_mode == true)
    ) {
        include $_PATH->get('Check');   // waiter, can I . . . ?
    }
    
    // Has this user already completed session 1?  If so, determine whether they have another session to complete or if they are done
    $_PATH->loadDefault('json', $_SESSION['Username'] . '.json');
    $sessionFilename = FileExists($_PATH->get('json'));
    if ($sessionFilename == true) {              // this file will only exist if this username has completed a session successfully
        $pastSession   = fopen($sessionFilename, 'r');
        $loadedSession = fread($pastSession, filesize($sessionFilename));
        $sessionData   = json_decode($loadedSession, true);
        // Load old session info
        $_SESSION = NULL;                       // get rid of current session in memory
        $_SESSION = $sessionData;               // load old session data into current $_SESSION
        // check if it is time for the next session
        $ExpOverFlag = $_SESSION['Trials'][ ($_SESSION['Position']) ]['Procedure']['Item'];
        if ($ExpOverFlag != 'ExperimentFinished') {                                                         // if this user hasn't done all sessions
            $wait = $_SESSION['Trials'][ ($_SESSION['Position']-1) ]['Procedure']['Max Time'];                  // check 'Max Time' column of *newSession* line
            $wait = durationInSeconds($wait);                                                                   // how many seconds was I supposed to wait until the next session?
            $sinceFinish = time() - $_SESSION['LastFinish'];
            if ($sinceFinish < $wait) {
                $timeRemaining = durationFormatted($wait - $sinceFinish);
                echo '<h1> Sorry, you must wait before you can complete this part of the experiment'
                     . '<br> Please return in ' . $timeRemaining . ' </h1>';
                exit;
            }
        } else {                                                                                            // if the user is done with all sessions then send back to done.php
            $_SESSION['alreadyDone'] = true;
            echo '<meta http-equiv="refresh" content="1; url=' . $_PATH->get('Done', 'url') . '">';
            exit;
        }
        // Overwrite values that need to be updated
        $outputFile = 
            'Output_Session' . 
            $_SESION['Session'] . '_' . 
            $_SESSION['Username'] . '_' . 
            $_SESSION['ID'] .
            '.csv';
        $_PATH->loadDefault('Output', $outputFile);
        $_SESSION['Start Time']  = date('c');
        
        #### Record info about the person starting the experiment to the status start file
        // information about the user logging in
        $userAgent = getUserAgentInfo();
        $UserData = array(
            'Username'              => $_SESSION['Username'],
            'ID'                    => $_SESSION['ID'],
            'Date'                  => $_SESSION['Start Time'],
            'Session'               => $_SESSION['Session'] ,
            'Condition_Number'      => $_SESSION['Condition']['Number'],
            'Condition_Description' => $_SESSION['Condition']['Condition Description'],
            'Output_File'           => $_PATH->getDefault('Output'),
            'Stimuli_File'          => $_SESSION['Condition']['Stimuli'],
            'Procedure_File'        => $_SESSION['Condition']['Procedure'],
            'Browser'               => $userAgent->Parent,
            'DeviceType'            => $userAgent->Device_Type,
            'OS'                    => $userAgent->Platform,
            'IP'                    => $_SERVER["REMOTE_ADDR"],
        );
        arrayToLine($UserData, $_PATH->get('Status Begin Data'));
        ###########################################################################
        
        echo '<meta http-equiv="refresh" content="1; url=' . $_PATH->get('Experiment Page', 'url') . '">';
        exit;               // do not run any of the other code, send to experiment.php
        
    } else {
        $_SESSION['Session'] = 1;               // if they have no .json file then they are in session 1
    }
    
    
    
    ##### Error Checking Code ####
    $errors = array('Count' => 0, 'Details' => array());
    if (file_exists($_PATH->get('Conditions')) == false) {      // does conditions exist? (error checking)
        $errors['Count']++;
        $errors['Details'][] = "No '{$_PATH->conditions}' found.";
    }
    // does the condition file have the required headers?
    $Conditions = GetFromFile($_PATH->get('Conditions'),  false);   // Loading conditions info
    $errors = keyCheck($Conditions, 'Number'    , $errors, $_PATH->get('Conditions'));
    $errors = keyCheck($Conditions, 'Stimuli'   , $errors, $_PATH->get('Conditions'));
    $errors = keyCheck($Conditions, 'Procedure' , $errors, $_PATH->get('Conditions'));
    
    
    
    #### Code to automatically choose condition assignment
    $_SESSION['Condition'] = array();
    $Conditions = GetFromFile($_PATH->get('Conditions'),  false);   // Loading conditions info
    $logFile    = $_PATH->get('Counter', 'relative', $_CONFIG->login_counter_file);
    if ($selectedCondition == 'Auto') {
        if (!is_dir($_PATH->get('Counter Dir'))) {                                  // create the 'Counter' folder if it doesn't exist
            mkdir($_PATH->get('Counter Dir'),  0777,  true);
        }
        
        if (file_exists($logFile)) {                                            // Read counter file & save value
            $fileHandle    = fopen($logFile, "r");
            $loginCount    = fgets($fileHandle);
            fclose($fileHandle);
        } else { $loginCount = 0; }
        
        $condCount = count($Conditions);
        while ($_SESSION['Condition'] === array()) {
            $conditionIndex = $loginCount % $condCount;
            if ($Conditions[$conditionIndex]['Condition Description'][0] === '#') {
                ++$loginCount;
            } else {
                $_SESSION['Condition'] = $Conditions[$conditionIndex];
            }
        }
        
        // write old value + 1 to login counter
        $fileHandle    = fopen($logFile, "w");
        fputs($fileHandle, $loginCount+1);
        fclose($fileHandle);
        
        $conditionIndex = ($loginCount % count($Conditions))+1;                // cycles through current condition assignment based on login counter
    }
    else {
        $conditionIndex = $selectedCondition;
        if (isset($Conditions[$conditionIndex])) {
            $_SESSION['Condition'] = $Conditions[$conditionIndex];
        }
    }
    
    
    $_PATH->loadDefault('Stimuli',   $_SESSION['Condition']['Stimuli']);
    $_PATH->loadDefault('Procedure', $_SESSION['Condition']['Procedure']);
    
    
    
    ###########################################################################
    ##### Error Checking Code #################################################
    ###########################################################################
    // did we fail to find the condition information?
    if ($_SESSION['Condition'] === array()) {
        $errors['Count']++;
        $errors['Details'][] = 'Could not find the selected condition index ' . ($conditionIndex+1) . ' in ' . $_PATH->get('Conditions');
    }
    
    // calculating path to Stimuli and Procedure file
    $stimPath = $_PATH->get('Stimuli');
    $procPath = $_PATH->get('Procedure');
    
    // does this condition point to a valid stimuli file?
    if (file_exists($stimPath) == false) {
        $errors['Count']++;
        $errors['Details'][] = 'No stimuli file found at "' . $_PATH->get('Stimuli', 'root') . '"';
    }
    // checking required columns from Stimuli file
    $temp = GetFromFile($stimPath, false);
    $errors = keyCheck($temp, 'Cue'    ,   $errors, $_SESSION['Condition']['Stimuli']);
    $errors = keyCheck($temp, 'Answer' ,   $errors, $_SESSION['Condition']['Stimuli']);
    
    // does this condition point to a valid procedure file?
    if (file_exists($procPath) == false) {
        $errors['Count']++;
        $errors['Details'][] = 'No procedure file found at "' . $_PATH->get('Procedure', 'root') . '"';
    }
    // checking required columns from Procedure file
    $temp = GetFromFile($procPath, false);
    $errors = keyCheck($temp, 'Item'       ,   $errors, $_SESSION['Condition']['Procedure']);
    $errors = keyCheck($temp, 'Trial Type' ,   $errors, $_SESSION['Condition']['Procedure']);
    $errors = keyCheck($temp, 'Max Time'     ,   $errors, $_SESSION['Condition']['Procedure']);
    unset($temp);           // clear $temp
    
    
    
    #### Find all of the columns that hold trial types (including 'Post# Trial Type's)
    $trialTypeColumns = array();                                                                // Each position will have the column name of a trial type column
    $proc = GetFromFile($_PATH->get('Procedure'), false); // load procedure file without padding
    foreach ($proc[0] as $col => $val) {                                    // check all column names
        if (substr($col, -10) == 'Trial Type') {                           // if ends with 'trial type'
            if ($col == 'Trial Type') {                                        // and is trial type
                $trialTypeColumns[0] = $col;                                            // save it
            } elseif (substr($col, 0, 5) == 'Post ') {                          // if it starts with 'post'
                $cleanCol = trim(substr($col, 5, -10));                            // drop the 'post' and 'trial type'
                if (is_numeric($cleanCol)) {                                            // if what remains is a # (e.g., '15')
                    $correctTitle = 'Post' . ' ' . (int)$cleanCol . ' ' . 'Trial Type';
                    if ($col !== $correctTitle) {
                        $errors['Count']++;
                        $errors['Details'][] = 'Column "' . $col . '" in ' . $_SESSION['Condition']['Procedure']
                                             . ' needs to have exactly one space before and after the number (e.g., ' . $correctTitle . ').';
                    } else {
                        $trialTypeColumns[$cleanCol] = $col;                                    // set $trialTypeColumns[15] to this column name
                    }
                } else {                                                                // if not, it should have been a #
                    $errors['Count']++;
                    $errors['Details'][] = 'Column "' . $column . '" in ' . $_SESSION['Condition']['Procedure']
                                         . ' needs to be numbered (e.g., "Post <b>1</b> Trial Type").';
                }
            }
        }
    }
    
    
    
    #### checking that all Post levels have the needed columns
    $needed = array('Max Time');                                                          // if we need more cols in the future they can be added here
    foreach ($needed as $need) {
        foreach ($trialTypeColumns as $number => $colName) {                                // check all trial type levels we found
            if ($number == 0) {
                continue;                                                                   // we already checked the non-post level elsewhere in the code
            }
            if (!isset($proc[0]['Post' . ' '  . $number . ' ' . $need])) {                  // if the associated needed row doesn't exist (e.g., 'Post 1 Max Time')
                $errors['Count']++;
                $errors['Details'][] = 'Post level ' . $number . ' is missing a "' . $need . '" column (i.e., add a column called 
                                            "Post ' . $number . ' ' . $need . '" to the file "' . $_SESSION['Condition']['Procedure'] . '" to fix this error).
                                            <br>
                                            <em> If you are not using Post level ' . $number . ' you can safely delete the column 
                                            "Post ' . $number . ' Trial Type" and this will also solve this error.</em>';
            }
        }
    }
    unset($proc);
    
    
    
    #### Checking stimuli files for correct image/media path and filenames
    if (($_CONFIG->check_all_files == true)
        OR ($_CONFIG->check_current_files == true)
    ) {
        $stimuliFiles = array();
        if ($_CONFIG->check_all_files == true) {
            $stimPath = $_PATH->get('Stimuli Dir').'/';
            foreach ($Conditions as $row => $cells) {
                $stimuliFiles[] = $stimPath . $Conditions[$row]['Stimuli'];
            }
        } else {
            $stimuliFiles[] = $_PATH->get('Stimuli');
        }
        
        foreach ($stimuliFiles as $fileName) {
            $temp = GetFromFile($fileName);
            foreach ($temp as $i => $row) {
                if ($i < 2) { continue; }                   // skip padding rows
                if (show($row['Cue']) !== $row['Cue']) {
                    // show() detects a file extension like .png, and will use FileExists to check that it exists
                    // but it will always return a string, for cases where you are showing regular text
                    // using FileExists, we can see if a cue detected as an image by show() is a file that actually exists
                    if (FileExists($_PATH->get('Experiment') . '/' . $row['Cue']) === false ) {
                        $errors['Count']++;
                        $errors['Details'][] = 'Image or audio file "' . $_PATH->get('Experiment') . '/' . $row['Cue'] . '" not found for row '
                                             . $i . ' in Stimuli File "' . basename($fileName) . '".';
                    }
                }
            }
        }
    }
    
    
    
    #### Check that we can find files for all trials in use (also finds custom scoring files)
    $procedure  = GetFromFile($_PATH->get('Procedure'));
    $trialTypes = array();                                                  // we will make a list of all found trial types, and what level they will be used at
    $notTrials  = array('off'   => true,                                    // if the 'Trial Type' value is one of these then it isn't a trial
                        'no'    => true,
                        ''      => true,
                        'n/a'   => true  );
    foreach ($procedure as $i => $row) {                                    // go through all rows of procedure
        if ($row === 0)                                 { continue; }       // skip padding
        if (strtolower($row['Item']) === '*newsession*')   { continue; }    // skip multisession markers
        foreach ($trialTypeColumns as $postNumber => $column) {             // check all trial levels
            $thisTrialType = strtolower($row[$column]);                     // which trial is being used at this level (e.g, Study, Test, etc.)
            if (isset($notTrials[$thisTrialType])) {                        // skip turned off trials (off, no, n/a, '')
                continue;
            }
            $trialFiles = getTrialTypeFiles($thisTrialType);
            $trialTypes[$thisTrialType]['files'] = $trialFiles;
            $trialTypes[$thisTrialType]['levels'][$postNumber] = true;      // make note what levels each trial type are used at (e.g., Study is a regular AND a Post 1 trial)
            if ($trialFiles === false) {
                $procName = pathinfo($_PATH->get('Procedure'), PATHINFO_FILENAME);
                $errors['Count']++;
                $errors['Details'][] = 
                    'The trial type '. $row[$column] .' for row '. $i .' in '
                  . 'the procedure file '. $procName .' has no folder in '
                  . 'either the "'. $_PATH->get('Custom Trial Types', 'root') .'" folder or '
                  . 'the "'. $_PATH->get('Trial Types', 'root') .'" folder.';
            }
        }
    }
    
    unset($procedure);
    ##### END Error Checking Code #######################################
    
    
    
    ###############################################################################
    #### Preparing The Experiment #################################################
    ###############################################################################
    // Setting up all the ['Response'] keys that will be needed during the experiment
    // Also checks scoring files if that trial type lists some required columns
    $proc = GetFromFile($_PATH->get('Procedure'), false); // load procedure file without padding
    $allColumnsNeeded = array();
    $allColumnsOutput = array();
    foreach ($trialTypes as $type => $info) {
        if (isset($info['files']['helper'])) {
            $neededColumns = array();
            $outputColumns = array();
            include $info['files']['helper'];
            $trialTypes[$type]['neededColumns'] = $neededColumns;
            $trialTypes[$type]['outputColumns'] = $outputColumns;
            if (isset($info['levels'][0])) {
                $allColumnsNeeded += array_flip($neededColumns);
                $allColumnsOutput += array_flip($outputColumns);
            }
        }
    }
    foreach ($trialTypes as $type => $info) {
        foreach ($info['levels'] as $postNumber => $null) {
            if ($postNumber === 0) continue;
            foreach ($info['neededColumns'] as $column) {
                $column = 'Post ' . $postNumber . ' ' . $column;
                $allColumnsNeeded[$column] = true;
            }
            foreach ($info['outputColumns'] as $column) {
                $column = 'post'  . $postNumber . '_' . $column;
                $allColumnsOutput[$column] = true;
            }
        }
    }
    foreach ($allColumnsOutput as &$column) {
        $column = null;
    }
    unset($column);
    
    
    foreach (array_keys($allColumnsNeeded) as $column) {
        $errors = keyCheck($proc, $column, $errors, $_SESSION['Condition']['Procedure']);
    }
    
    include $_PATH->get('Shuffle Functions');
    #### Create $_SESSION['Trials'] 
    #### Load all Stimuli and Procedure info for this participant's condition then combine to create the experiment
    // load stimuli for this condition then block shuffle
    $cleanStimuli = GetFromFile($_PATH->get('Stimuli'));
    $stimuli = multiLevelShuffle($cleanStimuli);
    $stimuli = shuffle2dArray($stimuli, $_CONFIG->stop_at_login);
    $_SESSION['Stimuli'] = $stimuli;
    
    // load and block shuffle procedure for this condition
    $cleanProcedure = GetFromFile($_PATH->get('Procedure'));
    
    $addColumns = array('Text');
    foreach ($addColumns as $add) {
        foreach ($trialTypeColumns as $number => $colName) {                                // check all trial type levels we found
            if ($number == 0) {
                $prefix = '';
            } else {
                $prefix = 'Post' . ' ' . $number . ' ';
            }
            $column = $prefix . $add;
            addColumn($cleanProcedure, $column);                // this will only add columns if they don't already exist; nothing is overwritten
        }
    }
    
    $procedure = multiLevelShuffle($cleanProcedure);
    $procedure = shuffle2dArray($procedure, $_CONFIG->stop_at_login);
    
    $_SESSION['Procedure'] = $procedure;
    
    // Load entire experiment into $Trials[1-X] where X is the number of trials
    $Trials = array(0=> 0);
    $procedureLength = count($procedure);
    for ($count=2; $count<$procedureLength; $count++) {
        // $Trials[$count-1] = makeTrial($procedure[$count]['Item']);
        $items = rangeToArray($procedure[$count]['Item']);
        $stim = array();
        foreach ($items as $item) {
            if (isset($stimuli[$item]) and is_array($stimuli[$item])) {
                foreach ($stimuli[$item] as $column => $value) {
                    $stim[$column][] = $value;
                }
            }
        }
        if ($stim === array()) {
            foreach ($stimuli[2] as $column => $value) {
                $stim[$column][] = '';
            }
        }
        foreach ($stim as $column => $valueArray) {
            $Trials[$count-1]['Stimuli'][$column] = implode('|', $valueArray);
        }
        // $Trials[$count-1]['Stimuli']    = $stimuli[ ($procedure[$count]['Item']) ];         // adding 'Stimuli', as an array, to each position of $Trials
        $Trials[$count-1]['Procedure']  = $procedure[$count];                               // adding 'Procedure', as an array, to each position of $Trials
        $Trials[$count-1]['Response']   = $allColumnsOutput;
        
        // on trials with no Stimuli info (e.g., freerecall) keep the same Stimuli structure but fill with 'n/a' values
        // I need a consistent Trial structure to do all of the automatic output creation I do later on
        if ($Trials[$count-1]['Stimuli'] == NULL) {
            $stim       =& $Trials[$count-1]['Stimuli'];
            $stim       =  $stimuli[2];
            $stimKey    =  array_keys($stim);
            $empty      =  array_fill_keys($stimKey, 'n/a');
            $Trials[$count-1]['Stimuli'] = $empty;
        }
        if ($count == ($procedureLength-1)) {                               // when the last trial has been loaded
            $Trials[$count] = cleanTrial($Trials[$count-1]);                    // return a copy of the last trial without any values in it
            $Trials[$count]['Procedure']['Item'] = 'ExperimentFinished';        // add this flag so we know when participants are done with all sessions
        }
    }
    
    
    
    #### Establishing $_SESSION['Trials'] as the place where all experiment trials are stored
    // $Trials also contains trials for other sessions but experiment.php sends to done.php once a *NewSession* shows up
    $_SESSION['Trials']     = $Trials;
    $_SESSION['Position']   = 1;
    $_SESSION['PostNumber'] = 0;
    
    
    
    #### Figuring out what the output filename will be
    $outputFile = 
        'Output_Session' . 
        $_SESSION['Session'] . '_' . 
        $_SESSION['Username'] . '_' . 
        $_SESSION['ID'] .
        '.csv';
    $_PATH->loadDefault('Output', $outputFile);
    $_SESSION['Start Time']  = date('c');
    
    
    ###############################################################################
    #### END Preparing The Experiment #############################################
    ###############################################################################
    
    
    
    #### Output errors & Stop progression
    if ($errors['Count'] > 0) {                                                     // if there is an error
        ?>
            <div id="ErrorCodes">
                <b> <?php echo $errors['Count']; ?> errors found in your code </b>
                <ol>
                    <?php   foreach ($errors['Details'] as $errorCode) {
                                echo '<li>' . $errorCode . '</li>';
                            }                                                   ?>
                </ol>
                <?php
                     if ($_CONFIG->stop_for_errors == true) {
                         echo '<br/> <h2>The program will not run until you have addressed the above errors</h2>';
                         exit;
                     }
                ?>
            </div>
        <?php
    }
    
    
    
    #### Record info about the person starting the experiment to the status start file
    // information about the user logging in
    $userAgent = getUserAgentInfo();
    $UserData = array(
        'Username'              => $_SESSION['Username'],
        'ID'                    => $_SESSION['ID'],
        'Date'                  => $_SESSION['Start Time'],
        'Session'               => $_SESSION['Session'] ,
        'Condition_Number'      => $_SESSION['Condition']['Number'],
        'Condition_Description' => $_SESSION['Condition']['Condition Description'],
        'Output_File'           => $_PATH->getDefault('Output'),
        'Stimuli_File'          => $_SESSION['Condition']['Stimuli'],
        'Procedure_File'        => $_SESSION['Condition']['Procedure'],
        'Browser'               => $userAgent->Parent,
        'DeviceType'            => $userAgent->Device_Type,
        'OS'                    => $userAgent->Platform,
        'IP'                    => $_SERVER["REMOTE_ADDR"],
    );
    arrayToLine($UserData, $_PATH->get('Status Begin Data'));
    ###########################################################################
    
    
    
    
    
    #### Send participant to next phase of experiment (demographics or instructions)
    if ($_CONFIG->run_demographics == true) {
        $link = $_PATH->get('Basic Info');
    } elseif ($_CONFIG->run_instructions) {
        $link = $_PATH->get('Instructions Page');
    } else {
        $link = $_PATH->get('Experiment Page');
    }
    
    
    if ($_CONFIG->stop_at_login == true) {             // if things are going wrong this info will help you figure out when the program broke
        Readable($_SESSION['Condition'],    'Condition information');
        Readable($stimuli,                  'Stimuli file in use ('   . $_PATH->get('Stimuli',   'root') . ')');
        Readable($procedure,                'Procedure file in use (' . $_PATH->get('Procedure', 'root') . ')');
        Readable($trialTypeColumns,         'Levels of trial types being used');
        Readable($trialTypes,  'All info about trial types used in experiment');
        Readable($_SESSION['Trials'],       '$_SESSION["Trials"] array');
        // for checking that shuffling is working as planned
        echo '<h1> Stimuli before/after</h1>';
        echo '<div class="before">';
                  display2darray($cleanStimuli);
        echo '</div>';
        echo '<div class="after">';
                  display2darray($stimuli);
        echo '</div>';
        
        echo '<div class="sectionBreak">';
        echo '<h1>Procedure before/after</h1>';
        echo '</div>';
        echo '<div class="before">';
                  display2darray($cleanProcedure);
        echo '</div>';
        echo '<div class="after">';
                  display2darray($procedure);
        echo '</div>';

        echo '<form action ="' . $link . '" method="get">'
               . '<button class="collectorButton">Press here to continue to experiment</button>'
           . '</form>';
    }
    else {
        echo '<form id="loadingForm" action="' . $link . '" method="get"> </form>';
    }
        
    require $_PATH->get('Footer');
?>

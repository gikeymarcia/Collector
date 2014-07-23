<?php
/*  Collector
    A program for running experiments on the web
 */
    require 'initiateCollector.php';

    $_SESSION = array();                                    // reset session so it doesn't contain any information from a previous login attempt
    $_SESSION['OutputDelimiter'] = $delimiter;
    $_SESSION['Debug'] = $debugMode;
    
    $title = 'Preparing the Experiment';
    require $_codeF . 'Header.php';
        
    echo '<h1> Please wait while we load the experiment... </h1>';
    

    ##### Error Checking Code ####
    $found  = FALSE;                                                        // will use this later to determine when loops fail
    $errors = array('Count'   => 0,
                    'Details' => array()  );
    if (file_exists($up . $expFiles . $conditionsFileName) == FALSE) {      // does conditions exist? (error checking)
        $errors['Count']++;
        $errors['Details'][] = 'No "' . $conditionsFileName . '" found';
    }
    
    
    #### Grabbing submitted info
    $username = preg_replace('/[^A-Za-z0-9]/', '', $_GET['Username']);      // grab Username from URL
    $selectedCondition = trim($_GET['Condition']);                          // grab Condition from URL
    if (isset($_GET['Session'])) {
        $_SESSION['Session'] = trim($_GET['Session']);                      // grab session# from URL
    } else {
        $_SESSION['Session'] = 1;                                           // if session is not set then set to 1
    }
    if ($_SESSION['Session'] != 1) {
        $doDemographics = FALSE;                                            // skip demographics for all but session1
    }
    
    
    #### Making decision about login info
    // determine user's unique ID
    if (!isset($_SESSION['ID'])) {
        if (isset($_GET['ID'])) {
            $_SESSION['ID'] = $_GET['ID'];
        } else {
            $_SESSION['ID'] = rand_string();
        }
    }
    // did we login as debug?
    if ((strlen($debugName) > 0)
        AND (substr($username, 0, strlen($debugName)) === $debugName)
    ) {
        $_SESSION['Debug'] = TRUE;
        $username = trim(substr($username, strlen($debugName)));
        if ($username === '') { $username = $_SESSION['ID']; }
    }
    // set Username
    $_SESSION['Username'] = $username;
    
    
    if ($_SESSION['Debug'] === TRUE) {
        $dataSubFolder = $debugF;
        $path = $up . $dataF . $dataSubFolder . $extraDataF;
        $statusBeginCompleteFileName = $path . $statusBeginFileName . $outExt;
    } else {
        error_reporting(0);
    }
     
    
    if ($checkElig == TRUE
        AND $mTurkMode == TRUE
    ) {
        include    'check.php';
    }
    
    #### Checking username is 3 characters or longer
    if ((strlen($username) < 3)
        AND (!$_SESSION['Debug'])
    ) {
        echo '<h1> Error: Login username must be 3 characters or longer</h1>
                <h2>Click <a href="' . $up . 'index.php">here</a> to enter a valid username</h2>';
        exit;
    }
    
    
    #### Code to automatically choose condition assignment
    $Conditions    = GetFromFile($up . $expFiles . $conditionsFileName,  FALSE);   // Loading conditions info
    $logFile    = $up . $dataF . $countF . $loginCounterName;
    if ($selectedCondition == 'Auto') {
        if (!is_dir($up . $dataF . $countF)) {
            mkdir($up . $dataF . $countF,  0777,  TRUE);
        }
        
        if (file_exists($logFile)) {                                            // Read counter file & save value
            $fileHandle    = fopen($logFile, "r");
            $loginCount    = fgets($fileHandle);
            fclose($fileHandle);
        } else { $loginCount = 1; }
        // write old value + 1 to login counter
        $fileHandle    = fopen($logFile, "w");
        fputs($fileHandle, $loginCount+1);
        fclose($fileHandle);
        
        $conditionNumber = ($loginCount % count($Conditions))+1;                // cycles through current condition assignment based on login counter
    }
    else{
        $conditionNumber = $selectedCondition;                                  // if condition is manually choosen then honor choice
    }
    
    
    #### loads condition info into $_Session['Condition']
    foreach ($Conditions as $aCond) {
        if ($aCond['Number'] == $conditionNumber) {
            $_SESSION['Condition'] = $aCond;
            $found = TRUE;
            break;
        }
    }
    // calculating path to Stimuli and Procedure file
    $stimPath = $up . $expFiles . $stimF . $_SESSION['Condition']['Stimuli'];
    $procPath = $up . $expFiles . $procF . $_SESSION['Condition']['Procedure'];
    
    
    ##### Error Checking Code ####
    // did we fail to find the condition information?
    if ($found == FALSE) {
        $errors['Count']++;
        $errors['Details'][] = 'Could not find the selected condition # ' . $conditionNumber . ' in ' . $conditionsFileName;
    }
    // does the condition file have the required headers?
    $errors = keyCheck($Conditions, 'Number'    , $errors, $conditionsFileName);
    $errors = keyCheck($Conditions, 'Stimuli'   , $errors, $conditionsFileName);
    $errors = keyCheck($Conditions, 'Procedure' , $errors, $conditionsFileName);
    // does this condition point to a valid stimuli file?
    if (file_exists($stimPath) == FALSE) {
        $errors['Count']++;
        $errors['Details'][] = 'No stimuli file found at "' . $stimF . $_SESSION['Condition']['Stimuli'] . '"';
    }
    // does this condition point to a valid procedure file?
    if (file_exists($procPath) == FALSE) {
        $errors['Count']++;
        $errors['Details'][] = 'No procedure file found at "' . $procF . $_SESSION['Condition']['Procedure'] . '"';
    }
    // checking required columns from Stimuli file
    $temp = GetFromFile($stimPath, FALSE);
    $errors = keyCheck($temp, 'Cue'    ,   $errors, $_SESSION['Condition']['Stimuli']);
    $errors = keyCheck($temp, 'Answer' ,   $errors, $_SESSION['Condition']['Stimuli']);
    $errors = keyCheck($temp, 'Shuffle',   $errors, $_SESSION['Condition']['Stimuli']);
    // checking required columns from Procedure file
    $temp = GetFromFile($procPath, FALSE);
    $errors = keyCheck($temp, 'Item'       ,   $errors, $_SESSION['Condition']['Procedure']);
    $errors = keyCheck($temp, 'Trial Type' ,   $errors, $_SESSION['Condition']['Procedure']);
    $errors = keyCheck($temp, 'Timing'     ,   $errors, $_SESSION['Condition']['Procedure']);
    $errors = keyCheck($temp, 'Shuffle'    ,   $errors, $_SESSION['Condition']['Procedure']);
    unset($temp);           // clear $temp
    
    
    #### Find all of the columns that hold trial types (including 'Post# Trial Type's)
    $trialTypeColumns = array();                                                                // Each position will have the column name of a trial type column
    $proc = GetFromFile($up . $expFiles . $procF . $_SESSION['Condition']['Procedure'], FALSE); // load procedure file without padding
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
    $needed = array('Timing');                                                          // if we need more cols in the future they can be added here
    foreach ($needed as $need) {
        foreach ($trialTypeColumns as $number => $colName) {                                // check all trial type levels we found
            if ($number == 0) {
                continue;                                                                   // we already checked the non-post level elsewhere in the code
            }
            if (!isset($proc[0]['Post' . ' '  . $number . ' ' . $need])) {                  // if the associated needed row doesn't exist (e.g., 'Post 1 Timing')
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
    if (($checkAllFiles == TRUE)
        OR ($checkCurrentFiles == TRUE)
    ) {
        $stimuliFiles = array();
        if ($checkAllFiles == TRUE) {
            $stimPath = $up . $expFiles . $stimF;
            $scanStimFiles = scandir($stimPath);
            foreach ($scanStimFiles as $fileName) {
                if (is_file($stimPath . $fileName)) {
                    $stimuliFiles[] = $stimPath . $fileName;
                }
            }
        } else {
            $stimuliFiles[] = $up . $expFiles . $stimF . $_SESSION['Condition']['Stimuli'];
        }
        
        foreach ($stimuliFiles as $fileName) {
            $temp = GetFromFile($fileName);
            foreach ($temp as $i => $row) {
                if ($i < 2) { continue; }                   // skip padding rows
                if (show($row['Cue']) !== $row['Cue']) {
                    // show() detects a file extension like .png, and will use FileExists to check that it exists
                    // but it will always return a string, for cases where you are showing regular text
                    // using FileExists, we can see if a cue detected as an image by show() is a file that actually exists
                    if (FileExists('../Experiment/' . $row['Cue']) === FALSE ) {
                        $errors['Count']++;
                        $errors['Details'][] = 'Image or audio file "../Experiment/' . $row['Cue'] . '" not found for row '
                                             . $i . ' in Stimuli File "' . basename($fileName) . '".';
                    }
                }
            }
        }
    }
    
    #### Check that we can find files for all trials in use (also finds custom scoring files)
    $procedure  = GetFromFile($up . $expFiles . $procF . $_SESSION['Condition']['Procedure']);
    $trialTypes = array();
    $notTrials  = array('off'   => TRUE,                                    // if the 'Trial Type' value is one of these then it isn't a trial
                        'no'    => TRUE,
                        ''      => TRUE,
                        'n/a'   => TRUE  );
    foreach ($procedure as $i => $row) {                                    // go through all rows of procedure
        if ($row === 0)                     { continue; }                   // skip padding
        if ($row['Item'] === '*newfile*')   { continue; }                   // skip multisession markers
        foreach ($trialTypeColumns as $postNumber => $column) {             // check all trial levels
            $thisTrialType = strtolower($row[$column]);                         // which trial is being used at this level (e.g, Study, Test, etc.)
            if (isset($notTrials[$thisTrialType])) {                        // skip turned off trials (off, no, n/a, '')
                continue;
            }
            $trialTypes[$thisTrialType]['levels'][$postNumber] = NULL;      // make note what levels each trial type are used at (e.g., Study is a regular AND a Post 1 trial)
            if (isset($trialTypes[$thisTrialType]['trial'])) {              // no need to find the trial type file twice
                continue;
            }
            $fileName = fileExists($trialF . $thisTrialType . '.php' );     // Find the folder OR .php file for this trial type
            // give an error if we couldn't find the trial file (or a folder for the trial)
            if ($fileName === FALSE ) {                                     
                $procName = pathinfo($up . $expFiles . $procF . $_SESSION['Condition']['Procedure'], PATHINFO_FILENAME);
                $errors['Count']++;
                $errors['Details'][] = 'The trial type ' . $row[$column] . ' for row ' . $i . ' in the procedure file '
                                     . $procName . ' has no file or folder in the "' . $codeF . $trialF . '" folder.';
            }
            // if this trial is a folder
            if (is_dir($fileName)) {
                $display = fileExists($fileName . '/trial.php', TRUE, FALSE);       // trial file for directory trials
                // give an error if no trial.php file is in the folder
                if ($display === FALSE) {
                    $procName = pathinfo($up . $expFiles . $procF . $_SESSION['Condition']['Procedure'], PATHINFO_FILENAME);
                    $errors['Count']++;
                    $errors['Details'][] = 'The trial type ' . $row[$column] . ' for row ' . $i . ' in the procedure file '
                                         . $procName . ' has a folder in the "' . $codeF . $trialF . '" folder, '
                                         . 'but there is no "trial.php" file within that folder.';
                } else {
                    // find custom scoring file in the folder
                    $scoringFile = fileExists($fileName . '/scoring.php', FALSE, FALSE);
                    if ($scoringFile === FALSE) {
                        $scoringFile = $scoring;                                            // use default scoring if we can't find custom scoring
                    }
                    $trialTypes[$thisTrialType]['trial']   = $display;
                    $trialTypes[$thisTrialType]['scoring'] = $scoringFile;
                }
            } else {
                // if this trial is not a folder then use the .php file for display and default scoring scheme
                $trialTypes[$thisTrialType]['trial']   = $fileName;
                $trialTypes[$thisTrialType]['scoring'] = $scoring;
            }
        }
    }
    unset($procedure);
    #### End of error checking #####################################
    
    
    
    
    #### Setting up all the ['Response'] keys that will be needed during the experiment
    $findingKeys   = TRUE;
    $allKeysNeeded = array();
    $scoringFiles  = array();
    foreach ($trialTypes as $thisTrialType) {                       // compile an array of the scoring files to check for keys
        $scoringFiles[ $thisTrialType['scoring'] ] = NULL;
    }
    foreach ($scoringFiles as $fileName => &$keys) {
        $keys = include $fileName;                                  // grab keys from scoring file when $findingKeys == TRUE
        if ($keys == 1) {
            $keys = array();
        } elseif(!is_array($keys)) {
            $keys = array($keys);
        }
        $keys = array_flip($keys);      // if array('RT', 'RTkey') is returned it will be saved as array('RT' => 0, 'RTkey' => 1)
    }
    unset($keys);
    
    $lev0keys = array();
    $postKeys = array();
    foreach ($trialTypes as $thisTrialType) {                               // for all trial types in use
        foreach ($thisTrialType['levels'] as $lvl => $null) {                   // look at all levels each is used at
            if ($lvl === 0) {                                                       // add needed keys for level 0, non-post, use
                $lev0keys += $scoringFiles[ $thisTrialType['scoring'] ];
            } else {                                                                // add needed keys for each post level
                $postKeys += AddPrefixToArray('post' . $lvl . '_', $scoringFiles[ $thisTrialType['scoring'] ]);
            }
        }
    }
    // Group Keys together by level (Trail, Post 1, Post 2, etc.)
    ksort($lev0keys);
    ksort($postKeys);
    $allKeysNeeded += $lev0keys;
    $allKeysNeeded += $postKeys;
    // set all key values == NULL (e.g., array('RT' => NULL, 'RTkey' => NULL, etc.))
    foreach( $allKeysNeeded as &$key ) {
        $key = NULL;
    }
    unset($key);
    
    
    
    #### Load all Stimuli and Procedure info for this participant's condition then combining to create the experiment
    if ($_SESSION['Session'] == 1) {
        $_SESSION['Trial Types'] = $trialTypes;                     // store array that contains locations of display and scoring files for each trial
        
        // load stimuli for this condition then block shuffle
        $stimuli = GetFromFile($up . $expFiles . $stimF . $_SESSION['Condition']['Stimuli']);
        $stimuli = BlockShuffle($stimuli, 'Shuffle');
        $_SESSION['Stimuli'] = $stimuli;
        
        // load and block shuffle procedure for this condition
        $procedure = GetFromFile($up . $expFiles . $procF . $_SESSION['Condition']['Procedure']);
        $procedure = BlockShuffle($procedure, 'Shuffle');
        $_SESSION['Procedure'] = $procedure;
        
        // Load entire experiment into $Trials[1-X] where X is the number of trials
        $Trials = array(0=> 0);
        $procedureLength = count($procedure);
        for ($count=2; $count<$procedureLength; $count++) {
            // $Trials[$count-1] = makeTrial($procedure[$count]['Item']);
            $Trials[$count-1]['Stimuli']    = $stimuli[ ($procedure[$count]['Item']) ];         // adding 'Stimuli', as an array, to each position of $Trials
            $Trials[$count-1]['Procedure']  = $procedure[$count];                               // adding 'Procedure', as an array, to each position of $Trials
            $Trials[$count-1]['Response']   = $allKeysNeeded;
            
            // on trials with no Stimuli info (e.g., freerecall) keep the same Stimuli structure but fill with 'n/a' values
            // I need a consistent Trial structure to do all of the automatic output creation I do later on
            if ($Trials[$count-1]['Stimuli'] == NULL) {
                $stim       =& $Trials[$count-1]['Stimuli'];
                $stim       =  $stimuli[2];
                $stimKey    =  array_keys($stim);
                $empty      =  array_fill_keys($stimKey, 'n/a');
                $Trials[$count-1]['Stimuli'] = $empty;
            }
        }
        
        ######## Go through $Trials and write session file(s)
        // session files go into subjects folder and will be formatted according to the template in fileLocations.php
        $fileNumber    = 0;
        foreach ($Trials as $Trial) {
            if((!isset($skippedFirstTrial))
                OR (strtolower($Trial['Procedure']['Item']) === '*newfile*')
            ) {
                $skippedFirstTrial = TRUE;
                $fileNumber++;
                $temp = array(
                    'Username'                     => $_SESSION['Username'],
                    'ID'                         => $_SESSION['ID'],
                    'Session'                     => $fileNumber,
                    'Condition' => array(
                        'Condition Number'      => $_SESSION['Condition']['Number'],
                        'Condition Notes'       => $_SESSION['Condition']['Condition Notes'],
                        'Condition Description' => $_SESSION['Condition']['Condition Description'] )
                );
                $sessionFile = $up . $dataF . $dataSubFolder . $expF . ComputeString($experimentFileName, $temp) . $outExt;
                continue;
            }
            
            // write ['Stimuli'] ['Procedure'] and ['Response'] data to next line of the file
            $line = array();
            foreach ($Trial as $key => $set) {
                $line += AddPrefixToArray($key . '*', $set);
            }
            arrayToLine($line, $sessionFile);
        }

    }
    #### Loading up $Trials for multisession experiments
    else {
        // Load headers from correct stimuli files
        $fileNumber  = $_SESSION['Session'];
        $sessionFile = $up . $dataF . $dataSubFolder . $expF . ComputeString($experimentFileName) . $outExt;
        #### ERROR Checking if the session file doesn't exist ####
        if (file_exists($sessionFile) == FALSE) {
            echo '<br/><br/>Could not find your session ' . $_SESSION['Session'] . 'file at ' . $sessionFile;
            exit;
        }
        $openSession = fopen($sessionFile, 'r');
        $headers     = fgetcsv($openSession, 0, "\t");
        foreach ($headers as &$head) {
            $head = explode('*', $head);                                // Response*RT becomes array( 0 => 'Response', 1 => 'RT' )
        }
        unset($head);
        
        // Loading up $Trials for this Username and Session
        $Trials     = array();
        $Trials[0]  = NULL;
        while ($line = fgetcsv($openSession, 0, "\t")) {
            $temp = array();
            foreach ($line as $i => $val) {
                $temp[ $head[$i][0] ][ $head[$i][1] ] = $val;            // for "Thing" found under Stimuli*Cue, $temp[ 'Stimuli' ][ 'Cue' ] = "Thing"
            }
            $Trials[] = $temp;
        }
    }
    
    #### Output errors & Stop progression
    if (($errors['Count'] > 0)
        AND ($_SESSION['Session'] == 1)
    ) {                                     // if there is an error (only stops for session 1 errors)
        ?>
            <div id="ErrorCodes">
                <b> <?php echo $errors['Count']; ?> errors found in your code </b>
                <ol>
                    <?php   foreach ($errors['Details'] as $errorCode) {
                                echo '<li>' . $errorCode . '</li>';
                            }                                                   ?>
                </ol>
                <?php
                     if ($stopForErrors == TRUE) {
                         echo '<br/> <h2>The program will not run until you have addressed the above errors</h2>';
                         exit;
                     }
                ?>
            </div>
        <?php
    }
    
    
    $outputFile = ComputeString($outputFileName) . $outExt;
    $_SESSION['Output File'] = $up . $dataF . $dataSubFolder . $outputF . $outputFile;
    $_SESSION['Start Time']  = date('c');
    
    #### Record info about the person starting the experiment to the status start file
    // information about the user loging in
    $UserData = array(
        'Username'              => $_SESSION['Username'],
        'ID'                    => $_SESSION['ID'],
        'Date'                  => $_SESSION['Start Time'],
        'Session'               => $_SESSION['Session'] ,
        'Condition_Number'      => $_SESSION['Condition']['Number'],
        'Condition_Description' => $_SESSION['Condition']['Condition Description'],
        'Output_File'           => $outputFile,
        'Stimuli_File'          => $_SESSION['Condition']['Stimuli'],
        'Procedure_File'        => $_SESSION['Condition']['Procedure'],
        'User_Agent_Info'       => $_SERVER['HTTP_USER_AGENT'],
        'IP'                    => $_SERVER["REMOTE_ADDR"],
        'Inclusion Notes'       => 'N/A',
    );
    arrayToLine($UserData, $statusBeginPath);
    ###########################################################################
    
    
    #### Establishing $_SESSION['Trials'] as the place where all experiment trials are stored
    // session1 $Trials also contains trials for other sessions but trial.php sends to done.php once a *newfile* shows up
    $_SESSION['Trials']        = $Trials;
    $_SESSION['Position']    = 1;
    $_SESSION['PostNumber'] = -1;
    
    
    #### Send participant to next phase of experiment (demographics or instructions)
    if ($doDemographics == TRUE) {
        $link = 'BasicInfo.php';
    }
    else {
        $link = 'instructions.php';
    }
    
    
    if ($stopAtLogin == TRUE) {                                                 // if things are going wrong this info will help you figure out when the program broke
        Readable($_SESSION['Condition'],    'Condition information');
        Readable($stimuli,                  'Stimuli file in use ('   . $stimF . $_SESSION['Condition']['Stimuli']   . ')');
        Readable($procedure,                'Procedure file in use (' . $procF . $_SESSION['Condition']['Procedure'] . ')');
        Readable($trialTypeColumns,         'Levels of trial types being used');
        Readable($_SESSION['Trial Types'],  'All info about trial types used in experiment');
        Readable($_SESSION['Trials'],       '$_SESSION["Trials"] array');
        echo '<form action ="' . $link . '" method="get">'
               . '<input type="submit" value="Press here to continue to experiment"/>'
           . '</form>';
    }
    else {
        echo '<form id="loadingForm" action="' . $link . '" method="get"> </form>';
    }
        
    require $_codeF . 'Footer.php';
?>
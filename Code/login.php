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
    
    
    #### Grabbing submitted info
    $username = $_GET['Username'];                                          // get username from URL
    $username = filter_var($username, FILTER_SANITIZE_EMAIL);              // cleaning characters that wouldn't write to a filename
	$badFileCharacters = array( '/', '\\', '?', '%', '*', ':', '|', '"', '<', '>' );
	$username = str_replace( $badFileCharacters, '', $username );
    $selectedCondition = trim($_GET['Condition']);                          // grab Condition from URL
    
    
    
    #### Setting a unique ID for this login
    if (!isset($_SESSION['ID'])) {
        if (isset($_GET['ID'])) {                                           // if the ID is in the URL
            $_SESSION['ID'] = $_GET['ID'];                                      // use it as the unique ID
        } else {
            $_SESSION['ID'] = rand_string();                                // otherwise, make a new ID
        }
    }
    
    
    
    #### Checking for debug mode
    if ((strlen($debugName) > 0)                                            // did we login as debug?
        AND (substr($username, 0, strlen($debugName)) === $debugName)
    ) {
        $_SESSION['Debug'] = TRUE;
        $username = trim(substr($username, strlen($debugName)));
        if ($username === '') { $username = $_SESSION['ID']; }
    }
    if ($_SESSION['Debug'] === TRUE) {                                      // if debug
        $dataSubFolder = $debugF;                                               // write data to separate folder
        $path = $up . $dataF . $dataSubFolder . $extraDataF;
        $statusBeginCompleteFileName = $path . $statusBeginFileName . $outExt;
    }
    
    
    
    #### Checking info about this username
    $_SESSION['Username'] = $username;                                      // set Username
    
    // is the username long enough (> 3 characters)
    if ((strlen($username) < 3)
        AND (!$_SESSION['Debug'])
    ) {
        echo '<h1> Error: Login username must be 3 characters or longer</h1>'
           . '<h2>Click <a href="' . $up . 'index.php">here</a> to enter a valid username</h2>';
        exit;
    }
    
    // is this user ineligible to participate in the experiment?
    if (($checkElig == TRUE)
        AND ($mTurkMode == TRUE)
    ) {
        include 'check.php';
    }
    
    // Has this user already completed session 1?  If so, determine whether they have another session to complete or if they are done
    $sessionFilename = FileExists($_rootF . $dataF . $dataSubFolder . $jsonF . $_SESSION['Username'] . '.json');
    if ($sessionFilename == TRUE) {              // this file will only exist if this username has completed a session successfully
        $pastSession   = fopen($sessionFilename, 'r');
        $loadedSession = fread($pastSession, filesize($sessionFilename));
        $sessionData   = json_decode($loadedSession, TRUE);
        // Load old session info
        $_SESSION = NULL;                       // get rid of current session in memory
        $_SESSION = $sessionData;               // load old session data into current $_SESSION
        // check if it is time for the next session
        $ExpOverFlag = $_SESSION['Trials'][ ($_SESSION['Position']) ]['Procedure']['Item'];
        if ($ExpOverFlag != 'ExperimentFinished') {                                                         // if this user hasn't done all sessions
            $wait = $_SESSION['Trials'][ ($_SESSION['Position']-1) ]['Procedure']['Timing'];                    // check 'Timing' column of *newSession* line
            $wait = durationInSeconds($wait);                                                                   // how many seconds was I supposed to wait until the next session?
            $sinceFinish = time() - $_SESSION['LastFinish'];
            if ($sinceFinish < $wait) {
                $timeRemaining = durationFormatted($wait - $sinceFinish);
                echo '<h1> Sorry, you must wait before you can complete this part of the experiment'
                     . '<br> Please return in ' . $timeRemaining . ' </h1>';
                exit;
            }
        }
        // Overwrite values that need to be updated
        $outputFile = ComputeString($outputFileName) . $outExt;                                 // write to new file
        $_SESSION['Output File'] = $_rootF . $dataF . $dataSubFolder . $outputF . $outputFile;
        $_SESSION['Start Time']  = date('c');
		
		#### Record info about the person starting the experiment to the status start file
		// information about the user logging in
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
	
        echo '<meta http-equiv="refresh" content="1; url=' . $_codeF . 'trial.php">';
        exit;               // do not run any of the other code, send to trial.php
        
    } else {
        $_SESSION['Session'] = 1;               // if they have no .json file then they are in session 1
    }
    
    
    
    ##### Error Checking Code ####
    $found  = FALSE;                                                            // will use this later to determine when loops fail
    $errors = array('Count'   => 0,
                    'Details' => array()  );
    if (file_exists($_rootF . $expFiles . $conditionsFileName) == FALSE) {      // does conditions exist? (error checking)
        $errors['Count']++;
        $errors['Details'][] = 'No "' . $conditionsFileName . '" found';
    }
    // does the condition file have the required headers?
    $Conditions = GetFromFile($up . $expFiles . $conditionsFileName,  FALSE);   // Loading conditions info
    $errors = keyCheck($Conditions, 'Number'    , $errors, $conditionsFileName);
    $errors = keyCheck($Conditions, 'Stimuli'   , $errors, $conditionsFileName);
    $errors = keyCheck($Conditions, 'Procedure' , $errors, $conditionsFileName);
    
    
    
    #### Code to automatically choose condition assignment
    $Conditions = GetFromFile($up . $expFiles . $conditionsFileName,  FALSE);   // Loading conditions info
    $logFile    = $up . $dataF . $countF . $loginCounterName;
    if ($selectedCondition == 'Auto') {
        if (!is_dir($up . $dataF . $countF)) {                                  // create the 'Counter' folder if it doesn't exist
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
    
    
    
    ###########################################################################
    ##### Error Checking Code #################################################
    ###########################################################################
    // did we fail to find the condition information?
    if ($found == FALSE) {
        $errors['Count']++;
        $errors['Details'][] = 'Could not find the selected condition # ' . $conditionNumber . ' in ' . $conditionsFileName;
    }
    
    // calculating path to Stimuli and Procedure file
    $stimPath = $up . $expFiles . $stimF . $_SESSION['Condition']['Stimuli'];
    $procPath = $up . $expFiles . $procF . $_SESSION['Condition']['Procedure'];
    
    // does this condition point to a valid stimuli file?
    if (file_exists($stimPath) == FALSE) {
        $errors['Count']++;
        $errors['Details'][] = 'No stimuli file found at "' . $stimF . $_SESSION['Condition']['Stimuli'] . '"';
    }
    // checking required columns from Stimuli file
    $temp = GetFromFile($stimPath, FALSE);
    $errors = keyCheck($temp, 'Cue'    ,   $errors, $_SESSION['Condition']['Stimuli']);
    $errors = keyCheck($temp, 'Answer' ,   $errors, $_SESSION['Condition']['Stimuli']);
    // $errors = keyCheck($temp, 'Shuffle',   $errors, $_SESSION['Condition']['Stimuli']);
    
    // does this condition point to a valid procedure file?
    if (file_exists($procPath) == FALSE) {
        $errors['Count']++;
        $errors['Details'][] = 'No procedure file found at "' . $procF . $_SESSION['Condition']['Procedure'] . '"';
    }
    // checking required columns from Procedure file
    $temp = GetFromFile($procPath, FALSE);
    $errors = keyCheck($temp, 'Item'       ,   $errors, $_SESSION['Condition']['Procedure']);
    $errors = keyCheck($temp, 'Trial Type' ,   $errors, $_SESSION['Condition']['Procedure']);
    $errors = keyCheck($temp, 'Timing'     ,   $errors, $_SESSION['Condition']['Procedure']);
    // $errors = keyCheck($temp, 'Shuffle'    ,   $errors, $_SESSION['Condition']['Procedure']);
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
    $trialTypes = include 'scanTrialTypes.php';                             // look through the trial type folders in the Experiment/ and the Code/ folder, return an array
    $notTrials  = array('off'   => TRUE,                                    // if the 'Trial Type' value is one of these then it isn't a trial
                        'no'    => TRUE,
                        ''      => TRUE,
                        'n/a'   => TRUE  );
    foreach ($procedure as $i => $row) {                                    // go through all rows of procedure
        if ($row === 0)                                 { continue; }       // skip padding
        if (strtolower($row['Item']) === '*newsession*')   { continue; }    // skip multisession markers
        foreach ($trialTypeColumns as $postNumber => $column) {             // check all trial levels
            $thisTrialType = strtolower($row[$column]);                         // which trial is being used at this level (e.g, Study, Test, etc.)
            if (isset($notTrials[$thisTrialType])) {                        // skip turned off trials (off, no, n/a, '')
                continue;
            }
            $trialTypes[$thisTrialType]['levels'][$postNumber] = NULL;      // make note what levels each trial type are used at (e.g., Study is a regular AND a Post 1 trial)
            if (!isset($trialTypes[$thisTrialType])) {
                $procName = pathinfo($up . $expFiles . $procF . $_SESSION['Condition']['Procedure'], PATHINFO_FILENAME);
                $errors['Count']++;
                $errors['Details'][] = 'The trial type ' . $row[$column] . ' for row ' . $i . ' in the procedure file '
                                     . $procName . ' has no file or folder in either the "' . $expFiles . $custTTF . '" folder '
                                     . 'or the "' . $codeF . $trialF . '" folder.';
            }
        }
    }
    unset($procedure);
    ##### END Error Checking Code #################################################
    
    
    
    
    
    ###############################################################################
    #### Preparing The Experiment #################################################
    ###############################################################################
    // Setting up all the ['Response'] keys that will be needed during the experiment
    // Also checks scoring files if that trial type lists some required columns
    $proc = GetFromFile($up . $expFiles . $procF . $_SESSION['Condition']['Procedure'], FALSE); // load procedure file without padding
    $findingKeys   = TRUE;
    $allKeysNeeded = array();
    $scoringFiles  = array();
    foreach ($trialTypes as $name => $thisTrialType) {                       // compile an array of the scoring files to check for keys
        $scoringFiles[ $thisTrialType['scoring'] ] = $name;
    }
    foreach ($scoringFiles as $fileName => &$keys) {
        $name = $keys;                                              // $keys is originally the name of the trial type, such as "mcpic"
        $requiredColumns = array();
        $keys = include $fileName;                                  // grab keys from scoring file when $findingKeys == TRUE
        $trialTypes[$name]['requiredColumns'] = $requiredColumns;
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
                if (isset($thisTrialType['requiredColumns'])) {
                    foreach ($thisTrialType['requiredColumns'] as $requiredColumn) {
                        $errors = keyCheck($proc, $requiredColumn, $errors, $_SESSION['Condition']['Procedure']);
                    }
                }
            } else {
                $postKeys += AddPrefixToArray('post' . $lvl . '_', $scoringFiles[ $thisTrialType['scoring'] ]);
                foreach ($thisTrialType['requiredColumns'] as $requiredColumn) {
                    $errors = keyCheck($proc, 'Post' . ' '  . $lvl . ' ' . $requiredColumn, $errors, $_SESSION['Condition']['Procedure']);
                }
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
    $_SESSION['Trial Types'] = $trialTypes;         // contains locations of display and scoring files for each trial type
    
    include 'advancedShuffles.php';
    #### Create $_SESSION['Trials'] 
    #### Load all Stimuli and Procedure info for this participant's condition then combine to create the experiment
    // load stimuli for this condition then block shuffle
    $stimuli = GetFromFile($up . $expFiles . $stimF . $_SESSION['Condition']['Stimuli']);
    $stimuli = BlockShuffle($stimuli, 'Shuffle');
    $stimuli = shuffle2dArray($stimuli, $stopAtLogin);
    $_SESSION['Stimuli'] = $stimuli;
    
    // load and block shuffle procedure for this condition
    $procedure = GetFromFile($up . $expFiles . $procF . $_SESSION['Condition']['Procedure']);
    
    $addColumns = array( 'Text' );
    foreach ($addColumns as $add) {
        foreach ($trialTypeColumns as $number => $colName) {                                // check all trial type levels we found
            if ($number == 0) {
                $prefix = '';
            } else {
                $prefix = 'Post' . ' ' . $number . ' ';
            }
            $column = $prefix . $add;
            addColumn($procedure, $column);                // this will only add columns if they don't already exist; nothing is overwritten
        }
    }
    
    $procedure = BlockShuffle($procedure, 'Shuffle');
    $procedure = shuffle2dArray($procedure, $stopAtLogin);
    
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
        if ($count == ($procedureLength-1)) {                               // when the last trial has been loaded
            $Trials[$count] = cleanTrial($Trials[$count-1]);                    // return a copy of the last trial without any values in it
            $Trials[$count]['Procedure']['Item'] = 'ExperimentFinished';        // add this flag so we know when participants are done with all sessions
        }
    }
    
    
    
    #### Establishing $_SESSION['Trials'] as the place where all experiment trials are stored
    // $Trials also contains trials for other sessions but trial.php sends to done.php once a *NewSession* shows up
    $_SESSION['Trials']     = $Trials;
    $_SESSION['Position']   = 1;
    $_SESSION['PostNumber'] = 0;
    
    
    
    #### Figuring out what the output filename will be
    $outputFile = ComputeString($outputFileName) . $outExt;
    $_SESSION['Output File'] = $up . $dataF . $dataSubFolder . $outputF . $outputFile;
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
                     if ($stopForErrors == TRUE) {
                         echo '<br/> <h2>The program will not run until you have addressed the above errors</h2>';
                         exit;
                     }
                ?>
            </div>
        <?php
    }
    
    
    
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
    
    
    
    
    
    #### Send participant to next phase of experiment (demographics or instructions)
    if ($doDemographics == TRUE) {
        $link = 'BasicInfo.php';
    } elseif ($doInstructions) {
        $link = 'instructions.php';
    } else {
        $link = 'trial.php';
    }
    
    
    if ($stopAtLogin == TRUE) {             // if things are going wrong this info will help you figure out when the program broke
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
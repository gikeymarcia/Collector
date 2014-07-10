<?php
/*  Collector
    A program for running experiments on the web
    Copyright 2012-2014 Mikey Garcia & Nate Kornell
 */
    require 'CustomFunctions.php';                          // Load custom PHP functions
    initiateCollector();
    require 'fileLocations.php';                            // sends files to the right places
    require $up . $expFiles . 'Settings.php';               // experiment variables

    $_SESSION = array();                                    // reset session so it doesn't contain any information from a previous login attempt
    $_SESSION['OutputDelimiter'] = $delimiter;
    $_SESSION['Debug'] = $debugMode;
?>

<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <link href="css/global.css" rel="stylesheet" type="text/css" />
    <link href='http://fonts.googleapis.com/css?family=Kreon' rel='stylesheet' type='text/css' />
    <title>Preparing the Experiment</title>
</head>
<body>
    <?php
        
        echo '<h1> Please wait while we load the experiment... </h1>';
        

        ##### Error Checking Code ####
        $found  = FALSE;                                                        // will use this later to determine when loops fail
        $errors = array('Count'   => 0,
                        'Details' => array()  );
        if (file_exists($up . $expFiles . $conditionsFileName) == FALSE) {      // does conditions exist? (error checking)
            $errors['Count']++;
            $errors['Details'][] = 'No "'.$conditionsFileName.'" found';
        }
        
        
        #### Grabbing submitted info
        $username = trim($_GET['Username']);                        // grab Username from URL
        $selectedCondition = trim($_GET['Condition']);              // grab Condition from URL
        if (isset($_GET['Session'])) {
            $_SESSION['Session'] = trim($_GET['Session']);          // grab session# from URL
        } else {
            $_SESSION['Session'] = 1;                               // if session is not set then set to 1
        }
        if ($_SESSION['Session'] != 1) {
            $doDemographics = FALSE;                                // skip demographics for all but session1
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
        if (strlen($debugName) > 0
            && substr($username, 0, strlen($debugName)) === $debugName
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
         
        
        if ($checkElig == TRUE AND $mTurkMode == TRUE) {
            include	'check.php';
        }
        
        #### Checking username is 3 characters or longer
        if (strlen($_GET['Username']) < 3
            AND !$_SESSION['Debug']
        ) {
        	echo '<h1> Error: Login username must be 3 characters or longer</h1>
        			<h2>Click <a href="' . $up . 'index.php">here</a> to enter a valid username</h2>';
        	exit;
        }
        
        
        #### Code to automatically choose condition assignment
        $Conditions	= GetFromFile($up . $expFiles . $conditionsFileName,  FALSE);   // Loading conditions info
        $logFile	= $up . $dataF . $countF . $loginCounterName;
        if ($selectedCondition == 'Auto') {
            if (!is_dir($up . $dataF . $countF)) {
                mkdir($up . $dataF . $countF,  0777,  TRUE);
            }
            
            if (file_exists($logFile)) {                                            // Read counter file & save value
                $fileHandle	= fopen($logFile, "r");
                $loginCount	= fgets($fileHandle);
                fclose($fileHandle);
            } else { $loginCount = 1; }
            // write old value + 1 to login counter
            $fileHandle	= fopen($logFile, "w");
            fputs($fileHandle, $loginCount+1);
            fclose($fileHandle);
            
            $conditionNumber = ($loginCount % count($Conditions))+1;                // cycles through current condition assignment based on login counter
        }
        else{
            $conditionNumber = $selectedCondition;                                  // if condition is manually choosen then honor choice
        }
        
        
        #### loads condition info into $_Session['Condition']
        foreach ($Conditions as $Acond) {
            if ($Acond['Number'] == $conditionNumber) {
                $_SESSION['Condition'] = $Acond;
                $found = TRUE;
                break;
            }
        }
        
        ##### Error Checking Code ####
        // did we fail to find the condition information?
        if($found == FALSE) {
            $errors['Count']++;
            $errors['Details'][] = 'Could not find the selected condition #'.$conditionNumber.' in '.$conditionsFileName;
        }
        // does the condition file have the required headers?
        $errors = keyCheck($Conditions, 'Number'    , $errors, $conditionsFileName);
        $errors = keyCheck($Conditions, 'Stimuli'   , $errors, $conditionsFileName);
        $errors = keyCheck($Conditions, 'Procedure' , $errors, $conditionsFileName);
        // does this condition point to a valid stimuli file?
        if (file_exists($up.$expFiles.$_SESSION['Condition']['Stimuli']) == FALSE) {
        	$errors['Count']++;
        	$errors['Details'][] = 'No stimuli file found at '.$_SESSION['Condition']['Stimuli'];
        }
        // does this condition point to a valid procedure file?
        if (file_exists($up.$expFiles.$_SESSION['Condition']['Procedure']) == FALSE) {
        	$errors['Count']++;
        	$errors['Details'][] = 'No procedure file found at '.$_SESSION['Condition']['Procedure'];
        }
        // checking required columns from Stimuli file
        $temp = GetFromFile($up.$expFiles.$_SESSION['Condition']['Stimuli'], FALSE);
        $errors = keyCheck($temp, 'Cue'    ,   $errors, $_SESSION['Condition']['Stimuli']);
        $errors = keyCheck($temp, 'Target' ,   $errors, $_SESSION['Condition']['Stimuli']);
        $errors = keyCheck($temp, 'Answer' ,   $errors, $_SESSION['Condition']['Stimuli']);
        $errors = keyCheck($temp, 'Shuffle',   $errors, $_SESSION['Condition']['Stimuli']);
        // checking required columns from Procedure file
        $temp = GetFromFile($up.$expFiles.$_SESSION['Condition']['Procedure'], FALSE);
        $errors = keyCheck($temp, 'Item'       ,   $errors, $_SESSION['Condition']['Procedure']);
        $errors = keyCheck($temp, 'Trial Type' ,   $errors, $_SESSION['Condition']['Procedure']);
        $errors = keyCheck($temp, 'Timing'     ,   $errors, $_SESSION['Condition']['Procedure']);
        $errors = keyCheck($temp, 'Shuffle'    ,   $errors, $_SESSION['Condition']['Procedure']);
        unset($temp);                                                                 // clear $temp
        
/*        
        #### Find all of the columns that hold trial types (including 'Post# Trial Type's)
        $PostCount = 0;                                                                 // counts how many levels of post trials are used (e.g., Post 1, Post 2, Post 3...)
        $trialTypeColumns = array();                                                    // Give me the column names of all Trial Type columns (e.g., Trial Type, Post 1 Trial Type, Post 2 Trial Type)
        $proc = GetFromFile($up.$expFiles.$_SESSION['Condition']['Procedure'], FALSE);  // load procedure file without padding
        foreach ($proc[0] as $column => $value) {                                       // check all procedure file columns (using the first line of procedure)
            $name = strtolower(trim($column));                                          // get column name (lower case)
            if (substr($name, 0, 4) !== 'post') {                                       // for non-post trial columns (don't start with 'post')
                if ($name == 'trial type') {                                                // is this the 'Trial Type' column?
                    $trialTypeColumns[0] = $column;                                             // log the location of the 'Trial Type' column
                }
            } else {                                                                    // for all Post columns
                $name = strtolower(trim(substr($name, 4)));                                // pull off the 'post' prefix, leading/trailing spaces, and lowercase it
                $i = 0;                                                                     // start checking at 0th remaining character
                while (is_numeric($name[$i])) {                                         // while the $i-th character is a #
                    $i++;                                                                   // check if next char is a #
                }
                if ($i === 0) {                                                         // if there wasn't a post # set (e.g., 'post Trial Type')
                    $errors['Count']++;
                    $errors['Details'][] = 'Column "' . $column . '" in ' . $_SESSION['Condition']['Procedure']
                                         . ' needs to be numbered (e.g., "Post<b>1</b> Trial Type")';
                    continue;
                } else {                                                                // if a # was found
                    $thisPostN = (int)substr($name, 0, $i);                                 // characters 0-$i = the post trial #
                }
                $PostCount = max($PostCount, $thisPostN);                               // find the highest post count used in the experiment (by checking if the curent post count > the highest post count found)
                $name = trim(substr($name, $i));                                        // pull off the # from column name and remove any leading/trailing blanks
                if ($name == 'trial type') {                                            // if this Post column is a 'Trial Type'
                    $trialTypeColumns[$thisPostN] = $column;                            // log the location of the 'Post{$thisPostN} Trial Type' (e.g., 'Post12 Trial Type')
                }
            }
        }
        ksort($trialTypeColumns);                                                           // put trial type columns in order within $trialTypeColumns
        readable($trialTypeColumns, 'tyson find trial types');

 *     
        #### Checking that each Post # has a 'Trial Type' and 'Timing' -- if the 
        $notTrials = array('off'    => TRUE,                                            // if the 'Trial Type' value is one of these then it isn't a trial
                           'no'     => TRUE,
                           ''       => TRUE,
                           'n/a'    => TRUE  );
        for ($i=1; $i<=$PostCount; ++$i) {                                              // go through all 'Post{$i} Trial Type' columns found above
            $column = $trialTypeColumns[$i];                                            // get 'Post{$i} Trial Type' column name
            $noPosts = TRUE;                                                            // start by assuming we aren't using post columns
            foreach ($proc as $row) {                                                   // go through all the rows in the procedure file
                $check = strtolower(trim($row[$column]));                                   // get the trimmed and lowercase value of the 'Post{$i} Trial Type' for this row
            	if (!isset($notTrials[$check])) {                                          // if the value isn't in $notTrials (i.e., isn't turned off)
            	    $noPosts = FALSE;                                                          // then we are using post columns
            		break;
            	}
            }
            if ($noPosts == TRUE) {
                continue;
            }
            $needed = array('trialType', 'timing');                                     // every level must have a trial type and a timing
            foreach ($needed as $need) {
            	unset($$need);
            }
            ExtractTrial($proc[0], $i);
            foreach ($needed as $need) {
                if (!isset($$need)) {
                    $column = ucwords(preg_replace('/[A-Z]/', ' \\0', $need));
                    $errors['Count']++;
                    $errors['Details'][] = 'Post Trial ' . $i . ' is missing the ' . $column . ' column (i.e., add a column called 
                                            "Post' . $i . ' ' . $column . '" to the file "' . $_SESSION['Condition']['Procedure'] . '" to fix this error).';
                }
            }
        }
        unset($proc);
*/

        #### Find all of the columns that hold trial types (including 'Post# Trial Type's)
        $trialTypeColumns = array();                                                        // Each position will have the column name of a trial type column
        $proc = GetFromFile($up . $expFiles . $_SESSION['Condition']['Procedure'], FALSE);  // load procedure file without padding
        foreach ($proc[0] as $col => $val) {                                                // check all column names
            $cleanCol = trim(strtolower($col));                                                 // lowercase it + remove trailing/leading whitespace
            if (substr($cleanCol, -10) == 'trial type') {                                       // if ends with 'trial type'
                if ($cleanCol == 'trial type') {                                                    // and is trial type
                    $trialTypeColumns[0] = $col;                                                        // save it
                } elseif (substr($cleanCol, 0, 4) == 'post') {                                      // if it starts with 'post'
                    $cleanCol = trim(substr($cleanCol, 4, -10));                                        // drop the 'post' and 'trial type'
                    if (is_numeric($cleanCol)) {                                                        // if what remains is a # (e.g., '15')
                        $trialTypeColumns[$cleanCol] = $col;                                                // set $trialTypeColumns[15] to this column name
                    } else {                                                                            // if not, it should have been a #
                        $errors['Count']++;
                        $errors['Details'][] = 'Column "' . $column . '" in ' . $_SESSION['Condition']['Procedure']
                                             . ' needs to be numbered (e.g., "Post<b>1</b> Trial Type")';
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
        #### End of error checking
        
        
        
        #### Backward compatability (do not rely on it becasue it will go away)
        // setting defaults again here, so that if people downloaded the new Code/ folder without updating their settings, we can proceed as normal
        if (!isset($checkAllFiles)) {
            $checkAllFiles = TRUE;
        }
        if (!isset($checkCurrentFiles)) {
            $checkCurrentFiles = FALSE;
        }
        
        
        
        #### Load all Stimuli and Info for this participant's condition then combine to create the experiment
        if ($_SESSION['Session'] == 1) {
            // load and block shuffle stimuli for this condition
            $stimuli = GetFromFile($up . $expFiles . $_SESSION['Condition']['Stimuli']);
            
            // check the stimuli for correct image/audio file names, before the shuffle, so that we can tell people which rows the errors are on.
            if ($checkAllFiles OR $checkCurrentFiles) {
                $stimuliFiles = array();
                if ($checkAllFiles) {
                    $stimPath = $up . $expFiles . 'Stimuli/';
                    $scanStimFiles = scandir($stimPath);
                    foreach ($scanStimFiles as $fileName) {
                        if (is_file($stimPath . $fileName)) {
                            $stimuliFiles[] = $stimPath . $fileName;
                        }
                    }
                } else {
                    $stimuliFiles[] = $up . $expFiles . $_SESSION['Condition']['Stimuli'];
                }
                foreach ($stimuliFiles as $fileName) {
                    if ($fileName === $up . $expFiles . $_SESSION['Condition']['Stimuli']) {
                        $temp = $stimuli;
                    } else {
                        $temp = GetFromFile($fileName);
                    }
                    foreach ($temp as $i => $row) {
                        if ($i < 2) { continue; }
                        if (show($row['Cue']) !== $row['Cue']) {
                            // show() detects a file extension like .png, and will use FileExists to check that it exists
                            // but it will always return a string, for cases where you are showing regular text
                            // using FileExists, we can see if a cue detected as an image by show() is a file that actually exists
                            if (FileExists('../Experiment/' . $row['Cue']) === FALSE ) {
                                $errors['Count']++;
                                $errors['Details'][] = 'Image or audio file "../Experiment/' . $row['Cue'] . '" not found for 
                                                        row ' . $i . ' in Stimuli File "' . basename($fileName) . '".';
                            }
                        }
                    }
                }
            }
			$stimuli = BlockShuffle($stimuli, 'Shuffle');
			
			
			// load and block shuffle procedure for this condition
			$procedure = GetFromFile($up . $expFiles . $_SESSION['Condition']['Procedure']);
			
			$trialTypes = array();
			foreach( $procedure as $i => $row ) {
				if( $row === 0 ) { continue; }
				if( $row['Item'] === '*newfile*' ) { continue; }
				foreach( $trialTypeColumns as $postNumber => $column ) {
					$trialType = strtolower(trim( $row[$column] ));
					if( isset( $notTrials[$trialType] ) ) { continue; }
					$trialTypes[ $trialType ]['levels'][ $postNumber ] = NULL;
					if( isset( $trialTypes[ $trialType ]['trial'] ) ) { continue; }			// no need to find the trial type twice
					$fileName = fileExists( 'TrialTypes/'.$trialType.'.php' );		// fileExists will find both files and folders
					if( $fileName === FALSE ) {
						$procName = pathinfo( $up.$expFiles.$_SESSION['Condition']['Procedure'], PATHINFO_FILENAME );
						$errors['Count']++;
						$errors['Details'][] = 'The trial type '.$row[$column].' for row '.$i.' in the procedure file '.$procName.' has no file or folder in the "'.$codeF.'TrialTypes/" folder.';
					}
					if( is_dir( $fileName ) ) {
						$display = fileExists( $fileName.'/trial.php', TRUE, FALSE );
						if( $display === FALSE ) {
							$procName = pathinfo( $up.$expFiles.$_SESSION['Condition']['Procedure'], PATHINFO_FILENAME );
							$errors['Count']++;
							$errors['Details'][] = 'The trial type '.$row[$column].' for row '.$i.' in the procedure file '.$procName.' has a folder in the "'.$codeF.'TrialTypes/" folder, but there is no "trial" file within that folder.';
						} else {
							$scoringFile = fileExists( $fileName.'/scoring.php', FALSE, FALSE );
							if( $scoringFile === FALSE ) {
								$scoringFile = $scoring;
							}
							$trialTypes[ $trialType ]['trial'] = $display;
							$trialTypes[ $trialType ]['scoring'] = $scoringFile;
						}
					} else {
						$trialTypes[ $trialType ]['trial'] = $fileName;
						$trialTypes[ $trialType ]['scoring'] = $scoring;
					}
				}
			}
			$findingKeys = TRUE;
			$allKeysNeeded = array();
			$scoringFiles = array();
			foreach( $trialTypes as $trialType ) {
				$scoringFiles[ $trialType['scoring'] ] = NULL;
			}
			foreach( $scoringFiles as $fileName => &$keys ) {
				$keys = include $fileName;
				if( $keys == 1 ) {
					$keys = array();
				} elseif( !is_array( $keys ) ) {
					$keys = array( $keys );
				}
				$keys = array_flip( $keys );
			}
			unset( $keys );
			foreach( $trialTypes as $trialType ) {
				foreach( $trialType['levels'] as $lvl => $null ) {
					if( $lvl === 0 ) {
						$allKeysNeeded += $scoringFiles[ $trialType['scoring'] ];
					} else {
						$allKeysNeeded += AddPrefixToArray( 'post'.$lvl.'_', $scoringFiles[ $trialType['scoring'] ] );
					}
				}
			}
			foreach( $allKeysNeeded as &$key ) {
				$key = NULL;
			}
			unset( $key );
			$_SESSION['Trial Types'] = $trialTypes;
			
			$procedure = BlockShuffle($procedure, 'Shuffle');
						
			// Load entire experiment into $Trials[1-X] where X is the number of trials
			$Trials = array(0=> 0);
			$procedureLength = count($procedure);
			for ($count=2; $count<$procedureLength; $count++) {
				$Trials[$count-1]['Stimuli']	= $stimuli[ ($procedure[$count]['Item']) ];			// adding 'Stimuli', as an array, to each position of $Trials
				$Trials[$count-1]['Procedure']	= $procedure[$count];								// adding 'Procedure', as an array, to each position of $Trials
				$Trials[$count-1]['Response']	= $allKeysNeeded;
															
				// on trials with no Stimuli info (e.g., freerecall) keep the same Stimuli structure but fill with 'n/a' values
				// I need a consistent Trial structure to do all of the automatic output creation I do later on
				if($Trials[$count-1]['Stimuli'] == NULL) {
					$stim		=& $Trials[$count-1]['Stimuli'];
					$stim		=  $stimuli[2];
					$stimKey	= array_keys($stim);
					$empty		= array_fill_keys($stimKey, 'n/a');
					$Trials[$count-1]['Stimuli'] = $empty;
				}
			}
			
			######## Go through $Trials and write session file(s)
			// session files go into subjects folder and will be formatted according to the template in fileLocations.php
			$fileNumber	= 0;
			foreach ($Trials as $Trial) {
				if( !isset($skippedFirstTrial) OR strtolower(trim($Trial['Procedure']['Item'])) === '*newfile*' ) {
					$skippedFirstTrial = TRUE;
					++$fileNumber;
					$temp = array(
						'Username' 					=> $_SESSION['Username'],
						'ID' 						=> $_SESSION['ID'],
						'Session' 					=> $fileNumber,
						'Condition' => array(
							'Condition Number' 		=> $_SESSION['Condition']['Number'],
							'Condition Notes' 		=> $_SESSION['Condition']['Condition Notes'],
							'Condition Description' => $_SESSION['Condition']['Condition Description']
						)
					);
					$sessionFile = $up.$dataF.$dataSubFolder.$expF.ComputeString( $experimentFileName, $temp ).$outExt;
					continue;
				}
				
				// write ['Stimuli'] ['Procedure'] and ['Response'] data to next line of the file
				$line = array();
				foreach( $Trial as $key => $set ) {
					$line += AddPrefixToArray( $key.'*', $set );
				}
				arrayToLine($line,$sessionFile);
			
			}
		}
		#### Loading up $Trials for multisession experiments
		else {
			// Load headers from correct stimuli files
			$fileNumber				= $_SESSION['Session'];
			$sessionFile 			= $up.$dataF.$dataSubFolder.$expF.ComputeString( $experimentFileName ).$outExt;
			#### ERROR Checking if the session file doesn't exist ####
			if (file_exists($sessionFile) == FALSE) {
				echo "<br/><br/>Could not find your session {$_SESSION['Session']} file at {$sessionFile}";
				exit;
			}
			$openSession			= fopen($sessionFile, 'r');
			$headers				= fgetcsv($openSession,0,"\t");
			foreach( $headers as &$head ) {
				$head = explode( '*', $head );								// Response*RT becomes array( 0 => 'Response', 1 => 'RT' )
			}
			unset( $head );
			
			// Loading up $Trials for this Username and Session
			$Trials		= array();
			$Trials[0]	= NULL;
			while($line = fgetcsv($openSession,0,"\t")) {
				$temp = array();
				foreach( $line as $i => $val ) {
					$temp[ $head[$i][0] ][ $head[$i][1] ] = $val;			// for "Thing" found under Stimuli*Cue, $temp[ 'Stimuli' ][ 'Cue' ] = "Thing"
				}
				$Trials[] = $temp;
			}
		}
		// readable($header1,'header top');																#### DEBUG ####
		// readable($header2,'header 2nd');																#### DEBUG ####
		
		#### Output errors & Stop progression
		if ($errors['Count'] > 0 AND $_SESSION['Session'] == 1 ) {										// if there is an error (only stops for session 1 errors)
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
		
		
		$outputFile = ComputeString($outputFileName).$outExt;
		$_SESSION['Output File'] = $up.$dataF.$dataSubFolder.$outputF.$outputFile;
		$_SESSION['Start Time'] = date('c');
		
		#### Record info about the person starting the experiment to the status start file
		// information about the user loging in
		$UserData = array(
							'Username' 				=> $_SESSION['Username'],
							'ID' 					=> $_SESSION['ID'],
							'Date' 					=> $_SESSION['Start Time'],
							'Session' 				=> $_SESSION['Session'] ,
							'Condition_Number' 		=> $_SESSION['Condition']['Number'],
							'Condition_Description' => $_SESSION['Condition']['Condition Description'],
							'Output_File' 			=> $outputFile,
							'Stimuli_File' 			=> $_SESSION['Condition']['Stimuli'],
							'Procedure_File' 		=> $_SESSION['Condition']['Procedure'],
							'User_Agent_Info' 		=> $_SERVER['HTTP_USER_AGENT'],
							'IP' 					=> $_SERVER["REMOTE_ADDR"],
							'Inclusion Notes' 		=> 'N/A'
						 );
		arrayToLine($UserData, $statusBeginPath);
		###########################################################################
		
		
		#### Establishing $_SESSION['Trials'] as the place where all experiment trials are stored
		// session1 $Trials also contains trials for other sessions but trial.php sends to done.php once a *newfile* shows up
		$_SESSION['Trials']		= $Trials;
		$_SESSION['Position']	= 1;
		$_SESSION['PostNumber'] = -1;
		
		
		#### Send participant to next phase of experiment (demographics or instructions)
		if($doDemographics == TRUE) {
			$link = 'BasicInfo.php';
		}
		else {
			$link = 'instructions.php';
		}
		
		
		if ($stopAtLogin == TRUE) {												// if things are going wrong this info will help you figure out when the program broke
			Readable($_SESSION['Condition'],	'Condition information');
			Readable($stimuli, 					'Stimuli file in use');
			Readable($procedure,				'Procedure file in use');
            Readable($trialTypeColumns,         'Levels of trial types being used');
			Readable($_SESSION['Trials'],		'$_SESSION["Trials"] array');
			echo '<form action ="'.$link.'" method="get">
					<input type="submit" value="Press here to continue to experiment"/>
				  </form>';
					
		}
		else {
			echo '<form id="loadingForm" action="'.$link.'" method="get"> </form>';
		}
		
		
	?>
	<script src="http://code.jquery.com/jquery-1.10.2.min.js" type="text/javascript"> </script>
	<script src="javascript/collector_1.0.0.js" type="text/javascript"> </script>
</body>
</html>
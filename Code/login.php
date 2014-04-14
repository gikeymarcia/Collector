<?php
/*	Collector
	A program for running experiments on the web
	Copyright 2012-2014 Mikey Garcia & Nate Kornell
 */
	ini_set('auto_detect_line_endings', true);				// fixes problems reading files saved on mac
	session_start();										// Start the session at the top of every page
	$_SESSION = array();									// reset session so it doesn't contain any information from a previous login attempt
	
	require 'CustomFunctions.php';							// Loads all of my custom PHP functions
	require 'fileLocations.php';							// sends file to the right place
	require $up.$expFiles.'Settings.php';					// experiment variables
	
	$_SESSION['Debug'] = $debugMode;
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link href="css/global.css" rel="stylesheet" type="text/css" />
	<link href='http://fonts.googleapis.com/css?family=Kreon' rel='stylesheet' type='text/css' />
	<title>Preparing the Experiment</title>
</head>
<?php flush();	?>

<body>
	
	<?php
		
		echo '<h1>Please wait while we load the experiment...</h1>';
		
		
		##### Error Checking Code ####
		$found	= FALSE;														// will use this later to determine when loops fail
		$errors	= array('Count' => 0, 'Details' =>array()  );					// the array to keep count and details of errors
		if (file_exists($up.$expFiles.$conditionsFileName) == FALSE):							// does conditions exist? (error checking)
			$errors['Count']++;
			$errors['Details'][] = 'No "'.$conditionsFileName.'" found';
		endif;
		
		
		#### Grabbing submitted info
		$username = trim($_GET['Username']);						// grab Username from URL
		if( substr( $username, 0, strlen($debugName) ) === $debugName ) {
			$_SESSION['Debug'] = TRUE;
			$username = trim( substr( $username, strlen($debugName) ) );
		}
		if ($_SESSION['Debug'] == FALSE) {
			error_reporting(0);
		}
		$_SESSION['Username'] = $username;
		$_SESSION['DataSubFolder'] = $_SESSION['Debug'] ? $debugF : $nonDebugF;
		
		if( !isset( $_SESSION['ID'] ) ) {
			$_SESSION['ID'] = isset($_GET['ID']) ? $_GET['ID'] : rand_string();
		}
		
		if( isset($_GET['Session']) ) {
			$_SESSION['Session'] = trim($_GET['Session']);				// grab session# from URL
		} else {
			$_SESSION['Session'] = 0;
		}
		if( $_SESSION['Session'] < 1 ){								// if session is not set then set to 1
			$_SESSION['Session'] = 1;
		} else { $doDemographics = FALSE; }							// skip demographics for all but session1
		
		$selectedCondition = trim($_GET['Condition']);
		
		
		if($checkElig == TRUE AND $mTurkMode == TRUE) {
			include	'check.php';
		}
		
		#### Checking username is 3 characters or longer
		if(strlen($_GET['Username']) < 3 AND !$_SESSION['Debug'] ) {
			echo '<h1>Error: Login username must be 3 characters or longer</h1>
					<h2>Click <a href="'.$up.'index.php">here</a> to enter a valid username</h2>';
			exit;
		}
		
		
		#### Code to automatically choose condition assignment
		$Conditions	= GetFromFile($up.$expFiles.$conditionsFileName, FALSE);		// Loading conditions info
		$logFile	= $up.$dataF.$countF.$loginCounterName;
		if( $selectedCondition == 'Auto') {
			if( !is_dir( $up.$dataF.$countF ) ) {
				mkdir( $up.$dataF.$countF, 0777, TRUE );
			}
			
			if(file_exists($logFile) ) {											// Read counter file & save value
				$fileHandle	= fopen ($logFile, "r");
				$loginCount	= fgets($fileHandle);
				fclose($fileHandle);
			} else { $loginCount = 1; }
			// write old value + 1 to login counter
			$fileHandle	= fopen($logFile, "w");
			fputs($fileHandle, $loginCount + 1);
			fclose($fileHandle);
			
			$conditionNumber = ( $loginCount % count($Conditions)) +1;				// cycles through current condition assignment based on login counter
		}
		else{
			$conditionNumber = $selectedCondition;									// if condition is manually choosen then honor choice
		}
		
		
		#### loads condition info into $_Session['Condition']
		foreach ($Conditions as $Acond) {
			if($Acond['Number'] == $conditionNumber) {
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
		$errors = keyCheck( $Conditions,	'Number',		$errors,	$conditionsFileName );
		$errors = keyCheck( $Conditions,	'Stimuli',		$errors,	$conditionsFileName );
		$errors = keyCheck( $Conditions,	'Procedure',	$errors,	$conditionsFileName );
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
		$temp = GetFromFile($up.$expFiles.$_SESSION['Condition']['Stimuli']);
		$errors = keyCheck( $temp, 'Cue'	,	$errors, $_SESSION['Condition']['Stimuli'] );
		$errors = keyCheck( $temp, 'Target'	,	$errors, $_SESSION['Condition']['Stimuli'] );
		$errors = keyCheck( $temp, 'Answer'	,	$errors, $_SESSION['Condition']['Stimuli'] );
		$errors = keyCheck( $temp, 'Shuffle',	$errors, $_SESSION['Condition']['Stimuli'] );
		// checking required columns from Procedure file
		$temp = GetFromFile($up.$expFiles.$_SESSION['Condition']['Procedure']);
		$errors = keyCheck( $temp, 'Item'		,	$errors, $_SESSION['Condition']['Procedure'] );
		$errors = keyCheck( $temp, 'Trial Type'	,	$errors, $_SESSION['Condition']['Procedure'] );
		$errors = keyCheck( $temp, 'Timing'		,	$errors, $_SESSION['Condition']['Procedure'] );
		$errors = keyCheck( $temp, 'Post Trial'	,	$errors, $_SESSION['Condition']['Procedure'] );
		$errors = keyCheck( $temp, 'Shuffle'	,	$errors, $_SESSION['Condition']['Procedure'] );
		$temp = null;
		// echo 'Username = '.$_SESSION['Username'].'</br>';											#### DEBUG ####
		// Readable($Conditions, "conditions loaded in");												#### DEBUG ####
		// echo "{$loginCount} logins and should be using condition {$conditionNumber}<br />";			#### DEBUG ####
		// Readable($_SESSION["Condition"],"this is what you're getting for condition:");				#### DEBUG ####
		#### End of error checking
		
		$_SESSION['OutputDelimiter'] = $delimiter;
		
		#### Load all Stimuli and Info for this participant's condition then combine to create the experiment
		if($_SESSION['Session'] == 1) {
			// load and block shuffle stimuli for this condition
			$stimuli = GetFromFile($up.$expFiles.$_SESSION['Condition']['Stimuli']);
			$stimuli = BlockShuffle($stimuli, 'Shuffle');
			
			// Readable($stimuli,'shuffled stimuli *fingers crossed*');							// uncomment this line to see what your shuffled stimuli file looks like
			
			// load and block shuffle procedure for this condition
			$procedure = GetFromFile($up.$expFiles.$_SESSION['Condition']['Procedure']);
			
			$trialTypes = array();
			$trialTypeColumns = array( 'Trial Type' );
			if( isset( $procedure[2]['Post Trial'] ) ) {
				$trialTypeColumns[] = 'Post Trial';
			} elseif( isset( $procedure[2]['Post Trial 1'] ) ) {
				$trialTypeColumns[] = 'Post Trial 1';
			}
			$i = 2;
			while( isset( $procedure[2]['Post Trial '.$i] ) ) {
				$trialTypeColumns[] = 'Post Trial '.$i;
			}
			$notTrials = array( 'off', 'no', '', 'n/a' );
			$notTrials = array_flip( $notTrials );
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
					$sessionFile = $up.$dataF.$_SESSION['DataSubFolder'].$expF.ComputeString( $experimentFileName, $temp ).$outExt;
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
			$sessionFile 			= $up.$dataF.$_SESSION['DataSubFolder'].$expF.ComputeString( $experimentFileName ).$outExt;
			#### ERROR Checking if the session file doesn't exist ####
			if (file_exists($sessionFile) == FALSE) {
				echo "<br/><br/>Could not find your session {$_SESSION['Session']} file at {$sessionFile}";
				exit;
			}
			$openSession			= fopen($sessionFile, 'r');
			$headers				= fgetcsv($openSession,0,"\t");
			foreach( $headers as &$head ) {
				$head = explode( '*', $heaed );								// Response*RT becomes array( 0 => 'Response', 1 => 'RT' )
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
			echo '<b>'.$errors['Count'].' error(s) found in your code!</b> <br/>';						// tell me how many
			foreach ($errors['Details'] as $errorCode) {												// give details of each error
				echo $errorCode;
				echo '<br/>';
			}
			if ($stopForErrors == TRUE) {
				echo '<br/><br/>The program will not run until you have addressed the above errors';
			// show information about $_SESSION['Trials'],
				exit;
			}
		}
		
		
		$outputFile = ComputeString($outputFileName).$outExt;
		$_SESSION['Output File'] = $up.$dataF.$_SESSION['DataSubFolder'].$outputF.$outputFile;
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
		arrayToLine($UserData, $up.$dataF.$_SESSION['DataSubFolder'].$extraDataF.$statusBeginFileName.$outExt);
		###########################################################################
		
		
		#### Establishing $_SESSION['Trials'] as the place where all experiment trials are stored
		// session1 $Trials also contains trials for other sessions but trial.php sends to done.php once a *newfile* shows up
		$_SESSION['Trials']		= $Trials;
		$_SESSION['Position']	= 1;
		$_SESSION['PostNumber'] = -1;
		// Readable($_SESSION['Trials'], '$_SESSION[\'Trials\']');										#### DEBUG ####
		
		
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
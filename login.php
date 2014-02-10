<?php
/*	Collector
	A program for running experiments on the web
	Copyright 2012-2013 Mikey Garcia & Nate Kornell
 */
	ini_set('auto_detect_line_endings', true);				// fixes problems reading files saved on mac
	session_start();										// Start the session at the top of every page
	$_SESSION = array();									// reset session so it doesn't contain any information from a previous login attempt
	@$_SESSION['Debug'] = $_GET['Debug'];
	if ($_SESSION['Debug'] == FALSE) {
		error_reporting(0);
	}
	require("CustomFunctions.php");							// Loads all of my custom PHP functions
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
		echo '<noscript>	<h1>	You must enable javascript to participate!!!	</h1>	</noscript>';
		
		##### Parameters #####			## SET ##
		$_SESSION['ExperimentName']	= "Collector";								// Recorded in datafile and can be useful.
		$_SESSION['LoginCounter Location'] = "LoginCounter/1.txt";				// Change to restart condition cycling
		$_SESSION['Demographics']	= FALSE;									// Can be TRUE or FALSE
		$_SESSION['NextExp']		= FALSE;									// to link use format "www.cogfog.com/Generic/" do not forget the www and the ending "/"
		// post-trial timing values
		$_SESSION['jolTime'] 		= 5;										// in seconds/trial	(JOL) - can also use value 'user'
		$_SESSION['FeedbackTime']	= user;										// in seconds/trial - can also use value 'user'
		$_SESSION['debugTiming']	= 1;										// timing for all trials when in debug mode
		// Mturk Mode settings
		$_SESSION['mTurkMode']		= FALSE;										// use mTurk mode (TRUE) or not (FALSE)
			$_SESSION['verifCode']	= 'boom goes the dynamite';					// code that shows on done.php
			$_SESSION['checkPrev']	= TRUE;										// use files in eligibility/ folder to check past participation 	
		##### Parameters END #####
		
		
		##### Error Checking Code ####
		$found	= FALSE;														// will use this later to determine when loops fail
		$errors	= array('Count' => 0, 'Details' =>array()  );					// the array to keep count and details of errors
		if (file_exists("Conditions.txt") == FALSE):							// does conditions exist? (error checking)
			$errors['Count']++;
			$errors['Details'][] = 'No "Conditions.txt" found';
		endif;
		
		
		#### Grabbing submitted info
		$_SESSION['Username']	= trim($_GET['Username']);			// grab Username from URL
		$_SESSION['Session']	= trim($_GET['Session']);			// grab session# from URL
		if( $_SESSION['Session'] < 1 ){								// if session is not set then set to 1
			$_SESSION['Session'] = 1;
		}
		else { $_SESSION['Demographics'] = FALSE; }					// skip demographics for all but session1
		$selectedCondition = trim($_GET['Condition']);
		
		
		#### Checking username is 3 characters or longer
		if(strlen($_SESSION['Username']) < 3) {
			echo '<h1>Error: Login username must be 3 characters or longer</h1>
					<h2>Click <a href="index.php">here</a> to enter a valid username</h2>';
			exit;
		}
		
		if($_SESSION['checkPrev'] == TRUE AND $_SESSION['mTurkMode'] == TRUE) {
			include	'check.php';
		}
		
		
		#### Code to automatically choose condition assignment
		$Conditions	=  GetFromFile("Conditions.txt", FALSE);		// Loading conditions info
		$logFile	=& $_SESSION["LoginCounter Location"];
		if( $selectedCondition == 'Auto') {
			
			if(file_exists($logFile) ) {							// Read counter file & save value
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
			$errors['Details'][] = 'Could not find the selected condition #'.$conditionNumber.' in Conditions.txt';
		}
		// does this condition point to a valid stimuli file?
		if (file_exists($_SESSION['Condition']['Stimuli']) == FALSE) {
			$errors['Count']++;
			$errors['Details'][] = 'No stimuli file found at '.$_SESSION['Condition']['Stimuli'];
		}
		// does this condition point to a valid procedure file?
		if (file_exists($_SESSION['Condition']['Procedure']) == FALSE) {
			$errors['Count']++;
			$errors['Details'][] = 'No procedure file found at '.$_SESSION['Condition']['Procedure'];
		}
		// checking required columns from Stimuli file
		$temp = GetFromFile($_SESSION['Condition']['Stimuli']);
		$errors = keyCheck( $temp, 'Cue'	,	$errors, $_SESSION['Condition']['Stimuli'] );
		$errors = keyCheck( $temp, 'Target'	,	$errors, $_SESSION['Condition']['Stimuli'] );
		$errors = keyCheck( $temp, 'Answer'	,	$errors, $_SESSION['Condition']['Stimuli'] );
		$errors = keyCheck( $temp, 'Shuffle',	$errors, $_SESSION['Condition']['Stimuli'] );
		// checking required columns from Procedure file
		$temp = GetFromFile($_SESSION['Condition']['Procedure']);
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
		
		
		#### Record info about the person starting the experiment to StatusFile.txt
		// information about the user loging in
		$UserData = array(
							$_SESSION['Username'] ,
							date('c') ,
							"Session " . $_SESSION['Session'] ,
							"Session Start" ,
							"Condition# {$_SESSION['Condition']['Number']}",
							$_SESSION['Condition']['Stimuli'],
							$_SESSION['Condition']['Procedure'],
							$_SESSION['Condition']['Condition Description'],
							$_SERVER['HTTP_USER_AGENT'],
							$_SERVER["REMOTE_ADDR"],
							'N/A'
						 );
		// header row for the Status File
		$UserDataHeader = array(
							'Username' ,
							'Date' ,
							'Session #' ,
							'Begin/End?' ,
							'Condition #',
							'Words File',
							'Order File',
							'Condition Description',
							'User Agent Info', 
							'IP',
							'Inclusion Notes'
						 );
		// if the file doesn't exist, write the header
	 	if (is_file("subjects/Status.txt") == FALSE) {
	 		arrayToLine ($UserDataHeader, "subjects/Status.txt");
	 	}
		arrayToLine ($UserData, "subjects/Status.txt");						// write $UserData to "subjects/Status.txt"
		###########################################################################
		
		
		#### Load all Stimuli and Info for this participant's condition then combine to create the experiment
		if($_SESSION['Session'] == 1) {
			// load and block shuffle stimuli for this condition
			$stimuli = GetFromFile($_SESSION['Condition']['Stimuli']);
			$stimuli = BlockShuffle($stimuli, "Shuffle");
			
			// Readable($stimuli,'shuffled stimuli *fingers crossed*');							// uncomment this line to see what your shuffled stimuli file looks like
			
			// load and block shuffle procedure for this condition
			$procedure = GetFromFile($_SESSION['Condition']['Procedure']);
			$procedure = BlockShuffle($procedure, "Shuffle");
						
			// Load entire experiment into $Trials[1-X] where X is the number of trials
			$Trials = array(0=> 0);
			for ($count=2; $count<count($procedure); $count++) {
				$Trials[$count-1]['Stimuli']	= $stimuli[ ($procedure[$count]['Item']) ];			// adding 'Stimuli', as an array, to each position of $Trials
				$Trials[$count-1]['Procedure']	= $procedure[$count];								// adding 'Procedure', as an array, to each position of $Trials
				$Trials[$count-1]['Response']	= array(	"Response1"		=> NULL,			// adding 'Response', as an array, to each position of $Trials
															"RT"			=> NULL,
															"RTkey"			=> NULL,
															"RTlast"		=> NULL,
															"strictAcc"		=> NULL,
															"lenientAcc"	=> NULL,
															"Accuracy"		=> NULL,
															"JOL"			=> NULL,
															"postRT"		=> NULL,
															"postRTkey"		=> NULL,
															## ADD ## if you're going to collect any responses you need to create the response placeholder here
														);
															
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
			
			// determine stimuli headers
			$example = $Trials[1];
			$header1 = array();
			$header2 = array();
			foreach($example as $key => $array) {
				foreach ($array as $subKey => $value) {
					$header1[] = $key;
					$header2[] = $subKey;
				}
			}
			// will use these later to record data
			$_SESSION['Header1'] = $header1;
			$_SESSION['Header2'] = $header2;
			
			######## Go through $Trials and write session file(s)
			// session files go into subjects folder and will be formatted as Username_Session1_StimuliFile.txt
			$fileNumber		= 1;
			$foreachcount	= 1;
			foreach ($Trials as $Trial) {
				if($foreachcount == 1) {
					$foreachcount++;
					continue;
				}
				// write to next file when we hit a newfile line
				$item = strtolower(trim($Trial['Procedure']['Item']));
				if($item == '*newfile*') {
					$fileNumber++;
					continue;
				}
				
				// if file doesn't exist then write the 2 header lines
				$sessionFile = 'subjects/'.$_SESSION['Username'].'_Session'.$fileNumber.'_StimuliFile.txt';
				if(is_file($sessionFile) == FALSE) {
					arrayToLine ($header1, $sessionFile);
					arrayToLine ($header2, $sessionFile);
				}
				#### TO DO #### write code that removes junk characters from session files (see Next.php)
				// write ['Stimuli'] ['Procedure'] and ['Response'] data to next line of the file
				$line = NULL;
				$junk = array( '\n' , '\t' , '\r' , chr(10) , chr(13) );
				for($i= 0; $i < count($header1); $i++) {
					$replaced = str_replace($junk, '<br /', $Trial[$header1[$i]] [$header2[$i]]);
					$line[] = $replaced;
				}
				arrayToLine($line,$sessionFile);
			
			}
		}
		#### Loading up $Trials for multisession experiments
		else {
			// Load headers from correct stimuli files	
			$fileNumber				= $_SESSION['Session'];
			$sessionFile			= 'subjects/'.$_SESSION['Username'].'_Session'.$fileNumber.'_StimuliFile.txt';
			$openSession			= fopen($sessionFile, 'r');
			$header1				= fgetcsv($openSession,0,"\t");
			$header2				= fgetcsv($openSession,0,"\t");
			$_SESSION['Header1']	= $header1;
			$_SESSION['Header2']	= $header2;
			
			// Loading up $Trials for this Username and Session
			$Trials		= array();
			$Trials[0]	= NULL;
			$tPos		= 0;
			while($line = fgetcsv($openSession,0,"\t")) {
				$tPos++;
				for($i=0; $i < count($line)-1; $i++) {
					$Trials[$tPos][$header1[$i]][$header2[$i]] = $line[$i];
				}
			}
			#### ERROR Checking if the session file doesn't exist ####
			if (file_exists($sessionFile) == FALSE) {
				echo "<br/><br/>Could not find your session {$_SESSION['Session']} file at {$sessionFile}";
				exit;
			}
		}
		// readable($header1,'header top');																#### DEBUG ####
		// readable($header2,'header 2nd');																#### DEBUG ####
		
		
		#### Establishing $_SESSION['Trials'] as the place where all experiment trials are stored
		// session1 $Trials also contains trials for other sessions but trial.php sends to done.php once a *newfile* shows up
		$_SESSION['Trials']		= $Trials;
		$_SESSION['Position']	= 1;
		// Readable($_SESSION['Trials'], '$_SESSION[\'Trials\']');										#### DEBUG ####
		
		
		#### Output errors & Stop progression
		if ($errors['Count'] > 0 AND $_SESSION['Session'] == 1 ) {										// if there is an error (only stops for session 1 errors)
			echo '<b>'.$errors['Count'].' error(s) found in your code!</b> <br/>';						// tell me how many
			foreach ($errors['Details'] as $errorCode) {												// give details of each error
				echo $errorCode;
				echo '<br/>';
			}
		echo '<br/><br/>The program will not run until you have addressed the above errors';
		exit;																							//  ## SET ## if you want to program to run when an error is hit then comment out this line of code
		}
		
		
		#### Send participant to next phase of experiment (demographics or trial.php)
		if($_SESSION['Demographics'] == TRUE) {
			$link = 'BasicInfo.php';
		}
		else {
			$link = 'instructions.php';
		}
		echo '<form id="loadingForm" action="'.$link.'" method="get"> </form>';							// commenting this line out will stop experiment from progressing past login.php (good to check diagnostics)
		
	?>
	<script src="http://code.jquery.com/jquery-1.8.0.min.js" type="text/javascript"> </script>
	<script src="javascript/jsCode.js" type="text/javascript"> </script>
</body>
</html>
<?php
// fixes problems reading files saved on mac
ini_set('auto_detect_line_endings', true);
// start the session at the top of each page
session_start();
if ($_SESSION['Debug'] == FALSE) {
	error_reporting(0);
}
require("CustomFunctions.php");						// Loads all of my custom PHP functions
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link href="css/global.css" rel="stylesheet" type="text/css" />
	<link href='http://fonts.googleapis.com/css?family=Kreon' rel='stylesheet' type='text/css' />
	<title>Test/Presentation</title>
</head>

<body>

<?php
	#### setting up aliases (for later use)
	$currentPos =& $_SESSION['Position'];
	$currentTrial =& $_SESSION['Trials'][$currentPos];
		$cue =& $_SESSION['Trials'][$currentPos]['Stimuli']['Cue'];
		$target =& $_SESSION['Trials'][$currentPos]['Stimuli']['Target'];
		$answer = $_SESSION['Trials'][$currentPos]['Stimuli']['Answer'];
		$trialType = trim(strtolower($_SESSION['Trials'][$currentPos]['Info']['Trial Type']));
		$feedback = trim(strtolower($currentTrial['Info']['Feedback']));
	$time = $_SESSION['FeedbackTime'];
	
	### getting response and making cleaned up versions (for later comparisons)
	$response1 = $_POST['Response'];
	$responseClean = trim(strtolower($response1));
	$answerClean = trim(strtolower($answer));
	$Acc = NULL;
	
	#### Saving data into $_Session
	$currentTrial['Response']['Response1'] = $_POST['Response'];
	$currentTrial['Response']['RT'] = $_POST['RT'];
	
	#### Calculating and saving accuracy for trials with responses given
	if( ($trialType == 'test') OR 	($trialType == 'testpic') OR
		($trialType == 'copy') OR	($trialType == 'freerecall') OR
		($trialType == 'jol') ) {
			
		$currentTrial['Response']['RTkey'] = $_POST['RTkey'];
		$currentTrial['Response']['RTkey'] = $_POST['RTlast'];
		
		
		if(($trialType != 'jol') && ($trialType != 'freerecall')) {
			similar_text($responseClean, $answerClean, $Acc);
			$currentTrial['Response']['Accuracy'] = $Acc;
			if($Acc == 100) {
				$currentTrial['Response']['strictAcc'] = 1;
			} else {	$currentTrial['Response']['strictAcc'] = 0;	}
			if($Acc >= 75) {											## SET ## determines the % match required to count an answer as 1(correct) or 0(incorrect)
				$currentTrial['Response']['lenientAcc'] = 1;
			} else {	$currentTrial['Response']['lenientAcc'] = 0;	}
		}
	}
	
	#### Writing to data file
	$fileName = 'subjects/Output_Session'.$_SESSION['Session'].'_'.$_SESSION['Username'].'.txt';
	$add = array(		$_SESSION['Username'],
						$_SESSION['ExperimentName'],
						$_SESSION['Session'],
						$_SESSION['Position'],
						date("c"),
						$_SESSION['Condition']['Number'],
						$_SESSION['Condition']['Stimuli'],
						$_SESSION['Condition']['Order'],
						$_SESSION['Condition']['Condition Description'],
						$_SESSION['Condition']['Condition Notes'],
					);
	$addHeader = array(	'Username',
						'ExperimentName',
						'Session',
						'Trial',
						'Date',
						'Condition Number',
						'Stimuli File',
						'Order File',
						'Condition Description',
						'Condition Notes',
					);
	
	// does the output file exist?
	// if not then write header lines
 	if (is_file($fileName) == FALSE) {
		$Header1 = $_SESSION['Header1'];
		$Header2 = $_SESSION['Header2'];
		for($i=count($addHeader)-1; $i >=0; $i--) {
			// add blanks to beginning of $Header1
			array_unshift($Header1,"");
			// add column names to beginning of $Header2
			array_unshift($Header2,$addHeader[$i]);
		}
		// arrayToLine($Header1,$fileName);
		// arrayToLine($Header2,$fileName);
		
		// combine header info into 1 line
		$combinedHeader = array();
		for($i=0; $i<count($Header1); $i++) {
			$combinedHeader[] = $Header1[$i].'*'.$Header2[$i];
		}
		
		arrayToLine($combinedHeader,$fileName);
	}
	// write line of data
	$Header1 =& $_SESSION['Header1'];
	$Header2 =& $_SESSION['Header2'];
	$data = array();
	foreach ($add as $value) {
		$data[] = $value;
	}
	$junk = array('\n','\t','\r',chr(10),chr(13));
	for($pos=0; $pos<count($Header1); $pos++) {
		// from Nate:  replaces returns (which are a 13 and a 10, I guess) with spaces
		$dataBit = str_replace($junk,' <br /> ', $currentTrial[$Header1[$pos]][$Header2[$pos]]);
		$data[] = $dataBit;
	}
	arrayToLine($data,$fileName);
	###########################################
	
	
	#### Showing the feedback
	if($feedback == 'yes') {
		echo '<div class="Feedback">
				<div class="gray">The correct answer was:</div>
					<span>' . show($answer).'</span>
			  </div>';
	}
	// echo $_POST['Response'].'<br />';									#### DEBUG ####
	// echo $_POST['RT'].'<br />';											#### DEBUG ####
	
	
	// progresses the trial counter
	$currentPos++;

	// if showing feedback use feedback time or else use 0
	if($feedback == 'yes'){
		echo '<meta http-equiv="refresh" content="'.$time.'; url=test.php">';						// comment out this line to stop feedback from auto advancing
	}
	else {
		echo '<meta http-equiv="refresh" content="0; url=test.php">';
	}
	
	// echo '<a href="test.php".">Click Here to continue</a>';										// uncomment to let participants continue at their own pace
?>
</body>
</html>
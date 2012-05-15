<?php
	ini_set('auto_detect_line_endings', true);				// fixes problems reading files saved on mac
	session_start();										// start the session at the top of each page
	if ($_SESSION['Debug'] == FALSE) {
		error_reporting(0);
	}
	require("CustomFunctions.php");							// Loads all of my custom PHP functions
	
	
	#### setting up aliases (for later use)
	$currentPos		=& $_SESSION['Position'];
	$currentTrial	=& $_SESSION['Trials'][$currentPos];
		$cue		=& $currentTrial['Stimuli']['Cue'];
		$target		=& $currentTrial['Stimuli']['Target'];
		$answer		=  $currentTrial['Stimuli']['Answer'];
		$trialType	=  trim(strtolower($currentTrial['Info']['Trial Type']));
		$postTrial	=  trim(strtolower($currentTrial['Info']['Post Trial']));
		$time		=  $_SESSION['FeedbackTime'];
	
	
	### getting response and making cleaned up versions (for later comparisons)
	$response1		= $_POST['Response'];
	$responseClean	= trim(strtolower($response1));
	$answerClean	= trim(strtolower($answer));
	$Acc			= NULL;
	
	
	#### Saving data into $_SESSION
	$currentTrial['Response']['Response1']	= $_POST['Response'];
	$currentTrial['Response']['RT']			= $_POST['RT'];
	@$currentTrial['Response']['RTkey']		= $_POST['RTkey'];
	@$currentTrial['Response']['RTlast']	= $_POST['RTlast'];
	
	
	#### saving past responses for later (you're right) feedbck
	// create session if it isn't created yet
	if(isset($_SESSION['PastResponse']) == FALSE) {
		$_SESSION['PastResponse'] = array();
	}
	// if it's a you're right trial then save response to pastresponse array
	if(	$trialType == 'test' &&
		$currentTrial['Info']['Phase'] == 'Study Phase' &&
		$currentTrial['Info']['Order Notes'] == 'right') {
		
		$_SESSION['PastResponse'][$cue] = $_POST['Response'];
		$answerClean	= trim(strtolower($_POST['Response']));
		$responseClean	= trim(strtolower($_POST['Response']));
	}
	// if this is a you're right trial then set answer to your previous response
	if(isset($_SESSION['PastResponse'][$cue])) {
		
	}
	
	
	#### Calculating and saving accuracy for trials in  which this would be appropriate (excluding JOL and FreeRecall)
	if( ($trialType == 'test')	OR 	($trialType == 'testpic') OR
		($trialType == 'copy')	OR	($trialType == 'mcpic') ) {
		// determining similarity
		similar_text($responseClean, $answerClean, $Acc);
		$currentTrial['Response']['Accuracy'] = $Acc;
		// scoring and saving
		if($Acc == 100):
			$currentTrial['Response']['strictAcc'] = 1;
		else:
			$currentTrial['Response']['strictAcc'] = 0;
		endif;
		if($Acc >= 75):												## SET ## determines the % match required to count an answer as 1(correct) or 0(incorrect)
			$currentTrial['Response']['lenientAcc'] = 1;
		else:
			$currentTrial['Response']['lenientAcc'] = 0;
		endif;
	}
		

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
	#### trial timing code
	if($postTrial == 'feedback') {
		$time = $_SESSION['FeedbackTime'];
	}
	elseif ($postTrial == 'jol') {
		$time = $_SESSION['jolTime'];
	}
	// hidden field that JQuery/JS uses to submit the trial to next.php
	echo '<div id="Time" class="Hidden">' . $time . '</div>';
	
	// changing form classname based on user or computer timing.  I use the classname to do JQuerty magic
	if($time == 'user'):
		$formName = 'UserTiming';
	else:
		$formName = 'ComputerTiming';
	endif;
	
	#### Showing feedback
	if($postTrial == 'feedback') {
		echo '<div class="Feedback">
				<div class="gray">The correct answer was:</div>
					<span>' . show($answer).'</span>
			  </div>';
		// Hidden form that collects RT and progresses trial to next.php
		echo '<form name="'.$formName.'" class="'.$formName.'" action="next.php" method="post">
				<input class="RT Hidden" name="RT" type="text" value="RT" />
				<input type="submit" id="FormSubmitButton" value="Submit">
			  </form>';
	}
	elseif ($postTrial == 'jol') {
		echo '<div id="jol">How likely are you to correctly remember this item on a later test?</div>
			  <div id="subpoint" class="gray">Type your response on a scale from 0-100 using the entire range of the scale</div>';
			
			echo '<form name="'.$formName.'" class="'.$formName.'" action="next.php" method="post">
					<input class="Textbox"		name="JOL"		type="text" value=""/><br />
					<input class="RT Hidden"	name="RT"		type="text" value="RT" />
					<input class="RTkey Hidden" name="RTkey"	type="text" value="RTkey" />
					<input type="submit" id="FormSubmitButton" value="Submit">
				  </form>';
	}
	else {
		echo '<meta http-equiv="refresh" content="0; url=next.php">';
	}
	// echo $_POST['Response'].'<br />';									#### DEBUG ####
	// echo $_POST['RT'].'<br />';											#### DEBUG ####
	
	
	// // if showing feedback use feedback time or else use 0
	// if($postTrial == 'feedback'){
		// echo '<meta http-equiv="refresh" content="'.$time.'; url=next.php">';						// comment out this line to stop feedback from auto advancing
	// }
	// else {
		// echo '<meta http-equiv="refresh" content="0; url=next.php">';
	// }
	
	// echo '<a href="next.php".">Click Here to continue</a>';										// uncomment to let participants continue at their own pace
?>
	<script src="javascript/jquery-1.7.2.min.js" type="text/javascript"> </script>
	<script src="javascript/test.js" type="text/javascript"> </script>
</body>
</html>
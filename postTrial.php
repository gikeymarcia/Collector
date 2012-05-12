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
	if($postTrial == 'no') {
		echo "this is working <br />";
		echo '<meta http-equiv="refresh" content="0; url=next.php">';
		exit;
	}
	
	
	#### Showing the feedback
	if($postTrial == 'feedback') {
		echo '<div class="Feedback">
				<div class="gray">The correct answer was:</div>
					<span>' . show($answer).'</span>
			  </div>';
	}
	// echo $_POST['Response'].'<br />';									#### DEBUG ####
	// echo $_POST['RT'].'<br />';											#### DEBUG ####
	
	
	// if showing feedback use feedback time or else use 0
	if($postTrial == 'feedback'){
		echo '<meta http-equiv="refresh" content="'.$time.'; url=next.php">';						// comment out this line to stop feedback from auto advancing
	}
	else {
		echo '<meta http-equiv="refresh" content="0; url=next.php">';
	}
	
	// echo '<a href="next.php".">Click Here to continue</a>';										// uncomment to let participants continue at their own pace
?>
</body>
</html>
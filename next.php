<?php
	session_start();
	#### setting up aliases (for later use)
	$currentPos		=& $_SESSION['Position'];
	$currentTrial	=& $_SESSION['Trials'][$currentPos];
		$cue		=& $currentTrial['Stimuli']['Cue'];
		$target		=& $currentTrial['Stimuli']['Target'];
		$answer		=  $currentTrial['Stimuli']['Answer'];
		$trialType	=  trim(strtolower($currentTrial['Info']['Trial Type']));
		$feedback	=  trim(strtolower($currentTrial['Info']['Feedback']));
		$time		=  $_SESSION['FeedbackTime'];
    
    // progresses the trial counter
	$currentPos++;
	
	header("Location: test.php");
?>
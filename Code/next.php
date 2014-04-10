<?php
/*	Collector
	A program for running experiments on the web
	Copyright 2012-2014 Mikey Garcia & Nate Kornell
 */
	require 'fileLocations.php';							// sends file to the right place
	require $up.$expFiles.'Settings.php';					// experiment variables
	require 'CustomFunctions.php';							// Load custom PHP functions
	initiateCollector();
	
	#### setting up aliases (for later use)
	$currentPos		=& $_SESSION['Position'];
	$currentTrial	=& $_SESSION['Trials'][$currentPos];
		$cue		=& $currentTrial['Stimuli']['Cue'];
		$target		=& $currentTrial['Stimuli']['Target'];
		$answer		=  $currentTrial['Stimuli']['Answer'];
		$trialType	=  trim(strtolower($currentTrial['Procedure']['Trial Type']));
	
	
	#### grabbing responses from postTrial
	@$currentTrial['Response']['JOL']		= $_POST['JOL'];
	@$currentTrial['Response']['postRT']	= $_POST['RT'];
	@$currentTrial['Response']['postRTkey']	= $_POST['RTkey'];
	## ADD ## if you've made a new post-trial type that collects data then you need to record that data into $currentTrial['Response']['whatever name']
	
	
	#### Calculating time difference from current to last trial
	$oldTime = $_SESSION['Timestamp'];
	$_SESSION['Timestamp'] = microtime(TRUE);
	$timeDif = $_SESSION['Timestamp'] - $oldTime;
	
	
	#### Writing to data file
	$data = array(	'Username'				=> $_SESSION['Username'],
					'ID'					=> $_SESSION['ID'],
					'ExperimentName' 		=> $experimentName,
					'Session' 				=> $_SESSION['Session'],
					'Trial' 				=> $_SESSION['Position'],
					'Date' 					=> date("c"),
					'TimeDif' 				=> $timeDif,
					'Condition Number' 		=> $_SESSION['Condition']['Number'],
					'Stimuli File' 			=> $_SESSION['Condition']['Stimuli'],
					'Order File' 			=> $_SESSION['Condition']['Procedure'],
					'Condition Description' => $_SESSION['Condition']['Condition Description'],
					'Condition Notes' 		=> $_SESSION['Condition']['Condition Notes']
				);
	foreach( $currentTrial as $category => $array ) {
		$data += AddPrefixToArray( $category.'*', $array );
	}
	arrayToLine($data,$_SESSION['Output File']);									// write data line to the file
	###########################################
	
	
    // progresses the trial counter
	$currentPos++;
	
	// are we done with all presentations? if so, send to finalQuestions.php
	if (array_key_exists($currentPos, $_SESSION['Trials']) == FALSE) {
		$_SESSION['finishedTrials'] = TRUE;											// stops people from skipping to the end
		header("Location: FinalQuestions.php");
		exit;
	}
	
	// redirects the page to trial.php after running all of the above code
	header("Location: trial.php");
	exit;
?>
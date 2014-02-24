<?php
/*	Collector
	A program for running experiments on the web
	Copyright 2012-2014 Mikey Garcia & Nate Kornell
 */
 	#### setting experiment variables ####
 	$experimentName		= 'Collector';			// Recorded in datafile and can be useful
	$loginCounterName	= '1.txt';				// Change to restart condition cycling
	$doDemographics		= FALSE;				// Can be TRUE or FALSE
	$nextExperiment		= FALSE;				// to link use format "www.cogfog.com/Generic/" do not forget the www and the ending "/"
 	
 	// debugging functionality
 	$debugMode = FALSE;							// Can be `TRUE` or `FALSE` (without ticks)
	$debugTime = 1;								// trial length (in seconds) when in debug mode
	$trialDiagnostics	= FALSE;				// show trial diagnostics? `TRUE` or `FALSE`
	
	//mTurk Mode
	$mTurkMode		= FALSE;					// turn on mTurkMode? `TRUE` or `FALSE` (without ticks)
	$verifcation	= 'Shinebox';				// code that shows on done.php
	$checkElig		= TRUE;						// use files in eligibility/ folder to check past participation 
	
	
	
	// post-trial timing values
	$jolTime		= 5;						// in seconds/trial	(JOL) - can also use value 'user'
	$feedbackTime	= 'user';					// in seconds/trial - can also use value 'user'
	
	// index.php
	$showConditionSelector = TRUE;				// Show (TRUE) or hide (FALSE) the condition selector at login?
	$expDescription	= '<p>This experiment will run for approximately 25 minutes.  Your goal is to learn some information</p>';
	$askForLogin	= '<p> Please enter your mTurk worker ID below </p>';
	
	// logging in settings
	$stopForErrors = TRUE;						// stop experiment progression if errors are found at login? `TRUE` or `FALSE`
	
	// scoring settings
	$lenientCriteria = 75;						// determines the % match required to count an answer as 1(correct) or 0(incorrect)
	
	// trial settings
	$MCitemsPerRow		= 4;					// sets how many items per row when using MCpic trials (use values 1-4; anything bigger causes problems which require css changes
	$MultiChoiceButtons  = array( "Cat1", "Cat2", "Cat3", "Cat4", "Cat6", "Cat6", "Cat7", "Cat 8", "Cat9", "Cat10", "Cat11", "Cat 12");
	
	// done.php
	$experimenterEmail = 'gikeymarcia@gmail.com';
?>
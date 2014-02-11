<?php
/*	Collector
	A program for running experiments on the web
	Copyright 2012-2013 Mikey Garcia & Nate Kornell
 */
	require 'CustomFunctions.php';							// Load custom PHP functions
	require 'fileLocations.php';							// sends file to the right place
	initiateCollector();
	
	
	// setting up aliases for later use
	$allFQs		=& $_SESSION['FinalQs'];
	$pos		=& $_SESSION['FQpos'];
	$FQ			=& $allFQs[$pos];						// all info about current final question
	
	$readablePos = $pos -1;
	
	
	// capture data
	$formData	= $_POST['formData'];
	$RT			= $_POST['RT'];
	
	
	//write header if file doesn't exist
	$fileName = $up.$dataF.'FinalQuestionsData.txt';
	if (is_file($fileName) == FALSE) {
		$header = array(	'Username',
							'FinalQuestions',
							'Trial',
							'Question',
							'Type',
							'RT',
							'Response');
	arrayToLine($header,$fileName);
	}
	
	// writing each selection to a line of data (for checkbox trials where more than one selection can be made)
	if(is_array($formData)) {
		foreach ($formData as $checked) {
			$data = array(	$_SESSION['Username'],
							'FinalQuestions',
							$readablePos,
							$FQ['Question'],
							$FQ['Type'],
							$RT,
							$checked);
			arrayToLine($data,$fileName);
		}
	}
	
	// writing form data to txt file (non-checkbox trials)
	else {
		$data = array(	$_SESSION['Username'],
						'FinalQuestions',
						$readablePos,
						$FQ['Question'],
						$FQ['Type'],
						$RT,
						$formData);
		arrayToLine($data,$fileName);
	}
	
	// advance counter before sending back to final questions
	$pos++;
	
	// these two lines redirect the page to FinalQuestions.php before any HTML is sent
	header("Location: FinalQuestions.php");
	exit;
?>
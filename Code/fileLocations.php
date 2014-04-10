<?php
/*	Collector
	A program for running experiments on the web
	Copyright 2012-2014 Mikey Garcia & Nate Kornell
 */
 	#### setting file locations ####
	
	#Code
	$up			= '../';
	$dataF		= 'Data/';
	$codeF		= 'Code/';
	$trialF		= 'TrialTypes/';
	
	#Experiment
	$expFiles 	= 'Experiment/';			// hard coded into show()
	$eligF 		= 'Eligibility/';
	$imageF 	= 'Images/';
	$audioF 	= 'Audio/';
	
	#Data
	$countF		= 'Counter/';
	$extraDataF = '';						// these are files like demographics and finalquestions
	$outputF 	= 'Output/';
	$expF 		= 'ExperimentFiles/';
	$debugF 	= 'Debug/';
	$nonDebugF 	= '';
	
	
	
	#### file names ####
	
	#Code
	$scoring 	= 'scoring.php';
	
	#Experiment
	$conditionsFileName 	= 'Conditions.txt';
	$finalQuestionsFileName = 'FinalQuestions.txt';
	$instructionsFileName 	= 'instructions.php';		// this file could store the question and possible answers, and leave the code to Code/instructions.php
	/**
	 * Future files to name
	 *
	 * $basicInfoFileName 		= ? just like finalQuestions, except this could later include a column to determine eligibility by demographics responses
	 * $settingsFileName 		= ? could make this a .ini file in the future
	 */
	
	#Output
	$outExt = '.txt';							// can be either .csv or .txt
	$delimiter = "\t";							// either "," or "\t"
	
	$demographicsFileName 		= 'Demographics';
	$statusBeginFileName 		= 'Status_Begin';
	$statusEndFileName 			= 'Status_End';
	$finalQuestionsDataFileName = 'FinalQuestionsData';
	$instructionsDataFileName 	= 'InstructionsData';
	
	/**
	 * You can use variables in the file names below.  Variables must be followed by an underscore_.
	 * Here are a list of available variables.  Keep in mind that certain variables, like Condition Description, might be a bit long for a file name.
	 * 
	 * $Username
	 * $ID
	 * $Session
	 * $Condition[Condition Number]
	 * $Condition[Condition Description]
	 * $Condition[Condition Notes]
	 */
	 
	$outputFileName 	= 'Output_Session$Session_$Username_$ID';
	$experimentFileName = '$Username_Session$Session_Experiment';
	
?>
<?php
/*	Collector
	A program for running experiments on the web
	Copyright 2012-2014 Mikey Garcia & Nate Kornell
 */
 	#### setting file locations ####
	$up		= '../';
	$dataF	= 'Data/';
	$codeF	= 'Code/';
	$expFiles = 'Experiment/';			// hard coded into show()
	$stimF	= 'Stimuli/';
	$countF	= 'Counter/';
	$trialF	= 'TrialTypes/';
	$scoring = 'scoring.php';

    #### global location helpers --- these work locally so you can use them in Header redirects ###
    define('DS', DIRECTORY_SEPARATOR);
    define('ROOT', dirname(dirname(__FILE__)));
?>
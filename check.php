<?php
/*	Collector
	A program for running experiments on the web
	Copyright 2012-2013 Mikey Garcia & Nate Kornell
 */
 	if(!isset($_SESSION)) {
 		include 'CustomFunctions.php';
 	}
 	#### variables needed for this page
	$folder	= "eligibility/";						// where to look for files containing workers
	$files	= scandir($folder);						// list all files containing workers
	$toCheck = null;								// who to check for eligibility
	$checked = array();								// list of all the files that were checked
	$current = array();								// each file will be loaded into this array
	$uniques = array();								// all the unique workers (no duplicates)
	$noGo	 = array();								// reasons to exclude someone from participation
	$ip = $_SERVER["REMOTE_ADDR"];					// user's ip address
	$ipFilename = 'rejected-IP.txt';				// name of bad IP file
	$ipPath = $folder.$ipFilename;					// path to bad IP file
	
	#### make a master list of unique user IDs (lowercase and trimmed)
	foreach ($files as $file) {						// check all files
		
		// set correct delimiter and skip incorrect filetypes
		if (inString('.txt', $file)) {
			$delimiter = "\t";
		} elseif (inString('.csv', $file)) {
			$delimiter = ',';
		} else { continue; }
		
		if($file == $ipFilename) {									// skip reading IP file
			continue;
		}
		$current = array();											// clear data from current file before loading next one
		$current = GetFromFile($folder.$file, FALSE, $delimiter);	// read a file containing workers
		$checked[] = $folder.$file;									// keep track of which files we've checked
		foreach ($current as $worker) {
			if(!in_array($worker['WorkerId'], $uniques)) {
				$uniques[] = trim(strtolower($worker['WorkerId']));
			}
		}
	}
		
	
	#### show prompt if going to to this while not logged in
	if(isset($_SESSION)) {										// if there is a session initiated already
		$toCheck = $_SESSION['Username'];						// use username if logged in
	} else {
		echo '<form method="POST" action="">
				<p> Whose eligibility would you like to check? <br/>
				<em>Checking from '.count($uniques).' workers within '.count($checked).' files</em>  </p>
				<input type="text" name="worker" class="eCheck" />
				<input type="submit" value="Eligible?" />
			  </form>';
		if(isset($_POST['worker'])) {
			$toCheck = $_POST['worker'];
		}
	}
	
	
	
	#### running checks
	if(isset($toCheck)) {										// if there is something to check then check it
		$noCaseCheck = trim(strtolower($toCheck));				// all lowercase version of ID to check
				
		####  check if we've already told this person not to come back (BOOM, headshot)
		if(isset($_SESSION) AND file_exists($ipPath)) {			// check IPs if logged in and there is a badIP file
			$badIPs = GetFromFile($ipPath, FALSE);
			foreach ($badIPs as $rejected) {
				if($ip == $rejected['ip address']) {
					$noGo[] = 'Sorry, you are not allowed to login to this experiment more than once.';
				}
			}
		}
		
		
		
		#### check if this user has previously participated
		if(in_array($noCaseCheck, $uniques)) {
			$noGo[] = 'Sorry, you are not eligible to participate in this study 
					   because you have participated in a previous version of this experiment before.';
			// log their IP to stop them from logging in again
			if(!is_file($ipPath)) {
				$ipFile = fopen($ipPath, 'a');
				fputs($ipFile, 'ip address');					// write header
				fputs($ipFile, PHP_EOL);						// write newline character
				fputs($ipFile, $ip);							// write IP to file
				fputs($ipFile, PHP_EOL);						// write newline character
			} else {
				$ipFile = fopen($ipPath, 'a');
				fputs($ipFile, $ip);							// write IP to file
				fputs($ipFile, PHP_EOL);						// write newline character
			}
			
		}
		rejectCheck($noGo);									// print errors
	}
	
	#### check if this user has previously logged in
		/*
		 * This will be completed once I finish updating some other functionality
		 * 
		 * Planed functionality once completed:
		 * 	 if a user tries to login and has not been rejected for IP or previous involvment
		 *   then this function will load up user session, figure out where they last were, 
		 *   reload all stimuli, and continue experiment.
		 * 
		 *   users who are restarted in this way should be denoted as special cases in status.txt
		 */
	
	
	if(count($noGo) == 0 AND isset($toCheck) AND !isset($_SESSION)) {
		echo '<h2>User <b>'.$toCheck.'</b> is eligible to participate</h2>';
	}
	// show all users to people who want to login
	if(!isset($_SESSION)) {
		Readable($files, 'Files in directory');
		Readable($uniques, 'Previous iteration workers');
	}
	
	
	#### functions and scripting needed to make this page work
	function rejectCheck ($errors) {
		if (count($errors) > 0) {
			foreach ($errors as $stopper) {
				echo "<h2>{$stopper}</h2>";
			}
			if(isset($_SESSION)) {
				exit;
			}
		}
	}
	
	if(!isset($_SESSION)) {
		echo '<script src="http://code.jquery.com/jquery-1.8.0.min.js" type="text/javascript"> </script>';
		echo  '<script src="javascript/jsCode.js" type="text/javascript"> </script>';
	}
	
	#### style to make the page looks right
	echo "<style>
			.eCheck { background:#A4DBFC; }
			p { font-size: 1.3em; }
		  </style>";
	####################
?>
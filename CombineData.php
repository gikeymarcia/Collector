<!-- Collector 1.00.00 alpha1
	A program for running experiments on the web
	Copyright 2012 Mikey Garcia & Nate Kornell
-->

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
	<title>Here are your data</title>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>

<body>

<?php
	$dataDir	= opendir("subjects/");										// tells us where to begin looking for data files
	$combined	= array();													// declares array that will eventually be written to the All Data file
	$fileCount	= 0;
	
	// echo '<h2>The Following are all of the files being used to aggregate data</h2>';
	
	#### While loop that loads all test file data into $combined
	while( ($file = readdir($dataDir)) != FALSE) {							// finds each file in the 'subjects/' directory
		if(strpos($file, 'put_Session') == TRUE) {							// if filename matches output format (contains "put_Session")
			// echo $file."<br />";											// Show filename of each file used (proof that I'm selecting the correct files) -- turned off because I don't want people to see participant email addresses
			$fileCount++;
			
			#loading data from individual file into '$combined' array
			$currentData =  fopen('subjects/'.$file, 'r');					// opening currently selected file from "subjects/" directory
			while( $line = fgetcsv($currentData, 0, "\t") ) {				// starts a loop that reads lines of data
				$combined[]= $line;											// save each line into $combined array
			}
		}
	}
	
	#### Writing all data as tab delimited .txt file into "subjects/" folder
	$theFile	= 'subjects/All Data - '.date("Y")."-".date("m")."-".date("d").' - '.date("U").'.txt';				// sets file to write to-- filename formatted as 'All Data - 2011-11-17 - #s since Unix Epoch.txt'
	$txt		= fopen($theFile,'w');
	foreach ($combined as $one){
	    $line = implode("\t", $one);
	    fwrite($txt,$line);
		fwrite($txt, "\n");
	}
	
	echo '<br /><br />  <h2>Number of Data Files Used</h2>';
	echo $fileCount;
?>

</body>
</html>
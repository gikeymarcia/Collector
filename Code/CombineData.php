<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
	<title>Here are your data</title>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>

<body>

<?php
/*	Collector
	A program for running experiments on the web
	Copyright 2012-2014 Mikey Garcia & Nate Kornell
 */
 	require 'fileLocations.php';											// sends file to the right place
 	require 'CustomFunctions.php';
	
	$OutFiles	= scandir($up.$dataF.$nonDebugF.$outputF);
	$finalQ		= array();													// will hold finalQuestions data
	$instruct	= array();													// will hold instructions data
	$status		= array();													// will hold status.txt data
	
	
	// remove non-output files
	$outTemp = array();
	foreach ($OutFiles as $file) {
		if(inString('.txt', $file)) {
			$outTemp[] = $file;
		}
	}
	$OutFiles = $outTemp;
	Readable($OutFiles, count($OutFiles).' output files scanned');
	
	/*
	$finalQ 	= GetFromFile($up.$dataF.$nonDebugF.$finalQuestionsDataFileName.$outExt, FALSE);
	$begin 		= GetFromFile($up.$dataF.$nonDebugF.$statusBeginFileName.$outExt, FALSE);
	$end 		= GetFromFile($up.$dataF.$nonDebugF.$statusEndFileName.$outExt, FALSE);
	$demog 		= GetFromFile($up.$dataF.$nonDebugF.$demographicsFileName.$outExt, FALSE);
	$instruct 	= GetFromFile($up.$dataF.$nonDebugF.$instructionsDataFileName.$outExt, FALSE);
	*/
	
	#### Get all headers across all output files
	$allHeaders = array();
	foreach ($OutFiles as $file) {
		$loc	= $up.$dataF.$nonDebugF.$outputF.$file;
		$handle = fopen($loc, 'r');
		$row	= fgets($handle);
		$pieces	= explode("\t",$row);
		foreach ($pieces as $col) {
			if(!in_array(trim($col), $allHeaders)) {
				// echo 'adding :'.$col.'<br>';
				$allHeaders[] = trim($col);
			}
		}
	}
	// fwrite(  fopen($up.$dataF.'headers.txt','w'),  implode("\t", $allHeaders)  );
	Readable($allHeaders,'these are all unique headers');
	
	
	
	#### Combine all output using common headers
	$combineLoc = $up.$dataF.'All Data - '.date("Y")."-".date("m")."-".date("d").' - '.date("U").'.txt';
	$handle = fopen($combineLoc, 'a');
	$delimiter = "\t";
	fwrite($handle, implode($delimiter, $allHeaders));					// write common file headers
	fwrite($handle, PHP_EOL);
	
	
	foreach ($OutFiles as $oneSS) {											// for each output file
		$temp = GetFromFile($up.$dataF.$nonDebugF.$outputF.$oneSS, FALSE);
		foreach ($temp as $trial) {											// for each trial
			$thisLine = array();											// clear holder line
			foreach ($allHeaders as $col) {									// for each unique header (across all output files)
				if(!isset($trial[$col])) {									// if the column doesn't exist then set it to blank
					$trial[$col] = '';
				}
				$thisLine[] = $trial[$col];									// build array representing this line
			}
			fwrite($handle, implode($delimiter, $thisLine).PHP_EOL);		// write newline
		}
	}
	fclose($handle);
	
?>
	<script src="http://code.jquery.com/jquery-1.10.2.min.js" type="text/javascript"> </script>
	<script src="javascript/collector_1.0.0.js" type="text/javascript"> </script>
</body>
</html>
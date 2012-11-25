<!-- Collector 1.00.00 alpha2
	A program for running experiments on the web
	Copyright 2012 Mikey Garcia & Nate Kornell
-->

<?php	#### #### CUSTOM FUNCTIONS #### ####

	
	#### Write array to a line of a tab delimited text file (if mode is not specified it uses "a")
	function arrayToLine ($array, $fileLocation, $mode = "a"){
			$fileHandle = fopen($fileLocation, $mode);
			fputs($fileHandle , implode( "\t" , $array) );				// write tab delimited array values
			fputs($fileHandle , "\n" );									// add a newline at the end of the array
			fclose($fileHandle);
	}
	
	
	
	#### Code that block shuffles an array.  Give it an $input array and the key for the grouping factor.
	function BlockShuffle( $input , $groupingFactor ){
		$outputArray = array();
		
		// Use this logic when second-order shuffling is present
		if(array_key_exists($groupingFactor.'2', $input[2])) {
			// creates a hierarchical structure of higher order blocks which contain lower order blocks which contain specific items
			$holder	 = array();
			$HiCount = 0;
			$LoCount = 0;
			$holder[$HiCount][$LoCount][] = $input[0];									// load initial item into first pos
			for( $arrayPos = 0; $arrayPos < (count($input) ); $arrayPos++ ){
				$CurrentLine = $input[ $arrayPos ];
				$NextLine	 = $input[ $arrayPos+1 ];
				if( $CurrentLine[$groupingFactor.'2'] == $NextLine[$groupingFactor.'2'] ){
					if ($CurrentLine[$groupingFactor] == $NextLine[$groupingFactor]) {
						$holder[$HiCount][$LoCount][] = $NextLine;
						continue;
					}
					else {
						$LoCount++;
						$holder[$HiCount][$LoCount][] = $NextLine;
						continue;
					}
				}
				elseif( $CurrentLine[$groupingFactor.'2'] <> $NextLine[$groupingFactor.'2'] ){
					$HiCount++;
					$LoCount = 0;
					$holder[$HiCount][$LoCount][] = $NextLine;
					continue;
				}
			}			// runs through the heirarchical structure and shuffles where applicable
			for ($hi=0; $hi < count($holder); $hi++) {
				if (trim(strtolower($holder[$hi][0][0][$groupingFactor.'2'])) <> 'off') {
					shuffle($holder[$hi]);
				}
				for ($lo=0; $lo < count($holder[$hi]) ; $lo++) {
					if (trim(strtolower($holder[$hi][$lo][0][$groupingFactor])) <> 'off') {
						shuffle($holder[$hi][$lo]);
					}
				}
			}
			// items are now higher and lower order shuffled so simply place them into outputArray
			foreach ($holder as $outer) {
				foreach ($outer as $inner) {
					foreach ($inner as $item) {
						$outputArray[] = $item;											// put the item into the next available output position
					}
				}
			}
			return $outputArray;
		}
		// Use this logic when second order shuffling is NOT present
		else {
			$block = NULL;
			for( $arrayPos = 0; $arrayPos < (count($input) ); $arrayPos++ ){
				$CurrentLine = $input[ $arrayPos ];
				$NextLine = $input[ $arrayPos+1 ];
				if($block == NULL){
					$block[] = $CurrentLine;
				}
				if( $CurrentLine[$groupingFactor] == $NextLine[$groupingFactor] ){
					$block[] = $NextLine;
					continue;
				}
				elseif( $CurrentLine[$groupingFactor] <> $NextLine[$groupingFactor] ){
					if( strtolower($CurrentLine[$groupingFactor]) <> "off" ){
						shuffle($block);
					}
					foreach ($block as $line) {
						$outputArray[]=$line;
					}
					$block = NULL;
				}
			}
			return $outputArray;
		}
	}
	
	
	#### function that converts smart quotes, em dashes, and u's with umlats so they display properly on web browsers
	function fixBadChars ($string) {
		// Function from http://shiflett.org/blog/2005/oct/convert-smart-quotes-with-php
		// added chr(252) 'lowercase u with umlat'
		$search = array(chr(145),
						chr(146),
						chr(147),
						chr(148),
						chr(151),
						chr(252));
						
		$replace = array("'",
						 "'",
						 '"',
						 '"',
						 '-',
						 '&uuml;');
		return str_replace($search, $replace, $string);
	}
	
	
	
	#### custom function to read from tab delimited data files;  pos 0 & 1 are blank,  header names are array keys
	function GetFromFile($fileLoc, $padding = TRUE) {
		
		$file	= fopen($fileLoc, 'r');					// open the file passed through the function arguement
		$keys	= fgetcsv($file, 0, "\t");				// pulling header data from top row of file
		if ($padding == TRUE):
			$out	= array(0 => 0, 1 => 0);			// leave positions 0 and 1 blank (so when I call $array[#] it will corespond to the row in excel)
		endif;
		while ($line = fgetcsv($file, 0, "\t")) {		// capture each remaining line from the file
			$tOut	= array_combine($keys, $line);		// combine the line of data with the header	
			$out[]	= $tOut;							// add this combined header<->line array to the ouput array
		}
		return $out;
	}
	
	
	
	function initiateCollector() {
		ini_set('auto_detect_line_endings', true);				// fixes problems reading files saved on mac
		session_start();										// start the session at the top of each page
		if ($_SESSION['Debug'] == FALSE) {						// disable error reporting during debug
			error_reporting(0);
		}
	}
	
	#### Debug function I use to display arrays in an easy to read fashion
	function Readable($displayArray, $NameOfDisplayed = "unspecified"){
		echo "<br />";	
		echo "Below is the array for <b>{$NameOfDisplayed}</b>";
		echo '<pre>';
		print_r($displayArray);
		echo '</pre>';
	}
	
	
	
	#### add html image tags to images but simply returns things that are not images
	function show($string){
		$stringLower	= strtolower($string);					// make lowercase version of input
		$findJPG		= strpos($stringLower, '.jpg');			// look for file extensions in the input
		$findGIF		= strpos($stringLower, '.gif');
		$findPNG		= strpos($stringLower, '.png');
		
		// if I found any of the above image file extensions, add html image tags
		// else, simply echo the orignal input (not the lowercase version)
		if( $findGIF == TRUE || $findJPG == TRUE || $findPNG == TRUE){
			$string = '<img src="'.$string.'">';
		}
		else {
			// don't change input string if it doesn't contain an image extension
		}
		return $string;
	}
	
	
	
	function SortByKey($input, $key){
		$sorter = array();											// declare holding array
		for($i = 0; $i < count($input); $i++){						// load $input sorting key into $sorter
			$sorter[] = $input[$i][$key];
		}
		array_multisort($sorter, $input);							// sort by $key value of each condition
		return $input;
	}
	
	
	
	#### Debug function that was quicker to write than an echo (mostly used it to make sure conditions of an if/for/while were being met)
	function x($input="this is working"){
		echo "<p>{$input}</p>";
	}


?>
<?php
/*	Collector
	A program for running experiments on the web
	Copyright 2012-2014 Mikey Garcia & Nate Kornell
 */


	#### #### CUSTOM FUNCTIONS #### ####


	#### Write array to a line of a tab delimited text file (if mode is not specified it uses "a")
	function arrayToLine ($array, $fileLocation, $mode = "a"){
			$fileHandle = fopen($fileLocation, $mode);
			fputs($fileHandle , implode( "\t" , $array) );				// write tab delimited array values
			fputs($fileHandle , PHP_EOL );								// add a newline at the end of the array
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
				if(isset($input[$arrayPos+1]) == FALSE) {								// check that there is a next line
					continue;
				}
				else {																	// save nextline for later inserting
					$NextLine	 = $input[ $arrayPos+1 ];
				}
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
			}			// runs through the hierarchical structure and shuffles where applicable
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
			$blockNum = 0;
			$temp = array();
			$temp[$blockNum][] = $input[0];						// start by loading initial item into temp
			// load items into array that groups as blocks then as items within blocks. e.g., $temp[$blockNum][#]
			for ($pos=0; $pos < count($input); $pos++) { 		// go through all items
				$currentLine = $input[$pos];					// set currentLine for comparison
				if(isset($input[$pos+1])) {						// if there is another line to add
					$nextLine = $input[$pos+1];						// grab it
				} else {	continue;	}							// or stop loading
				if($currentLine[$groupingFactor ] !== $nextLine[$groupingFactor]) {		// if the nextline uses a different shuffle then change blockNum
					$blockNum++;
				}
				$temp[$blockNum][] = $nextLine;					// loading nextLine into the correct $temp block of items
			}
			// shuffle appropriate blocks then load into output
			foreach ($temp as $group) {
				if(trim(strtolower($group[0][$groupingFactor])) != 'off') {
					shuffle($group);
				}
				foreach ($group as $line) {
					$outputArray[] = $line;
				}
			}
			return $outputArray;
		}
	}

    /**
     *  arrayCleaner
     *
     *  Barebones function to prevent passing code along
     *  This works with nested arrays
     *
     *  Add any other cleaning functions you want to it
     */
    function arrayCleaner($cleanarr) {
        if (is_array($cleanarr)) {
            return(array_map('arrayCleaner', $cleanarr));
        } else {
          return htmlspecialchars($cleanarr, ENT_QUOTES);
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
	function GetFromFile($fileLoc, $padding = TRUE, $delimiter = "\t") {
		$file	= fopen($fileLoc, 'r');					// open the file passed through the function arguement
		$keys	= fgetcsv($file, 0, $delimiter);		// pulling header data from top row of file
		if ($padding == TRUE):
			$out	= array(0 => 0, 1 => 0);			// leave positions 0 and 1 blank (so when I call $array[#] it will corespond to the row in excel)
		endif;
		while ($line = fgetcsv($file, 0, $delimiter)) {		// capture each remaining line from the file
			while (count($keys) > count($line)) {			// make sure each line has the right # of columns
				$line[] = '';
			}
			$tOut	= array_combine($keys, $line);		// combine the line of data with the header
			if(isBlankLine($tOut)) {					// do not include blank lines in output
				continue;
			}
			$out[]	= $tOut;							// add this combined header<->line array to the ouput array
		}
		fclose($file);
		return $out;
	}



	function initiateCollector() {
		ini_set('auto_detect_line_endings', true);				// fixes problems reading files saved on mac
		session_start();										// start the session at the top of each page
		if ($_SESSION['Debug'] == FALSE) {						// disable error reporting during debug
			error_reporting(0);
		}
	}



	#### function that returns TRUE or FALSE if a string is found in another string
	function inString ($needle, $haystack, $caseSensitive = FALSE){
		if ($caseSensitive == FALSE) {
			$haystack = strtolower($haystack);
			$needle = strtolower($needle);
		}
		if (strpos($haystack, $needle) !== FALSE) {
			return TRUE;
		} else { return FALSE; }
	}



	#### if an array is empty, all positions == "", return TRUE
	function isBlankLine($array) {
		foreach ($array as $item) {
			if($item <> "") {
				return FALSE;
			}
		}
		return TRUE;
	}


	#### checking if a key exists within a GetFromFile array;  returns TRUE/FALSE
	function keyCheck ($array, $key, $errorArray, $searched) {
		foreach ($array as $line) {
			if ($line == 0) {
				continue;
			}
			else {
				if(array_key_exists($key, $line) == TRUE) {
					return $errorArray;
				}
				else {
					$errorArray['Count']++;
					$errorArray['Details'][] = 'Did not find required column <b>'. $key.'</b> within '.$searched;
					return $errorArray;
				}
			}
		}
		return $errorArray;
	}


	#### takes an input ($info) array and merges it into a target array ($place).  Optional, prepend all $info keys with a $keyMod string
	function placeData ($data, $place, $keyMod = '') {
		$dataKeys = array_keys($data);
		foreach ($dataKeys as $key) {
			$place[$keyMod.$key] = $data[$key];
		}
		return $place;
	}


	#### Debug function I use to display arrays in an easy to read fashion
	function readable($displayArray, $name = "Untitled array"){
		// convert to string to prevent parsing code
		$clean_displayArray = arrayCleaner($displayArray);

		echo '<div>';
		echo     '<div class="button collapsibleTitle">
		              <h3>'.$name.'</h3>
		              <p>(click to open/close)</p>
		          </div>';
		echo     '<pre>';
		              print_r($clean_displayArray);
		echo     '</pre>';
		echo '</div>';
	}



	#### add html tags for images and audio files but do nothing to everything else
	function show($string){
		$stringLower	= strtolower($string);					// make lowercase version of input
		$findJPG		= strpos($stringLower, '.jpg');			// look for file extensions in the input
		$findGIF		= strpos($stringLower, '.gif');
		$findPNG		= strpos($stringLower, '.png');
		$findMP3		= strpos($stringLower, '.mp3');
		$findOGG		= strpos($stringLower, '.ogg');
		$findWAV		= strpos($stringLower, '.wav');


		// if I found an image file extension, add html image tags
		if( $findGIF == TRUE || $findJPG == TRUE || $findPNG == TRUE){
			if(!inString('www.', $string)) {								// navigate path to Experiment folder (unless linking to external image)
				$string = '../Experiment/'.$string;
			}
			$string = '<img src="'.$string.'">';
		}
		// if I found an audio file extension, add pre-cache code
		elseif ($findMP3 == TRUE || $findOGG == TRUE || $findWAV == TRUE) {
			if(!inString('www.', $string)) {							// navigate path to Experiment folder (unless linking to external image)
				$string = '../Experiment/'.$string;
			}
			$string = '<source src="'.$string.'"/>';
		}
		else {
			// leave input as-is if no audio or image extensions are found
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


	#### function to determine which timing to apply to the current trial
	function trialTiming(){
		global $formClass;
		global $time;
		global $minTime;
		global $compTime;
		global $timingReported;
		global $_SESSION;
		global $debugTime;

		if (is_numeric($timingReported)) {				// use manually set time if possible
			$time = $timingReported;
		}
		elseif ($timingReported <> 'computer') {		// if not manual or computer then timing is user
			$time = 'user';
		}
		elseif (isset($compTime)) {						// if a $compTime is set then use that
			$time = $compTime;
		}
		else { $time = 5; }								// default compTime if none is set

		if($_SESSION['Debug'] == TRUE) {
			$time = $debugTime;
		}

		if($time == 'user') {
			$formClass	= 'UserTiming';
		} else {
			$formClass	= 'ComputerTiming';
		}

		// if minTime exists and is a number
		if( isset($_SESSION['Trials'][$_SESSION['Position']]['Procedure']['MinTime']) && is_numeric($_SESSION['Trials'][$_SESSION['Position']]['Procedure']['MinTime']) ) {
			$minTime = $_SESSION['Trials'][$_SESSION['Position']]['Procedure']['MinTime'];
		}

	}



	#### Debug function that was quicker to write than an echo (mostly used it to make sure conditions of an if/for/while were being met)
	function x($input="this is working"){
		echo "<p>{$input}</p>";
	}

	function FileExists( $filePath, $altExtensions = TRUE ) {
		//if( is_file($filePath) ) { return $filePath; }
		$path_parts = pathinfo($filePath);
		$returnPath = './';
		$fileDirs = explode( '/', $path_parts['dirname'] );
		if( $fileDirs === array( '.' ) ) { $fileDirs = array(); }
		$fileName = $path_parts['basename'];
		foreach( $fileDirs as $dir ) {
			if( is_dir( $returnPath.$dir.'/' ) ) {
				$returnPath .= $dir.'/';
				continue;
			} else {
				$scan = scandir($returnPath);
				foreach( $scan as $entry ) {
					if( strtolower($entry) === strtolower($dir) ) {
						$returnPath .= $entry.'/';
						continue 2;
					}
				}
				return FALSE;
			}
		}
		if( is_file($returnPath.$fileName) ) { return substr($returnPath,2).$fileName; }
		$scan = scandir($returnPath);
		$lowerFile = strtolower($fileName);
		foreach( $scan as $entry ) {
			if( strtolower($entry) === $lowerFile ) {
				return substr($returnPath,2).$entry;
			}
		}
		if( $altExtensions ) {
			$baseFileName = strtolower($path_parts['filename']);
			foreach( $scan as $entry ) {
				if( !is_file( $returnPath.$entry ) ) { continue; }
				if( strpos($entry, '.') === FALSE ) {
					$entryName = strtolower($entry);
				} else {
					$entryName = strtolower(substr($entry, 0, strpos($entry, '.') ));
				}
				if( $entryName === $baseFileName ) {
					return substr($returnPath,2).$entry;
				}
			}
		}
		return FALSE;
	}


?>
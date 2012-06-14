<?php	#### #### CUSTOM FUNCTIONS #### ####

	
	#### Write array to a line of a tab delimited text file (if mode is not specified it uses "a")
	function arrayToLine ($array, $fileLocation, $mode = "a"){
			$fileHandle = fopen($fileLocation, $mode);					// write tab delimited array values
			fputs($fileHandle , implode( "\t" , $array) );				// add a newline at the end of the array
			fputs($fileHandle , "\n" );
			fclose($fileHandle);
	}
	
	
	
	#### Code that block shuffles an array.  Give it an $input array and the key for the grouping factor.
	function BlockShuffle( $input , $groupingFactor ){
		$outputArray = array();
		$block = NULL;																// load initial item into block
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
	function GetFromFile($fileLoc) {
		
		$file	= fopen($fileLoc, 'r');					// open the file passed through the function arguement
		$keys	= fgetcsv($file, 0, "\t");				// pulling header data from top row of file
		$out	= array(0 => 0, 1 => 0);				// leave positions 0 and 1 blank (so when I call $array[#] it will corespond to the row in excel)
		while ($line = fgetcsv($file, 0, "\t")) {		// capture each remaining line from the file
			$tOut	= array_combine($keys, $line);		// combine the line of data with the header	
			$out[]	= $tOut;							// add this combined header<->line array to the ouput array
		}
		return $out;
	}
	
	
	
	#### Debug function I use to display arrays in an easy to read fashion
	function Readable($displayArray, $NameOfDisplayed = "unspecified"){
		echo "<br />";	
		echo "Below is the array for <b>{$NameOfDisplayed}</b>";
		echo '<pre>';
		print_r($displayArray);
		echo '</pre>';
	}
	
	
	
	#### finish this function (currently does nothing and I've forgotten what I was even aiming to accomplish here)
	// function setIf ($posted, $session) {
		// global $$session;
		// if($posted == TRUE) {
			// $$session = $posted;
		// }
	// }
	
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
	
	
	
	#### custom function to shuffle the middle of an array (excluding 1st and last items)
	function ShuffleMiddle($arrayIn) {
		$zero	= array_shift($arrayIn);
		$first	= array_shift($arrayIn);								// pull off the 1st item
		$last	= array_pop($arrayIn);									// pull off last item
		shuffle($arrayIn);												// shuffle middle
		array_unshift($arrayIn, $first);								// put back 1st item
		array_unshift($arrayIn, $zero);									// put back 0 item
		$arrayIn[] = $last;												// put back last
	
		return $arrayIn;
	}
	
	
	#### Debug function that was quicker to write than an echo (mostly used it to make sure conditions of an if/for/while were being met)
	function x($input="this is working"){
		echo "<p>{$input}</p>";
	}


?>
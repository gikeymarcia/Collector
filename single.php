<?php	#### #### CUSTOM FUNCTIONS #### ####
	/*
	$tree = array ( 0 => array('cat', 'fox', 'hound', 'goose'),
					1 => array( 'one', 'two', 'three', 'four', 'five', 'six', 'seven',
								array('fun', 'in', 'the', 'sun')),
					2 => 'hut hut',
					3 => 'vrooooom'
					);
	Readable($tree, 'pre');
	shuffle($tree[1]);
	Readable($tree, 'post');
	*/
	
	$stimuli = GetFromFile('Words/FullStim.txt');
			Readable($stimuli,'UNshuffled stimuli');
	$stimuli = BlockShuffle($stimuli, "Shuffle");
			Readable($stimuli,'shuffled stimuli *fingers crossed*');							// uncomment this line to see what your shuffled stimuli file looks like
			
			
	// load and block shuffle order for this condition
	$order = GetFromFile('Orders/Demo.txt');
			Readable($order, 'order unshuffled');
	$order = BlockShuffle($order, "Shuffle");
			Readable($order, 'order SHUFFLED');
	
	
	
	#### Code that block shuffles an array.  Give it an $input array and the key for the grouping factor.
	function BlockShuffle( $input , $groupingFactor ){
		$outputArray = array();
		
		// Use this logic when second-order shuffling is present
		if(array_key_exists($groupingFactor.'2', $input[2])) {
			echo "double shuffle <br />";
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
			}
			// runs through the heirarchical structure and shuffles where applicable
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
			echo "single shuffle <br />";
			$blockNum = 0;
			$temp = array();
			$temp[$blockNum][] = $input[0];						// start by loading initial item into temp
			for ($pos=0; $pos < count($input); $pos++) { 		// go through all items
					$currentLine = $input[$pos];				// set currentLine for comparison
					if(isset($input[$pos+1])) {					// if there is another line to add
						$nextLine = $input[$pos+1];					// grab it
					} else {	continue;	}						// or stop loading
					if($currentLine[$groupingFactor ] !== $nextLine[$groupingFactor]) {		// if the nextline is different shuffle then change blockNum
						$blockNum++;
					}
					$temp[$blockNum][] = $nextLine;				// loading nextLine into the correct $temp block of items
			}
			// Readable($temp, 'loading up');
			foreach ($temp as $group) {
				if(trim(strtolower($group[0][$groupingFactor])) != 'off') {
					shuffle($group);
				}
				foreach ($group as $line) {
					$outputArray[] = $line;					
				}
			}
			return $outputArray;
			
			// $block = NULL;
			// for( $arrayPos = 0; $arrayPos < (count($input) ); $arrayPos++ ){
				// $CurrentLine = $input[ $arrayPos ];
				// $NextLine	 = $input[ $arrayPos+1 ];
				// // if(isset($input[$arrayPos+1]) == FALSE) {								// check that there is a next line
					// // continue;
				// // }
				// // else {																	// save nextline for later inserting
					// // $NextLine	 = $input[ $arrayPos+1 ];
				// // }
				// if($block == NULL){
					// $block[] = $CurrentLine;
				// }
				// if( $CurrentLine[$groupingFactor] == $NextLine[$groupingFactor] ){
					// $block[] = $NextLine;
					// continue;
				// }
				// elseif( $CurrentLine[$groupingFactor] <> $NextLine[$groupingFactor] ){
					// if( strtolower($CurrentLine[$groupingFactor]) <> "off" ){
						// shuffle($block);
					// }
					// foreach ($block as $line) {
						// $outputArray[]=$line;
					// }
					// $block = NULL;
				// }
			// }
			// return $outputArray;
		}
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

	
	#### Debug function I use to display arrays in an easy to read fashion
	function Readable($displayArray, $NameOfDisplayed = "unspecified"){
		echo "<br />";	
		echo "Below is the array for <b>{$NameOfDisplayed}</b>";
		echo '<pre>';
		print_r($displayArray);
		echo '</pre>';
	}
?>
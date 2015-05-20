<?php
/*  Collector
    A program for running experiments on the web
    Copyright 2012-2015 Mikey Garcia & Nate Kornell
 */


    #### #### CUSTOM FUNCTIONS #### ####
    
    
    #### add a column (sub-array key) to a 2D-array (like getFromFile() creates)
    function addColumn(&$array, $column, $value = '', $overwrite = FALSE, $caseInsensitive = TRUE) {
        $lowerCol = strtolower($column);
        foreach ($array as $i => &$row) {
            if (!is_array($row)) { continue; } // skip padding
            if (!$overwrite) {
                if (isset($row[$column])) { continue; }
                if ($caseInsensitive) {
                    $tempRow = array_change_key_case($row, CASE_LOWER);
                    if (isset($tempRow[$lowerCol])) { continue; }         // some issues were being caused by 'text' as a lowercase column.  This should avoid creating an empty 'Text' column along side it
                }
            }
            if ($value === '$i') {
                $row[$column] = (string)$i;     // using a string, to keep the contents similar to what getFromFile() creates
            } else {
                $row[$column] = $value;
            }
        }
    }


    #### Write array to a line of a CSV text file
    function arrayToLine ($row, $fileName, $d = NULL, $encodeUtf8ToWin = TRUE) {
        if ($d === NULL) {
            $d = isset ($_SESSION['OutputDelimiter']) ? $_SESSION['OutputDelimiter'] : ",";
        }
        if (!is_dir(dirname($fileName))) {
            mkdir(dirname($fileName), 0777, true);
        }
        if ($encodeUtf8ToWin) {
            if (mb_detect_encoding(implode('', $row), 'UTF-8', TRUE)) {
                foreach ($row as &$datum) {
                    $datum = mb_convert_encoding($datum, 'Windows-1252', 'UTF-8');
                }
                unset($datum);
            }
        }
        foreach ($row as &$datum) {
            $datum = str_replace(array("\r\n", "\n", "\t", "\r", chr(10), chr(13)), ' ', $datum);
        }
        unset($datum);
        $fileTrue = fileExists($fileName);
        if (!$fileTrue) {
            $file = fopen($fileName, "w");
            fputcsv($file, array_keys($row), $d);
            fputcsv($file, $row, $d);
        } else {
            $file = fopen($fileTrue, "r+");
            $headers = array_flip(fgetcsv($file, 0, $d));
            $newHeaders = array_diff_key($row, $headers);
            if ($newHeaders !== array()) {
                $headers = $headers+$newHeaders;
                $oldData = stream_get_contents($file);
                rewind($file);
                fputcsv($file, array_keys($headers), $d);
                fwrite($file, $oldData);
            }
            fseek($file, 0, SEEK_END);
            $row = SortArrayLikeArray($row, $headers);
            fputcsv($file, $row, $d);
        }
        fclose($file);
        return $row;
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

    
    
    function camelCase($str) {
        $str = ucwords(strtolower(trim($str)));
        $str = str_replace(' ', '', $str);
        $str[0] = strtolower($str[0]);
        return $str;
    }
    
    
    
    function createAliases($array, $overwrite = FALSE) {
        foreach ($array as $name => $tempVal) {
            $name = preg_replace('/[A-Z]/', ' \\0', $name);
            $name = camelCase($name);
            $name = preg_replace('/[^0-9a-zA-Z_]/', '', $name);
            if ($name === '') {
                continue;
            }
            if (is_numeric($name[0])) {
                $name = '_' . $name;
            }
            global $$name;
            if (!isset($$name) OR $overwrite)
            {
                $$name = $tempVal;
            }
        }
    }
    
    
    
    #### Make a copy of a trial and remove all values (but not keys) from ['Stimuli'], ['Response'], and ['Procedure']
    ##  If you'd only like to clean specific arrays then pass the names as a single string with commas separating each name
    ##  (e.g., "Response, Procedure")
    function cleanTrial ($trial, $selections = FALSE) {
        // if arrays are selected
        if ($selections != FALSE) {
            $selected = explode(',', $selections);
            foreach ($selected as &$name) {
                $name = trim(strtolower($name));
            }
        }
        foreach ($trial as $group => &$data) {
            // skip value wipe for arrays that were not selected
            if (($selections != FALSE)
                AND (in_array(strtolower($group), $selected) == FALSE)
            ) {
                continue;                           // if this array was not selected then skip value wipe
            }
            // wipe values from array
            foreach ($data as $key => &$value) {
                $value = '';
            }
        }
        return $trial;
    }
    
    
    
    #### echoes out a table from a 2d array
    function display2dArray($arr, $nonArrayCol = FALSE) {
        if ($nonArrayCol === FALSE) {
            $i = 0;
            while (is_scalar($arr[$i])) {
                unset($arr[$i]);
                $i++;
            }
        }
        static $doInit = TRUE;
        if ($doInit) {
            $doInit = FALSE;
            ?>
            <style>
                .display2dArray                         { border-collapse: collapse; }
                .display2dArray td, .display2dArray th  { border: 1px solid #000; vertical-align: middle; text-align: center; padding: 2px 6px; overflow: hidden; }
                .display2dArray td                      { max-width: 200px; }
                .display2dArray th                      { max-width: 100px; white-space: normal; }
                .display2dArray td > div                { max-height: 1.5em; overflow: hidden; }
            </style>
            <?php
        }
        $columns = array();
        foreach ($arr as &$row) {
            if (is_scalar($row)) { $row = array( 'Non-array Value' => $row ); }
            foreach ($row as $col => $val) {
                $columns[$col] = TRUE;
            }
        }
        unset($row);
        $columns = array_keys($columns);
        echo '<table class="display2dArray">',
                '<thead>',
                    '<tr>',
                        '<th></th>',
                        '<th><div>',
                            implode('</div></th><th><div>', $columns),
                        '</div></th>',
                    '</tr>',
                '</thead>',
                '<tbody>';
        $columns = array_flip($columns);
        foreach ($arr as $i => $row) {
            $row = sortArrayLikeArray($row, $columns);
            foreach ($row as &$field) {
                $field = htmlspecialchars($field);
            }
            unset($field);
            echo '<tr>',
                        '<td>',
                            $i,
                        '</td>',
                        '<td><div>',
                            implode('</div></td><td><div>', $row),
                        '</div></td>',
                    '</tr>';
        }
        echo '</tbody>',
            '</table>';
    }
    
    
    
    #### Takes a time in seconds and formats it as 02h:03m:20s
    function durationFormatted ($seconds) {
        $hours   = floor($seconds/3600);
        $minutes = floor( ($seconds - $hours*3600)/60);
        $seconds = $seconds - $hours*3600 - $minutes*60;
        if ($hours   < 10 ) { $hours   = '0' . $hours;   }
        if ($minutes < 10 ) { $minutes = '0' . $minutes; }
        if ($seconds < 10 ) { $seconds = '0' . $seconds; }
        
        $formatted = $hours . 'h:' . $minutes . 'm:' . $seconds . 's';
        return $formatted;
    }
    
    
    
    #### Takes time formatted in 5d:2h:3m:20s and turns it into seconds
    function durationInSeconds ($input) {
        if ($input == '') {                                          // return 0 if no input is given
            return 0;
        } 
        $input = trim(strtolower($input));                          // lowercase and trim input
        $input = explode(':', $input);                              // break into componenet pieces
        
        $duration = 0;                                              // total # of seconds of input
        foreach ($input as $bit) {
            $value = preg_replace('/[^0-9]/', '', $bit);            // remove everything but the #
            if(instring('d', $bit)) {
                $duration += ($value * 24 * 60 * 60);               // add # of seconds in the given # of days
            } else if (instring('h', $bit)){
                $duration += ($value * 60 * 60);                    // add # of seconds in the given # of hours
            } else if (instring('m', $bit)){
                $duration += ($value * 60);                         // add # of seconds in the given # of minutes
            } else if (instring('s', $bit)){
                $duration += $value;                                // add # of seconds
            }
        }
        return $duration;
    }
    
    
    #### finding column entires specific to a given $postNumber (e.g., Post 1, Post 2, Post 3)
    function ExtractTrial($procedureRow, $postNumber) {
        $output = array();
        if ($postNumber < 1) {
            foreach ($procedureRow as $column => $value) {
                if (substr($column, 0, 5 ) === 'Post ' AND is_numeric($column[5])) { continue; }
                $output[$column] = $value;
            }
        } else {
            $prefix = 'Post '.$postNumber;
            $prefixLength = strlen($prefix);
            foreach ($procedureRow as $column => $value) {
                if (substr($column, 0, $prefixLength ) !== $prefix) { continue; }
                $output[ substr($column, $prefixLength) ] = $value;
            }
        }
        
        createAliases($output);
        return $output;
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
                        chr(252)  );

        $replace = array("'",
                         "'",
                         '"',
                         '"',
                         '-',
                         '&uuml;' );
        return str_replace($search, $replace, $string);
    }
    
    
    
    #### custom function to read from tab delimited data files;  pos 0 & 1 are blank,  header names are array keys
    function GetFromFile ($fileLoc, $padding = TRUE, $delimiter = ",") {
        ini_set('auto_detect_line_endings', true);             // make sure PHP auto-detects line endings
        $contents  = file_get_contents($fileLoc);              // first, grab all the bytes, so we can look at the encoding
        $encodings = array('ISO-8859-1', 'Windows-1252');
        if (mb_detect_encoding($contents, $encodings)) {        // if we need to encode, make our cleaning function do this for us
            $cleanCell = function(&$cell) {
                $cell = trim(mb_convert_encoding($cell, 'UTF-8', 'Windows-1252'));
            };
        } else {                                                // if we don't need to encode to UTF-8, have our cleaner function just trim()
            $cleanCell = function(&$cell) {
                $cell = trim($cell);
            };
        }
        $file = fopen($fileLoc, 'r');                           // open the file passed through the function argument
        $keys = fgetcsv($file, 0, $delimiter);                  // pulling header data from top row of file
        foreach ($keys as &$key) {
            $cleanCell($key);                                   // trim the keys, and convert encoding if necessary
        }
        unset($key);
        if ($padding == TRUE) {
            $out = array(0 => 0, 1 => 0);                       // leave positions 0 and 1 blank (so when I call $array[#] it will correspond to the row in excel)
        }
        $c1 = count($keys);
        while ($line = fgetcsv($file, 0, $delimiter)) {         // capture each remaining line from the file
            $c2 = count($line);
            while ($c1 > $c2) {                                 // make sure each line has the right # of columns
                $line[] = '';
                $c2++;
            }
            if ($c1 < $c2) {
                $line = array_slice($line, 0, $c1);             // trim off excess cells in this row
            }
            foreach ($line as &$field) {
               $cleanCell($field);                              // trim the cell, and convert encoding if necessary
            }
            unset($field);
            $tOut = array_combine($keys, $line);                // combine the line of data with the header
            $isBlank = TRUE;                                    // assume line is blank (no data)
            foreach ($tOut as $cell) {                          // check every cell of line
                if ($cell !== '') {                             // if we find data
                    $isBlank = FALSE;                               // line is not blank
                    break;
                }
            }
            if ($isBlank === FALSE) {                           // if line is not blank
                $out[] = $tOut;                                     // add this combined header<->line array to the output array
            }
        }
        fclose($file);
        return $out;
    }



    #### function that returns TRUE or FALSE if a string is found in another string
    #### similar to stripos()
    function inString ($needle, $haystack, $caseSensitive = FALSE) {
        if ($caseSensitive == FALSE) {
            $haystack = strtolower($haystack);
            $needle   = strtolower($needle);
        }
        if (strpos($haystack, $needle) !== FALSE) {
            return TRUE;
        } else { return FALSE; }
    }



    #### checking if a key exists within a GetFromFile array;  returns TRUE/FALSE
    function keyCheck ($array, $key, $errorArray, $searched) {
        foreach ($array as $line) {
            if ($line == 0) {
                continue;
            } else {
                if (array_key_exists($key, $line) == TRUE) {
                    return $errorArray;
                } else {
                    $errorArray['Count']++;
                    $errorArray['Details'][] = 'Did not find required column <b>' . $key . '</b> within ' . $searched;
                    return $errorArray;
                }
            }
        }
        return $errorArray;
    }
    
    
    #### I plan on making a function that creates the trials in login.php 
    function makeTrial () {
        global $procedure;
        global $stimuli;
        global $allKeysNeeded;
                
        $trial = array();
        
        return $trial;
        
        /*
         * Ideas:
         *     Accept a range of stimuli instead of just single stimuli (implode by pipes)
         *     Automatically fill 0 stim items with 'n/a'
         */
    }
    
    
    /**
     * Recursively shuffles an array from top (highest level) to bottom
     * Disabling shuffle for an item at a given level
     *   - Use 'off' in whichever case you'd like (e.g., 'Off', 'OFF', etc.)
     *   - OR include a hashtag/pound sign in the shuffle column (e.g., '#Group1')
     * @param array $input 2-dimensional data read from a .csv table using GetFromFile().
     * @param int $levels tells the program which level it is currently shuffling.
     *   - 0 is the default value and initializes the code that counts how many levels exist
     * @return array
     * @see GetFromFile()
     */
    function multiLevelShuffle ($input, $levels = 0) {
        $root   = 'Shuffle';
        $offChar = '#';
        $output = array();
        $subset = array(); 
        
        #### initialize shuffling
        if ($levels == 0) {
            $padding = array();                                         // save padding, if it exists
            while ($input[0] === 0) {
                $padding[] = array_shift($input);
            }
            if (!isset($input[0][$root])) {                             // skip shuffling if no 'Shuffle' column is present
                for ($i=0; $i < count($padding); $i++) {                // prepend the removed padding
                    array_unshift($input, 0);
                }
                return $input;
            }
            $checkLevel = 2;                                            // Find maximum Shuffle level used
            while (isset($input[0][$root.$checkLevel])) {                   // while 'Shuffle#' exists
                $checkLevel++;                                                  // check next level of shuffling
            }
            $maxLevel = $checkLevel - 1;
            $output   = multiLevelShuffle($input, (int)$maxLevel);      // run this function at the highest shuffle level
            for ($i=0; $i < count($padding); $i++) {                    // prepend the removed padding
                array_unshift($output, 0);
            }
            return $output;
        }
        
        #### do higher order block shuffling from max down to 2
        if ($levels > 1) {
            $subLevel = '';                                             // What is below the current level
            if ($levels > 2) {
                $subLevel = $levels - 1;
            }
            $begin = $input[0][$root.$levels];                          // save starting shuffle code
            for ($i=0; $i < count($input); $i++) {
                $current   = $input[$i][$root.$levels];                     // save current shuffle code
                $currentLo = $input[$i][$root.$subLevel];                   // save lower shuffle code
                if ((strpos($begin, $offChar) !== FALSE)                     // if the current shuffle code is turned off
                     OR (strtolower($begin) == 'off')
                 ) {
                    if ($begin == $current) {
                        $subset[] = $input[$i];                             // add it to the subset if the code hasn't changed
                        continue;
                    } else {                                                // if the shuffle code has changed
                        $output = array_merge($output, multiLevelShuffle($subset, (int)$levels-1));
                        $subset = array();                                  // empty the subset
                        $begin = $current;
                        // $beginLo = $currentLo;
                        if ((strpos($begin, $offChar) !== FALSE)                 // if the next code is turned off
                             OR (strtolower($begin) == 'off')
                         ) {
                            $subset[] = $input[$i];                             // add it to the current subset
                            continue;
                        }
                    }
                }
                if ($begin == $current) {                               // if the shuffle code hasn't changed (and isn't off)
                    $holder[$currentLo][] = $input[$i];                     // add it to a $holder array (grouped by lower shuffle column)
                } else {                                                // when the shuffle code changes (and isn't off)
                    shuffle($holder);                                       // shuffle the lower groups
                    $subset = array();
                    foreach ($holder as $group) {
                        foreach ($group as $item) {
                            $subset[] = $item;
                            // add all items from the $holder to the $subset
                        }
                    }
                    $output = array_merge($output, multiLevelShuffle($subset, (int)$levels-1));
                    $subset = array();
                    $begin  = $current;
                    $holder = array();
                    if ((strpos($begin, $offChar) !== FALSE)
                        OR (strtolower($begin) == 'off')
                    ) {
                        $subset[] = $input[$i];                         // add current item to the subset if shuffle code is off
                    } else {
                        $holder[$currentLo][] = $input[$i];             // add current item to the $holder if shuffle code is not off
                    }
                }
            }
            if ($subset != array()) {                                   // send the final subset to be shuffled (if the file ends with an off code)
                $output = array_merge($output, multiLevelShuffle($subset, (int)$levels-1));
                $subset = array();
            } else {                                                    // send the final holder to be shuffled (if the file does not end with an off code)
                shuffle($holder);
                foreach ($holder as $group) {
                    foreach ($group as $item) {
                        $subset[] = $item;
                    }
                }
                $output = array_merge($output, multiLevelShuffle($subset, (int)$levels-1));
            }
            return $output;
        }
        
        #### Level 1 shuffle (aware of names)
        if ($levels == 1) {
            $groupedItems = array();
            foreach ($input as $subArray) {
                $group = $subArray[$root];
                $groupedItems[$group][] = $subArray;                    // group each item by shuffle code
            }
            foreach ($groupedItems as $shuffleType => &$items) {
                if ((strtolower($shuffleType) == 'off')                 // if the group code is set to off
                    OR (strpos($shuffleType, $offChar) !== FALSE)
                ) {
                    continue;                                               // skip shuffling of items (within the group)
                } else {
                    shuffle($items);                                    // otherwise, shuffle items within a group
                }
            }
            foreach ($input as $pos => $item) {                             // go through unshuffled input
                $shuffleCode  = $item[$root];
                $output[$pos] = array_shift($groupedItems[$shuffleCode]);   // pull items from the shuffled groups and put them into the output
            }
            return $output;
        }
    }
    

    #### takes an input ($info) array and merges it into a target array ($place).  Optional, prepend all $info keys with a $keyMod string
    function placeData ($data, $place, $keyMod = '') {
        foreach ($data as $key => $val) {
            $place[$keyMod . $key] = $val;
        }
        return $place;
    }
	
	
	#### turns a string into an array, converting something like '2,4::6' into array(2, 4, 5, 6)
	function rangeToArray ($string, $seperator = ',', $connector = '::') {
		$output = array();
		$ranges = explode($seperator, $string);
		foreach ($ranges as $range) {
			$endPoints = explode($connector, $range);
			$count = count($endPoints);
			if ($count === 1) {
				$output[] = trim($endPoints[0]);
			} else {
				$output = array_merge($output, range(trim($endPoints[0]), trim($endPoints[$count-1])));
			}
		}
		return $output;
	}


    #### Debug function I use to display arrays in an easy to read fashion
    function readable ($displayArray, $name = "Untitled array") {
        $clean_displayArray = arrayCleaner($displayArray);              // convert to string to prevent parsing code
        $clean_displayArray = print_r($clean_displayArray, TRUE);       // capture print_r output
        
        echo '<div>'
              . '<div class="button collapsibleTitle">'
              .     '<h3>' . $name . '</h3>'
              .     '<p>(Click to Open/Close)</p>'
              . '</div>'
              . '<pre>'
              .     $clean_displayArray
              . '</pre>'
           . '</div>';
    }
    
    
    function RemoveLabel ($input, $label, $extendLabel = TRUE) {
        $trim = trim($input);
        $lower = strtolower($trim);
        $label = strtolower(trim($label));
        $trimLength = strlen($label);
        if (substr($lower, 0, $trimLength) !== $label) {
            return FALSE;
        } else {
            if ($extendLabel) {
                if (substr($lower, $trimLength, 1) === 's') ++$trimLength;
                if (substr($lower, $trimLength, 1) === ' ') ++$trimLength;
                if (substr($lower, $trimLength, 1) === ':') ++$trimLength;
                if (substr($lower, $trimLength, 1) === '=') ++$trimLength;
            }
            $trim = trim(substr($trim, $trimLength));
            if (($trim === '') OR ($trim === FALSE)) return TRUE;
            return $trim;
        }
    }


    #### add html tags for images and audio files but do nothing to everything else
    function show ($string, $endOnly = TRUE) {
        if (!inString('www.', $string)) {                           // navigate path to Experiment folder (unless linking to external image)
            $fileName = '../Experiment/' . $string;
            if (FileExists($fileName)) {
                $fileName = FileExists($fileName);
            }
        }
        $stringLower = strtolower($fileName);                       // make lowercase version of input
        if ($endOnly == TRUE) {
            $stringLower = substr($stringLower, strlen($stringLower) - 6); // only check last 5 characters for file extensions
        }
        $findJPG     = strpos($stringLower, '.jpg');                // look for file extensions in the input
        $findGIF     = strpos($stringLower, '.gif');
        $findPNG     = strpos($stringLower, '.png');
        $findBMP     = strpos($stringLower, '.bmp');
        $findMP3     = strpos($stringLower, '.mp3');
        $findOGG     = strpos($stringLower, '.ogg');
        $findWAV     = strpos($stringLower, '.wav');


        if (   ($findGIF == TRUE)
            OR ($findJPG == TRUE)
            OR ($findPNG == TRUE)
            OR ($findBMP == TRUE)
        ) {
            // if I found an image file extension, add html image tags
            $string = '<img src="' . $fileName . '">';
        } elseif (   ($findMP3 == TRUE)
                  OR ($findOGG == TRUE)
                  OR ($findWAV == TRUE)
       ) {
            // if I found an audio file extension, add pre-cache code
            $string = '<source src="' . $fileName . '"/>';
        } else {
            // leave input as-is if no audio or image extensions are found
        }
        return $string;
    }



    function SortByKey ($input, $key) {
        $sorter = array();                                  // declare holding array
        for ($i = 0; $i < count($input); $i++) {            // load $input sorting key into $sorter
            $sorter[] = $input[$i][$key];
        }
        array_multisort($sorter, $input);                   // sort by $key value of each condition
        return $input;
    }


    #### function to determine which timing to apply to the current trial
    function trialTiming() {
        global $formClass;
        global $time;
        global $minTime;
        global $compTime;
        global $timingReported;
        global $_SESSION;
        global $debugTime;

        // determine which timing value to use
        if (is_numeric($timingReported)) {              // use manually set time if possible
            $time = $timingReported;
        } elseif ($timingReported != 'computer') {      // if not manual or computer then timing is user
            $time = 'user';
        } elseif (isset($compTime)) {                   // if a $compTime is set then use that
            $time = $compTime;
        } else { $time = 5; }                           // default compTime if none is set
        
        // override time in debug mode, use standard timing if no debug time is set
        if ($_SESSION['Debug'] == TRUE && $debugTime != '') {
            $time = $debugTime;
        }
        
        // set class for input form (shows or hides 'submit' button)
        if ($time == 'user') {
            $formClass = 'UserTiming';
        } else {
            $formClass = 'ComputerTiming';
        }
    }



    function FileExists ($filePath, $altExtensions = TRUE, $findDirectories = TRUE) {
        if (is_file($filePath)) { return $filePath; }
        if (is_dir($filePath) AND $findDirectories) {
            if (substr($filePath, -1) === '/') {
                $filePath = substr($filePath, 0, -1);
            }
            return $filePath;
        }
        $filePath = (string) $filePath;
        if ($filePath === '') { return FALSE; }
        $path_parts = pathinfo($filePath);
        $fileName = $path_parts['basename'];
        if (!isset($path_parts['dirname'])) $path_parts['dirname'] = '.';
        if (is_dir($path_parts['dirname'])) {
            $dir = $path_parts['dirname'];
            $pre = ($dir === '.' AND $filePath[0] !== '.') ? 2 : 0;
        } else {
            $dirs = explode('/', $path_parts['dirname']);
            if(is_dir($dirs[0])) {
                $dir = array_shift($dirs);
                $pre = 0;
            } else {
                $dir = '.';
                $pre = 2;
            }
            foreach ($dirs as $dirPart) {
                if (is_dir($dir . '/' . $dirPart)) {
                    $dir .= '/' . $dirPart;
                    continue;
                } else {
                    $scan = scandir($dir);
                    foreach ($scan as $entry) {
                        if (strtolower($entry) === strtolower($dirPart)) {
                            $dir .= '/' . $entry;
                            continue 2;
                        }
                    }
                    return FALSE;
                }
            }
            if (is_file($dir . '/' . $fileName)) {
                return substr($dir . '/' . $fileName, $pre);
            }
            if (is_dir($dir . '/' . $fileName) AND $findDirectories) {
                return substr($dir . '/' . $fileName, $pre);
            }
        }
        $scan = scandir($dir);
        $lowerFile = strtolower($fileName);
        foreach ($scan as $entry) {
            if (strtolower($entry) === $lowerFile) {
                if (is_dir($dir . '/' . $entry) AND !$findDirectories) { continue; }
                return substr($dir . '/' . $entry, $pre);
            }
        }
        if ($altExtensions) {
            $possibleEntries = array();
            foreach ($scan as $entry) {
                if ($entry === '.' OR $entry === '..') { continue; }
                if (is_dir($dir . '/' . $entry) AND !$findDirectories) { continue; }
                if (strrpos($entry, '.') === FALSE) {
                    $entryName = strtolower($entry);
                } else {
                    $entryName = strtolower(substr($entry, 0, strrpos($entry, '.')));
                }
                $possibleEntries[$entryName] = $entry;
            }
            foreach ($possibleEntries as $entryName => $entry) {
                if ((string)$entryName === $lowerFile) {
                    return substr($dir . '/' . $entry, $pre);
                }
            }
            $baseFileName = strtolower($path_parts['filename']);
            foreach ($possibleEntries as $entryName => $entry) {
                if ((string)$entryName === $baseFileName) {
                    return substr($dir . '/' . $entry, $pre);
                }
            }
        }
        return FALSE;
    }
    
    function ComputeString ($template, $fileData = array()) {
        if (($fileData === array())
             AND (isset($_SESSION))
        ) {
            $fileData = $_SESSION;
        }
        foreach ($fileData as $key => $value) {
            $fileData[strtolower($key)] = $value;                                // so that $username will be found in $fileData['Username']
        }
        $templateParts = explode('_', $template);
        $outputParts = array();
        foreach ($templateParts as $part) {
            if (strpos($part, '$') === FALSE) {
                $outputParts[] = $part;
            } else {
                $str = substr($part, 0, strpos($part, '$'));                    // e.g., from 'Sess$Session', get 'Sess'
                $var = substr($part, strpos($part, '$')+1);                     // e.g., from 'Sess$Session', get 'Session'
                if (strpos($var, '[') === FALSE) {
                    if (isset($fileData[$var]) AND is_scalar($fileData[$var])) {
                        $str .= $fileData[$var];
                    } else {
                        $str .= '$' . $var;                                        // return the '$' so that it is obvious that a variable was searched for and not found
                    }
                } else {                                                        // if they want $_SESSION['Condition']['Condition Description'], we need to search index by index
                    $key = substr($var, 0, strpos($var, '['));
                    $indices = explode(']', substr($var, strpos($var, '[')));
                    if (isset($fileData[$key])) {
                        $val = $fileData[$key];
                        foreach ($indices as $i) {
                            if (strlen($i) === 0) { continue; }
                            if ($i[0] !== '[') { continue; }
                            if (isset($val[ substr($i, strpos($i,'[')+1)  ])) {
                                $val = $val[ substr($i, strpos($i,'[')+1) ];
                            } else {
                                $val = NULL;
                                break;
                            }
                        }
                        if (is_scalar($val)) {
                            $str .= $val;
                        } else {
                            $str .= '$' . $var;
                        }
                    } else {
                        $str .= '$' . $var;                                        // return the '$' so that it is obvious that a variable was searched for and not found
                    }
                }
                $outputParts[] = $str;
            }
        }
        $outputParts = implode('_', $outputParts);
        return $outputParts;
    }
    
    
    
    function rand_string ($length = 10) {
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";    

        $size = strlen($chars);
        $str = '';
        for ($i = 0; $i < $length; $i++) {
            $str .= $chars[mt_rand(0, $size-1)];
        }

        return $str;
    }
    
    
    
    function AddPrefixToArray ($pre, $arr) {
        $out = array();
        foreach ($arr as $key => $val) {
            $out[$pre.$key] = $val;
        }
        return $out;
    }
    
    
    
    function SortArrayLikeArray ($arr, $template) {
        $out = array();
        foreach ($template as $key => $val) {
            $out[$key] = isset($arr[$key]) ? $arr[$key] : '';
        }
        return $out;
    }
?>
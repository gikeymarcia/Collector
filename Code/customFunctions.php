<?php
/*  Collector
    A program for running experiments on the web
    Copyright 2012-2015 Mikey Garcia & Nate Kornell
*/
/* CustomFunctions */
/**
 * Add a column (sub-array key) to a 2D-array (like getFromFile() creates)
 * @param array $array The array to add to.
 * @param string $column The name of the key (column) to add.
 * @param mixed $value The value to insert into the column.
 * @see getFromFile()
 */
function addColumn(array &$array, $column, $value = '')
{
    // only compare against lowercase keys to prevent misleading duplicates
    $lowerCol = strtolower($column);
    foreach ($array as $i => &$row) {
        if (!is_array($row) || isset($row[$column])) {
            // skip the first two indices which are just used as offsets
            // do not overwrite
            continue;
        }
        // only compare against lowercase keys to prevent misleading duplicates
        $lowerKeyRow = array_change_key_case($row, CASE_LOWER);
        if (isset($lowerKeyRow[$lowerCol])) {
            continue;
        }
        // add the values
        if ($value === '$i') {
            // cast to string to match getFromFile() contents
            $row[$column] = (string) $i;
        } else {
            $row[$column] = $value;
        }
    }
}
/**
 * Prepares an array and then writes it to a line of a CSV file.
 * @param array $data The associative array of data to write.
 * @param string $filename The path to the file to write to.
 * @param string $delim A single character noting the delimiter in the file.
 * @param bool $encodeToWin Set false if you want to retain current encoding.
 * @return array
 * @see writeLineToFile()
 */
function arrayToLine(array $data, $filename, $delim = null, $encodeToWin = true)
{
    // set delimiter
    if (null === $delim) {
        if (isset($_SESSION['OutputDelimiter'])) {
            $delim = $_SESSION['OutputDelimiter'];
        } else {
            $delim = ',';
        }
    }
    // convert encoding
    foreach ($data as &$datum) {
        $datum = whitespaceToSpace($datum);
        if ($encodeToWin) {
            $datum = convertEncoding($datum, 'Windows-1252');
        }
    }
    unset($datum);
    // write to file
    return writeLineToFile($data, $filename, $delim);
}
/**
 * Converts all whitespace in a string to a single space.
 * @param string $string
 * @return string
 */
function whiteSpaceToSpace($string)
{
    return preg_replace("/[\s]+/", " ", $string);
}
/**
 * Converts a string of unknown encoding to a desired encoding.
 * @param string $string The string to convert.
 * @param string $desiredEncoding The desired encoding.
 * @return string
 */
function convertEncoding($string, $desiredEncoding = 'UTF-8')
{
    $currentEncoding = determineEncoding($string);
    return iconv($currentEncoding, $desiredEncoding, $string);
}
/**
 * Determines a string's encoding.
 * @param string $string
 * @return string
 */
function determineEncoding($string)
{
    return mb_detect_encoding($string, mb_detect_order(), true);
}
/**
 * Converts a file's contents' encoding to desired encoding if it does not match.
 * @param string $filename
 * @param string $desiredEncoding
 */
function convertFileEncoding($filename, $desiredEncoding = 'UTF-8')
{
    $contents = file_get_contents($filename);
    if ($desiredEncoding !== determineEncoding($contents)) {
        file_put_contents($filename, convertEncoding($contents));
    }
}
/**
 * Writes a single row to a CSV file, merging headers before writing, if needed.
 * @param array $array The row to write to the file.
 * @param string $filename The path to the file to write to.
 * @param string $delim A single character noting the delimiter in the file.
 * @see readCsv()
 * @see writeCsv()
 */
function writeLineToFile(array $array, $filename, $delim = ',')
{
    if (!FileExists($filename)) {
        // file doesn't exist, write away
        $file = fForceOpen($filename, "wb");
        fputcsv($file, array_keys($array), $delim);
        fputcsv($file, $array, $delim);
    } else {
        // file already exists, need to merge headers before writing
        $data = readCsv($filename, $delim);
        $headers = array_flip($data[0]);
        $newHeaders = array_diff_key($array, $headers);
        if (count($newHeaders) > 0) {
            $headers = $headers + $newHeaders;
            $data[0] = array_keys($headers);
            $data[]  = SortArrayLikeArray($array, $headers);
            writeCsv($filename, $data, $delim);
        } else {
            writeCsv($filename, array(SortArrayLikeArray($array, $headers)), $delim, true);
        }
    }
    return $array;
}
/**
 * Reads a full CSV file to an array.
 * @param string $filename The path to the CSV file.
 * @param string $delim A single character noting the delimiter in the file.
 * @param int $length The max length of each line.
 * @return array
 */
function readCsv($filename, $delim = ',', $length = 0)
{
    $file = fopen($filename, "rb");
    $data = array();
    while (($line = fgetcsv($file, $length, $delim)) !== false) {
        $data[] = $line;
    }
    fclose($file);
    return $data;
}
/**
 * Writes a 2D array of data to a CSV file. If the filepath contains a directory
 * that does not exist, the directory will be created using fForceOpen().
 * @param string $filename File to output the file to.
 * @param array $data 2D array of data.
 * @param string $delim A single character noting the delimiter in the file.
 * @param bool $append Change to true to append instead of overwrite the file.
 * @see fForceOpen()
 */
function writeCsv($filename, array $data, $delim = ',', $append = false)
{
    if (true === $append) {
        $mode = "ab";
    } else {
        $mode = "wb";
    }
    $file = fForceOpen($filename, $mode);
    foreach ($data as $datum) {
        fputcsv($file, $datum, $delim);
    }
    fclose($file);
}
/**
 * Opens a file, and creates file's directory if it does not exist.
 * @param string $filename The file to open.
 * @param string $mode The way the file should be opened.
 * @return mixed Returns a file pointer resource on success, or false on error.
 * @see \fopen()
 */
function fForceOpen($filename, $mode)
{
    $dirname = dirname($filename);
    if (!is_dir($dirname)) {
        mkdir($dirname, 0777, true);
    }
    touch($filename);
    return fopen($filename, $mode);
}
/**
 * Recursively escapes an array to prevent passing code along from user input.
 * @param mixed $input
 * @return array
 */
function arrayCleaner($input)
{
    if (is_array($input)) {
        return(array_map('arrayCleaner', $input));
    } else {
      return htmlspecialchars($input, ENT_QUOTES);
    }
}
/**
 * Recursively trims an array's values.
 * @param mixed $input
 * @return array
 */
function trimArrayRecursive($input)
{
    if (is_array($input)) {
        return(array_map('trimArrayRecursive', $input));
    } else {
      return trim($input);
    }
}
/**
 * convert input to encoding if necessary
 * @param mixed $input
 * @param string $encoding
 * @return mixed
 */
function convertArrayEncodingRecursive($input, $encoding) {
    if (is_array($input)) {
        foreach ($input as $key => $value) {
            $input[$key] = convertArrayEncodingRecursive($value, $encoding);
        }
    } else {
        $thisEncoding = mb_detect_encoding($input, 'UTF-8,ISO-8859-1', true);
        // Windows-1252 is always detected as iso-8891-1, even though win is a superset
        if ($thisEncoding === 'ISO-8859-1') { $thisEncoding = 'Windows-1252'; }
        if ($thisEncoding !== $encoding) {
            $input = mb_convert_encoding($input, $encoding, $thisEncoding);
        }
    }
    return $input;
}
/**
 * Converts words separated by space to unspaced camel case.
 * @param string $string
 * @return string
 */
function camelCase($string)
{
    $studlyCase = ucwords(strtolower(trim($string)));
    $noSpace = str_replace(' ', '', $studlyCase);
    $noSpace[0] = strtolower($noSpace[0]);
    return $noSpace;
}
/**
 * Creates global variables for each of an array of key=>value pairs. Numeric
 * keys are prepended with an underscore like this: '_2'.
 * @global mixed $name A variable is made with each key and set to its value.
 * @param array $array The array of variables.
 * @param bool $overwrite Set to 'true' to allow overwriting existing values.
 */
function createAliases(array $array, $overwrite = false)
{
    foreach ($array as $rawName => $tempVal) {
        // remove any unwanted characters
        $strippedName = preg_replace('/[^0-9a-zA-Z_]/', '', $rawName);
        // break apart any camel case into spaced strings
        $brokenName = preg_replace('/[A-Z]/', ' \\0', $strippedName);
        // rejoin all as single camel case string
        $name = camelCase($brokenName);
        // handle illegal characters
        if ($name === '') {
            // variable had no name or had no legal characters
            continue;
        }
        if (is_numeric($name[0])) {
            // variable is numeric, format to a legal name
            $name = '_' . $name;
        }
        // create the global variable from the legal name and set the value
        global $$name;
        if (!isset($$name) OR $overwrite) {
            $$name = $tempVal;
        }
    }
}
/**
 * Makes a copy of a trial with all values removed from the subkeys of the
 * Stimuli, Response, and Procedure keys. Specific keys can be selected for
 * cleaning if resetting the entire trial is unwanted.
 * @param array $trial The trial array.
 * @param string $selections A comma separated string of the subarrays to be
 * cleaned. Leave empty to clean entire trial.
 * @return array
 */
function cleanTrial (array $trial, $selections = '')
{
    if (!empty($selections)) {
        // only cleaning selected arrays in trial
        // clean up the selection names and break into array
        $selectedDirty = explode(',', $selections);
        $selected = array_map(function($str) {
            return trim(strtolower($str));
        }, $selectedDirty);
        // clean selected arrays
        foreach ($trial as $group => &$data) {
            if (in_array(strtolower($group), $selected)) {
                eraseArrayValues($data);
            }
        }
    } else {
        // clean all arrays in trial
        foreach ($trial as $group => &$data) {
            eraseArrayValues($data);
        }
    }
    return $trial;
}
/**
 * Sets all array values to null while preserving the keys.
 * @param array $array
 */
function eraseArrayValues(array &$array)
{
    array_map(function() {return null;}, $array);
}
/**
 * Echoes a 2D array as an HTML table.
 * @staticvar boolean $doInit Keeps track of if the function has been called.
 * @param array $array The array to display.
 * @see print2dArrayCss()
 * @see scalarsToArray()
 * @see getColumnsFrom2d()
 */
function display2dArray(array $array, $nonArrayCol = false)
{
    static $doInit = true;
    if ($doInit) {
        // only print the CSS the first call
        $doInit = false;
        print2dArrayCss();
    }
    // format array and extract columns
    if ($nonArrayCol == false) {
        $i = 0;
        while (is_scalar($array[$i])) {
            unset($array[$i]);
            $i++;
        }
    }
    $arrayNoScalars = scalarsToArray($array);
    $columns = getColumnsFrom2d($arrayNoScalars);
    // write table header
    echo '<table class="display2dArray"><thead><tr><th></th><th><div>',
         implode('</div></th><th><div>', $columns),
         '</div></th></tr></thead><tbody>';
    // write cell values
    foreach ($arrayNoScalars as $i => $row) {
        $row = sortArrayLikeArray($row, array_flip($columns));
        foreach ($row as &$field) {
            $field = htmlspecialchars($field);
        }
        echo '<tr><td>', $i, '</td><td><div>',
             implode('</div></td><td><div>', $row), '</div></td></tr>';
    }
    echo '</tbody></table>';
}
/**
 * Echos the CSS for display2dArray.
 */
function print2dArrayCss()
{
    echo '
      <style>
        .display2dArray          { border-collapse:collapse; }
        .display2dArray td,
        .display2dArray th       { border:1px solid #000;
                                   vertical-align:middle; text-align:center;
                                   padding:2px 6px; overflow:hidden; }
        .display2dArray td       { max-width:200px; }
        .display2dArray th       { max-width:100px; white-space: normal; }
        .display2dArray td > div { max-height:1.5em; overflow:hidden; }
      </style>
    ';
}
/**
 * Converts scalars in a 2D array to arrays with specified key name.
 * @param array $array
 * @param string $keyname
 * @return array
 */
function scalarsToArray(array $array, $keyname = 'Non-array Value')
{
    foreach ($array as &$row) {
        if (is_scalar($row)) {
            $row = array($keyname => $row);
        }
    }
    return $array;
}
/**
 * Gets all the column names from a 2D array.
 * @param array $array
 * @return array
 */
function getColumnsFrom2d(array $array)
{
    $columns = array();
    foreach ($array as $row) {
        // get all the keys from the row
        $columnsMerge = array_merge($columns, array_keys($row));
        // remove duplicates (preserves order)
        $columns = array_keys(array_count_values($columnsMerge));
    }
    return $columns;
}
/**
 * Formats a duration in seconds to something like 03d:02h:03m:20s.
 * @param int $durationInSeconds
 * @return string
 */
function durationFormatted($durationInSeconds)
{
    $hours   = floor($durationInSeconds/3600);
    $minutes = floor(($durationInSeconds - $hours*3600)/60);
    $seconds = $durationInSeconds - $hours*3600 - $minutes*60;
    if ($hours > 23) {
        $days = floor($hours/24);
        $hours = $hours - $days*24;
        if ($days < 10) {
            $days = '0' . $days;
        }
    }
    if ($hours < 10) {
        $hours   = '0' . $hours;
    }
    if ($minutes < 10) {
        $minutes = '0' . $minutes;
    }
    if ($seconds < 10) {
        $seconds = '0' . $seconds;
    }
    return $days.'d:' . $hours.'h:' . $minutes.'m:' . $seconds.'s';
}
/**
 * Formats a time like 5d:2h:3m:20s into seconds.
 * @param string $duration
 * @return int
 */
function durationInSeconds($duration = '')
{
    if ('' === $duration) {
        // no duration was given
        return 0;
    }
    // format the duration and convert to array based on colon delimiters
    $durationArray = explode(':', trim(strtolower($duration)));
    $output = 0;
    foreach ($durationArray as $part) {
        // sanitize each part to just the digits
        $value = preg_replace('/[^0-9]/', '', $part);
        if(false !== stripos($part, 'd')) {
            // days in seconds
            $output += ($value * 24 * 60 * 60);
        } else if (false !== stripos($part, 'h')){
            // hours in seconds
            $output += ($value * 60 * 60);
        } else if (false !== stripos($part, 'm')){
            // minutes in seconds
            $output += ($value * 60);
        } else if (false !== stripos($part, 's')){
            // seconds... in seconds
            $output += $value;
        }
    }
    return $output;
}
# TODO Unclear what ExtractTrial() does or is for.
/**
 * Finds column entries specific to a given $postNumber (e.g., Post 1, Post 2)
 * @param array $procedureRow
 * @param type $postNumber
 * @return array
 */
function ExtractTrial(array $procedureRow, $postNumber)
{
    $output = array();
    if ($postNumber < 1) {
        foreach ($procedureRow as $column => $value) {
            if (substr($column, 0, 5) === 'Post ' && is_numeric($column[5])) {
                continue;
            }
            $output[$column] = $value;
        }
    } else {
        $prefix = 'Post ' . $postNumber;
        $prefixLength = strlen($prefix);
        foreach ($procedureRow as $column => $value) {
            if (substr($column, 0, $prefixLength) !== $prefix) {
                continue;
            }
            $output[substr($column, $prefixLength)] = $value;
        }
    }
    createAliases($output);
    return $output;
}
/**
 * Transliterates special characters (like smart quotes in ISO 8859-1) to the
 * desired encoding. Defaults to 'UTF-8', the standard for web browsers.
 * @param string $string The string to transliterate.
 * @param string $outputEncoding The encoding to transliterate to.
 * @return string
 */
function fixBadChars($string, $outputEncoding = 'UTF-8')
{
    $strEncoding = determineEncoding($string);
    $outEncoding = trim(strtoupper($outputEncoding));
    return iconv($strEncoding, $outEncoding.'//IGNORE//TRANSLIT', $string);
}
/**
 * Reads a CSV file in as an associative array using the header names as array
 * keys. Contents are converted to UTF-8, the row lengths are normalized, and
 * two padding rows are used at the top to ensure that row indices correspond
 * to matching Excel rows.
 * @param string $filename The file to read.
 * @param bool $padding Set false if no padding rows are desired.
 * @param string $delimiter A single character noting the delimiter in the file.
 * @return array
 */
function GetFromFile($filename, $padding = true, $delimiter = ",")
{
    // make sure PHP auto-detects line endings
    ini_set('auto_detect_line_endings', true);
    // read the file in and get the header
    $dataDirty = readCsv($filename, $delimiter);
    $data = trimArrayRecursive($dataDirty);
    $data = convertArrayEncodingRecursive($data, 'UTF-8');
    $columns = array_shift($data);
    $columnsCount = count($columns);
    // make first two indices blank so that others correspond to Excel rows
    // build the rest of the output array
    $out = ($padding == true) ? array(0 => 0, 1 => 0) : array();
    foreach ($data as $row) {
        // add values to row if there are more columns than values
        for ($rowCount = count($row); $columnsCount > $rowCount; $rowCount++) {
            $row[] = '';
        }
        // trim values from row if there are fewer columns than values
        if ($columnsCount < $rowCount) {
            $row = array_slice($row, 0, $columnsCount);
        }
        // convert to column=>value pairs and add to output
        if (!isBlankLine($row)) {
            $out[] = array_combine($columns, $row);
        }
    }
    return $out;
}
/**
 * Finds the location of a trial type's files. Returns either an array of file
 * paths or the boolean false if the type cannot be found.
 * @param string $trialTypeName The name of the trial type
 * @return mixed
 */
function getTrialTypeFiles($trialTypeName) {
    global $_PATH;
    
    // convert user inputs to lowercase; e.g. 'Likert' === 'likert'
    $trialType = strtolower(trim($trialTypeName));
    
    // initialize a static variable to cache the results, so that we can run
    // the function multiple times and efficiently get our data back
    static $trialTypes = array();
    if (isset($trialTypes[$trialType])) return $trialTypes[$trialType];
    
    // as it stands, we have two places where trial types can be found
    // we will search the Experiment/ folder first, so that if we find
    // the trial type, we won't have to look in the Code/ folder
    // this way, the Experiment/ trial types will overwrite the Code/ types
    $customDisplay = $_PATH->get('custom trial display', 'relative', $trialType);
    $normalDisplay = $_PATH->get('trial display',        'relative', $trialType);
    
    if (fileExists($customDisplay)) {
        $pre = 'custom trial ';
    } elseif (fileExists($normalDisplay)) {
        $pre = 'trial ';
    } else {
        return false;
    }
    
    $files = array (
        'display',
        'scoring',
        'helper',
        'script',
        'style'
    );
    
    $foundFiles = array();
    
    foreach ($files as $file) {
        $path = $_PATH->get($pre . $file, 'relative', $trialType);
        $existingPath = fileExists($path);
        if ($existingPath !== false) {
            $foundFiles[$file] = $existingPath;
        }
    }
    
    if (!isset($foundFiles['scoring'])) {
        $foundFiles['scoring'] = $_PATH->get('default scoring');
    }
    
    if (!isset($foundFiles['helper'])) {
        $foundFiles['helper'] = $_PATH->get('default helper');
    }
    
    return $foundFiles;
}

/**
 * Finds all trial types and their files
 * @return array
 */
function getAllTrialTypeFiles() {
    global $_PATH;
    $trialTypes = array();
    $trialTypeDirs = array($_PATH->get('Custom Trial Types'), $_PATH->get('Trial Types'));
    foreach ($trialTypeDirs as $dir) {
        $dirScan = scandir($dir);
        foreach ($dirScan as $entry) {
            $type = strtolower(trim($entry));
            if (isset($trialTypes[$type])) { continue; }    // dont override custom trial types, found first
            $files = getTrialTypeFiles($type);
            if ($files !== false) {
                $trialTypes[strtolower($entry)] = $files;
            }
        }
    }
    return $trialTypes;
}
/**
 * Checks if a given string can be found within another.
 * (Wrapper function for strpos and stripos.)
 * @param string $needle The string to search for.
 * @param string $haystack The string to search within.
 * @param bool $caseSensitive True if the search should be case-sensitive.
 * @return bool
 */
function inString ($needle, $haystack, $caseSensitive = false)
{
    return ($caseSensitive == false && stripos($haystack, $needle) !== false)
        || ($caseSensitive == true && strpos($haystack, $needle) !== false);
}
/**
 * Returns true if each item in an array is empty (or not 0).
 * @param array $array
 * @return boolean
 */
function isBlankLine(array $array)
{
    foreach ($array as $item) {
        if (!empty($item) || $item === 0) {
            return false;
        }
    }
    return true;
}
/**
 * Checks if a key exists within a GetFromFile array.
 * @param array $array The array to search.
 * @param string $key The key to search.
 * @param array $errorArray The array that collects errors.
 * @param type $searched The source of the array being searched (for error log).
 * @return array
 * @see GetFromFile()
 */
function keyCheck(array $array, $key, array $errorArray, $searched)
{
    foreach ($array as $line) {
        if ($line == 0) {
            continue;
        }
        if (array_key_exists($key, $line)) {
            // the key exists, do not modify error collection
            return $errorArray;
        } else {
            // the key does not exist, update error collection
            $errorArray['Count']++;
            $errorArray['Details'][] = "Could not find required column "
                                     . "<b>$key</b> within $searched";
            return $errorArray;
        }
    }
    return $errorArray;
}
/**
 * Creates the trials in login.php
 *
 * @TODO Implement makeTrial
 * Ideas:
 *   Accept a range of stimuli instead of just single stimuli (implode by pipes)
 *   Automatically fill 0 stim items with 'n/a'
 */
function makeTrial()
{
//    global $procedure;
//    global $stimuli;
//    global $allKeysNeeded;
//
//    $trial = array();
//
//    return $trial;
}
/**
 * Merges an input array into a target array. Optionally adds a prefix to the
 * beginning of each array key as it is being added to the target array.
 * @param array $input The array to merge from.
 * @param array $target The array to merge into.
 * @param string $prefix The prefix to add to each key.
 * @return array
 */
function placeData(array $input, array $target, $prefix = '')
{
    foreach ($input as $key => $value) {
        $target[$prefix . $key] = $value;
    }
    return $target;
}
/**
 * Turns a string like '2,4::6' into an array like [2, 4, 5, 6].
 * @param string $string A string indicating how the array should be constructed.
 * @param string $separator A string indicating how the ranges are separated.
 * @param string $rangeIndicator A string that symbolizes a continuous range.
 * @return array
 */
function rangeToArray($string, $separator = ',', $rangeIndicator = '::')
{
    $output = array();
    $ranges = explode($separator, $string);
    foreach ($ranges as $range) {
        // get the end points of the range
        $endPointsDirty = explode($rangeIndicator, $range);
        $endPoints = array_map('trim', $endPointsDirty);
        // update the output array
        $count = count($endPoints);
        if ($count === 1) {
            $output[] = $endPoints[0];
        } else {
            $lastPoint       =& $endPointsDirty[$count-1];
            $stepExploded    =  explode('#', $lastPoint);
            if (isset($stepExploded[1]) AND is_numeric($stepExploded[1])) {
                $step = trim($stepExploded[1]);
            } else {
                $step = 1;
            }
            $lastPoint = trim($stepExploded[0]);
            unset($lastPoint);
            $output = array_merge(
                $output, range($endPoints[0], $endPoints[$count-1], $step)
            );
        }
    }
    return $output;
}
/**
 * Prints an array in a readable manner and appends collapsible tags for CSS and
 * Javascript manipulation. Useful for debugging.
 * @param array $displayArray The array to print.
 * @param string $name The title of the array.
 */
function readable(array $displayArray, $name = "Untitled array")
{
    // convert to string to prevent parsing code
    $clean_displayArray = arrayCleaner($displayArray);
    // echo HTML
    echo '<div>'
          . '<div class="button collapsibleTitle">'
          .     '<h3>' . $name . '</h3>'
          .     '<p>(Click to Open/Close)</p>'
          . '</div>'
          . '<pre>', print_r($clean_displayArray, true), '</pre>'
       . '</div>';
}
/**
 * Removes the label from a string.
 * @param string $input The string to strip the label from.
 * @param string $label The label to strip.
 * @param bool $extendLabel Checks if the label is followed by certain.
 * characters removes them as well. Set false for strict matching to $label.
 * @return mixed
 */
function removeLabel($input, $label, $extendLabel = true)
{
    $inputString = trim($input);
    $inputLower = strtolower($inputString);
    $labelClean = strtolower(trim($label));
    $trimLength = strlen($labelClean);
    if (substr($inputLower, 0, $trimLength) !== $labelClean) {
        return false;
    } else {
        if ($extendLabel) {
            foreach(['s', ' ', ':', '='] as $char) {
                if (substr($inputLower, $trimLength, 1) === $char) {
                    ++$trimLength;
                }
            }
        }
        $output = trim(substr($inputString, $trimLength));
        if (($output === '') || ($output === false)) {
            return true;
        }
        return $output;
    }
}
/**
 * Determine if the string refers to an audio or image file and generate tags.
 * @param string $string
 * @return string
 */
function show($string, $endOnly = true)
{
    global $_PATH;
    // navigate path to Experiment folder (unless linking to external file)
    if (!inString('www.', $string)) {
        $fileName = $_PATH->get('Experiment') . '/' . $string;
        if (FileExists($fileName)) {
            $fileName = FileExists($fileName);
        }
    } else {
        $fileName = $string;
    }
    if ($endOnly) {
        $searchString = substr($fileName, -5);  // only check last 5 characters for file extensions
    } else {
        $searchString = $fileName;
    }
    // check extension to determine which tags to add
    if (strripos($searchString, '.jpg') !== false
        || strripos($searchString, '.jpeg') !== false
        || strripos($searchString, '.png') !== false
        || strripos($searchString, '.gif') !== false
        || strripos($searchString, '.bmp') !== false
    ) {
        // add image tags
        $string = '<img src="' . $fileName . '">';
    } elseif (strripos($searchString, '.mp3')
        || strripos($searchString, '.wav')
        || strripos($searchString, '.ogg')
    ) {
        // audio tags
        $string = '<source src="' . $fileName . '"/>';
    }
    return $string;
}
/**
 * Sorts a multidimensional array using the value of a second-level key. Keys
 * are sorted in an alphanumeric, case-insensitive manner using strnatcasecmp.
 * @param array $input The array to sort.
 * @param string|int $key The key to sort by.
 * @return array
 */
function SortByKey(array $input, $key)
{
    usort($input, function ($a, $b) use ($key) {
        return strnatcasecmp($a[$key], $b[$key]);
    });
    return $input;
}
/**
 * Determines which timing to apply to the current trial.
 * @global string $formClass The CSS timing class to apply to form elements.
 * @global int|string $maxTime Either specifies the amount of time for the trial in
 * seconds, or is a string indicating manual ('user') or computer timing.
 * @global int|string $minTime min time of trial
 * @global int $compTime The trial's specified computer timing, if set.
 * @global int|string $timingReported The timing value indicated by the creator.
 * @global int|string $debugTime Amount of time to use when debuging, if set.
 * @global Config config The experiment configuration file.
 */
function trialTiming()
{
    global $formClass;
    global $maxTime;
    global $minTime;
    global $compTime;
    global $timingReported;
    global $_SESSION;
    global $_CONFIG;
    // determine which timing value to use
    if (is_numeric($timingReported)) {
        // use manually set time if possible
        $maxTime = $timingReported;
    } elseif ($timingReported != 'computer') {
        // if not manual or computer then timing is user
        $maxTime = 'user';
    } elseif (isset($compTime)) {
        // if a $compTime is set then use that
        $maxTime = $compTime;
    } else { $maxTime = 'user'; } // default compTime if none is set
    
    // override time in debug mode, use standard timing if no debug time is set
    if ($_SESSION['Debug'] == true && $_CONFIG->debug_time != '') {
        $maxTime = $_CONFIG->debug_time;
    }
    
    // set class for input form (shows or hides 'submit' button)
    if ($maxTime == 'user') {
        $formClass = 'UserTiming';
    } else {
        $formClass = 'ComputerTiming';
    }
}
/**
 * removes insignificant components of a file path, such as 'dir/../'
 * @param string $path The path to clean
 * @return string
 */
function cleanPath ($path) {
    // Normally, file functions can parse both '\' and '/'
    // as directory separators, but explode() can't, so we
    // will convert all possible separators to standard '/'
    $cleanSeparators = strtr($path, '\\', '/');
    $pathComponents  = explode('/', $path);
    
    // Now lets clean up the path components a little bit.
    // First, create an array to populate with the indices
    // of actual directories, as opposed to '.' and '..'
    $dirs = array();
    // then, start scanning components and removing unneeded
    foreach ($pathComponents as $i => &$comp) {
        $comp = trim($comp);
        // the current directory, '.', is trivial
        if ($comp === '.') {
            unset ($pathComponents[$i]);
            continue;
        }
        // an empty component, '', is also trivial, except in
        // the case where it is the first component, indicating
        // that this is an absolute path in a unix-like OS
        if ($i > 0 AND $comp === '') {
            unset ($pathComponents[$i]);
            continue;
        }
        // The other situation to check for is the case when a
        // directory is entered and then exited, using the parent
        // directory (e.g. 'dir/../').
        // However, if this is used to navigate above the current
        // directory with a relative path, the parent directory
        // component should be left in (e.g., '../file.php').
        // We will keep track if there is a parent directory of
        // the '..' component, keeping in mind that there might
        // be trivial directories (e.g. '.') in between, so we
        // can't just use $i and --$i
        if ($comp === '..') {
            // if we previously navigated into a folder, then its
            // index would have been added to $dirs, but made
            // irrelevant by navigating out with the current '..'
            if ($dirs !== array()) {
                $currentDirIndex = array_pop($dirs);
                unset($pathComponents[$currentDirIndex],
                      $pathComponents[$i]);
            }
        } else {
            // keep track of indices of actual directories that
            // might be rendered irrelevant by an upcoming '..'
            $dirs[] = $i;
        }
    }
    unset($comp);
    $pathComponents = implode('/', $pathComponents); // rejoin into string
    return $pathComponents;
}
/**
 * searches a given directory for a target file or directory
 * @param string $dir The dir to search inside
 * @param string $target The file or directory to find
 * @param bool $findAltExt whether or not to ignore file extensions
 * @param int $findDir Set 0 to only find files
 *                     Set 1 to find files and directories
 *                     Set 2 to only find directories
 * @return string|bool
 */
function find_in_dir($dir, $target, $findAltExt = true, $findDir = 1) {
    // this function is expecting valid file paths
    // so, if you need to trim or remove bad characters,
    // do that before sending them to this function
    
    $findDir = (int) $findDir; // 0: no, 1: yes, 2: only
    
    // efficiency checks
    if (!is_dir($dir) AND $dir !== '') {
        return false; // come on now...
    }
    $test = $dir . '/' . $target;
    if (is_file($test)) {
        if ($findDir < 2) {
            return $target;
        } elseif (!$findAltExt) {
            return false;
        }
    }
    if (is_dir($test)) {
        if ($findDir > 0) {
            return $target;
        } elseif (!$findAltExt) {
            return false;
        }
    }
    
    // we need to search the directory, so lets check for
    // existence and permissions (which might be denied for '/home/')
    if (!is_readable($dir)) {
        // we can't scan the dir, but we can guess by removing
        // the file extension
        $targets = array(strtolower($target), strtoupper($target));
        foreach ($targets as $t) {
            $test = $dir . '/' . $t;
            if (   (is_file($test) AND $findDir < 2)
                OR (is_dir( $test) AND $findDir > 0)
            ) {
                return $t;
            }
        }
        if ($findAltExt AND (strpos($target, '.') !== false)) {
            $target = substr($target, 0, strrpos($target, '.'));
            $targets = array(strtolower($target), strtoupper($target));
            foreach ($targets as $t) {
                $test = $dir . '/' . $t;
                if (   (is_file($test) AND $findDir < 2)
                    OR (is_dir( $test) AND $findDir > 0)
                ) {
                    return $t;
                }
            }
        }
        // else, we can't scan, so we must give up
        return false;
    }
    
    $scandir = scandir($dir);
    $lowerTarget = strtolower($target);
    foreach ($scandir as $entry) {
        $lowerEntry = strtolower($entry);
        if ($lowerEntry === $lowerTarget) {
            $test = $dir . '/' . $entry;
            if (   (is_file($test) AND $findDir < 2)
                OR (is_dir( $test) AND $findDir > 0)
            ) {
                return $entry;
            }
        }
    }
    
    // still haven't found it yet, try alt extensions
    if ($findAltExt) {
        if (strpos($lowerTarget, '.') !== false) {
            $lowerTarget = substr($lowerTarget, 0, strrpos($lowerTarget, '.'));
        }
        foreach ($scandir as $entry) {
            $lowerEntry = strtolower($entry);
            if (strpos($lowerEntry, '.') !== false) {
                $lowerEntry = substr($lowerEntry, 0, strrpos($lowerEntry, '.'));
            }
            if ($lowerEntry === $lowerTarget) {
                $test = $dir . '/' . $entry;
                if (   (is_file($test) AND $findDir < 2)
                    OR (is_dir( $test) AND $findDir > 0)
                ) {
                    return $entry;
                }
            }
        }
    }
    
    // failed to find match, return false
    return false;
}
/**
 * Given a string that is presumably the start of a file path,
 * this will convert the path component into the absolute root of
 * this OS if the given string looks like a root directory
 * otherwise, returns false
 * @param string $dir the path component to examine
 * @return string|bool
 */
function convertAbsoluteDir($dir) {
    // this function expects just the first component of a path
    if ($dir === '' OR substr($dir, 1, 1) === ':') {
        return substr(realpath('/'), 0, -1); // return root without trailing slash
    } else {
        return false;
    }
}
/**
 * Finds a path to a target file, checking the filename and each directory
 * name in the path case-insensitively. If a target file is found, returns
 * the path with the correct, existing casing. Otherwise, returns false.
 * Optionally searches for files with the same name but alternative
 * extensions (defaults to true). Optionally searches for only files
 * ($findDir = 0), files and directories ($findDir = 1), or only
 * directories ($findDir = 2)
 *
 * @param string $path The file to search for.
 * @param bool $findAltExtensions Set false for strict extension checking.
 * @param int  $findDir Set 0 to only return paths to actual files,
 *                      Set 1 to return paths to both files and directories
 *                      Set 2 to only return paths to directories
 * @return string|bool
 */
function fileExists ($path, $findAltExt = true, $findDir = 1) {
    // This function is expecting valid path names.
    // So, if you need to trim or remove bad characters,
    // do that before sending them to this function
    
    // guard against bad input (such as a null path)
    $findDir = (int) $findDir; // 0: no, 1: yes, 2: only
    $path    = (string) $path;
    if ($path === '') { return false; }
    
    // efficiency checks
    if (is_file($path)) {
        if ($findDir < 2) {
            return $path;
        } elseif (!$findAltExt) {
            return false;
        }
    }
    if (is_dir($path)) {
        if ($findDir > 0) {
            return $path;
        } elseif (!$findAltExt) {
            return false;
        }
    }
    
    // -convert Windows directory separators '\' to standard '/'
    // -remove unneeded path elements, such as '.' or 'dir/../'
    // -remove trailing slash
    // -trim each component
    // -this is so we can explode by '/' and correctly identify
    //  each path components (e.g., 'one' and 'two' from 'one\two')
    $path = cleanPath($path);
    $path = explode('/', $path);
    
    // if they only supplied a single component, there is the unlikely
    // case that they are searching for the root directory
    // Let's check for that, before assuming that they are looking for
    // a file or directory in the current working directory
    if (count($path) === 1) {
        $absDir = convertAbsoluteDir($path[0]);
        if ($absDir !== false) {
            // in this case, we have an absolute path of a root directory
            if ($findDir === 0) {
                return false;
            } else {
                // this will give them the actual root directory for this OS
                return $absDir;
            }
        } else {
            // in this case, just try to find a relative target
            return find_in_dir('.', $path[0], $findAltExt, $findDir);
        }
    }
    
    // we are going to search for the final component a bit differently,
    // since it can be either a directory or a file, so lets pull that off
    $finalComponent = array_pop($path);
    
    // now we need to find the directory portion of the path
    // if is_dir() cannot find it, then we will start pulling off
    // components from the end of the path until we get a directory
    // we can locate
    $dirsNotFound = array();
    while (!is_dir(implode('/', $path))) {
        // for the first dir, check if its an absolute or relative dir
        if (count($path) === 1) {
            $absDir = convertAbsoluteDir($path[0]);
            if ($absDir !== false) {
                // if absolute, set the starting path to the actual root
                $path = array($absDir);
            } else {
                $dirsNotFound[] = array_pop($path);
            }
            break; // checking first dir, can't go back any more
        } else {
            // move last dir in $path to start of $dirsNotFound
            $dirsNotFound[] = array_pop($path);
        }
    }
    $dirsNotFound = array_reverse($dirsNotFound); // correct order of dirs
    
    // if $path is empty, not even the first dir could be identified
    // so, we will assume its a relative path
    // otherwise, we are going to use what we could
    if ($path === array()) {
        $baseDir = '.';
    } else {
        $baseDir = implode('/', $path);
    }
    
    // now lets do a case-insensitive search for the rest of the dirs
    foreach ($dirsNotFound as $targetDir) {
        // use find_in_dir, but only search for dirs
        $search = find_in_dir($baseDir, $targetDir, false, 2);
        if ($search === false) { return false; }
        $baseDir .= '/' . $search;
    }
    
    // Huzzah! At this point, we should have found our directory,
    // and we just need to search for the final component
    $finalSearch = find_in_dir($baseDir, $finalComponent, $findAltExt, $findDir);
    if ($finalSearch === false) {
        return false;
    } else {
        $existingPath = $baseDir . '/' . $finalSearch;
        if (substr($existingPath, 0, 2) === './') {
            $existingPath = substr($existingPath, 2);
        }
        return $existingPath;
    }
}
/**
 * Generates a random, lowercase alphanumeric string.
 * @param int $length Optional length of string. Defaults to 10.
 * @return string
 */
function rand_string($length = 10)
{
    $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
    $size = strlen($chars);
    $randString = '';
    for ($i = 0; $i < $length; $i++) {
        $randString .= $chars[mt_rand(0, $size-1)];
    }
    return $randString;
}
/**
 * Prefixes all keys in an array.
 * @param string $prefix
 * @param array $array
 * @return array
 */
function AddPrefixToArray($prefix, array $array)
{
    $out = array();
    foreach ($array as $key => $val) {
        $out[$prefix.$key] = $val;
    }
    return $out;
}
/**
 * Sorts an array to match the order of a template array. Keys in the template
 * that are not present in the target are given null values in the target array.
 * @param array $array The array to sort.
 * @param array $template The sorting template.
 * @return array
 */
function SortArrayLikeArray(array $array, array $template)
{
    $out = array();
    foreach (array_keys($template) as $key) {
        if (isset($array[$key])) {
            $out[$key] = $array[$key];
        } else {
            $out[$key] = null;
        }
    }
    return $out;
}

function getUserAgentInfo()
{
    require_once 'phpbrowscap/Browscap.php';
    
    // phpbrowscap requires a cache; create cache dir if it doesn't exist
    if (!file_exists('phpbrowscap/cache')) {
        mkdir('phpbrowscap/cache', 0777, true);
    }
    
    // get and return the user agent info
    $bc = new phpbrowscap\Browscap('phpbrowscap/cache');
    return $bc->getBrowser();
}
/**
 * Strips the URL scheme (HTTP, HTTPS) from a URL and ensures that the URL
 * starts with '//'.
 * @param string $url
 * @return string
 */
function stripUrlScheme($url)
{
    $stripped = preg_replace("@^(?:https?:)?//@", "//", $url);
    if (0 !== strpos($stripped, '//')) {
        $stripped = '//'.$stripped;
    }
    return $stripped;
}

/**
 * Returns a normalized YouTube link. All links are converted to YouTube's
 * embed format and stripped of all parameters passed as queries.
 * @param string $url The YouTube URL to clean-up
 * @return string
 */
function youtubeUrlCleaner($url, $justReturnId = false)
{
    $urlParts = parse_url(stripUrlScheme($url));
    
    if ('youtu.be' === strtolower($urlParts['host'])) {
        // share links: youtu.be/[VIDEO ID]
        $id = ltrim($urlParts['path'], '/');
    } else if (stripos($urlParts['path'], 'watch') === 1) {
        // watch links: youtube.com/watch?v=[VIDEO ID]
        parse_str($urlParts['query']); 
        $id = $v;
    } else {
        // embed links: youtube.com/embed/[VIDEO ID]
        // API links: youtube.com/v/[VIDEO ID]
        $pathParts = explode('/', $urlParts['path']);
        $id = end($pathParts);
    }
    
    if ($justReturnId) {
        return $id;
    } else {
        return '//www.youtube.com/embed/'.$id;
    }
}
/**
 * Returns a normalized Vimeo link. All links are converted to Vimeo's
 * embed format and stripped of all parameters passed as queries.
 * @param string $url The Vimeo URL to clean-up
 * @return string
 */
function vimeoUrlCleaner($url)
{
    $urlParts = parse_url(stripUrlScheme($url));
    $pathParts = explode('/', $urlParts['path']);
    $id = end($pathParts);
    
    return '//player.vimeo.com/video/'.$id;
}
/**
 * Determines if a file is local or not.
 * @param string $path The path to check
 * @return boolean
 */
function isLocal($path)
{
    return !filter_var($path, FILTER_VALIDATE_URL);
}

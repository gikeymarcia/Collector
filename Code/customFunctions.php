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
        if (!isset($_SESSION['OutputDelimiter'])) {
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
 * finds the location of a trial type's files. Returns either an array of file
 * paths or the boolean false if the type cannot be found.
 * @param string $trialType the name of the trial type
 * @return mixed
 */
function getTrialTypeFiles($trialType) {
    $trialType = strtolower(trim($trialType));  // just in case. . .
    
    // initialize a static variable to cache the results, so that we can run
    // the function multiple times and efficiently get our data back
    static $trialTypes = array();
    if (isset($trialTypes[$trialType])) return $trialTypes[$trialType];
    
    // list all the variables from fileLocations.php that we will need
    // it seemed wasteful to include the file again, so I just use global
    // to grab what I need
    global
        $_rootF,
        $codeF,
        $trialF,
        $expFiles,
        $custTTF,
        $trialTypeDisplay,
        $customScoring,
        $customStyle,
        $customScript,
        $helperFile,
        $defaultScoring,
        $defaultHelper;
    
    // as it stands, we have two places where trial types can be found
    // we will search the Experiment/ folder first, so that if we find
    // the trial type, we won't have to look in the Code/ folder
    // this way, the Experiment/ trial types will overwrite the Code/ types
    $possibleDirs = array(
        $_rootF . $expFiles . $custTTF,
        $_rootF . $codeF    . $trialF
    );
    
    // list the types of files we will be able to use
    $possibleFiles = array(
        'display' => $trialTypeDisplay,
        'scoring' => $customScoring,
        'script'  => $customScript,
        'style'   => $customStyle,
        'helper'  => $helperFile
    );
    
    $trialFiles = array();
    
    foreach ($possibleDirs as $dir) {
        $ttFolder = is_iDir($dir . $trialType);
        if ($ttFolder !== false) {
            foreach ($possibleFiles as $type => $filename) {
                $possibleFile = fileExists($ttFolder . '/' . $filename, true, false);
                if ($possibleFile !== false) $trialFiles[$type] = $possibleFile;
            }
            break; // only use the first matching trial type folder
        }
    }
    
    if ($trialFiles === array()) {
        // when you need to see if the trial type exists, check the return for false
        $trialFiles = false;
    } else {
        if (!isset($trialFiles['scoring'])) {
            // fill in the default scoring if this trial doesn't have custom scoring
            $trialFiles['scoring'] = $_rootF . $codeF . $defaultScoring;
        }
        if (!isset($trialFiles['helper'])) {
            // also find a default helper, for things like $compTime and output columns
            $trialFiles['helper']  = $_rootF . $codeF . $defaultHelper;
        }
    }
    
    // store the results in the static cache
    $trialTypes[$trialType] = $trialFiles;
    return $trialFiles;
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
            $output = array_merge(
                $output, range($endPoints[0], $endPoints[$count-1])
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
    // navigate path to Experiment folder (unless linking to external file)
    if (!inString('www.', $string)) {
        $fileName = '../Experiment/' . $string;
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
    global $config;
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
    if ($_SESSION['Debug'] == true && $config->debug_time != '') {
        $maxTime = $config->debug_time;
    }
    
    // set class for input form (shows or hides 'submit' button)
    if ($maxTime == 'user') {
        $formClass = 'UserTiming';
    } else {
        $formClass = 'ComputerTiming';
    }
}
/**
 * case-insensitive version of is_dir(), returns directory path with existing case if found,
 * returns boolean false if not found
 * @param string $dirname The directory to search for
 * @return string|bool
 */
function is_iDir ($dirname) {
    if (is_dir($dirname)) { return $dirname; }
    $dirs = explode('/', $dirname);
    $foundDir = '.';
    foreach ($dirs as $dir) {
        if (is_dir($foundDir . '/' . $dir)) {
            $foundDir .= '/' . $dir;
            continue;
        }
        $lowerDir = strtolower($dir);
        $currentDir = scandir($foundDir);
        foreach ($currentDir as $dirEntry) {
            if (!is_dir($foundDir . '/' . $dirEntry)) { continue; }
            if (strtolower($dirEntry) === $lowerDir) {
                $foundDir .= '/' . $dirEntry;
                continue 2;
            }
        }
        return false;
    }
    return substr($foundDir, 2);
}
/**
 * Finds a path to a target file, checking the filename and each directory
 * name in the path case-insensitively. If a target file is found, returns
 * the path with the correct, existing casing. Otherwise, returns false.
 * Optionally searches for files with the same name but alternative
 * extensions (defaults to true). Optionally searches for both files
 * and directories (defaults to true, and if set to false, will return
 * false if a directory is found)
 *
 * @param string $path The file to search for.
 * @param bool $findAltExtensions Set false for strict extension checking.
 * @param bool $findDir Set false to only return paths to actual files,
 *                      rather than directories
 * @return string|bool
 */
function fileExists ($path, $findAltExtensions = true, $findDir = true) {
    if (is_file($path)) { return $path; }
    if (is_dir($path)) {
        if ($findDir) {
            return $path;
        } elseif (!$findAltExtensions) {
            return false;
        }
    }
    $path = (string) $path;
    if ($path === '') { return false; }
    $pathinfo = pathinfo($path);
    // find the directory
    if (!isset($pathinfo['dirname'])) { $pathinfo['dirname'] = '.'; }
    if (!is_dir($pathinfo['dirname'])) {
        $pathinfo['dirname'] = is_iDir($pathinfo['dirname']);
        if ($pathinfo['dirname'] === false) { return false; }
        $test = $pathinfo['dirname'] . '/' . $pathinfo['basename'];
        if (is_file($test)) { return $test; }
        if (is_dir($test)) {
            if ($findDir) {
                return $test;
            } elseif (!$findAltExtensions) {
                return false;
            }
        }
    }
    // find the target file or final directory
    $currentDir = scandir($pathinfo['dirname']);
    $lowerBase = strtolower($pathinfo['basename']);
    foreach ($currentDir as $entry) {
        if (strtolower($entry) === $lowerBase) {
            $test = $pathinfo['dirname'] . '/' . $entry;
            if (is_file($test)) { return $test; }
            if (is_dir($test)) {
                if ($findDir) {
                    return $test;
                } elseif (!$findAltExtensions) {
                    return false;
                }
            }
        }
    }
    // check alt extensions
    if (!$findAltExtensions) { return false; }
    $lowerFile = strtolower($pathinfo['filename']);
    foreach ($currentDir as $entry) {
        $baseEntry = strtolower($entry);
        $findDot = strRpos($baseEntry, '.');
        if ($findDot !== false) {
            $baseEntry = substr($baseEntry, 0, -$findDot-1);
        }
        if ($baseEntry === $lowerFile) {
            $test = $pathinfo['dirname'] . '/' . $entry;
            if (is_file($test)) { return $test; }
            if (is_dir($test) AND $findDir) {
                return $test;
            }
        }
    }
    return false;
}
/**
 * @TODO Unclear what ComputeString() does.
 *
 * @TODO Optimize and/or break-up this function.
 *
 * @param type $template
 * @param type $fileData
 * @return type
 */
function ComputeString ($template, $fileData = array()) {
    if (($fileData === array()) && (isset($_SESSION))) {
        $fileData = $_SESSION;
    }
    foreach ($fileData as $key => $value) {
        // sets $username to $fileData[{Username}]
        $fileData[strtolower($key)] = $value;
    }
    $templateParts = explode('_', $template);
    $outputParts = array();
    foreach ($templateParts as $part) {
        if (strpos($part, '$') === false) {
            $outputParts[] = $part;
        } else {
            // e.g., from 'Sess$Session', get 'Sess'
            $str = substr($part, 0, strpos($part, '$'));
            // e.g., from 'Sess$Session', get 'Session'
            $var = substr($part, strpos($part, '$')+1);
            if (strpos($var, '[') === false) {
                if (isset($fileData[$var]) && is_scalar($fileData[$var])) {
                    $str .= $fileData[$var];
                } else {
                    $str .= '$' . $var;
                }
            } else {
                // if they want $_SESSION['Condition']['Condition Description'],
                // we need to search index by index
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
                            $val = null;
                            break;
                        }
                    }
                    if (is_scalar($val)) {
                        $str .= $val;
                    } else {
                        $str .= '$' . $var;
                    }
                } else {
                    // prepend a '$' so that it is obvious a variable was searched for and not found
                    $str .= '$' . $var;
                }
            }
            $outputParts[] = $str;
        }
    }
    return implode('_', $outputParts);
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

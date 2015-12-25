<?php
/*  Collector
    A program for running experiments on the web
    Copyright 2012-2015 Mikey Garcia & Nate Kornell
*/

/**
 * Global functions for Collector.
 */

/**
 * Load settings from common settings, as well as current Experiment folder.
 * The common settings are loaded first and then the Experiment settings are
 * loaded, overwriting any collisions.
 * 
 * @global Pathfinder $_PATH Pathfinder object currently in use.
 * 
 * @param string $currentExp (Optional) Load settings for the given experiment.
 * 
 * @return stdClass Object with a property for each setting.
 */
function getCollectorSettings($currentExp = null)
{
    global $_PATH;
    $settings = Parse::fromConfig($_PATH->get('Common Settings'), true);

    if ($currentExp === null &&
        $_PATH->getDefault('Current Experiment') !== null
    ) {
        $currentExp = $_PATH->getDefault('Current Experiment');
    }

    if ($currentExp !== null) {
        $def = array('Current Experiment' => $currentExp);
        $newSettings = Parse::fromConfig(
            $_PATH->get('Experiment Settings', 'relative', $def)
        );
        foreach ($newSettings as $settingName => $setting) {
            $settings->$settingName = $setting;
        }
    }

    return $settings;
}

/**
 * Create a list of valid experiments found in the Experiments folder.
 * 
 * @global Pathfinder $_PATH Pathfinder object currently in use.
 * 
 * @return array Indexed array of the valid experiments.
 */
function getCollectorExperiments()
{
    global $_PATH;

    $possibleExperiments = scandir($_PATH->get('Experiments'));
    foreach ($possibleExperiments as $i => $possExp) {
        if ($possExp === '.'
            || $possExp === '..'
            || $possExp === '.htaccess'
            || $possExp === $_PATH->get('Common', 'base')
            || !isValidExperimentDir($possExp)
        ) {
            unset($possibleExperiments[$i]);
        }
    }

    return array_values($possibleExperiments);
}

/**
 * Determines if the given experiment name is a valid experiment directory.
 * Checks if the given experiment directory existsand if it has the required 
 * directory tree: index file, settings file, conditions file, stimuli 
 * directory, and procedure directory.
 *
 * @global Pathfinder $_PATH Pathfinder object currently in use.
 * 
 * @param string $expName The name of subdirectory to check in the Experiments folder.
 *
 * @return bool
 */
function isValidExperimentDir($expName)
{
    global $_PATH;

    $default = array('Current Experiment' => $expName);
    $requiredFiles = array('Current Index', 'Conditions', 'Experiment Settings',
        'Stimuli Dir', 'Procedure Dir', );

    foreach ($requiredFiles as $req) {
        $test = $_PATH->get($req, 'relative', $default);

        if (!fileExists($test)) {
            return false;
        }
    }

    return true;
}

/**
 * Add a column (sub-array key) to a 2-D array (like getFromFile() creates).
 *
 * @param array  $array  The array to add to (by-reference).
 * @param string $column The name of the key (column) to add.
 * @param mixed  $value  The value to insert into the column.
 *
 * @see getFromFile()
 */
function addColumn(array &$array, $column, $value = '')
{
    // only compare against lowercase keys to prevent misleading duplicates
    $lowerCol = strtolower($column);
    foreach ($array as $i => &$row) {
        // only compare against lowercase keys to prevent misleading duplicates
        $lowerKeyRow = array_change_key_case($row, CASE_LOWER);

        // skip the first two indices (offsets) and do not overwrite
        if (!is_array($row) || isset($lowerKeyRow[$lowerCol])) {
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
 * Writes and array to a line of a CSV file.
 * 
 * All strings in the array have whitespace converted to single spaces, and then
 * the encoding of the string is converted to the given encoding.
 *
 * @param array  $data        The associative array of data to write.
 * @param string $filename    The path to the file to write to.
 * @param string $delim       The single character delimiter to use.
 * @param bool   $encodeToWin Indicates whether to convert to Win-1252 encoding.
 *
 * @return array
 *
 * @see writeLineToFile()
 */
function arrayToLine(array $data, $filename, $delim = null, $encodeToWin = true)
{
    // set delimiter
    if (null === $delim) {
        $delim = isset($_SESSION['OutputDelimiter']) ? $_SESSION['OutputDelimiter'] : ',';
    }

    // convert encoding
    foreach ($data as &$datum) {
        $datum = whitespaceToSpace($datum);
        if ($encodeToWin) {
            $datum = convertEncoding($datum, 'Windows-1252');
        }
    }

    // write to file
    return writeLineToFile($data, $filename, $delim);
}

/**
 * Converts all whitespace in a string to a single space.
 *
 * @param string $string
 *
 * @return string
 */
function whiteSpaceToSpace($string)
{
    return preg_replace("/[\s]+/", ' ', $string);
}

/**
 * Converts a string of unknown encoding to a desired encoding.
 *
 * @param string $string          The string to convert.
 * @param string $desiredEncoding The desired encoding.
 *
 * @return string
 */
function convertEncoding($string, $desiredEncoding = 'UTF-8')
{
    $currentEncoding = determineEncoding($string);

    return iconv($currentEncoding, $desiredEncoding, $string);
}

/**
 * Determines a string's encoding.
 *
 * @param string $string
 *
 * @return string
 */
function determineEncoding($string)
{
    return mb_detect_encoding($string, mb_detect_order(), true);
}

/**
 * Converts a file's contents' encoding to desired encoding if it does not match.
 *
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
 *
 * @param array  $array    The row to write to the file.
 * @param string $filename The path to the file to write to.
 * @param string $delim    A single character noting the delimiter in the file.
 *
 * @uses readCsv()  Reads the CSV file to which the data will be appended.
 * @uses writeCsv() Write the data back to the CSV file.
 */
function writeLineToFile(array $array, $filename, $delim = ',')
{
    if (!fileExists($filename)) {
        // file doesn't exist, write away
        $file = fForceOpen($filename, 'wb');
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
            $data[] = sortArrayLikeArray($array, $headers);
            writeCsv($filename, $data, $delim);
        } else {
            writeCsv($filename, array(sortArrayLikeArray($array, $headers)), $delim, true);
        }
    }

    return $array;
}

/**
 * Reads a full CSV file to an array.
 *
 * @param string $filename The path to the CSV file.
 * @param string $delim    A single character noting the delimiter in the file.
 * @param int    $length   The max length of each line.
 *
 * @return array
 */
function readCsv($filename, $delim = ',', $length = 0)
{
    if (is_readable($filename)) {
        $file = fopen($filename, 'rb');
        $data = array();
        while (false !== $line = fgetcsv($file, $length, $delim)) {
            $data[] = $line;
        }
        fclose($file);

        return $data;
    }

    // file could not be read
    trigger_error(__FUNCTION__.'('.$filename.'): failed to read file: '
        .'Unreadable or does not exist', E_USER_WARNING);
}

/**
 * Writes a 2D array of data to a CSV file. If the filepath contains a directory
 * that does not exist, the directory will be created using fForceOpen().
 *
 * @param string $filename File to output the file to.
 * @param array  $data     2D array of data.
 * @param string $delim    A single character noting the delimiter in the file.
 * @param bool   $append   Change to true to append instead of overwrite the file.
 *
 * @see fForceOpen()
 */
function writeCsv($filename, array $data, $delim = ',', $append = false)
{
    $mode = (true === $append) ? 'ab' : 'wb';
    $file = fForceOpen($filename, $mode);
    foreach ($data as $datum) {
        fputcsv($file, $datum, $delim);
    }
    fclose($file);
}

/**
 * Opens a file, and creates file's directory if it does not exist.
 *
 * @param string $filename The file to open.
 * @param string $mode     The way the file should be opened.
 *
 * @return mixed Returns a file pointer resource on success, or false on error.
 *
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
 *
 * @param mixed $input The array to clean.
 *
 * @return array The array with all values stripped using htmlspecialchars.
 */
function arrayCleaner($input)
{
    if (is_array($input)) {
        return array_map('arrayCleaner', $input);
    }

    return htmlspecialchars($input, ENT_QUOTES);
}

/**
 * Recursively trims an array's values.
 *
 * @param mixed $input The array to trim.
 *
 * @return array The array with all of its values trimmed.
 */
function trimArrayRecursive($input)
{
    if (is_array($input)) {
        return array_map('trimArrayRecursive', $input);
    }

    return trim($input);
}

/**
 * Converts a value to the desired encoding, recursively.
 *
 * @param mixed  $input    The value to convert.
 * @param string $encoding The encoding to convert to.
 *
 * @return mixed The input value(s) converted to the new encoding.
 * 
 * @todo there are multiple convert encoding functions in customFuncs -- should we consolidate them?
 */
function convertArrayEncodingRecursive($input, $encoding)
{
    if (is_array($input)) {
        foreach ($input as $key => $value) {
            $input[$key] = convertArrayEncodingRecursive($value, $encoding);
        }
    } else {
        $thisEncoding = mb_detect_encoding($input, 'UTF-8,ISO-8859-1', true);
        // Windows-1252 is always detected as iso-8891-1, even though win is a superset
        if ($thisEncoding === 'ISO-8859-1') {
            $thisEncoding = 'Windows-1252';
        }
        if ($thisEncoding !== $encoding) {
            $input = mb_convert_encoding($input, $encoding, $thisEncoding);
        }
    }

    return $input;
}

/**
 * Converts words separated by space to unspaced camel case.
 *
 * @param string $string
 *
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
 * Creates global variables for each of an array of keys => values. Numeric
 * keys are prepended with an underscore like this: '_2'.
 *
 * @global mixed $name A variable is made for each key and set to its value.
 *
 * @param array $array     The array of variables.
 * @param bool  $overwrite Set to 'true' to allow overwriting existing values.
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

        // skip variables with no name or no legal characters
        if ($name === '') {
            continue;
        }

        // convert numeric variables to legal variable name strings
        if (is_numeric($name[0])) {
            $name = '_'.$name;
        }

        // create the global variable from the legal name and set the value
        global $$name;
        if (!isset($$name) || $overwrite) {
            $$name = $tempVal;
        }
    }
}

/**
 * Makes a copy of a trial with all values removed from the subkeys of the
 * Stimuli, Response, and Procedure keys. Specific keys can be selected for
 * cleaning if resetting the entire trial is unwanted.
 *
 * @param array  $trial      The trial array.
 * @param string $selections A comma separated string of the subarrays to be
 *                           cleaned. Leave empty to clean entire trial.
 *
 * @return array
 * 
 * @deprecated Trials are now created differently. This function will be removed.
 */
function cleanTrial(array $trial, $selections = '')
{
    if (!empty($selections)) {
        // only cleaning selected arrays in trial
        // clean up the selection names and break into array
        $selectedDirty = explode(',', $selections);
        $selected = array_map(function ($str) {
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
 *
 * @param array $array The array to erase (by-reference).
 * 
 * @deprecated This function primarly supports the now deprecated function 
 *             'cleanTrial'. This function will be removed
 * @see cleanTrial()
 */
function eraseArrayValues(array &$array)
{
    array_map(function () {return;}, $array);
}

/**
 * Echoes a 2-D array as an HTML table.
 *
 * @staticvar bool $doInit Keeps track of whether the function has been called.
 *
 * @param array $array       The array to display.
 * @param bool  $nonArrayCol Indicates whether or not to include scalar values 
 *                           in the resultant table, or only to create the table
 *                           from the arrays present.
 *
 * @see print2dArrayCss
 * @see scalarsToArray
 * @see getColumnsFrom2d
 */
function display2dArray(array $array, $nonArrayCol = false)
{
    // only print the CSS the first call
    static $doInit = true;
    if ($doInit) {
        $doInit = false;
        print2dArrayCss();
    }

    // format array and extract columns
    if ($nonArrayCol === false) {
        $i = 0;
        while (is_scalar($array[$i])) {
            unset($array[$i]);
            ++$i;
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
            implode('</div></td><td><div>', $row),
            '</div></td></tr>';
    }
    echo '</tbody></table>';
}

/**
 * Echoes the CSS for display2dArray().
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
 * Converts scalars in a 2-D array to arrays with the specified key name.
 *
 * @param array  $array   The array to convert.
 * @param string $keyname The name of the key to use for scalar values.
 *
 * @return array The array with all scalars converted to [$keyname => $value].
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
 * Gets all the column names (keys) from a 2-D array.
 *
 * @param array $array The array to extract the keys from.
 *
 * @return array The extracted keys.
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
 *
 * @param int $durationInSeconds The duration (in seconds) to convert.
 *
 * @return string The converted time string.
 */
function durationFormatted($durationInSeconds)
{
    $min = 60;
    $hour = 60 * 60;
    $day = 60 * 60 * 24;

    $time = array();
    $time['d'] = floor($durationInSeconds / $day);
    $time['h'] = floor(($durationInSeconds - $time['d'] * $day) / $hour);
    $time['m'] = floor(($durationInSeconds - $time['d'] * $day - $time['h'] * $hour) / $min);
    $time['s'] = $durationInSeconds - $time['d'] * $day - $time['h'] * $hour - $time['m'] * $min;
    $output = '';
    foreach ($time as $chunk => $amount) {
        if ($amount > 0) {
            $output .= ($amount < 10) ? "0$amount{$chunk}:" : "$amount{$chunk}:";
        }
    }

    return rtrim($output, ':');
}

/**
 * Formats a time like 5d:2h:3m:20s into seconds.
 *
 * @param string $duration The time string to convert.
 *
 * @return int The number of seconds in the given duration.
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
        if (false !== stripos($part, 'd')) {
            // days in seconds
            $output += ($value * 24 * 60 * 60);
        } elseif (false !== stripos($part, 'h')) {
            // hours in seconds
            $output += ($value * 60 * 60);
        } elseif (false !== stripos($part, 'm')) {
            // minutes in seconds
            $output += ($value * 60);
        } elseif (false !== stripos($part, 's')) {
            // seconds... in seconds
            $output += $value;
        }
    }

    return $output;
}

/**
 * Finds column entries specific to a given $postNumber (e.g., Post 1, Post 2).
 *
 * @param array $procedureRow The procedure row to extract.
 * @param int   $postNumber   The post trial number to extract.
 *
 * @return array The extracted trial.
 *
 * @deprecated Trials are no longer created in a way that this function is 
 *             necessary. This function will be removed.
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
        $prefix = 'Post '.$postNumber;
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
 *
 * @param string $string         The string to transliterate.
 * @param string $outputEncoding The encoding to transliterate to.
 *
 * @return string
 *
 * @todo fixBadChars(): another encoding conversion function
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
 *
 * @param string $filename  The file to read.
 * @param bool   $padding   Set false if no padding rows are desired.
 * @param string $delimiter A single character noting the delimiter in the file.
 *
 * @return array
 */
function getFromFile($filename, $padding = true, $delimiter = ',')
{
    // make sure PHP auto-detects line endings
    ini_set('auto_detect_line_endings', true);

    // read the file in and get the header
    $dataDirty = readCsv($filename, $delimiter);
    $trimmed = trimArrayRecursive($dataDirty);
    $data = convertArrayEncodingRecursive($trimmed, 'UTF-8');
    $columns = array_shift($data);
    $columnsCount = count($columns);

    // make first two indices blank so that others correspond to Excel rows
    // build the rest of the output array
    $out = ($padding == true) ? array(0 => 0, 1 => 0) : array();
    foreach ($data as $row) {
        // add values to row if there are more columns than values
        for ($rowCount = count($row); $columnsCount > $rowCount; ++$rowCount) {
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
 * Finds the location of a trial type's files.
 * 
 * @global Pathfinder $_PATH    The Pathfinder currently in use.
 * 
 * @staticvar array $trialTypes Caches the results of the function.
 * 
 * @param string $trialTypeName The name of the trial type
 * 
 * @return bool|array An array of file paths if the type was found, or false.
 */
function getTrialTypeFiles($trialTypeName)
{
    global $_PATH;

    // convert user inputs to lowercase; e.g. 'Likert' === 'likert'
    $trialType = strtolower(trim($trialTypeName));

    // initialize a static variable to cache the results, so that we can run
    // the function multiple times and efficiently get our data back
    static $trialTypes = array();
    if (isset($trialTypes[$trialType])) {
        return $trialTypes[$trialType];
    }

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

    $files = array('display', 'scoring', 'helper', 'script', 'style', 'validator');

    $foundFiles = array();

    foreach ($files as $file) {
        $path = $_PATH->get($pre.$file, 'relative', $trialType);
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
 * Finds all trial types and their files.
 *
 * @global Pathfinder $_PATH The Pathfinder currently in use.
 * 
 * @return array Associative array of $trialtype => $arrayOfFiles.
 */
function getAllTrialTypeFiles()
{
    global $_PATH;
    $trialTypes = array();
    $trialTypeDirs = array($_PATH->get('Custom Trial Types'), $_PATH->get('Trial Types'));
    foreach ($trialTypeDirs as $dir) {
        $dirScan = scandir($dir);
        foreach ($dirScan as $entry) {
            $type = strtolower(trim($entry));

            // dont override custom trial types, which are found first
            if (isset($trialTypes[$type])) {
                continue;
            }

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
 * (Wrapper function for strpos and stripos.).
 *
 * @param string $needle        The string to search for.
 * @param string $haystack      The string to search within.
 * @param bool   $caseSensitive True if the search should be case-sensitive.
 *
 * @return bool True if the string was found, else false.
 */
function inString($needle, $haystack, $caseSensitive = false)
{
    return ($caseSensitive == false && stripos($haystack, $needle) !== false)
        || ($caseSensitive == true && strpos($haystack, $needle) !== false);
}

/**
 * Returns true if each item in an array is empty (or not 0).
 *
 * @param array $array The array to check.
 *
 * @return bool True if the array was empty, else false.
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
 * Checks if a key exists within a getFromFile array.
 *
 * @param array  $array      The array to search.
 * @param string $key        The key to search.
 * @param array  $errorArray The array that collects errors.
 * @param string $searched   The source of the array being searched (for error log).
 *
 * @return array
 *
 * @see getFromFile()
 * @deprecated This function is now handled within a different class and is no 
 *             longer used in Collector. This function will be removed.
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
            ++$errorArray['Count'];
            $errorArray['Details'][] = 'Could not find required column '
                                     ."<b>$key</b> within $searched";

            return $errorArray;
        }
    }

    return $errorArray;
}

/**
 * Merges an input array into a target array. Optionally adds a prefix to the
 * beginning of each array key as it is being added to the target array.
 *
 * @param array  $input  The array to merge from.
 * @param array  $target The array to merge into.
 * @param string $prefix [Optional] The prefix to add to each key.
 *
 * @return array The merged array.
 */
function placeData(array $input, array $target, $prefix = '')
{
    foreach ($input as $key => $value) {
        $target[$prefix.$key] = $value;
    }

    return $target;
}

/**
 * Turns a string like '2,4::6' into an array like [2, 4, 5, 6].
 *
 * @param string $string         A string indicating how the array should be constructed.
 * @param string $separator      A string indicating how the ranges are separated.
 * @param string $rangeIndicator A string that symbolizes a continuous range.
 *
 * @return array The range of values.
 * 
 * @todo is there any way rangeToArray can be refactored/cleaned-up?
 */
function rangeToArray($string, $separator = ',', $rangeIndicator = '::')
{
    $output = array();
    $ranges = explode($separator, $string);
    $rangesCount = count($ranges);
    $rangesEscaped = array();
    $currentStr = '';
    for ($i = 0; $i < $rangesCount; ++$i) {
        if (isset($ranges[$i][0]) and $ranges[$i][strlen($ranges[$i]) - 1] === '\\') {
            // escaped
            $currentStr .= substr($ranges[$i], 0, -1).$separator; // remove backslash, add separator
        } else {
            $rangesEscaped[] = $currentStr.$ranges[$i];
            $currentStr = null;
        }
    }
    // if the last string appeared escaped, add it in
    if ($currentStr !== null) {
        $rangesEscaped[] = substr($currentStr, 0, -strlen($separator));
    }
    foreach ($rangesEscaped as $range) {
        // get the end points of the range
        $endPointsDirty = explode($rangeIndicator, $range);
        $endPoints = array_map('trim', $endPointsDirty);

        // update the output array
        $count = count($endPoints);
        if ($count === 1) {
            $output[] = $endPoints[0];
        } else {
            $lastPoint = &$endPointsDirty[$count - 1];
            $stepExploded = explode('#', $lastPoint);
            if (isset($stepExploded[1]) and is_numeric($stepExploded[1])) {
                $step = trim($stepExploded[1]);
            } else {
                $step = 1;
            }
            $lastPoint = trim($stepExploded[0]);
            unset($lastPoint);
            $output = array_merge(
                $output, range($endPoints[0], $endPoints[$count - 1], $step)
            );
        }
    }

    return $output;
}

/**
 * Prints an array in a readable manner and appends collapsible tags for CSS and
 * Javascript manipulation. Useful for debugging.
 *
 * @param array  $displayArray The array to print.
 * @param string $name         The title of the array.
 */
function readable(array $displayArray, $name = 'Untitled array')
{
    // convert to string to prevent parsing code
    $clean_displayArray = arrayCleaner($displayArray);
    // echo HTML
    echo '<div>'
          .'<div class="button collapsibleTitle">'
          .'<h3>'.$name.'</h3>'
          .'<p>(Click to Open/Close)</p>'
          .'</div>'
          .'<pre>', print_r($clean_displayArray, true), '</pre>'
       .'</div>';
}

/**
 * Var_dump's the input value inside of pre tags.
 * 
 * @param mixed  $input
 * @param string $label
 * 
 * @deprecated This is not used anywhere in the Collector. This function will be
 *             removed.
 */
function pre_var_dump($input, $label = '')
{
    echo '<pre>';
    if ($label !== '') {
        echo "<b>$label</b><br>";
    }
    var_dump($input);
    echo '</pre>';
}

/**
 * Removes the label from the beginning of a string.
 *
 * @param string $input       The string to strip the label from.
 * @param string $label       The label to strip.
 * @param bool   $extendLabel Checks if the label is followed by certain.
 *                            characters removes them as well. Set false for 
 *                            strict matching to $label.
 *
 * @return mixed The string with the label removed, true if the string only
 *               contained the label, and false if the label was not present.
 */
function removeLabel($input, $label, $extendLabel = true)
{
    $inputString = trim($input);
    $inputLower = strtolower($inputString);
    $labelClean = strtolower(trim($label));
    $trimLength = strlen($labelClean);

    if (substr($inputLower, 0, $trimLength) !== $labelClean) {
        // the first part of the string does not match the label
        return false;
    }

    // remove extra characters for extendLabel
    if ($extendLabel) {
        foreach (['s', ' ', ':', '='] as $char) {
            if (substr($inputLower, $trimLength, 1) === $char) {
                ++$trimLength;
            }
        }
    }

    $output = trim(substr($inputString, $trimLength));

    if (($output === '') || ($output === false)) {
        // output string is empty
        return true;
    }

    return $output;
}

/**
 * Get all the file paths inside a given directory and its subdirectories.
 *
 * @param string $dir Directory to scan.
 *
 * @return array List of complete paths to files.
 */
function scanDirRecursively($dir)
{
    $scan = scandir($dir);
    foreach ($scan as $i => $entry) {
        // do not include relative location markers
        if ($entry === '.' || $entry === '..') {
            unset($scan[$i]);
            continue;
        }

        $path = "{$dir}/{$entry}";

        // do not include directories
        if (is_dir($path)) {
            unset($scan[$i]);

            // get all files from subdirectories
            foreach (scanDirRecursively($path) as $subPath) {
                $scan[] = $subPath;
            }
        } else {
            $scan[$i] = $path;
        }
    }

    return array_values($scan);
}

/**
 * Converts the given stimulus such that it can be shown.
 * Determines if the given string is a path refers to an audio or image file and
 * generates tags if so. Otherwise the string is simply returned as is.
 *
 * @param string $string The string to check.
 * @param bool   $noTags Omit tags even if the file is an image or audio file. 
 *
 * @return string The original string, or the string within appropriate tags.
 */
function show($string, $noTags = false)
{
    global $_PATH;

    // navigate path to Experiment folder (unless linking to external file)
    if (filter_var($string, FILTER_VALIDATE_URL) === false) {
        $filename = $_PATH->get('Common').'/'.$string;
        if (fileExists($filename)) {
            $filename = fileExists($filename);
        }
    }

    // determine whether to add tags to the string
    if ($noTags === false) {
        if (isImage($filename)) {
            $string = '<img src="'.$filename.'">';
        }

        if (isAudio($filename)) {
            $string = '<source src="'.$filename.'"/>';
        }
    }

    return $string;
}

/**
 * Determines if the given path points to an image file.
 * Accepted image types are GIF, JPEG, PNG, and BMP. These are the most widely
 * accepted image types across browsers.
 * 
 * @param string $path The path to the file to check.
 * 
 * @return bool True if the path points to an image, else false.
 */
function isImage($path)
{
    $mimetype = getMimeType($path);
    $imgMimes = array('image/gif', 'image/jpeg', 'image/png', 'image/bmp');

    return ($mimetype !== false) && in_array($mimetype, $imgMimes, true);
}

/**
 * Determines if the given path points to an audio file.
 * 
 * @param string $path The path to the file to check.
 * 
 * @return bool True if the path points to an audio file, else false.
 */
function isAudio($path)
{
    $mimetype = getMimeType($path);
    $imgMimes = array('audio/mpeg3', 'audio/wav', 'audio/ogg');

    return ($mimetype !== false) && in_array($mimetype, $imgMimes, true);
}

/**
 * Determines a file's mime type using finfo or mime_content_type.
 * 
 * @param string $path The path to the file to check.
 * 
 * @return string|bool The mime type of the file (e.g. "audio/mpeg3"), or false.
 */
function getMimeType($path)
{
    $mimetype = false;

    if (function_exists('finfo_fopen')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimetype = finfo_file($finfo, $path);
    } elseif (function_exists('mime_content_type')) {
        $mimetype = @mime_content_type($path);
    }

    return $mimetype;
}

/**
 * Sorts a multidimensional array using the value of a second-level key. Keys
 * are sorted in an alphanumeric, case-insensitive manner using strnatcasecmp.
 *
 * @param array      $input The array to sort.
 * @param string|int $key   The key to sort by.
 *
 * @return array The sorted array.
 * 
 * @deprecated This function is no longer used anywhere in Collector. This
 *             function will be removed.
 */
function SortByKey(array $input, $key)
{
    usort($input, function ($a, $b) use ($key) {
        return strnatcasecmp($a[$key], $b[$key]);
    });

    return $input;
}

/**
 * Determine if the given path points to a valid file or directory.
 * By default, if the file is not immediately found the function will search
 * against only the filenames in the directory (i.e. ignore extensions) and
 * return the first match it finds. The file can additionally be checked
 * strictly as to whether it is a file or a directory with the $findDir
 * argument. That is, if the parameter is set to only search for directories,
 * a path will only be returned if it points to a directory, not a file.
 * 
 * @param string $filename The path to check.
 * @param bool   $altExt   Allows alternate extensions to be searched.
 * @param int    $findDir  Indicates whether (0) only files should be searched,
 *                         (1) both files and directories should be searched, or
 *                         (2) only directories should be searched.
 * 
 * @return string|bool The path to the file, or false if one was not found.
 */
function fileExists($filename, $altExt = true, $findDir = 1)
{
    // normalize the path
    $search = str_replace(DIRECTORY_SEPARATOR, '/', $filename);

    if (!is_file($search) && ($findDir < 2) && ($altExt === true)) {
        // no exact match found, but we can search for other extensions
        $path = altFileExists($search);
    }

    // no file with other ext exists, but a dir might: restore search if false
    $path = isset($path) ? $path : $search;

    // alter output based on $findDir's value
    if (($findDir === 1 && file_exists($path))
        || ($findDir === 0 && is_file($path))
        || ($findDir === 2 && is_dir($path))
    ) {
        return $path;
    }

    return false;
}

/**
 * Determines if any files in the directory of the given path match the filename
 * of the given path, regardless of extension.
 * For a path like "path/to/some/file.php", the first file found that matches
 * the path regardless of the extension will be returned, like 
 * "path/to/some/file.txt". 
 * 
 * @param string $path The path to check.
 * 
 * @return string|bool The file with the alternative extension, else false.
 */
function altFileExists($path)
{
    $path_parts = pathinfo($path);
    if (is_dir($path_parts['dirname'])) {
        foreach (scandir($path_parts['dirname']) as $file) {
            $filename = pathinfo($file, PATHINFO_FILENAME);
            if (strtolower($path_parts['filename']) === strtolower($filename)) {
                return $path_parts['dirname'].'/'.$file;
            }
        }
    }

    return false;
}

/**
 * Generates a random, lowercase alphanumeric string.
 *
 * @param int $length [Optional] The length of string.
 *
 * @return string The generated random string.
 */
function randString($length = 10)
{
    $chars = 'abcdefghijklmnopqrstuvwxyz0123456789';
    $size = strlen($chars);
    $randString = '';
    for ($i = 0; $i < $length; ++$i) {
        $randString .= $chars[mt_rand(0, $size - 1)];
    }

    return $randString;
}

/**
 * Prefixes all keys in an array.
 *
 * @param string $prefix The prefix to add to each key.
 * @param array  $array  The array to modify.
 *
 * @return array The modified array.
 */
function addPrefixToArray($prefix, array $array)
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
 *
 * @param array $array    The array to sort.
 * @param array $template The sorting template.
 *
 * @return array The sorted array.
 */
function sortArrayLikeArray(array $array, array $template)
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

/**
 * Retrieves user-agent information.
 * 
 * @return array The array of user-agent information.
 * 
 * @see phpbrowscap\Browscap->getBrowser()
 */
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
 * Returns a normalized YouTube link. All links are converted to YouTube's
 * embed format and stripped of all parameters passed as queries.
 *
 * @param string $url          The YouTube URL to clean-up
 * @param bool   $justReturnId Indicates whether only the video's ID should be
 *                             returned (true), or the full link (false).
 *
 * @return string The normalized YouTube link with parameters stripped.
 */
function youtubeUrlCleaner($url, $justReturnId = false)
{
    $urlParts = parse_url(stripUrlScheme($url));

    if ('youtu.be' === strtolower($urlParts['host'])) {
        // share links: youtu.be/[VIDEO ID]
        $id = ltrim($urlParts['path'], '/');
    } elseif (stripos($urlParts['path'], 'watch') === 1) {
        // watch links: youtube.com/watch?v=[VIDEO ID]
        parse_str($urlParts['query']);
        $id = $v;
    } else {
        // embed links: youtube.com/embed/[VIDEO ID]
        // API links: youtube.com/v/[VIDEO ID]
        $pathParts = explode('/', $urlParts['path']);
        $id = end($pathParts);
    }

    return $justReturnId ? $id : '//www.youtube.com/embed/'.$id;
}

/**
 * Returns a normalized Vimeo link. All links are converted to Vimeo's
 * embed format and stripped of all parameters passed as queries.
 *
 * @param string $url          The Vimeo URL to clean-up
 * @param bool   $justReturnId Indicates whether only the video's ID should be
 *                             returned (true), or the full link (false).
 *
 * @return string The normalized Vimeo link with all parameters stripped.
 */
function vimeoUrlCleaner($url, $justReturnId = false)
{
    $urlParts = parse_url(stripUrlScheme($url));
    $pathParts = explode('/', $urlParts['path']);
    $id = end($pathParts);

    return $justReturnId ? $id : '//player.vimeo.com/video/'.$id;
}

/**
 * Strips the URL scheme (HTTP, HTTPS) from a URL and ensures that the URL
 * starts with '//'.
 *
 * @param string $url The URL to strip the scheme from.
 *
 * @return string The stripped URL.
 */
function stripUrlScheme($url)
{
    $stripped = preg_replace('@^(?:https?:)?//@', '//', $url);
    if (0 !== strpos($stripped, '//')) {
        $stripped = '//'.$stripped;
    }

    return $stripped;
}

/**
 * Determines if a file is local or not.
 *
 * @param string $path The path to check.
 *
 * @return bool True if the file is local.
 * 
 * @todo isLocal only checks if the file is a URL or not, not whether it acually exists locally.
 */
function isLocal($path)
{
    return !filter_var($path, FILTER_VALIDATE_URL);
}

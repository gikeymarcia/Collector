<?php

namespace Collector;

use phpbrowscap\Browscap;

/**
 * Description of Helpers
 *
 * @author Adam Blake <adamblake@g.ucla.edu>
 */
class Helpers
{
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
     *
     * @global Pathfinder $_PATH Pathfinder object currently in use.
     *
     * @return array Indexed array of the valid experiments.
     */
    public static function getCollectorExperiments()
    {
        global $_PATH;

        $possibleExperiments = scandir($_PATH->get('Experiments'));
        foreach ($possibleExperiments as $i => $possExp) {
            if ($possExp === '.'
                || $possExp === '..'
                || $possExp === '.htaccess'
                || $possExp === $_PATH->get('Common', 'base')
                || !self::isValidExperimentDir($possExp)
            ) {
                unset($possibleExperiments[$i]);
            }
        }

        return array_values($possibleExperiments);
    }

    /**
     * Indicates whether the named experiment is a valid collector experiment.
     *
     * @param string $name The name of the experiment to check.
     *
     * @return bool True if the experiment exists and is valid, else false.
     */
    public static function issetCollectorExperiment($name)
    {
            $flippedExpts = array_flip(self::getCollectorExperiments());

            return isset($flippedExpts[$name]);
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
    public static function isValidExperimentDir($expName)
    {
        global $_PATH;

        $default = array('Current Experiment' => $expName);
        $requiredFiles = array(
            'Current Index', 'Conditions',
            'Stimuli Dir', 'Procedure Dir',
        );

        foreach ($requiredFiles as $req) {
            $test = $_PATH->get($req, 'relative', $default);

            if (!self::fileExists($test)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Add a column (sub-array key) to a 2-D array (like self::getFromFile() creates).
     *
     * @param array  $array  The array to add to (by-reference).
     * @param string $column The name of the key (column) to add.
     * @param mixed  $value  The value to insert into the column.
     *
     * @see self::getFromFile()
     */
    public static function addColumn(array &$array, $column, $value = '')
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
                // cast to string to match self::getFromFile() contents
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
     * @see self::writeLineToFile()
     */
    public static function arrayToLine(array $data, $filename, $delim = null, $encodeToWin = true)
    {
        // set delimiter
        if (null === $delim) {
            $delim = isset($_SESSION['OutputDelimiter']) ? $_SESSION['OutputDelimiter'] : ',';
        }

        // convert encoding
        foreach ($data as &$datum) {
            $datum = self::whiteSpaceToSpace($datum);
            if ($encodeToWin) {
                $datum = self::convertEncoding($datum, 'Windows-1252');
            }
        }

        // write to file
        return self::writeLineToFile($data, $filename, $delim);
    }

    /**
     * Converts all whitespace in a string to a single space.
     *
     * @param string $string
     *
     * @return string
     */
    public static function whiteSpaceToSpace($string)
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
    public static function convertEncoding($string, $desiredEncoding = 'UTF-8')
    {
        $currentEncoding = self::determineEncoding($string);

        return iconv($currentEncoding, $desiredEncoding, $string);
    }

    /**
     * Determines a string's encoding.
     *
     * @param string $string
     *
     * @return string
     */
    public static function determineEncoding($string)
    {
        return mb_detect_encoding($string, mb_detect_order(), true);
    }

    /**
     * Converts a file's contents' encoding to desired encoding if it does not match.
     *
     * @param string $filename
     * @param string $desiredEncoding
     */
    public static function convertFileEncoding($filename, $desiredEncoding = 'UTF-8')
    {
        $contents = file_get_contents($filename);
        if ($desiredEncoding !== self::determineEncoding($contents)) {
            file_put_contents($filename, self::convertEncoding($contents));
        }
    }

    /**
     * Writes a single row to a CSV file, merging headers before writing, if needed.
     *
     * @param array  $array    The row to write to the file.
     * @param string $filename The path to the file to write to.
     * @param string $delim    A single character noting the delimiter in the file.
     *
     * @uses self::readCsv()  Reads the CSV file to which the data will be appended.
     * @uses self::writeCsv() Write the data back to the CSV file.
     */
    public static function writeLineToFile(array $array, $filename, $delim = ',')
    {
        if (!self::fileExists($filename)) {
            // file doesn't exist, write away
            $file = self::fForceOpen($filename, 'wb');
            fputcsv($file, array_keys($array), $delim);
            fputcsv($file, $array, $delim);
        } else {
            // file already exists, need to merge headers before writing
            $data = self::readCsv($filename, $delim);
            $headers = array_flip($data[0]);
            $newHeaders = array_diff_key($array, $headers);
            if (count($newHeaders) > 0) {
                $headers = $headers + $newHeaders;
                $data[0] = array_keys($headers);
                $data[] = self::sortArrayLikeArray($array, $headers);
                self::writeCsv($filename, $data, $delim);
            } else {
                self::writeCsv($filename, array(self::sortArrayLikeArray($array, $headers)), $delim, true);
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
    public static function readCsv($filename, $delim = ',', $length = 0)
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
            .'Unself::readable or does not exist', E_USER_WARNING);
    }

    /**
     * Writes a 2D array of data to a CSV file. If the filepath contains a directory
     * that does not exist, the directory will be created using self::fForceOpen().
     *
     * @param string $filename File to output the file to.
     * @param array  $data     2D array of data.
     * @param string $delim    A single character noting the delimiter in the file.
     * @param bool   $append   Change to true to append instead of overwrite the file.
     *
     * @see self::fForceOpen()
     */
    public static function writeCsv($filename, array $data, $delim = ',', $append = false)
    {
        $mode = (true === $append) ? 'ab' : 'wb';
        $file = self::fForceOpen($filename, $mode);
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
    public static function fForceOpen($filename, $mode)
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
    public static function arrayCleaner($input)
    {
        if (is_array($input)) {
            return array_map('self::arrayCleaner', $input);
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
    public static function trimArrayRecursive($input)
    {
        if (is_array($input)) {
            return array_map('self::trimArrayRecursive', $input);
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
     * @todo there are multiple convert encoding public static functions in customFuncs -- should we consolidate them?
     */
    public static function convertArrayEncodingRecursive($input, $encoding)
    {
        if (is_array($input)) {
            foreach ($input as $key => $value) {
                $input[$key] = self::convertArrayEncodingRecursive($value, $encoding);
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
    public static function camelCase($string)
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
    public static function createAliases(array $array, $overwrite = false)
    {
        foreach ($array as $rawName => $tempVal) {
            // remove any unwanted characters
            $strippedName = preg_replace('/[^0-9a-zA-Z_]/', '', $rawName);

            // break apart any camel case into spaced strings
            $brokenName = preg_replace('/[A-Z]/', ' \\0', $strippedName);

            // rejoin all as single camel case string
            $name = self::camelCase($brokenName);

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
     * Echoes a 2-D array as an HTML table.
     *
     * @staticvar bool $doInit Keeps track of whether the public static function has been called.
     *
     * @param array $array       The array to display.
     * @param bool  $nonArrayCol Indicates whether or not to include scalar values
     *                           in the resultant table, or only to create the table
     *                           from the arrays present.
     *
     * @see self::print2dArrayCss
     * @see self::scalarsToArray
     * @see self::getColumnsFrom2d
     */
    public static function display2dArray(array $array, $nonArrayCol = false)
    {
        // only print the CSS the first call
        static $doInit = true;
        if ($doInit) {
            $doInit = false;
            self::print2dArrayCss();
        }

        // format array and extract columns
        if ($nonArrayCol === false) {
            $i = 0;
            while (is_scalar($array[$i])) {
                unset($array[$i]);
                ++$i;
            }
        }

        $arrayNoScalars = self::scalarsToArray($array);
        $columns = self::getColumnsFrom2d($arrayNoScalars);

        // write table header
        echo '<table class="self::display2dArray"><thead><tr><th></th><th><div>',
            implode('</div></th><th><div>', $columns),
            '</div></th></tr></thead><tbody>';

        // write cell values
        foreach ($arrayNoScalars as $i => $row) {
            $row = self::sortArrayLikeArray($row, array_flip($columns));
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
     * Echoes the CSS for self::display2dArray().
     */
    public static function print2dArrayCss()
    {
        echo '
          <style>
            .self::display2dArray          { border-collapse:collapse; }
            .self::display2dArray td,
            .self::display2dArray th       { border:1px solid #000;
                                       vertical-align:middle; text-align:center;
                                       padding:2px 6px; overflow:hidden; }
            .self::display2dArray td       { max-width:200px; }
            .self::display2dArray th       { max-width:100px; white-space: normal; }
            .self::display2dArray td > div { max-height:1.5em; overflow:hidden; }
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
    public static function scalarsToArray(array $array, $keyname = 'Non-array Value')
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
    public static function getColumnsFrom2d(array $array)
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
    public static function formatDuration($durationInSeconds)
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
    public static function durationInSeconds($duration = '')
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
     * Transliterates special characters (like smart quotes in ISO 8859-1) to the
     * desired encoding. Defaults to 'UTF-8', the standard for web browsers.
     *
     * @param string $string         The string to transliterate.
     * @param string $outputEncoding The encoding to transliterate to.
     *
     * @return string
     *
     * @todo self::fixBadChars(): another encoding conversion public static function
     */
    public static function fixBadChars($string, $outputEncoding = 'UTF-8')
    {
        $strEncoding = self::determineEncoding($string);
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
    public static function getFromFile($filename, $padding = true, $delimiter = ',')
    {
        // make sure PHP auto-detects line endings
        ini_set('auto_detect_line_endings', true);

        // read the file in and get the header
        $dataDirty = self::readCsv($filename, $delimiter);
        $trimmed = self::trimArrayRecursive($dataDirty);
        $data = self::convertArrayEncodingRecursive($trimmed, 'UTF-8');
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
            if (!self::isBlankLine($row)) {
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
     * @staticvar array $trialTypes Caches the results of the public static function.
     *
     * @param string $trialTypeName The name of the trial type
     *
     * @return bool|array An array of file paths if the type was found, or false.
     */
    public static function getTrialTypeFiles($trialTypeName)
    {
        global $_PATH;

        // convert user inputs to lowercase; e.g. 'Likert' === 'likert'
        $trialType = strtolower(trim($trialTypeName));

        // initialize a static variable to cache the results, so that we can run
        // the public static function multiple times and efficiently get our data back
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

        if (self::fileExists($customDisplay)) {
            $pre = 'custom trial ';
        } elseif (self::fileExists($normalDisplay)) {
            $pre = 'trial ';
        } else {
            return false;
        }

        $files = array('display', 'scoring', 'helper', 'script', 'style', 'validator');

        $foundFiles = array();

        foreach ($files as $file) {
            $path = $_PATH->get($pre.$file, 'relative', $trialType);
            $existingPath = self::fileExists($path);
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
    public static function getAllTrialTypeFiles()
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

                $files = self::getTrialTypeFiles($type);
                if ($files !== false) {
                    $trialTypes[strtolower($entry)] = $files;
                }
            }
        }

        return $trialTypes;
    }

    /**
     * Checks if a given string can be found within another.
     * (Wrapper public static function for strpos and stripos.).
     *
     * @param string $needle        The string to search for.
     * @param string $haystack      The string to search within.
     * @param bool   $caseSensitive True if the search should be case-sensitive.
     *
     * @return bool True if the string was found, else false.
     */
    public static function inString($needle, $haystack, $caseSensitive = false)
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
    public static function isBlankLine(array $array)
    {
        foreach ($array as $item) {
            if (!empty($item) || $item === 0) {
                return false;
            }
        }

        return true;
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
    public static function placeData(array $input, array $target, $prefix = '')
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
     * @todo is there any way self::rangeToArray can be refactored/cleaned-up?
     */
    public static function rangeToArray($string, $separator = ',', $rangeIndicator = '::')
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
     * Prints an array in a self::readable manner and appends collapsible tags for CSS and
     * Javascript manipulation. Useful for debugging.
     *
     * @param array  $displayArray The array to print.
     * @param string $name         The title of the array.
     *
     * @todo make it public static function cleanly as it did in Collector 1.0
     */
    public static function readable(array $displayArray, $name = 'Untitled array')
    {
        // convert to string to prevent parsing code
        $clean_displayArray = self::arrayCleaner($displayArray);
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
     * Used to debug code and inspect arrays
     *
     * @param mixed  $input
     * @param string $label
     *
     */
    public static function pre_var_dump($input, $label = '')
    {
        echo '<pre class="pre_var_dump">';
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
    public static function removeLabel($input, $label, $extendLabel = true)
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
    public static function scanDirRecursively($dir)
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
                foreach (self::scanDirRecursively($path) as $subPath) {
                    $scan[] = $subPath;
                }
            } else {
                $scan[$i] = $path;
            }
        }

        return array_values($scan);
    }

    /**
     * Determine if the string refers to an audio or image file and generate tags.
     * @param string $string
     * @return string
     */
    public static function show($string, $endOnly = true)
    {
        global $_PATH;
        // navigate path to Experiment folder (unless linking to external file)
        if (!self::inString('www.', $string)) {
            $fileName = $_PATH->get('Media') . '/' . $string;
            if (self::fileExists($fileName)) {
                $fileName = self::fileExists($fileName);
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
     * removes insignificant components of a file path, such as 'dir/../'
     * @param string $path The path to clean
     * @return string
     */
    public static function cleanPath ($path) {
        // Normally, file public static functions can parse both '\' and '/'
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
    public static function findInDir($dir, $target, $findAltExt = true, $findDir = 1) {
        // this public static function is expecting valid file paths
        // so, if you need to trim or remove bad characters,
        // do that before sending them to this public static function

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
    public static function convertAbsoluteDir($dir) {
        // this public static function expects just the first component of a path
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
    public static function fileExists ($path, $findAltExt = true, $findDir = 1) {
        // This public static function is expecting valid path names.
        // So, if you need to trim or remove bad characters,
        // do that before sending them to this public static function

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
        $path = self::cleanPath($path);
        $path = explode('/', $path);

        // if they only supplied a single component, there is the unlikely
        // case that they are searching for the root directory
        // Let's check for that, before assuming that they are looking for
        // a file or directory in the current working directory
        if (count($path) === 1) {
            $absDir = self::convertAbsoluteDir($path[0]);
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
                return self::findInDir('.', $path[0], $findAltExt, $findDir);
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
                $absDir = self::convertAbsoluteDir($path[0]);
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
            // use self::findInDir, but only search for dirs
            $search = self::findInDir($baseDir, $targetDir, false, 2);
            if ($search === false) { return false; }
            $baseDir .= '/' . $search;
        }

        // Huzzah! At this point, we should have found our directory,
        // and we just need to search for the final component
        $finalSearch = self::findInDir($baseDir, $finalComponent, $findAltExt, $findDir);
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
     * Determine if the given path points to a valid file or directory.
     * By default, if the file is not immediately found the public static function will search
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
    public static function fileExistsAlt($filename, $altExt = true, $findDir = 1)
    {
        // normalize the path
        $search = str_replace(DIRECTORY_SEPARATOR, '/', $filename);

        if (!is_file($search) && ($findDir < 2) && ($altExt === true)) {
            // no exact match found, but we can search for other extensions
            $path = altself::fileExists($search);
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
    public static function altFileExists($path)
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
    public static function randString($length = 10)
    {
        $chars = 'abcdefghijklmnopqrstuvwxyz0123456789';
        $size = strlen($chars);
        $out = '';
        for ($i = 0; $i < $length; ++$i) {
            $out .= $chars[mt_rand(0, $size - 1)];
        }

        return $out;
    }

    /**
     * Prefixes all keys in an array.
     *
     * @param string $prefix The prefix to add to each key.
     * @param array  $array  The array to modify.
     *
     * @return array The modified array.
     */
    public static function addPrefixToArray($prefix, array $array)
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
    public static function sortArrayLikeArray(array $array, array $template)
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
    public static function getUserAgentInfo()
    {
        // phpbrowscap requires a cache; create cache dir if it doesn't exist
        if (!file_exists('phpbrowscap/cache')) {
            mkdir('phpbrowscap/cache', 0777, true);
        }

        // get and return the user agent info
        $bc = new Browscap('phpbrowscap/cache');

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
    public static function youtubeUrlCleaner($url, $justReturnId = false)
    {
        $urlParts = parse_url(self::stripUrlScheme($url));

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
    public static function vimeoUrlCleaner($url, $justReturnId = false)
    {
        $urlParts = parse_url(self::stripUrlScheme($url));
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
    public static function stripUrlScheme($url)
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
     * @todo self::isLocal only checks if the file is a URL or not, not whether it acually exists locally.
     */
    public static function isLocal($path)
    {
        return !filter_var($path, FILTER_VALIDATE_URL);
    }
}

<?php

use phpbrowscap\Browscap;

/**
 *
 */
function get_Collector_experiments(FileSystem $_files) {
    $experiment_names = $_files->read('Experiments');

    foreach ($experiment_names as $i => $exp) {
        $temp_defaults = array('Current Experiment' => $exp);

        if ($_files->read('Conditions', $temp_defaults) === array()) {
            unset($experiment_names[$i]);
        }
    }

    return $experiment_names;
}
/**
 * creates an experiment by reading the relevant stim and proc files of
 * either a given or a randomly assigned condition
 */
function create_experiment(FileSystem $_files, $condition_index = null) {
    $condition = ConditionAssignment::get($_files, $condition_index);

    $stimuli   = load_exp_files($_files, 'Stimuli',   $condition);
    $procedure = load_exp_files($_files, 'Procedure', $condition);

    return array(
        'Condition' => $condition,
        'Stimuli'   => $stimuli,
        'Procedure' => $procedure,
        'Position'  => array(1, 0)
    );
}
/**
 * reads and shuffles a string of comma-separated stimuli or procedure files
 *
 * @param string     $filenames comma-delimited filenames to load
 * @param string     $type      "Stimuli" or "Procedure"
 * @param FileSystem $_files    the method of locating and loading files
 *
 * @return array the data from all the csvs, combined and shuffled
 */
function load_exp_files(FileSystem $_files, $type, $condition) {
    $files      = array();
    $file_index = 1;

    while (isset($condition["$type $file_index"])) {
        $files[] = $condition["$type $file_index"];
        ++$file_index;
    }

    $all_data = array();

    foreach ($files as $file) {
        $file_data = $_files->read($type, array($type => $file));
        $all_data  = array2d_merge($all_data, $file_data);
    }

    require_once $_files->get_path('Shuffle Functions');
    $all_data = multiLevelShuffle($all_data);
    $all_data = shuffle2dArray($all_data);

    return $all_data;
}

/**
 * combines the rows of 2 associative arrays so that every row has each header
 * in the same order
 *
 * @param array $arr1 the first array to combine
 * @param array $arr2 the second array to combine
 *
 * @return array the two arrays combined
 */
function array2d_merge($arr1, $arr2) {
    $all_headers = array();

    foreach (array($arr1, $arr2) as $arr) {
        $first_row = reset($arr);

        if ($first_row !== false) $all_headers += $first_row;
    }

    $all_headers = array_keys($all_headers);

    $all_data = array();

    foreach (array($arr1, $arr2) as $arr) {
        foreach ($arr as $row) {
            $merged_row = array();

            foreach ($all_headers as $header) {
                if (isset($row[$header])) {
                    $merged_row[$header] = $row[$header];
                } else {
                    $merged_row[$header] = '';
                }
            }

            $all_data[] = $merged_row;
        }
    }

    return $all_data;
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
function formatDuration($durationInSeconds)
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
 * Get all the file paths inside a given directory and its subdirectories.
 *
 * @param string $dir Directory to scan.
 *
 * @return array List of complete paths to files.
 * @TODO: why are there two scan dirs?
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
 * removes insignificant components of a file path, such as 'dir/../'
 * @param string $path The path to clean
 * @return string
 */
function cleanPath ($path) {
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
 * Searches a given directory for a target file or directory.
 *
 * @param string $dir        The directory to search inside.
 * @param string $target     The file or directory to look for.
 * @param bool   $findAltExt If TRUE, files with the same name but different
 *                           extensions will be matched.
 * @param int    $findDir    Set 0 to only find files, 1 to find files and
 *                           directories, or 2 to only find directories.
 *
 * @return string|bool Returns the path to the file if it was found, else
 *                     false.
 */
function findInDir($dir, $target, $findAltExt = true,
    $findDir = 1
) {
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
function convertAbsoluteDir($dir) {
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
 * @param string $path     The file to search for.
 * @param bool $findAltExt Set false for strict extension checking.
 * @param int  $findDir    Set 0 to only return paths to actual files, 1 to
 *                         return paths to both files and directories, or 2
 *                         to only return paths to directories
 *
 * @return string|bool Returns the path to the file if it was found, else
 *                     false.
 */
function fileExists ($path, $findAltExt = true, $findDir = 1) {
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
            return findInDir('.', $path[0], $findAltExt, $findDir);
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
        // use findInDir, but only search for dirs
        $search = findInDir($baseDir, $targetDir, false, 2);
        if ($search === false) { return false; }
        $baseDir .= '/' . $search;
    }

    // Huzzah! At this point, we should have found our directory,
    // and we just need to search for the final component
    $finalSearch = findInDir($baseDir, $finalComponent, $findAltExt, $findDir);
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
 *
 * @param int $length [Optional] The length of string.
 *
 * @return string The generated random string.
 */
function rand_string($length = 10)
{
    $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $out = '';
    for ($i = 0; $i < $length; ++$i) {
        $out .= $chars[mt_rand(0, 35)];
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

// Gregor Macgregor at http://stackoverflow.com/questions/25232975/php-filter-inputinput-server-request-method-returns-null
/**
 * Pulls a variable from a superglobal, using the provided filter
 *
 * Fixes issue where $_SERVER is not populated on fast-cgi servers
 *
 * @param int $type One of INPUT_GET, INPUT_POST, INPUT_COOKIE, INPUT_SERVER, or INPUT_ENV
 * @param string $variable_name Name of a variable to get.
 * @param $filter [Optional] The ID of the filter to apply, default FILTER_DEFAULT
 * @param mixed $options [Optional] Associative array of options or bitwise disjunction of flags.
 *                                  If filter accepts options, flags can be provided in "flags" field of array.
 *
 * @return mixed null if not found, else variable with provided filter
 */
function filter_input_fix($type, $variable_name, $filter = FILTER_DEFAULT, $options = NULL )
{
    $checkTypes = array(
        INPUT_GET,
        INPUT_POST,
        INPUT_COOKIE
    );

    if ($options === NULL) {
        // No idea if this should be here or not
        // Maybe someone could let me know if this should be removed?
        $options = FILTER_NULL_ON_FAILURE;
    }

    if (in_array($type, $checkTypes) || filter_has_var($type, $variable_name)) {
        return filter_input($type, $variable_name, $filter, $options);
    } else if ($type == INPUT_SERVER && isset($_SERVER[$variable_name])) {
        return filter_var($_SERVER[$variable_name], $filter, $options);
    } else if ($type == INPUT_ENV && isset($_ENV[$variable_name])) {
        return filter_var($_ENV[$variable_name], $filter, $options);
    } else {
        return NULL;
    }
}

/**
 * prints information about a value
 *
 * @param mixed $data the data to be printed
 */
function data_dump($data) {
    require_once __DIR__ . '/vendor/kint/Kint.class.php';
    d($data);
}

/**
 * replaces substrings wrapped in square brackets inside a larger string
 *
 * @param string $string the template containing the substrings to replace
 * @param array $inputs assoc array where instances of "[$key]" are replaced
 *                      with the value, while numeric keys are used to
 *                      replace successive instances of [var]
 * return string
 */
function fill_template($string, $inputs, $throw_exception_on_incomplete_template = true) {
    $vars = array();

    foreach ($inputs as $key => $val) {
        if (is_numeric($key)) {
            $vars[] = $val;
            unset($inputs[$key]);
        }
    }
    unset($inputs['var']);

    $components = explode('[', $string);

    $output = $components[0];
    unset($components[0]);

    foreach ($components as $var_and_static_string) {
        $var_and_static_array = explode(']', $var_and_static_string);
        $var_key = $var_and_static_array[0];

        if ($var_key === 'var') {
            if (count($vars) < 1) {
                if ($throw_exception_on_incomplete_template) {
                    throw new Exception(
                        'Not enough vars for template: ' . $string
                    );
                } else {
                    return false;
                }
            }

            $output .= array_shift($vars);
        } else {
            if (!isset($inputs[$var_key])) {
                if ($throw_exception_on_incomplete_template) {
                    throw new Exception(
                        'Missing input with key: ' . $var_key
                    );
                } else {
                    return false;
                }
            }

            $output .= $inputs[$var_key];
        }

        if (isset($var_and_static_array[1])) {
            $output .= $var_and_static_array[1];
        }
    }

    return $output;
}


function Collector_session_start() {
    $sess_dir = __DIR__ . '/../Data/sess';
    if (!is_dir($sess_dir)) mkdir($sess_dir, 0777, true);

    session_save_path($sess_dir);
    session_start();
}

function Collector_prepare_autoloader() {
    $code_folder = __DIR__;
    require "$code_folder/classes/Autoloader.php";
    $autoloader = new Collector\Autoloader();
    $autoloader->register();
    $autoloader->add('Collector', "$code_folder/classes");
    $autoloader->add('phpbrowscap', "$code_folder/vendor/phpbrowscap");
}

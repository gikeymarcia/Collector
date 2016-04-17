<?php
/**
 * Pathfinder class.
 */

namespace Collector;

/**
 * Provides access to a system map.
 *
 * Goals:
 * - provide a way to use system map, so that you can ask for
 *   each file or directory simply by a label
 * - provide a way to create the paths to access these files
 * - this way, a file's location can be independent from its
 *   purpose, and files can be moved/renamed without trouble
 *
 * Requirements:
 * - this class is expecting to be able to require the
 *   systemMap.php file from the already existing include
 *   paths. So, keep systemMap.php in the same directory
 * - this class is also expecting the label "Pathfinder"
 *   to exist, so that it can use that to figure out where
 *   the program root directory is
 *
 * How to use:
 * - once the systemMap.php file has mapped out the file
 *   structure, this class will flip that array, so that
 *   the labels each become a key in an associative array,
 *   pointing to the path they came from
 * - So, if there was something like
 *   'dir => array( 'file.php' => 'Test' ), then this class
 *   will create $_pathList['Test'] = 'dir/file.php'
 *
 * - to get that path, you can either access it directly,
 *   by using $_PATH->test, or by using the get() function,
 *   $_PATH->get('Test').
 * - when accessing directly, the name must be in all
 *   lowercase, and the spaces should be replaced with
 *   underscores. So, 'Custom Functions' would be
 *   accessed as $_PATH->custom_functions
 * - if you use the get() function, casing doesn't matter,
 *   and you can still use the original spaces.
 *
 * - furthermore, the get() function let's you pass a few
 *   additional parameters
 * - the first one is the type of path you want to get back
 * - by default, you get the relative path, which is formed
 *   by prepending some number of '../' to navigate back to
 *   the root of the  program, and then using the path
 *   calculated from the system map
 * - you can instead ask for 'absolute', 'url', 'base',
 *   'root', or 'static'
 *
 * - 'absolute' will give you the absolute path for that OS
 * - 'url' will craft a url to that directory or file
 * - 'base' will give just the last component of the path,
 *   which is either the file name or the last directory
 * - 'root' will give you the path from the program root,
 *   without prepending the ../ needed to get back to
 *   the program root
 * - 'static' will give you the path after the last variable
 *   directory in the path, so dir/{var}/subdir/file.php
 *   would become subdir/file.php
 *
 * - there are two kinds of variables used to construct these
 *   paths
 * - the first kind is the typical variable, which requires
 *   you to pass part of the path along with your get() function
 *   call, as the third parameter
 * - this would be used with something like a trial type name,
 *   which can vary, but ultimately points to the same
 *   directory structure.
 *   in the example 'test' => 'dir/{var}/subdir/file.php', using
 *   $_PATH->get('test', 'relative', 'instruct') from inside Code/
 *   would return '../dir/instruct/subdir/file.php'
 *
 * - certain paths may have more than one variable
 * - when then occurs, passing a variable will replace both entries
 * - if you want to use different variables for each step, instead
 *   pass an array of variables. Each will be used once, in order
 *
 * - the second kind of variable is the $default setting, which is
 *   used along with a default label wrapped in curly braces,
 *   'test' => 'dir/{Username}/output.csv'
 * - in this case, you would first load a default value for
 *   'Username', by using the setDefault() function, like so:
 *   $_PATH->setDefault('Username', 'participant001');
 * - then, when you call $_PATH->get('test'), you would receive
 *   'dir/participant001/subdir/file.php'
 * - when you initialize the Pathfinder, you can pass it a reference
 *   to some external variable, and the defaults will be shared with
 *   that array. The file 'initiateCollector.php' has already been
 *   set up to use the $_SESSION['Pathfinder'] array for this purpose,
 *   so that on every page, all the defaults set previously are able
 *   to be accessed.
 * - therefore, defaults are good for values that won't be changing
 *   much, like username info, while wild cards are used to frequently
 *   changing values, like trial types
 *
 * - if you ever lose the external reference to these defaults, you can
 *   restore the link by using $_PATH->setDefaultsCopy($newArray)
 * - this way, if you need to wipe the session, but for some reason want
 *   to keep using the same defaults, you can simply call this function
 *   after the session wipe. Although, if you are looking for security,
 *   you should probably just recreate the $_PATH variable with the
 *   wiped session, to start completely fresh
 *
 * - you can also check the default value, using getDefault()
 */
class Pathfinder
{
    /**
     * The associative array of all the names => paths.
     *
     * @var array
     */
    private $pathList = array();

    /**
     * The relative, absolute, and url paths to the root from current location.
     *
     * @var array
     */
    private $rootPath = array(
        'relative' => '',
        'absolute' => '',
        'url' => '',
    );

    /**
     * The directory name.
     *
     * @var string
     *
     * @todo what is $dirName used for? update property docblock
     */
    private $dirName = 'dir name';

    /**
     * The variable name.
     *
     * @var string
     *
     * @todo what is $varName used for? update property docblock
     */
    private $varName = 'var';

    /**
     * The default values for the Pathfinder.
     *
     * @var array
     */
    private $defaults = array();

    /**
     * Constructor.
     *
     * @param array $defaultHolder An external array passed by-reference to hold
     *                             the default values.
     */
    public function __construct(array &$defaultHolder = null)
    {
        $map = $this->getFileMap();
        $path = $this->convertFileMapToPathList($map);

        foreach ($path as $name => $thisPath) {
            $name = $this->cleanPathfinderName($name);
            $this->pathList[$name] = $thisPath;
        }

        $this->setRootPaths();

        // if $defaultHolder was passed, make sure it is an array then use it
        if (func_num_args() > 0) {
            $this->setDefaultsCopy($defaultHolder);
        }

        $this->updateHardcodedPaths();
    }

    /**
     * Retrieves the system map from file.
     *
     * @return array An associative array of all named paths in system map.
     */
    private function getFileMap()
    {
        return require 'systemMap.php';
    }

    /**
     * Converts the system map array from a tree to a list.
     *
     * @param array $systemMap The system map from Pathfinder::getFileMap().
     *
     * @return array The system map with trees joined.
     */
    private function convertFileMapToPathList($systemMap)
    {
        $list = $this->array_flip_multiDim($systemMap);

        $dirName = strtolower($this->dirName);
        $dirNameLen = strlen($dirName);

        foreach ($list as &$path) {
            if (strtolower(substr($path, -$dirNameLen)) === $dirName) {
                $path = dirname($path);
            }

            $path = strtr($path, '\\', '/');

            if ($path === '/') {
                $path = '';
            }
        }

        return $list;
    }

    /**
     * Performs array_flip on a multidimensional array.
     * Takes an associative array, takes all non-array values and makes them the
     * keys, joining each array key with a separator.
     *
     * @param array  $array The array to flip.
     * @param string $sep   The character to use when joining keys.
     *
     * @return array The flipped array.
     */
    public function array_flip_multiDim(array $array, $sep = '/')
    {
        $list = array();

        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $values = $this->array_flip_multiDim($value);
                foreach ($values as &$subKey) {
                    $subKey = $key.$sep.$subKey;
                }
            } else {
                $values = array($value => $key);
            }
            $this->addUniqueToArray($list, $values);
        }

        return $list;
    }

    /**
     * Adds a key => value pair to an array, throwing an exception if the key
     * already exists.
     *
     * @param array $array    The array (by-reference) to modify.
     * @param array $mergedIn The key => value pair to merge in.
     *
     * @throws Exception Throws if the key already exists in the array.
     */
    public function addUniqueToArray(array &$array, array $mergedIn)
    {
        foreach ($mergedIn as $key => $value) {
            if (isset($array[$key])) {
                throw new Exception(
                    'Could not add "'.$key.'" to array in '.__FUNCTION__.
                    ', this key already exists.'
                );
            }
            $array[$key] = $value;
        }
    }

    /**
     * Cleans a string.
     * Trims, lowercases, and replaces internal spaces with underscores.
     *
     * @param string $name The string to clean.
     *
     * @return string The cleaned string.
     */
    public function cleanPathfinderName($name)
    {
        return str_replace(' ', '_', strtolower(trim($name)));
    }

    /**
     * Finds the relative path, absolute path, and url path to the root.
     *
     * @throws Exception Throws if root cannoth be found.
     */
    private function setRootPaths()
    {
        $i = 0;
        $test = $this->get('Pathfinder');
        $relRoot = '';
        $urlRoot = dirname($this->getURL().'a');

        while (!is_file('./'.$relRoot.$test)) {
            $relRoot .= '../';
            $urlRoot = dirname($urlRoot);
            if (++$i > 5) {
                throw new Exception('Root path not found.');
            }
        }

        $roots = array(
            'relative' => trim($relRoot, '/'),
            'absolute' => realpath($relRoot),
            'url' => $urlRoot,
        );

        foreach ($roots as $root => $path) {
            $path = strtr($path, '\\', '/');
            $this->rootPath[$root] = $path;
        }
    }

    /**
     * Gets the current URL, complete with protocol.
     *
     * @return string The current URL.
     */
    public function getURL()
    {
        $port = filter_input(INPUT_SERVER, 'SERVER_PORT', FILTER_SANITIZE_NUMBER_INT);
        $https = filter_input(INPUT_SERVER, 'HTTPS', FILTER_SANITIZE_STRING);
        $domain = filter_input(INPUT_SERVER, 'HTTP_HOST', FILTER_SANITIZE_URL);
        $resource = filter_input(INPUT_SERVER, 'REQUEST_URI', FILTER_SANITIZE_URL);

        // from http://stackoverflow.com/q/4503135
        if ($port === 443 || ($https !== null && $https !== 'off')) {
            $protocol = 'https://';
        } else {
            $protocol = 'http://';
        }

        return $protocol.$domain.$resource;
    }

    /**
     * Sets the array of defaults to a reference of some external array.
     *
     * @param mixed $copy The external array (other types will be converted).
     */
    public function setDefaultsCopy(&$copy)
    {
        $defaults = $this->defaults;

        $this->defaults = &$copy;

        if (!is_array($this->defaults)) {
            $this->defaults = $defaults;
        } else {
            foreach ($defaults as $key => $value) {
                $this->defaults[$key] = $value;
            }
        }
    }

    /**
     * Updates all of the path names using the values from Pathfinder::_pathList.
     *
     * @throws Exception If the path names are not unique.
     */
    private function updateHardcodedPaths()
    {
        $path = $this->pathList;
        foreach (array_keys($path) as $name) {
            $name = $this->cleanPathfinderName($name);
            unset($this->$name);    // clear everything, so we can write it anew
        }
        foreach (array_keys($path) as $name) {
            $name = $this->cleanPathfinderName($name);
            if (isset($this->$name)) {
                throw new Exception(
                    'Bad path name: "'.$name.'" unavailable. '.
                    'Make sure names are unique, case-insensitive.'
                );
            }
            $this->$name = $this->get($name);
        }
    }

    /**
     * Gets the path from the systemMap.
     *
     * @param string       $name      The name of the path.
     * @param string       $type      Type of path to get: relative, absolute, or url.
     * @param array|string $variables Variables for modifying the path.
     *
     * @return string The requested path.
     *
     * @throws Exception Throws if the requested path name does not exist.
     */
    public function get($name, $type = 'relative', $variables = array())
    {
        $cleanName = $this->cleanPathfinderName($name);
        if (!isset($this->pathList[$cleanName])) {
            throw new Exception('"'.$name.'" not found in '.__CLASS__.' path list.');
        }

        $rawpath = $this->pathList[$cleanName];

        // example: getting "support/includes/fileLocations.php"
        switch ($type) {
            // return: fileLocations.php
            case 'base':
                $parts = explode('/', $rawpath);
                $path = array_pop($parts);
                break;

            // return: support/includes/fileLocations.php
            case 'root':
                $path = $rawpath;
                break;

            // return: everything after the last variable path component (if vars are included)
            case 'static':
                $path = explode('}', $rawpath);
                $path = array_pop($path);
                $path = explode('/', $path);
                array_shift($path);
                $path = implode('/', $path);
                $path = trim($path, '/');
                break;

            // return: C:/wamp/www/Collector/support/includes/fileLocations.php
            case 'absolute':
                $path = $this->rootPath['absolute'].'/'.$rawpath;
                break;

            // return: http://localhost/Collector/support/includes/fileLocations.php
            case 'url':
                $path = $this->rootPath['url'].'/'.$rawpath;
                break;

            // 'relative', return: ../support/includes/fileLocations.php
            default:
                if ($this->rootPath['relative'] !== '') {
                    $pre = $this->rootPath['relative'].'/';
                }
                $path = isset($pre) ? $pre.$rawpath : $rawpath;
        }

        $path = $this->updateVariableString($path, $variables);

        if (substr($path, -2) === '/.') {
            $path = substr($path, 0, -2);
        }

        return $path;
    }

    /**
     * Modifies a string using the passed variables.
     *
     * @param string       $string    The string to modify.
     * @param array|string $variables The array of variables, or string to use.
     *
     * @return string The modified string.
     */
    private function updateVariableString($string, $variables)
    {
        $defaults = array_change_key_case($this->defaults, CASE_LOWER);

        if (is_array($variables)) {
            foreach ($variables as $i => $var) {
                if (!is_numeric($i)) {
                    unset($variables[$i]);
                    $defaults[strtolower($i)] = $var; // allow variables to overwrite defaults, temporarily
                }
            }
        }

        $varKeyword = $this->varName;

        $explodeLeftBrace = explode('{', $string);
        $output = array_shift($explodeLeftBrace);

        foreach ($explodeLeftBrace as $stringWithVar) {
            $explodeRightBrace = explode('}', $stringWithVar);

            $rawVar = array_shift($explodeRightBrace);
            $var = strtolower(trim($rawVar));

            if ($var === $varKeyword) {
                $insert = '{'.$varKeyword.'}'; // will do later, but needs to be trim() and strtolower()
            } elseif (isset($defaults[$var])) {
                $insert = $defaults[$var];
            } else {
                $insert = '{'.$rawVar.'}'; // missing default
            }

            $output .= $insert.$explodeRightBrace[0];
        }

        $varKeyword = '{'.$varKeyword.'}';

        if (is_array($variables)) {
            $varMarker = '/'.preg_quote($varKeyword).'/';
            foreach ($variables as $var) {
                $output = preg_replace($varMarker, $var, $output, 1);
            }
        } else {
            if (is_string($variables)) {
                $output = str_replace($varKeyword, $variables, $output);
            }
        }

        return $output;
    }

    /**
     * Gets all paths that do not contain a wild card or a default value.
     * Use this to get a list of all non-variable paths. You can then run
     * Helpers::fileExists() on them, as a diagnostic check that all typical files exist.
     *
     * @return array The array of standard paths.
     */
    public function getStandardPaths()
    {
        $paths = array();
        foreach ($this->pathList as $name => $path) {
            if (strpos($path, '{') === false) {
                $paths[$name] = $path;
            }
        }

        return $paths;
    }

    /**
     * Replaces a path component default with a new value.
     * Can either pass an associative array to set multiple defaults at once,
     * or a single key and value as separate parameters.
     *
     * @param array|string $arrayOrKey The array of keys => values to set, or a
     *                                 single key to set.
     * @param string       $value      The value to set if a single key is passed.
     */
    public function setDefault($arrayOrKey, $value = null)
    {
        if (is_array($arrayOrKey)) {
            $newDefaults = $arrayOrKey;
        } else {
            $newDefaults = array($arrayOrKey => $value);
        }

        foreach ($newDefaults as $key => $value) {
            $this->defaults[$key] = $value;
        }

        $this->updateHardcodedPaths();
    }

    /**
     * Gets the value of the desired default setting.
     *
     * @param string $key The name of the setting to return.
     *
     * @return string|null The value of the setting or null.
     */
    public function getDefault($key)
    {
        return isset($this->defaults[$key]) ? $this->defaults[$key] : null;
    }

    /**
     * Clears all the defaults that exist in the Pathfinder.
     */
    public function clearDefaults()
    {
        $this->defaults = array();
    }

    /**
     * Determines if current URL targets specified path.
     *
     * @param string $loc The path to check against.
     *
     * @return bool True if the path matches current location.
     */
    public function atLocation($loc)
    {
        $script = filter_input(INPUT_SERVER, 'SCRIPT_FILENAME', FILTER_SANITIZE_URL);
        $target = $this->get($loc, 'absolute');
        $current = strtr(realpath($script), '\\', '/');

        return $current === $target;
    }

    /**
     * Checks if current URL targets a file inside the specified directory.
     *
     * @param string $dir The directory to check against.
     *
     * @return bool True if the directory holds the current file.
     */
    public function inDir($dir)
    {
        $dirUrl = $this->get($dir, 'url');
        $current = $this->getURL();

        return (stripos($current, $dirUrl) === false) ? false : true;
    }

    /**
     * Sends a string appropriate for calling a stylesheet in the HTML header.
     *
     * @param string $selector The stylesheet to get the element for.
     */
    public function getStylesheetTag($selector)
    {
        $path = $this->get($selector);

        return "<link href='$path' rel='stylesheet' type='text/css'/>";
    }

    /**
     * Sends a string appropriate for calling a script in the HTML header.
     *
     * @param string $selector The script to get the element for.
     */
    public function getScriptTag($selector)
    {
        $path = $this->get($selector);

        return "<script src='$path' type='text/javascript'></script>";
    }
}

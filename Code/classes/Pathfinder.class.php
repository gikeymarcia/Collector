<?php
    // Pathfinder class
    //
    // Goals:
    //     -provide a way to use system map, so that you can ask for
    //      each file or directory simply by a label
    //     -provide a way to create the paths to access these files
    //     -this way, a file's location can be independent from its
    //      purpose, and files can be moved/renamed without trouble
    //
    // Requirements:
    //     -this class is expecting to be able to require the 
    //      systemMap.php file from the already existing include
    //      paths. So, keep systemMap.php in the same directory
    //     -this class is also expecting the label "Pathfinder"
    //      to exist, so that it can use that to figure out where
    //      the program root directory is
    //
    // How to use:
    //     -once the systemMap.php file has mapped out the file
    //      structure, this class will flip that array, so that
    //      the labels each become a key in an associative array,
    //      pointing to the path they came from
    //     -So, if there was something like 
    //      'dir => array( 'file.php' => 'Test' ), then this class
    //      will create $_pathList['Test'] = 'dir/file.php'
    //
    //     -to get that path, you can either access it directly,
    //      by using $_PATH->test, or by using the get() function,
    //      $_PATH->get('Test').
    //     -when accessing directly, the name must be in all 
    //      lowercase, and the spaces should be replaced with
    //      underscores. So, 'Custom Functions' would be 
    //      accessed as $_PATH->custom_functions
    //     -if you use the get() function, casing doesn't matter,
    //      and you can still use the original spaces.
    //
    //     -furthermore, the get() function let's you pass a few
    //      additional parameters
    //     -the first one is the type of path you want to get back
    //     -by default, you get the relative path, which is formed
    //      by prepending some number of '../' to navigate back to
    //      the root of the  program, and then using the path
    //      calculated from the system map
    //     -you can instead ask for 'absolute', 'url', 'base',
    //      'root', or 'static'
    //
    //     -'absolute' will give you the absolute path for that OS
    //     -'url' will craft a url to that directory or file
    //     -'base' will give just the last component of the path,
    //      which is either the file name or the last directory
    //     -'root' will give you the path from the program root,
    //      without prepending the ../ needed to get back to
    //      the program root
    //     -'static' will give you the path after the last variable
    //      directory in the path, so dir/{var}/subdir/file.php
    //      would become subdir/file.php
    //
    //     -there are two kinds of variables used to construct these
    //      paths
    //     -the first kind is the typical variable, which requires
    //      you to pass part of the path along with your get() function
    //      call, as the third parameter
    //     -this would be used with something like a trial type name,
    //      which can vary, but ultimately points to the same 
    //      directory structure.
    //      in the example 'test' => 'dir/{var}/subdir/file.php', using
    //      $_PATH->get('test', 'relative', 'instruct') from inside Code/
    //      would return '../dir/instruct/subdir/file.php'
    //
    //     -certain paths may have more than one variable
    //     -when then occurs, passing a variable will replace both entries
    //     -if you want to use different variables for each step, instead
    //      pass an array of variables. Each will be used once, in order
    //
    //     -the second kind of variable is the $default setting, which is
    //      used along with a default label wrapped in curly braces,
    //      'test' => 'dir/{Username}/output.csv'
    //     -in this case, you would first load a default value for 
    //      'Username', by using the setDefault() function, like so:
    //      $_PATH->setDefault('Username', 'participant001');
    //     -then, when you call $_PATH->get('test'), you would receive
    //      'dir/participant001/subdir/file.php'
    //     -when you initialize the Pathfinder, you can pass it a reference
    //      to some external variable, and the defaults will be shared with
    //      that array. The file 'initiateCollector.php' has already been
    //      set up to use the $_SESSION['Pathfinder'] array for this purpose,
    //      so that on every page, all the defaults set previously are able
    //      to be accessed.
    //     -therefore, defaults are good for values that won't be changing
    //      much, like username info, while wild cards are used to frequently
    //      changing values, like trial types
    //
    //     -if you ever lose the external reference to these defaults, you can
    //      restore the link by using $_PATH->setDefaultsCopy($newArray)
    //     -this way, if you need to wipe the session, but for some reason want
    //      to keep using the same defaults, you can simply call this function
    //      after the session wipe. Although, if you are looking for security,
    //      you should probably just recreate the $_PATH variable with the 
    //      wiped session, to start completely fresh
    //
    //     -you can also check the default value, using getDefault()
    //
    //
    //     Public functions
    //
    //     -get($pathName, (optional) $pathType, (opt string or array) $variables)
    //      gets the path from the systemMap. See above for detailed explanation
    //
    //     -setDefault((string or array) $defaultKey, (optional string) $value)
    //      can either pass an associative array, or the key and value as separate
    //      parameters. Allows replacement of a path component {key} with $value
    //
    //     -getDefault($defaultName)
    //      returns value of this default name, or null if not set
    //
    //     -array_flip_multiDim($array)
    //      takes an associative array, takes all non-valuesand makes them the
    //      keys, joining each array key with aseparator as the second 
    //      parameter, default '/'
    //
    //     -addUniqueToArray($addedArray, $existingArray)
    //      adds a key => value pair to an array, throwing
    //      an exception if the key already exists
    //
    //     -cleanPathfinderName($string)
    //      trims, lowercases, and replaces internal 
    //      spaces with underscores for a string
    //
    //     -getURL() - returns the current URL, complete with protocol
    //
    //     -getStandardPaths() - gets all paths that dont contain a wild card
    //      or a default value
    //
    //     -setDefaultsCopy(&$array)
    //      sets the array of defaults to a reference of some external array
    //
    //     -clearDefaults() - clears all the defaults that exist in the pathfinder
    //
    //     -atLocation($pathName) checks if current URL targets specified path
    //
    //     -inDir($pathName) checks if current URL targets file inside specified directory
    
    
    class Pathfinder
    {
        private $_pathList  = array ();
        private $_rootPath  = array (
            'relative' => '',
            'absolute' => '',
            'url'      => '',
        );
        
        private $_dirName = 'dir name';
        private $_varName = 'var';
        
        private $_defaults = array();
        
        function __construct(&$defaultHolder = null) {
            $map  = $this->getFileMap();
            $path = $this->convertFileMapToPathList($map);
            
            foreach ($path as $name => $thisPath) {
                $name = $this->cleanPathfinderName($name);
                $this->_pathList[$name] = $thisPath;
            }
            
            $this->setRootPaths();
            
            // if $defaultHolder was passed, make sure it is an array, then get ready to use it
            if (func_num_args() > 0) {
                $this->setDefaultsCopy($defaultHolder);
            }
            
            $this->updateHardcodedPaths();
        }
        
        private function getFileMap() {
            require 'systemMap.php';
            return $systemMap;
        }
        
        private function convertFileMapToPathList($systemMap) {
            $list = $this->array_flip_multiDim($systemMap);
            
            $dirName    = strtolower($this->_dirName);
            $dirNameLen = strlen($dirName);
            
            foreach ($list as &$path) {
                if (strtolower(substr($path, -$dirNameLen)) === $dirName) {
                    $path = dirname($path);
                }
                
                $path = strtr($path, '\\', '/');
                
                if ($path === '/') { $path = ''; }
            }
            
            return $list;
        }
        
        public function array_flip_multiDim($array, $sep = '/') {
            $list = array();
            
            foreach ($array as $key => $value) {
                if (is_array($value)) {
                    $values = $this->array_flip_multiDim($value);
                    foreach ($values as &$subKey) {
                        $subKey = $key . $sep . $subKey;
                    }
                    unset($val);
                } else {
                    $values = array($value => $key);
                }
                $this->addUniqueToArray($list, $values);
            }
            
            return $list;
        }
        
        public function addUniqueToArray(array &$array, array $mergedIn) {
            foreach ($mergedIn as $key => $value) {
                if (isset($array[$key])) {
                    throw new Exception(
                        'Could not add "'.$key.'" to array in ' . __FUNCTION__ .
                        ', this key already exists.'
                    );
                }
                $array[$key] = $value;
            }
        }
        
        public function cleanPathfinderName($name) {
            $name = strtolower(trim($name));
            $name = str_replace(' ', '_', $name);
            return $name;
        }
        
        private function setRootPaths() {
            $i       = 0;
            $test    = $this->get('Pathfinder');
            $relRoot = '';
            $urlRoot = dirname($this->getURL() . 'a');
            
            while (!is_file('./' . $relRoot . $test)) {
                $relRoot .= '../';
                $urlRoot = dirname($urlRoot);
                ++$i;
                if ($i > 5) {
                    throw new Exception('Root path not found.');
                }
            }
            
            $relRoot = trim($relRoot, '/');
            $absRoot = realpath($relRoot);
            
            $roots = array(
                'relative' => $relRoot,
                'absolute' => $absRoot,
                'url'      => $urlRoot
            );
            
            foreach ($roots as $root => $path) {
                $path = strtr($path, '\\', '/');
                $this->_rootPath[$root] = $path;
            }
        }
        
        public function getURL() {
            // from anon445699 at stackoverflow
            // http://stackoverflow.com/questions/4503135/php-get-site-url-protocol-http-vs-https
            if (   $_SERVER['SERVER_PORT'] == 443
                OR (    isset($_SERVER['HTTPS'])
                    AND $_SERVER['HTTPS'] !== 'off'
                   )
            ) {
                $protocol = 'https://';
            } else {
                $protocol = 'http://';
            }
            $domain   = $_SERVER['HTTP_HOST'];
            $resource = $_SERVER['REQUEST_URI'];
            return $protocol . $domain . $resource;
        }
        
        public function setDefaultsCopy(&$copy) {
            $defaults = $this->_defaults;
            
            $this->_defaults = &$copy;
            
            if (!is_array($this->_defaults)) {
                $this->_defaults = $defaults;
            } else {
                foreach ($defaults as $key => $value) {
                    $this->_defaults[$key] = $value;
                }
            }
        }
        
        private function updateHardcodedPaths() {
            $path = $this->_pathList;
            foreach (array_keys($path) as $name) {
                $name = $this->cleanPathfinderName($name);
                unset($this->$name);    // clear everything, so we can write it anew
            }
            foreach ($path as $name => $pathToName) {
                $name = $this->cleanPathfinderName($name);
                if (isset($this->$name)) {
                    throw new exception(
                        'Bad path name: "'.$name.'" unavailable. ' .
                        'Make sure names are unique, case-insensitive.'
                    );
                }
                $this->$name = $this->get($name);
            }
        }
        
        public function get($name, $type = 'relative', $variables = array()) {
            $cleanName = $this->cleanPathfinderName($name);
            if (!isset($this->_pathList[$cleanName])) {
                throw new exception('"'.$name.'" not found in ' . __CLASS__ . ' path list.');
            }
            
            $path = $this->_pathList[$cleanName];
            
            # example: getting "support/includes/fileLocations.php" from "core/Error.php"
            switch($type) {
                case 'base':     # fileLocations.php
                    $path = explode('/', $path);
                    $path = array_pop($path);
                    break;
                
                case 'root':     # support/includes/fileLocations.php
                 // $path = $path; // path is being returned unmodified
                    break;
                
                case 'static':   # everything after the last variable path component
                    $path = explode('}', $path);
                    $path = array_pop($path);
                    $path = explode('/', $path);
                    array_shift($path);
                    $path = implode('/', $path);
                    $path = trim($path, '/');
                    break;
                
                case 'absolute': # C:/wamp/www/Collector/support/includes/fileLocations.php
                    $path = $this->_rootPath['absolute'] . '/' . $path;
                    break;
                
                case 'url':      # http://localhost/Collector/support/includes/fileLocations.php
                    $path = $this->_rootPath['url']      . '/' . $path;
                    break;
                
                default:         # relative, ../support/includes/fileLocations.php
                    $pre  = $this->_rootPath['relative'];
                    if ($pre !== '') { $pre .= '/'; }
                    $path = $pre . $path;
                    break;
            }
            
            $path = $this->updateVariableString($path, $variables);
            
            if (substr($path, -2) === '/.') { $path = substr($path, 0, -2); }
            
            return $path;
        }
        
        private function updateVariableString($string, $variables) {
            $defaults = $this->_defaults;
            
            if (is_array($variables)) {
                foreach ($variables as $i => $var) {
                    if (!is_numeric($i)) {
                        unset($variables[$i]);
                        $defaults[$i] = $var; // allow variables to overwrite defaults, temporarily
                    }
                }
            }
            
            $defaults = array_change_key_case($defaults, CASE_LOWER);
            
            $varKeyword = $this->_varName;
            
            $explodeLeftBrace = explode('{', $string);
            $output = array_shift($explodeLeftBrace);
            
            foreach ($explodeLeftBrace as $stringWithVar) {
                $explodeRightBrace = explode('}', $stringWithVar);
                
                $rawVar = array_shift($explodeRightBrace);
                $var    = strtolower(trim($rawVar));
                
                if ($var === $varKeyword) {
                    $insert = '{' . $varKeyword . '}'; // will do later, but needs to be trim() and strtolower()
                } elseif (isset($defaults[$var])) {
                    $insert = $defaults[$var];
                } else {
                    $insert = '{' . $rawVar . '}'; // missing default
                }
                
                $output .= $insert . $explodeRightBrace[0];
            }
            
            $varKeyword = '{' . $varKeyword . '}';
            
            if (is_array($variables)) {
                $varMarker = '/' . preg_quote($varKeyword) . '/';
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
        
        public function getStandardPaths() {
            // use this to get a list of all non-variable paths
            // you can then run fileExists() on them, as a 
            // diagnostic check that all typical files exist
            $paths = array();
            
            foreach ($this->_pathList as $name => $path) {
                if (strpos($path, '{') === FALSE) {
                    $paths[$name] = $path;
                }
            }
            
            return $paths;
        }
        
        public function setDefault($arrayOrKey, $value = null) {
            if (is_array($arrayOrKey)) {
                $newDefaults = $arrayOrKey;
            } else {
                $newDefaults = array($arrayOrKey => $value);
            }
            
            foreach ($newDefaults as $key => $value) {
                $this->_defaults[$key] = $value;
            }
            
            $this->updateHardcodedPaths();
        }
        
        public function getDefault($key) {
            if (isset($this->_defaults[$key])) {
                return $this->_defaults[$key];
            } else {
                return null;
            }
        }
        
        public function clearDefaults() {
            $this->_defaults = array();
        }
        
        public function atLocation($loc) {
            $target  = $this->get($loc, 'absolute');
            $current = realpath($_SERVER['SCRIPT_FILENAME']);
            $current = strtr($current, '\\', '/');
            return $current === $target;
        }
        
        public function inDir($dir) {
            $dirUrl  = $this->get($dir, 'url');
            $current = $this->getURL();
            
            if (stripos($current, $dirUrl) === false) {
                return false;
            } else {
                return true;
            }
        }
        // Sends a string appropriate for calling a stylesheet in the HTML document head
        public function stylesheet($selector)
        {
            $path = $this->get($selector);
            echo "<link href='$path'  rel='stylesheet'   type='text/css'/>";
        }
        // Sends a string appropriate for calling a script in the HTML document head
        public function script($selector)
        {
            $path = $this->get($selector);
            echo "<script src='$path' type='text/javascript'></script>";
        }
    }
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
    // To work:
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
    //      by prepending ../ to navigate back to the root of the 
    //      program, and then using the path calculated from the
    //      system map
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
    //      directory in the path, so dir/$variable/subdir/file.php
    //      would become subdir/file.php
    //
    //     -there are two kinds of variables used to construct these
    //      paths
    //     -the first kind is the wild card variable, which requires
    //      you to pass part of the path along with your get() function
    //      call, as the third parameter
    //     -this would be used with something like a trial type name,
    //      which can vary, but ultimately points to the same 
    //      directory structure.
    //      in the example 'test' => 'dir/$variable/subdir/file.php', using
    //      $_PATH->get('test', 'relative', 'instruct') from inside Code/
    //      would return '../dir/instruct/subdir/file.php'
    //
    //     -certain paths may have more than one wild card
    //     -when then occurs, passing a wild card will replace both entries
    //     -if you want to use different wild cards for each step, instead
    //      pass an array of wild cards. Each will be used once, in order
    //
    //     -the second kind of variable is the $default setting, which is
    //      used along with a default label, such as 
    //      'test' => 'dir/$default . 'Username'/output.csv'
    //     -in this case, you would first load a default value for 
    //      'Username', by using the loadDefault() function, like so:
    //      $_PATH->loadDefault('Username', 'participant001');
    //     -then, when you call $_PATH->get('test'), you would receive
    //      'dir/participant001/subdir/file.php'
    //     -as long as the session has already been started, these defaults
    //      will be loaded into $_SESSION[__CLASS__], so you do not need
    //      to reload these defaults on each page load
    //     -therefore, defaults are good for values that won't be changing
    //      much, like username info, while wild cards are used to frequently
    //      changing values, like trial types
    //
    //     -you can also check the default value, using getDefault()
    //
    //
    //     Other functions
    //
    //     -array_flip_multiDim() - takes an associative array, takes all non-
    //      values and makes them the keys, joining each array key with a
    //      separator as the second parameter, default '/'
    //
    //     -addUniqueToArray() - adds a key => value pair to an array, throwing
    //      an exception if the key already exists
    //
    //     -cleanPathfinderName() - trims, lowercases, and replaces internal 
    //      spaces with underscores for a string
    //
    //     -getURL() - returns the current URL, complete with protocol
    //
    //     -getStandardPaths() - gets all paths that dont contain a wild card
    //      or a default value
    //
    //     -updateDefaults() - makes sure that the internal defaults of the
    //      object are synced up with the $_SESSION variable. Only helpful 
    //      if you started the session after loading some defaults
    
    class Pathfinder
    {
        private $_pathList  = array ();
        private $_rootPath  = array (
            'relative' => '',
            'absolute' => '',
            'url'      => '',
        );
        
        private $_dirName = '*name*';
        private $_varName = '*variable*';
        private $_default = '*default*';
        
        private $_defaults = array();
        
        function __construct() {
            $map  = $this->getFileMap();
            $path = $this->convertFileMapToPathList($map);
            
            foreach ($path as $name => $thisPath) {
                $name = $this->cleanPathfinderName($name);
                $this->_pathList[$name] = $thisPath;
            }
            
            $this->setRootPaths();
            
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
            
            if (isset($_SESSION)) {
                $this->updateDefaults();
            }
        }
        
        private function getFileMap() {
            $dirName  = $this->_dirName;
            $wildCard = $this->_varName;
            $default  = $this->_default;
            require 'systemMap.php';
            return $systemMap;
        }
        
        private function convertFileMapToPathList($systemMap) {
            $list = $this->array_flip_multiDim($systemMap);
            
            $dirName    = $this->_dirName;
            $dirNameLen = strlen($dirName);
            
            foreach ($list as &$path) {
                if (substr($path, -$dirNameLen) === $dirName) {
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
                if ($i > 100) {
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
        
        public function get($name, $type = 'relative', $wildCards = array()) {
            $name = $this->cleanPathfinderName($name);
            if (!isset($this->_pathList[$name])) {
                throw new exception('"'.$name.'" not found in ' . __CLASS__ . ' path list.');
            }
            
            $path = $this->_pathList[$name];
            
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
                    $path = explode($this->_varName, $path);
                    $path = array_pop($path);
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
            
            $default = $this->_default;
            $pathParts = explode($default, $path);
            $path = array_shift($pathParts);
            foreach ($pathParts as $part) {
                $parts = explode('/', $part);
                $firstPart = array_shift($parts);
                $firstPart = $this->getDefault($firstPart);
                if ($firstPart === null) {
                    $firstPart = $default;
                }
                array_unshift($parts, $firstPart);
                $part = implode('/', $parts);
                $path .= $part;
            }
            
            $wildMarker = $this->_varName;
            
            if (!is_array($wildCards)) {
                if (is_string($wildCards)) {
                    $path = str_replace($wildMarker, $wildCards, $path);
                }
            } else {
                // for arrays, use each replacement only once
                // this way, it's possible to do something like
                // data/{$username}/{$trialType}/specialOutput.csv
                $wildMarker = '/' . preg_quote($wildMarker) . '/';
                foreach ($wildCards as $wild) {
                    $path = preg_replace($wildMarker, $wild, $path, 1);
                }
            }
            
            return $path;
        }
        
        public function getStandardPaths() {
            // use this to get a list of all non-variable paths
            // you can then run fileExists() on them, as a 
            // diagnostic check that all typical files exist
            $paths = array();
            
            foreach ($this->_pathList as $name => $path) {
                if (    strpos($path, $this->_varName) === FALSE
                    AND strpos($path, $this->_default) === FALSE
                ) {
                    $paths[$name] = $path;
                }
            }
            
            return $paths;
        }
        
        public function loadDefault($arrayOrKey, $value = null) {
            if (is_array($arrayOrKey)) {
                $newDefaults = $arrayOrKey;
            } else {
                $newDefaults = array($arrayOrKey => $value);
            }
            
            foreach ($newDefaults as $key => $value) {
                $this->_defaults[$key] = $value;
                
                if (isset($_SESSION)) {
                    
                    $_SESSION[__CLASS__][$key] = $value;
                }
            }
        }
        
        public function getDefault($key) {
            if (isset($_SESSION[__CLASS__][$key])) {
                return $_SESSION[__CLASS__][$key];
            } elseif (isset($this->_defaults[$key])) {
                return $this->_defaults[$key];
            } else {
                return null;
            }
        }
        
        public function updateDefaults() {
            // use this if you set some defaults before starting the session
            // also used on __construct, to load in the defaults
            if (!isset($_SESSION)) {
                throw new exception(
                    __CLASS__ . ' can\'t update defaults without a global $_SESSION variable'
                );
            }
            
            if (!isset($_SESSION[__CLASS__]) OR !is_array($_SESSION[__CLASS__])) {
                $_SESSION[__CLASS__] = array();
            }
            
            foreach ($this->_defaults as $key => $value) {
                $_SESSION[__CLASS__][$key] = $value;
            }
            
            foreach ($_SESSION[__CLASS__] as $key => $value) {
                $this->_defaults[$key] = $value;
            }
        }
    }

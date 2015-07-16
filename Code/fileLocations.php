<?php
/*  Collector
    A program for running experiments on the web
    Copyright 2012-2015 Mikey Garcia & Nate Kornell
 */

/**
 * Class that helps with File Pathing
 *
 * @author Adam Blake <adamblake@g.ucla.edu>
 */
class FileLocations
{
    public function __construct($rootPath, array $configArray = [])
    {
        $realpath = realpath($rootPath);
        if (!is_dir($realpath)) {
            throw new InvalidArgumentException(
                'Could not set the root path when constructing '.__CLASS__.': '
              . 'the given path does not point to a valid directory.');
        }
        $this->root = new CollectorPath('');
        $this->root->base_path = self::clean($realpath);
        $this->root->base_url = $this->determineBaseUrl();
        
        if (!empty($configArray)) {
            $this->configure($configArray);
        }
    }
    
    public function determineBaseUrl()
    {  
        // get root folder name
        $rootParts = explode('/', self::convertSlashes($this->root));
        $rootFolderName = end($rootParts);
        
        // filter the URI and trim everything past the root folder
        $filtered_uri = filter_input(INPUT_SERVER, 'REQUEST_URI', FILTER_SANITIZE_URL);
        if (false !== strpos($filtered_uri, $rootFolderName)) {
            $basePos = strpos($filtered_uri, $rootFolderName);
            $baseLen = strlen($rootFolderName);
            $filtered_uri = substr($filtered_uri, 0, $basePos + $baseLen);
        }

        // get other parts of the URL
        $scheme = 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://';
        $filtered_host = filter_input(INPUT_SERVER, 'HTTP_HOST', FILTER_SANITIZE_URL);
        
        return rtrim($scheme . $filtered_host . $filtered_uri, '/');
    }
    
    /**
     * Adds file locations from a config file.
     * @param array $configArray An array of configurations
     */
    public function configure(array $configArray)
    {
        // load paths
        foreach ($configArray['directories'] as $name => $path) {
            $section = preg_replace('/_folder$/', '', $name);
            $this->add($section, $path);
            $this->addPathsFromSection($configArray, $section);
        }
        
        // load non-path file settings
        if (isset($configArray['trial_type_files'])) {
            $this->trial_type_files = new stdClass();
            foreach ($configArray['trial_type_files'] as $name => $value) {
                $this->trial_type_files->$name = $value;
            }
        }
    }
    
    /**
     * Adds a named path. If a parent is specified the parent's path is 
     * prepended to the child path.
     * @param string $name The name/alias of the path to add
     * @param string $path The path to add, relative to the root
     * @param string $parent The name of the parent path
     */
    public function add($name, $path, $parent = null)
    {
        $clnpath = '/'.self::clean($path);
        
        if (null !== $parent) {
            // check for parent in named paths
            $parent = $this->$parent;
        } else {
            // no parent specified
            $parent = $this->root;
        }
        $this->$name = new CollectorPath($clnpath, $parent);
    }
    
    /**
     * Adds all paths under a section, using the section as parent path.
     * @param array $config The full config array that has been loaded from file
     * @param type $section The section to add paths from
     */
    private function addPathsFromSection(array $config, $section)
    {
        if (array_key_exists($section, $config)) {
            foreach ($config[$section] as $name => $path) {
                if (!is_array($path)) {
                    $this->add($name, $path, $section);
                }
            }
        }
    }    
    
    /**
     * Updates a parent path (in root directory, e.g. data) and its children.
     * @param string $parentName The name/label of the parent to update
     * @param string $path The new path for the parent path
     */
    public function updateParentPath($parentName, $path)
    {
        $oldParentPath = (string) $this->$parentName;
        $this->add($parentName, $path);
        foreach ($this as $cPath) {
            if (!is_object($cPath) || 'CollectorPath' !== get_class($cPath)) {
                continue;
            }
            
            if (false !== strpos($cPath->base_path, $oldParentPath)) {
                $cPath->base_path = (string) $this->$parentName;
                $cPath->base_url = $this->$parentName->toUrl();
            }
        }
    }
    
    /**
     * Determines the relative path between two absolute filepaths.
     * @param string $fromPath The path the result should be relative to
     * @param string $toPath The path the result should point to
     * @return string
     */
    public static function getRelativePath($fromPath, $toPath)
    {
        // fix Windows backslashes, then explode path into component dirs
        $from = is_dir($fromPath) ? explode('/', self::clean($fromPath, 'r') .'/')
                              : explode('/', self::convertSlashes($fromPath));
        $to = is_dir($toPath) ? explode('/', self::clean($toPath, 'r') .'/') 
                          : explode('/', self::convertSlashes($toPath));

        $relPath = $to;
        
        // traverse 'from' depth and add '..' onto the relPath
        foreach($from as $depth => $dir) {
            // find first non-matching dir
            if($dir === $to[$depth]) {
                // ignore this directory
                array_shift($relPath);
            } else {
                // get number of remaining dirs to $from
                $remaining = count($from) - $depth;
                if($remaining > 1) {
                    // add traversals up to first matching dir
                    $padLength = (count($relPath) + $remaining - 1) * -1;
                    $relPath = array_pad($relPath, $padLength, '..');
                    break;
                } else {
                    $relPath[0] = './' . $relPath[0];
                }
            }
        }
        
        // rebuild output path
        return rtrim(implode('/', $relPath), '/');
    }
    
    /**
     * Convert all backslashes and trim from the ends.
     * @param string $path The path to convert
     * @return string
     */
    public static function clean($path, $trimSide = null)
    {
        $out = self::convertSlashes($path);
        if ('r' === strtolower($trimSide)) {
            $out = rtrim($out, '/');
        } else if ('l' === strtolower($trimSide)) {
            $out = ltrim($out, '/');
        } else {
            $out = trim($out, '/');
        }
        return $out;
    }
    
    /**
     * Convert all backslashes (Windows-only directory separators) to forward
     * slashes (universal directory separator).
     * @param type $path
     * @return type
     */
    public static function convertSlashes($path)
    {
        return str_replace('\\', '/', $path);
    }
}

/**
 * Helper class for generating filesystem paths and URLs from parts.
 *
 * @author Adam Blake <adamblake@g.ucla.edu>
 */
class CollectorPath
{
    public $path;
    public $base_url;
    public $base_path;
    
    public function __construct($path, CollectorPath $parent = null)
    {
        $this->path = $path;
        if (isset($parent)) {
            $this->base_url = $parent->toUrl();
            $this->base_path = (string) $parent;
        } else {
            $this->base_url = '';
            $this->base_path = '';
        }
    }
    
    /**
     * Builds the absolute path from the base_path and path properties.
     * @return string
     */
    public function buildPath()
    {
        return $this->base_path . $this->path;
    }
    
    /**
     * Returns the relative path from the given path, to the stored path.
     * @param string $path The path that the return path should point to
     * @return string
     */
    public function relativeTo($path)
    {
        return FileLocations::getRelativePath($path, $this->buildPath());
    }
    
    /**
     * Builds the absolute URL from the base_url and path properties.
     * @return type
     */
    public function toUrl()
    {
        return $this->base_url . $this->path;
    }
    
    /**
     * When cast to string, the class outputs the fully built filesystem path.
     * @return string
     */
    public function __toString()
    {
        return $this->buildPath();
    }
}

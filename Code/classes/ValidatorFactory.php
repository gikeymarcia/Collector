<?php
/**
 * ValidatorFactory class.
 */

namespace Collector;

/**
 * Collection of static functions for creating Validators and groups of
 * Validators.
 * 
 * The more useful functions of this class are the createFromDir functions.
 * These functions create Validators for every trial type found in the given
 * directory that has a 'validator.php' file.
 */
class ValidatorFactory {
    /**
     * Searches a directory for a single trial type and returns its validator if
     * it exists.
     * 
     * @param string|array  $trialtype The trial type(s) to get the Validator(s) for.
     * @param string|array  $directory The directory or directories to search in.
     * @param bool          $merge     If multiple directories are given, it is
     *                                 possible to find more than one validator
     *                                 script. This parameter indicates whether 
     *                                 multiple Validators for a trial type 
     *                                 should be merged, or if only the first
     *                                 should be kept.
     * 
     * @return array|Validator|null For each specific trial type, if the 
     *                              Validator is found in the specified
     *                              directory it is returned, else null. If an 
     *                              array of trial types is given, the 
     *                              Validators are returned in an associative 
     *                              array with the trial types as keys.
     */
    public static function createSpecific($trialtype, $directory, $merge = false)
    {
        $types = is_array($trialtype)
               ? array_map('strtolower', $trialtype)
               : array_map('strtolower', array($trialtype));
        $paths = is_array($directory)
               ? self::filter(self::getPathsMultiple($directory), $types)
               : self::filter(self::getPathsMultiple(array($directory)), $types);
        $validators = array();
        foreach ($paths as $name => $path) {
            if (isset($path)) {
                $validators[$name] = new Validator($merge ? $path : $path[0]);
            }
        }
        
        return count($validators) > 1 ? $validators : array_pop($validators);
    }
    
    /**
     * Searches a directory for a single trial type and returns its validator if
     * it exists.
     * 
     * @param array|string $trialtype   The trial type to get the Validator for.
     * @param array        $directories The directory to search in.
     * @param bool         $merge       Indicates whether multiple Validators
     *                                  for a trial type should be merged, or if
     *                                  only the first should be kept.
     * 
     * @return array|Validator|null For each specific trial type, if the 
     *                              Validator is found in the specified
     *                              directory it is returned, else null. If an 
     *                              array of trial types is given, the 
     *                              Validators are returned in an associative 
     *                              array with the trial types as keys.
     */
    protected static function createSpecificFromMultipleDirs($trialtype, array $directories,
        $merge = false
    ) {
        if (!is_array($trialtype)) {
            $trialtype = array($trialtype);
        }
        $validators = self::createFromDirs($directories, $merge);
        $typesLower = array_map('strtolower', $trialtype);
        $filtered = self::filter($validators, $typesLower);
        
        return count($filtered) > 1 ? $filtered : array_pop($filtered);
    }
    
    /**
     * Finds all validator.php files nested one subdirectory down from the given
     * path (e.g. "$directory/subdir/validator.php").
     * 
     * @param string $directory The directory to search within.
     * 
     * @return array Returns an indexed array of the paths to the validators.
     */
    public static function getPaths($directory)
    {
        $files = array();
        $pattern = rtrim(str_replace('\\', '/', $directory), '/') . '/*';
        $dirs = glob($pattern, GLOB_ONLYDIR);
        foreach ($dirs as $dir) {
            if (file_exists($dir.'/validator.php')) {
                $name = substr($dir, strrpos($dir, '/') + 1);
                $files[$name] = $dir.'/validator.php';
            }
        }
        
        return $files;
    }
    
    /**
     * Gets the paths to validators in multiple directories.
     * 
     * @param array $directories The directories to search.
     * 
     * @return array Returns an array of the paths to all of the validators
     *               found in the given directories. Validators are indexed by
     *               trial type.
     * 
     * @uses getPaths Uses getPaths method on each of the given directories.
     */
    public static function getPathsMultiple(array $directories)
    {
        $paths = array();
        foreach ($directories as $directory) {
            $temp = self::getPaths($directory);
            foreach ($temp as $pathName => $path) {
                $paths[$pathName][] = $path;
            }
        }
        
        return $paths;
    }
    
    /**
     * Given an array of strings and an associative array, a new array is
     * created with all of the strings used as keys and values taken from
     * matching keys in the associative array.
     * 
     * @param array $haystack The array to draw values from.
     * @param array $needles  The keys to use in the new array.
     * @return array Returns the needles array with all values converted to keys
     *               and new values inserted from matching keys in the haystack.
     *               Non-matching keys are assigned null.
     */
    protected static function filter(array $haystack, array $needles)
    {
        $out = array();
        foreach ($needles as $key) {
            $out[$key] = isset($haystack[$key]) ? $haystack[$key] : null;
        }
        
        return $out;
    }
    
    /**
     * Merges two Validators by combining their registered check functions.
     * 
     * Unlike Validator::merge, this function does not alter the Validators but
     * instead returns a new one.
     * 
     * @param Validator $validator1 A Validator to merge.
     * @param Validator $validator2 A Validator to merge.
     * 
     * @return Validator Returns a Validator with all check functions present in
     *                   both of the given Validators.
     */
    public static function merge(Validator $validator1, Validator $validator2)
    {
        $validator = new Validator();
        $validator->merge($validator1);
        $validator->merge($validator2);
        
        return $validator;
    }
    
    /**
     * Merges an array of Validators with another array of Validators.
     * 
     * By default, if multiple Validators are found for the same trial type,
     * they are merged. If the strict parameter is set as TRUE, the only the
     * first Validator found will be kept.
     * 
     * @param array $group1 The first group of Validators to merge.
     * @param array $group2 The group of Validators to merge into the first.
     * @param bool  $merge  Indicates whether multiple Validators for a trial 
     *                      type should be merged, or if only the first should
     *                      be kept.
     * 
     * @return array Returns the merged array of Validators indexed by trial type.
     * 
     * @uses merge Uses the merge function to merge Validators when required.
     */
    public static function mergeGroup(array $group1, array $group2, $merge = true)
    {
        foreach ($group2 as $name => $validator) {
            if (!array_key_exists($name, $group1)) {
                $group1[$name] = $validator;
            } else if ($merge) {
                $group1[$name] = self::merge($group1[$name], $validator);
            }
        }
        
        return $group1;
    }
}

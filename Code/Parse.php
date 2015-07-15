<?php

//use Symfony\Component\Yaml\Yaml;

/**
 * Collection of static functions for parsing files.
 *
 * @author Adam Blake <adamblake@g.ucla.edu>
 */
class Parse {

    /**
     * Parses a configuration file and returns an associative array.
     * @param string $filename configuration file to parse
     * @return array|stdClass associative array or class of the configurations
     * @throws Exception
     */
    public static function fromConfig($filename, $returnObject = false)
    {
        $ext = strtolower(self::getExt($filename));
        
        if ("yml" === $ext || "yaml" === $ext) {
            $config = self::fromYaml($filename);
        } else if ("json" === $ext) {
            $config = self::fromJson($filename);
        } else if ("ini" === $ext) {
            $config = self::fromIni($filename);
        }
        if (empty($config)) {
            throw new \InvalidArgumentException("The given config file "
                . "'$filename' is empty or of an unsupported file type "
                . "(supported: YAML, JSON, INI.");
        }

        if ($returnObject) {
            $config = self::arrayToObject($config);
        }
        
        return $config;
    }

    /**
     * Parses a YAML file or string
     * @param string $input The YAML file path or YAML string
     * @param bool $isString Switch to true for string input instead of file
     * @return array
     * @throws \InvalidArgumentException when config cannot be parsed
     */
    public static function fromYaml($input, $isString = false)
    {
        $contents = $isString ? $input : file_get_contents($input);
        $yaml = Yaml::parse(trim($contents));

        if (null === $yaml) {
            // empty file
            $yaml = [];
        }

        if (!is_array($yaml)) {
            // not an array
            throw new \InvalidArgumentException(sprintf('The input "%s" must '
                . 'contain or be a valid YAML structure.', $input));
        }

        return $yaml;
    }

    /**
     * Parses a JSON file or string
     * @param string $input The JSON file path or JSON string
     * @param bool $isString Switch to true for string input instead of file
     * @return array
     * @throws \LogicException when JSON decode cannot parse the file
     */
    public static function fromJson($input, $isString = false)
    {
        $contents = $isString ? $input : file_get_contents($input);
        $json = json_decode(trim($contents), true);
        $error = json_last_error();

        if (null === $json) {
            // empty file
            $json = [];
        }

        if (JSON_ERROR_NONE !== $error) {
            // error occurred
            throw new \InvalidArgumentException(sprintf("Failed to parse JSON "
                . "file '%s', error: '%s'", $input, json_last_error_msg()));
        }

        return $json;
    }

    /**
     * Parses an INI file or string
     * @param string $input The INI file path or INI string
     * @param bool $isString Switch to true for string input instead of file
     * @return array
     * @throws \InvalidArgumentException when the INI file cannot be parsed
     */
    public static function fromIni($input, $isString = false)
    {
        $contents = $isString ? $input : file_get_contents($input);
        $ini = parse_ini_string(trim($contents), true);

        if (null === $ini) {
            // empty file
            $ini = [];
        }

        if (!is_array($ini)) {
            // not an array
            throw new \InvalidArgumentException(sprintf('The file "%s" must '
                . 'have a valid INI structure.', $input));
        }

        // multidimensional inis
        self::fixIniMulti($ini);

        return $ini;
    }

    /**
     * Unpacks nested INI sections/arrays
     * @param array $ini_arr The INI array to unpack.
     */
    private static function fixIniMulti(array &$ini_arr)
    {
        foreach ($ini_arr as $key => &$value) {
            if (is_array($value)) {
                self::fixIniMulti($value);
            }
            if (false !== strpos($key, '.')) {
                $key_arr = explode('.', $key);
                $last_key = array_pop($key_arr);
                $cur_elem = &$ini_arr;
                foreach ($key_arr as $key_step) {
                    if (!isset($cur_elem[$key_step])) {
                        $cur_elem[$key_step] = array();
                    }
                    $cur_elem = &$cur_elem[$key_step];
                }
                $cur_elem[$last_key] = $value;
                unset($ini_arr[$key]);
            }
        }
    }
    
    /**
     * Converts an array to an object using stdClass.
     * @param array $array The array to convert
     * @return \stdClass
     */
    public static function arrayToObject(array $array)
    {
        $object = new stdClass();
        foreach ($array as $key => $value) {
            $object->{$key} = $value;
        }
        return $object;
    }

    /**
     * @TODO something seems to be wrong with the call_user_func_array in combination with the file_get_contents: returns empty string
     * 
     * Wrapper for file_get_contents that throws Exceptions, rather than 
     * returning false and throwing a warning.
     * 
     * @param string $filename Name of the file to read.
     * @param bool $use_include_path [optional] As of PHP 5 the 
     *   FILE_USE_INCLUDE_PATH constant can be used to trigger include path search.
     * @param resource $context [optional] A valid context resource created with
     *   stream_context_create. If you don't need to use a custom context, you 
     *   can skip this parameter by NULL.
     * @param int $offset [optional] The offset where the reading starts on the 
     *   original stream. Seeking (offset) is not supported with remote files. 
     *   Attempting to seek on non-local files may work with small offsets, but 
     *   this is unpredictable because it works on the buffered stream.
     * @param int $maxlen [optional] Maximum length of data read. The default is
     *   to read until end of file is reached. Note that this parameter is 
     *   applied to the stream processed by the filters.
     * @return string The read data.
     * @throws \Exception Throws an exception when file_get_contents cannot open the file.
     */
    public static function fget_contents($filename, $use_include_path = false, 
        $context = null, $offset = -1, $maxlen = null
    ) {
        $args = array($filename, $use_include_path, $context, $offset, $maxlen);
        $contents = call_user_func_array('file_get_contents', $args);
        if (false === $contents) {
            throw new \Exception('Failed to open ' . $filename);
        } else {
            return $contents;
        }
    }    
    
    /**
     * Determines the extension of a given file.
     * @param string $filename The path to the file.
     * @return string
     */
    public static function getExt($filename)
    {
        return substr(strrchr($filename, "."), 1);
    }
    
    /**
     * Detects the end-of-line character(s) of a string.
     * @param string $string String to check.
     * @return string Detected EOL.
     */
    public static function detectEol($string)
    {
        $eols = array_count_values(str_split(preg_replace("/[^\r\n]/", "", $string)));
        $eola = array_keys($eols, max($eols));
        $eol = implode("", $eola);
        return $eol;
    }
}

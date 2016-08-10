<?php

class FileSystem
{
    private $root = '.';
    private $map;
    private $defaults = array();
    private $validated_data_types = array();

    public function __construct()
    {
        $this->map = $this->get_system_map();
    }

    /**
     * Return systemMap data with all keys lowercased
     * @return array systemMap.php data structure
     */
    private function get_system_map()
    {
        // change key case defaults to return lowercased keys
        return array_change_key_case(
            require __DIR__ . '/systemMap.php'
        );
    }


    private function get_root() {
        $root  = $this->root;
        $count = 0;

        while (!is_file("$root/Code/classes/InputOutput.php")) {
            if (++$count === 1) {
                $root = '.';
            } else {
                $root .= '/..';
            }

            if ($count > 10) throw new Exception(
                'Could not find the InputOutput.php file expected in '
                . '/Code/classes/');
        }

        $this->root = $root;
        return $root;
    }

    /**
     * Return the path to a file with all variables and defaults replaced
     * Sources based on the keys from /Code/classes/systemMap.php
     * @param  [type]     $source    name of specific data source as defined in systemMap.php
     * @param  array|bool $variables list of replacements for [keys] in path,
                                     can be false to return raw path
     * @return string                relative path to source
     */
    public function get_path($source, $variables = array())
    {
        $source = strtolower(trim($source));

        if (!isset($this->map[$source])) {
            throw new Exception("Specified source, '$source', does not exist in the system map", 1);
        } elseif ($variables === false) {
            // force the raw string from SystemMap.php
            return $this->map[$source][1];
        } else {
            if (!is_array($variables)) {
                $variables = array($variables);
            }
            $variables = array_merge($this->defaults, $variables);

            $varsReplaced = fill_template(
                $this->map[$source][1], $variables
            );
            return $this->get_root() . "/$varsReplaced";
        }
    }

    public function get_type($source)
    {
        $source = strtolower(trim($source));

        if (!isset($this->map[$source])) {
            throw new Exception("Specified source, '$source', does not exist in the system map", 1);
        }
        return $this->map[$source][0];
    }

    public function set_default($key, $val)
    {
        if ($key == "var") throw new Exception("You cannot set a default [var]", 1);

        $this->defaults[$key] = $val;
    }

    private function access($source, $command, $data = null, $index = null)
    {
        $path = $this->get_path($source);
        $class_name = 'ioDataType_' . $this->get_type($source);
        $this->validate_date_type($class_name);

        if ($command == "query") {
            return $class_name::$command($path, $index);
        }

        return $class_name::$command($path, $data, $index);
    }

    private function validate_date_type($dataType) {
        if (isset($this->validated_data_types[$dataType])) {
            return true;
        }

        $parents = class_parents($dataType);

        if (isset($parents['ioAbstractDataType'])) {
            $this->validated_data_types[$dataType] = true;
            return true;
        }

        throw new Exception("System map cannot use '$dataType', as it is not "
            . "an extension of 'ioAbstractDataType'.");
    }

    public function __sleep() {
        return array('root', 'defaults');
    }

    public function __wakeup() {
        $this->map = $this->get_system_map();
    }

/**
 * These are the functions that any data implementation need to have
 * to add another access method, add the method to ioAbstractDataType
 * and it will enforce that all datatypes implement that method
 * read, write, writeMany, overwrite, query
 */
    public function read($source)
    {
        return $this->access($source, 'read');
    }

    public function write($source, $data, $index = null)
    {
        return $this->access($source, 'write', $data, $index);
    }

    public function writeMany($source, $data)
    {
        return $this->access($source, 'writeMany', $data);
    }

    public function overwrite($source, $data, $index = null)
    {
        return $this->access($source, 'overwrite', $data, $index);
    }

    public function query($source, $index)
    {
        return $this->access($source, 'query', null, $index);
    }
}

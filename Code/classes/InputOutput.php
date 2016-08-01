<?php

class InputOutput
{
    private $root = '.';
    private $map;
    private $defaults = array();
    private $validatedDataTypes = array();

    public function __construct()
    {
        $this->map = $this->getSystemMap();
    }

    /**
     * Return systemMap data with all keys lowercased
     * @return array systemMap.php data structure
     */
    private function getSystemMap()
    {
        // change key case defaults to return lowercased keys
        return array_change_key_case(
            require __DIR__ . '/systemMap.php'
        );
    }


    private function getRoot() {
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
    public function getPath($source, $variables = array())
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
            return $this->getRoot() . "/$varsReplaced";
        }
    }

    public function getType($source)
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
        $path = $this->getPath($source);
        $className = 'ioDataType_' . $this->getType($source);
        $this->validateDataType($className);

        if ($command == "query") {
            return $className::$command($path, $index);
        }

        return $className::$command($path, $data, $index);
    }
    
    private function validateDataType($dataType) {
        if (isset($this->validatedDataTypes[$dataType])) {
            return true;
        }
        
        $parents = class_parents($dataType);
        
        if (isset($parents['ioAbstractDataType'])) {
            $this->validatedDataTypes[$dataType] = true;
            return true;
        }
        
        throw new Exception("System map cannot use '$dataType', as it is not "
            . "an extension of 'ioAbstractDataType'.");
    }
    
    public function __sleep() {
        return array('root', 'defaults');
    }
    
    public function __wakeup() {
        $this->map = $this->getSystemMap();
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

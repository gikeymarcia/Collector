<?php

class ioDataType_Dir extends ioAbstractDataType
{
    public static function read($path) {
        $scan = scandir($path);
        
        foreach ($scan as $i => $entry) {
            if ($entry === '.' || $entry === '..') {
                unset($scan[$i]);
            }
        }
        
        return $scan;
    }
    
    public static function overwrite($path, $data) {
        // technically, this function should clear out the existing dir,
        // but that is way too powerful, and too easy to accidentally abuse
        // also, writing data would probably mean creating files/dirs, but
        // for now, that will be left for people to do manually
        return is_dir($path) ? false : mkdir($path, 0777, true);
    }
}

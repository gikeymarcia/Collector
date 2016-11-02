<?php

class fsDataType_Dir extends fsDataType_Abstract
{
    public static function read($path) {
        if (!is_dir($path)) return null;
        $scan = scandir($path);

        foreach ($scan as $i => $entry) {
            if ($entry === '.' || $entry === '..') {
                unset($scan[$i]);
            }
        }

        return array_values($scan);
    }

    public static function overwrite($path, $data) {
        // technically, this function should clear out the existing dir,
        // but that is way too powerful, and too easy to accidentally abuse
        // also, writing data would probably mean creating files/dirs, but
        // for now, that will be left for people to do manually
        return is_dir($path) ? false : mkdir($path, 0777, true);
    }
}

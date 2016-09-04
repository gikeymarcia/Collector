<?php

abstract class fsDataType_Abstract implements fsDataType_Interface
{
    public static function write($path, $data, $index = null) {
        $all_data = static::read($path);

        if ($index === null) {
            $all_data[] = $data;
        } else {
            $all_data[$index] = $data;
        }

        return static::overwrite($path, $all_data);
    }

    // this should really be optimized by each extending class
    public static function write_many($path, $data) {
        $all_data = array_merge(static::read($path), $data);
        
        foreach ($data as $index => $datum) {
            $last_write = static::write($path, $datum, $index);
        }

        return static::overwrite($path, $all_data);
    }

    public static function query($path, $index) {
        $data = static::read($path);

        return isset($data[$index]) ? $data[$index] : null;
    }
}

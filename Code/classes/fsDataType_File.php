<?php

class fsDataType_File extends fsDataType_Abstract
{
    public static function read($path) {
        return is_file($path) ? file_get_contents($path) : null;
    }

    public static function overwrite($path, $data) {
        return file_put_contents($path, $data);
    }

    public static function write($path, $data, $unused_index = null) {
        return file_put_contents($path, $data, FILE_APPEND);
    }

    public static function write_many($path, $all_data) {
        return file_put_contents($path, implode('', $all_data), FILE_APPEND);
    }
}

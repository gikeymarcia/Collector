<?php

class ioDataType_File extends ioAbstractDataType
{
    public static function read($path) {
        return file_get_contents($path);
    }

    public static function overwrite($path, $data) {
        return file_put_contents($path, $data);
    }

    public static function write($path, $data) {
        return file_put_contents($path, $data, FILE_APPEND);
    }

    public static function writeMany($path, $allData) {
        return file_put_contents($path, implode('', $allData), FILE_APPEND);
    }
}

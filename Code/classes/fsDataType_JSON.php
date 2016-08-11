<?php

class ioDataType_JSON extends ioAbstractDataType
{
    public static function read($path) {
        if (!is_file($path)) return null;
        
        return json_decode(file_get_contents($path), true);
    }

    public static function overwrite($path, $data) {
        $dir = dirname($path);
        if (!is_dir($dir)) mkdir($dir, 0777, true);
        
        return file_put_contents($path, json_encode($data));
    }
}

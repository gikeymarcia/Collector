<?php

class fsDataType_JSON extends fsDataType_Abstract
{
    public static function read($path) {
        if (!is_file($path)) return null;

        return json_decode(file_get_contents($path), true);
    }

    public static function overwrite($path, $data) {
        $dir = dirname($path);
        if (!is_dir($dir)) mkdir($dir, 0777, true);

        $json_options = (static::can_pretty_print()) ? JSON_PRETTY_PRINT : null;

        return file_put_contents($path, json_encode($data, $json_options));
    }

    private static function can_pretty_print()
    {
        return (version_compare(PHP_VERSION, '5.4.0') >= 0) ? true : false;
    }
}

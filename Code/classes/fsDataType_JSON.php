<?php

class fsDataType_JSON extends fsAbstractDataType
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

    private function can_pretty_print()
    {
        return (version_compare(PHP_VERSION, '5.4.0') >= 0) ? true : false;
    }
}

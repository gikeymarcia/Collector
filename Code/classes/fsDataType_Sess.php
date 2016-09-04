<?php

class fsDataType_Sess extends fsDataType_Abstract
{
    public static function read($path) {
        if (!is_file($path)) return array();

        $old_session = $_SESSION;
        $_SESSION = array();

        session_decode(file_get_contents($path));

        $requested_session = $_SESSION;
        $_SESSION = $old_session;

        return $requested_session;
    }

    public static function overwrite($path, $data) {
        self::validate_keys($data);

        $real_session = $_SESSION;
        $_SESSION = $data;
        $dir = dirname($path);

        if (!is_dir($dir)) mkdir($dir, 0777, true);

        $write = file_put_contents($path, session_encode());

        $_SESSION = $real_session;

        return $write;
    }

    public static function write($path, $data, $index) {
        return self::write_many($path, array($index => $data));
    }

    public static function write_many($path, $data) {
        self::validate_keys($data);

        if (!is_file($path)) {
            return self::overwrite($path, $data);
        }

        $old_session = $_SESSION;
        $_SESSION = array();

        session_decode(file_get_contents($path));

        foreach ($data as $key => $val) {
            $_SESSION[$key] = $val;
        }

        $write = file_put_contents($path, session_encode());

        $_SESSION = $old_session;

        return $write;
    }

    private static function validate_keys($data) {
        foreach ($data as $key => $val) {
            if ($key === null || is_numeric($key)) {
                throw new Exception('PHP Session data must be indexed with a string key, you cannot use a number or null');
            }
        }
    }
}

<?php

class ioDataType_Sess extends ioAbstractDataType
{
    public static function read($path) {
        if (!is_file($path)) return array();

        $oldSess = $_SESSION;
        $_SESSION = array();

        session_decode(file_get_contents($path));

        $requestedSession = $_SESSION;
        $_SESSION = $oldSess;

        return $requestedSession;
    }

    public static function overwrite($path, $data) {
        self::validateKeys($data);

        $realSess = $_SESSION;
        $_SESSION = $data;
        $dir = dirname($path);

        if (!is_dir($dir)) mkdir($dir, 0777, true);

        $write = file_put_contents($path, session_encode());

        $_SESSION = $realSess;

        return $write;
    }

    public static function write($path, $data, $index) {
        return self::writeMany($path, array($index => $data));
    }

    public static function writeMany($path, $data) {
        self::validateKeys($data);

        if (!is_file($path)) {
            return self::overwrite($path, $data);
        }

        $oldSess = $_SESSION;
        $_SESSION = array();

        session_decode(file_get_contents($path));

        foreach ($data as $key => $val) {
            $_SESSION[$key] = $val;
        }

        $write = file_put_contents($path, session_encode());

        $_SESSION = $oldSess;

        return $write;
    }

    private static function validateKeys($data) {
        foreach ($data as $key => $val) {
            if ($key === null || is_numeric($key)) {
                throw new Exception('PHP Session data must be indexed with a string key, you cannot use a number or null');
            }
        }
    }
}

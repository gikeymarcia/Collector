<?php
/*
This is the contract all fsDataType_* implementations must honor
 */
abstract class fsAbstractDataType
{
    abstract public static function read($path);
    abstract public static function overwrite($path, $data);

    public static function write($path, $data, $index = null) {
        $allData = static::read($path);

        if ($index === null) {
            $allData[] = $data;
        } else {
            $allData[$index] = $data;
        }

        return static::overwrite($path, $allData);
    }

    // this should really be optimized by each extending class
    public static function writeMany($path, $data) {
        foreach ($data as $index => $datum) {
            $lastWrite = static::write($path, $datum, $index);
        }

        return $lastWrite;
    }

    public static function query($path, $index) {
        $data = static::read($path);

        return isset($data[$index]) ? $data[$index] : null;
    }
}

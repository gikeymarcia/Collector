<?php
/*
This is the contract all fsDataType_* implementations must honor
 */
interface fsDataType_Interface
{
    public static function read($path);
    public static function overwrite($path, $data);
    public static function write($path, $data, $index = null);
    public static function write_many($path, $data);
    public static function query($path, $index);
}

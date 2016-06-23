<?php
namespace Collector;

class Datadump
{
    static private function loadFunctions() {
        require_once(__DIR__ . '/../vendor/kint/Kint.class.php');
    }
    
    static function display($var) {
        self::loadFunctions();
        d($var);
    }
    
    static function displayAndStopScript($var) {
        self::loadFunctions();
        ddd($var);
    }
}

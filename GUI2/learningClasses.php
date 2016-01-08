<?php

include_once("curseFilter.php");
$curseFilter = new curseFilter;

$string= "You jerky idiot.";
$clean_str = ($curseFilter -> clean ($string));
echo $clean_str;

?>
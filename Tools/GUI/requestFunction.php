<?php
  require '../../Code/initiateCollector.php';
	$title = 'Collector GUI';
  require $_PATH->get('Header'); 
  require_once("../loginFunctions.php");
  adminOnly();
  require("guiCss.php");
  $functionList=file_get_contents("functionList.csv");
  
  print_r($functionList);
?>

<br>



There will be a list based on votes for functions that are requested for the Gui.
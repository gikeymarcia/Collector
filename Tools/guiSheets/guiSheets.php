<?php
	adminOnly();
	if(isset($_POST['currentGuiSheetPage'])){
		$_SESSION['currentGuiSheetPage']=$_POST['currentGuiSheetPage'];
	}
	
	if(!isset($_SESSION['currentGuiSheetPage'])){
		require('indexGui.php');
		$_SESSION['currentGuiSheetPage']='indexGui';
	}
	
	else {
		require(($_SESSION['currentGuiSheetPage']).".php");
	}

?>
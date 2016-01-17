<?php 

	require('guiFunctions.php'); //for use of recurse_copy

	#create a new study
	$studySource="../Experiments/".$_POST['templateExperiment'];
	$studyDest="../Experiments/".$_POST["csvPostName"];
	recurse_copy($studySource,$studyDest);
	unlink("../Experiments/".$_POST["csvPostName"]."/name.txt");//to facilitate new name
	$_SESSION['thisDir']="../Experiments/".$_POST['csvPostName'];
	$_SESSION['studyName']=$_POST['csvPostName'];
	$_SESSION['currentGuiSheetPage']='sheetsEditor';

	header ("Location: index.php");

?>	
	
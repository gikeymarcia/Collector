<?php 

	require('guiFunctions.php');

	#create a new study
	$studySource="../Experiments/".$_POST['templateExperiment'];
	$studyDest="../Experiments/".$_POST["studyName"];

	recurse_copy($studySource,$studyDest);

	session_start();
	$_SESSION['studyName']=$_POST['studyName'];

	print_r($_SESSION);

	header ("Location: csvRoar.php");

?>	
	
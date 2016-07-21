<?php 
	adminOnly();
	//require('guiFunctions.php'); //for use of recurse_copy

  $illegalInputs=array('<?','{','}','/','.',"'",',') ; // need to also exclude \
  $legitPosts=array('templateExperiment',
                    'csvPostName');
  
  checkPost($_POST,$legitPosts,$illegalInputs);
  
	#create a new study
	$studySource="../Experiments/".$_POST['templateExperiment'];
	$studyDest="../Experiments/".$_POST["csvPostName"];
	recurse_copy($studySource,$studyDest);
	unlink("../Experiments/".$_POST["csvPostName"]."/name.txt");//to facilitate new name
	$_DATA['guiSheets']['thisDir']="../Experiments/".$_POST['csvPostName'];
	$_DATA['guiSheets']['studyName']=$_POST['csvPostName'];
	$_DATA['guiSheets']['currentGuiSheetPage']='sheetsEditor';

	header ("Location: index.php");

?>	
	
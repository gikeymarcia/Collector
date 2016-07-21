<?php 

	if(!isset($_SESSION)){ exit; }

  //require('guiFunctions.php'); //for use of recurse_copy

  $illegalInputs=array('<?','{','}','/','.',"'",',') ; // need to also exclude \
  $legitPosts=array('templateExperiment',
                    'csvPostName');
  
  checkPost($_POST,$legitPosts,$illegalInputs);
  
	#create a new study
  $expFolder  = $_PATH->get('Experiments');
  
	$studySource  = $expFolder."/".$_POST['templateExperiment'];
	$studyDest    = $expFolder."/".$_POST["csvPostName"];
	recurse_copy($studySource,$studyDest);
	unlink($expFolder."/".$_POST["csvPostName"]."/name.txt");//to facilitate new name
	$_DATA['guiSheets']['thisDir']=$expFolder."/".$_POST['csvPostName'];
	$_DATA['guiSheets']['studyName']=$_POST['csvPostName'];
	$_DATA['guiSheets']['currentGuiSheetPage']='sheetsEditor';

	header ("Location: index.php");

?>	
	
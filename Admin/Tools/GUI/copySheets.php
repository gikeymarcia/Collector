<?php 

	if(!isset($_SESSION)){ exit; }

  //require('guiFunctions.php'); //for use of recurse_copy

  /*
  $illegalInputs=array('<?','{','}','/','.',"'",',') ; // need to also exclude \
  $legitPosts=array('templateExperiment',
                    'csvPostName');
  
  checkPost($_POST,$legitPosts,$illegalInputs);
  
  */
  
  print_r($_POST);
  
	#create a new study
  $expFolder  = $_PATH->get('Experiments');
  
	$studySource  = $expFolder."/".$_POST["createStudyName"];
	$studyDest    = $expFolder."/".$_POST["newStudyName"];
	recurse_copy($studySource,$studyDest);
	$_DATA['guiSheets']['thisDir']=$expFolder."/".$_POST['newStudyName'];
	$_DATA['guiSheets']['studyName']=$_POST['newStudyName'];

	//header ("Location: index.php");

?>	
	
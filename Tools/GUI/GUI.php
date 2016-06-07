<?php
	adminOnly();
?>	  

<style>
  
  #returnToIndex{
    position:relative;
  }

</style>

<div>
  <form action="index.php" method="post">
    <input type="hidden" name="currentGuiSheetPage" value="indexGui">
    <button id="returnToIndex" name="goBackToBegin" value="done" class="collectorButton"> return to index </button>
  </form>
  
<?php
  require_once("guiFunctions.php");
  require_once("guiCss.php");
        
  $pages = array (
      'indexGui'     ,
      'copySheets'   ,
      'newSheet'     ,
      'sheetsEditor' ,
      'TrialTypeEditor',
      
      
      
      'surveyEditor'
      
      
      
      
  );
  
  $illegalInputs=array('<?','{','}','/','\\') ; // need to also exclude \
    
    
  if(isset($_POST['currentGuiSheetPage'])){
    checkPost($_POST,array('currentGuiSheetPage'),$illegalInputs);
    if(!in_array($_POST['currentGuiSheetPage'],$pages)){
//      die ("You tried to go somewhere you were not allowed!");
      
      echo "<div class='alert alert-warning fade in'>
        <a href='#' class='close' data-dismiss='alert' aria-label='close'>&times;</a>
        <strong>Warning!</strong>You tried to go somewhere you were not allowed!
      </div>";
      //print_r($_POST);
      //$_POST['currentGuiSheetPage']='indexGui';
      //die();
      
    }  
  } // else needed? other checks are dealt with on pages themselves
  
  if($_POST['goBackToBegin']="done"){
    $_DATA['guiSheets']['currentGuiSheetPage']='indexGui';
  }
  
	if(isset($_POST['currentGuiSheetPage'])){
		$_DATA['guiSheets']['currentGuiSheetPage']=$_POST['currentGuiSheetPage'];
	}
  if(isset($_DATA['trialTypeEditor'])){
   
   // this may be legitimate code
   /* if($_DATA['guiSheets']['currentGuiSheetPage']!="trialTypeEditor" & $_DATA['guiSheets']['currentGuiSheetPage']!="createTrialType"){
      unset($_DATA['trialTypeEditor']);
    }  */ 
  }

	
  if(!isset($_DATA['guiSheets']['currentGuiSheetPage'])){
    require('indexGui.php');
    $_DATA['guiSheets']['currentGuiSheetPage']='indexGui';
  }
  
	
  else {
    require(($_DATA['guiSheets']['currentGuiSheetPage']).".php");
  }

  
  
?>

</div>

<?php

  require '../../../initiateTool.php';
  require '../guiFunctions.php';


// resume here!



  ob_end_clean();
  header('Content-type: text/plain; charset=utf-8');

  $file     = $_GET['file'];
  $content  = $_GET['content'];

  /*


  //checking whether the post is legitimate
  $legitPostNames=array
    ( 'currentGuiSheetPage',
      'currStudyName',
      'csvSelected',
      'sheetName',
      'stimTableInput',
      'newSheet',
      'DeleteSheet',
      'Save');
  $illegalInputs=array('<?','{','}','/','\\'); // need to also exclude \
  
  //insert Tyson's illegal character thing here
    //preg_replace('([^ \\-0-9A-Za-z])', '', $_POST['u']);
   
  checkPost($_POST,$legitPostNames,$illegalInputs); // defined in guiFunctions

*/  
  
  //Saving whichever csv you are currently working on
    
  // renaming spreadsheet if the user renamed it
  /*
  if ($studySheetsInfo->thisSheetName!='Conditions' && strcmp($_POST['sheetName'],$studySheetsInfo->thisSheetName)!=0){      
    $illegalChars = array('  ',' ','.');
    foreach ($illegalChars as $illegalChar){
      $_POST['sheetName'] = str_ireplace($illegalChar,'',$_POST['sheetName']);
    }
    $newFile      = $thisDirInfo->studyDir.'/'.$studySheetsInfo->thisSheetFolder.'/'.$_POST['sheetName'].'.csv';
    $originalFile = $thisDirInfo->studyDir.'/'.$studySheetsInfo->thisSheetFilename;
    copy($originalFile,$newFile);
    unlink($originalFile);
    $studySheetsInfo->thisSheetName     = $_POST['sheetName'];
    $studySheetsInfo->thisSheetFilename = "$studySheetsInfo->thisSheetFolder/$studySheetsInfo->thisSheetName.csv";        
  }
  */

  $stimTableArray = json_decode($content, true);                            // converting json from POST into array table data into array
  writeHoT($file,$stimTableArray);   // saving array into HoT format

?>
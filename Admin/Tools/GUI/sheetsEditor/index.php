<?php
  
/*
  GUI

  Collector
    A program for running experiments on the web
    Copyright 2012-2015 Mikey Garcia & Nate Kornell


    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License version 3 as published by
    the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>

 */

  require "../../../initiateTool.php";

  // check whether the page has been refreshed //
  
  $oldToken = isset($_SESSION['token']) ? $_SESSION['token'] : null;

  $_SESSION['token'] = chr( mt_rand( 97 ,122 ) ) .substr( md5( time( ) ) ,1 );
  
  $refreshSkip  = false;                                          // assume that we are not refreshing the page
  if (isset($_POST['token']) AND $_POST['token'] !== $oldToken) { // but if we are
    $refreshSkip  = true;                                         // skip computations that are muddled by refreshing a page
  } 
  
 ?>

<link rel="stylesheet" href="sheetsEditor.css">
 
<?php
   
  // requiring files and calling in classes
  require_once  ('../guiFunctions.php');
  require_once  ('../guiClasses.php');
  $thisDirInfo      = new csvDirInfo(); // calling in class for directory information
  $studySheetsInfo  = new csvSheetsInfo(); // calling in class for sheets information
  
  
  // functions //
  
  function copyStudy($source,$dest){
    /*
      $illegalInputs=array('<?','{','}','/','.',"'",',') ; // need to also exclude \
      $legitPosts=array('templateExperiment',
                        'csvPostName');    
      checkPost($_POST,$legitPosts,$illegalInputs);
    */
    global $_DATA,$_PATH;
        
    #create a new study
    $expFolder  = $_PATH->get('Experiments');
    
    $studySource  = $expFolder."/".$source; //$_POST["createStudyName"];
    $studyDest    = $expFolder."/".$dest;   //$_POST["newStudyName"];
    recurse_copy($studySource,$studyDest);
    $_DATA['guiSheets']['thisDir']    = $expFolder."/".$_POST['newStudyName'];
    $_DATA['guiSheets']['studyName']  = $_POST['newStudyName'];
    
    return $_DATA['guiSheets']['studyName'];
    
  }  
  
  function editStudy($originalName,$newName){
    
    global $_PATH,$thisDirInfo;
    
    $thisDirInfo->studyDir      =   $_PATH->get("Experiments")."/".$newName;
    if($newName  !=  $originalName){
      
      //rename folder
      $oldDir = $_PATH->get("Experiments")."/".$originalName;
      $newDir = $_PATH->get("Experiments")."/".$newName;
      rename($oldDir,$newDir);                
    }
    return $newName;
  }
  
  // set filename and copy if necessary
  if(isset($_POST['editStudy'])){
    //  if file has been selected for editing (from higher level index file in GUI folder)
    $thisDirInfo->studyDir            = $_PATH->get("Experiments")."/".$_POST['editStudyName'];    
    $_DATA['guiSheets']['studyName']  = $_POST['editStudyName'];
  } else {    
    //if file has just been created
    if(isset($_POST['createStudy'])){
      $_DATA['guiSheets']['studyName']  = copyStudy($_POST["createStudyName"],$_POST["newStudyName"]);
      $thisDirInfo->studyDir            = $_PATH->get("Experiments")."/".$_POST['newStudyName'];          
    }
    else {  

      $_DATA['guiSheets']['studyName']    = editStudy($_DATA['guiSheets']['studyName'],$_POST['currStudyName']);  //file is in the process of being edited
      
      $_DATA['guiSheets']['csvSelected']  = $_POST['csvSelected'];    // in case the user is not working from the default ("conditions.csv") spreadsheet
    }
  }
  
  //updating study name
  $thisDirInfo->studyName=$_DATA['guiSheets']['studyName'];

  
  // loading either default csv files, or whichever file was loaded //
  if(!isset($_DATA['guiSheets']['csvSelected'])){ //i.e. if this page has just been opened
    $studySheetsInfo->thisSheetName='Conditions';
    $studySheetsInfo->thisSheetFilename='Conditions.csv';
    $studySheetsInfo->thisSheetFolder='';
  } else { // checking whether browsing to "Conditions.csv";
    if(strcmp($_DATA['guiSheets']['csvSelected'],'Conditions.csv,')==0){
      $studySheetsInfo->thisSheetName='Conditions';
      $studySheetsInfo->thisSheetFolder='';
      $studySheetsInfo->thisSheetFilename="$studySheetsInfo->thisSheetFolder/Conditions.csv";
    }  else {
      $studySheetsInfo->postSheetInfo($_DATA['guiSheets']['csvSelected']);  
    }  
  }

  
  // List csv files in the directories
  $studySheetsInfo->stimSheets  = getCsvsInDir($thisDirInfo->studyDir.'/Stimuli/');
  $studySheetsInfo->procSheets  = getCsvsInDir($thisDirInfo->studyDir.'/Procedure/');
  $studySheetsInfo->legitSheets = array_merge($studySheetsInfo->stimSheets,$studySheetsInfo->procSheets); //note that new sheet and conditions is not in this array  
  

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
         
  
  $branches = scandir($_PATH->get("Experiments"));
  $listStudyNames = array();
  foreach ($branches as $branch) {
    if ($branch === '.' or $branch === '..' or $branch === "_Common") continue;

    if (is_dir($_PATH->get("Experiments") . '/' . $branch)) {
        array_push($listStudyNames,$branch);
    }
  } 
  
  $listStudyNamesJson=json_encode($listStudyNames);
  
  if(isset($_POST['DeleteSheet'])){      //something is being deleted and page isn't being refreshed
    if($refreshSkip == false) {unlink ("$thisDirInfo->studyDir/$studySheetsInfo->thisSheetFilename");}
    $studySheetsInfo->thisSheetName     = 'Conditions';
    $studySheetsInfo->thisSheetFolder   = '';
    $studySheetsInfo->thisSheetFilename = "$studySheetsInfo->thisSheetFolder/Conditions.csv";
  }
  
  
  // creating new sheet //
  
  function createNewSheet($procStim,$studySheetsInfo,$refreshSkip){
    global $_PATH, $thisDirInfo,$_DATA;
    
    $newName  = 0;
    $newNo    = 0;
    
    if($procStim=="Procedure"){
      $thisSheetsList=$studySheetsInfo->procSheets;
    } else { // it is a stimuli sheet being created
      $thisSheetsList=$studySheetsInfo->stimSheets;
    }
    
    if($refreshSkip){                 //  if refresh
      $newNo  = $_DATA['refreshNo'];  //  then load number calculated last time
    } else {    
      while ($newName==0){            //  calculate new number from scratch
        $newNo++;
        if(!in_array("$procStim$newNo.csv",$thisSheetsList)){
          $newName=1;
        }          
      }
      $_DATA['refreshNo'] = $newNo;   // and store in case of refresh
    }
    
    $studySheetsInfo->thisSheetName     = "$procStim$newNo";
    $studySheetsInfo->thisSheetFolder   = "$procStim";
    $studySheetsInfo->thisSheetFilename = "$studySheetsInfo->thisSheetFolder/$studySheetsInfo->thisSheetName.csv";
    copy($_PATH->get("Experiments")."/New Experiment/$procStim/$procStim.csv","$thisDirInfo->studyDir/$studySheetsInfo->thisSheetFilename");   
    return $studySheetsInfo;
  }
  
  if(isset($_POST['newSheet'])){  
    switch ($_POST['newSheet']){
      case "stim":
        //identify what novel filename needs to be          
        $studySheetsInfo  = createNewSheet("Stimuli",$studySheetsInfo,$refreshSkip);
        break;
      case "proc":
        //identify what novel filename needs to be        
        $studySheetsInfo  = createNewSheet("Procedure",$studySheetsInfo,$refreshSkip);
        break;
    }
  }
  
  //Saving whichever csv you are currently working on
  if (isset($_POST['Save'])){ 
    
    // renaming spreadsheet if the user renamed it
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

    $stimTableArray = json_decode($_POST['stimTableInput'], true);                            // converting json from POST into array table data into array
		writeHoT("$thisDirInfo->studyDir/$studySheetsInfo->thisSheetFilename",$stimTableArray);   // saving array into HoT format
  }      
  
  // extract table from csv file
  $stimuli  = getFromFile("$thisDirInfo->studyDir/$studySheetsInfo->thisSheetFilename",false,',');  // reading csv file into array
  $stimData = array(array_keys(reset($stimuli)));                                                   // storing header into array
  
  foreach ($stimuli as $row) {
    
    $stimData[] = array_values($row);   //  adding new row to stimData array;
    
  }
  $stimData = json_encode($stimData);   //  json encoding stimData
  
  
  //list all csv files - should this be a function within $studySheetsInfo?
  $studySheetsInfo->stimSheets=getCsvsInDir($thisDirInfo->studyDir.'/Stimuli/');      //  stim sheets
  $studySheetsInfo->procSheets=getCsvsInDir($thisDirInfo->studyDir.'/Procedure/');    //  proc sheets
  $sheetsList=array_merge($studySheetsInfo->stimSheets,$studySheetsInfo->procSheets); //  merging the two together
  
  $jsonSheets=json_encode($sheetsList);
  
  
?>

<form id="sheetsForm" action='index.php' method='post'>
  <input type="hidden" name="token" value="<?= $_SESSION['token'] ?>"> 
  <input type="button" id="indexButton" class="collectorButton" value="Go back to Index" onclick="document.location.href = '../';" style=" position:absolute;
  right:20px;">
  <h1>
    <textarea id="currStudyName" name="currStudyName" style="color:#069;" rows="1" onkeyup="checkName()"><?=$thisDirInfo->studyName?></textarea>
  </h1>
  
  <span>
    <button name="newSheet" value="stim" class="collectorButton" id="newStimButton"> new stimuli sheet </button>
    <button name="newSheet" value="proc" class="collectorButton" id="newProcButton"> new procedure sheet </button>
    <button type ="button" class ="collectorButton" id="stimButton"> list of stimuli files</button>
  </span>
  <br>
  

<div style="display:inline-block; width:1000px">
  <div>
    
    <select id="csvSelected" name="csvSelected" title="[filename],[folder]">

      <?php  
        // what is the first item in list - either the current sheet or the default sheet ("conditions");
        
        if ($studySheetsInfo->thisSheetFilename !=  ''){            // current sheet
        
          echo"<option  value='$studySheetsInfo->thisSheetName.csv,$studySheetsInfo->thisSheetFolder'>$studySheetsInfo->thisSheetName.csv,$studySheetsInfo->thisSheetFolder</option>";      
          
        } 

        if('Conditions' != $studySheetsInfo->thisSheetName){        // conditions sheet
        
          echo "<option value='Conditions.csv,'>Conditions.csv,</option>";
        
        }      
        
        // rest of the list
        foreach ($studySheetsInfo->procSheets as $procFile){      // procedure files
          if($procFile != "$studySheetsInfo->thisSheetName.csv"){
            echo "<option value='$procFile,Procedure'>$procFile,Procedure</option>";
          }  
        }      
        
        foreach ($studySheetsInfo->stimSheets as $stimFile){      // stimuli files
          if($stimFile != "$studySheetsInfo->thisSheetName.csv"){
            echo "<option value='$stimFile,Stimuli'>$stimFile,Stimuli</option>";  
          }
        }
      ?>    
    </select>
  
    <button type='submit' class='collectorButton' value='Select'>Open</button>
  
  </div>    
  
  <script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
  <link rel="stylesheet" href="../handsontables/handsontables.full.css">
  <script src="../handsontables/handsontables.full.js"></script>
  <script src="../handsontables/GUI_Handson.js"></script>

  <?php
    if (strcmp("$studySheetsInfo->thisSheetName.csv","Conditions.csv")==0){ ?>
      <h2 title="You cannot edit the Conditions.csv filename or delete the file.">Conditions.csv</h2>
  <?php    
    }  else {
  ?>
      
      <h2>
        <textarea id="sheetName" name="sheetName" style="color:#069;" rows="1" onchange="checkSheetName()"><?=$studySheetsInfo->thisSheetName?></textarea>
      </h2>
  <?php 
    }

    // doing this in PHP to prevent whitespace
    echo '<div id="stimArea" class="tableArea">'
       .         '<div id="stimTable"></div>'
       .     '</div>'
       //. '</div>';
  ?>
  <br>

  <input type="hidden" name="stimTableInput">
    
  <input id="saveButton" type="button" class="collectorButton" value="Save">  
  <button id="submitButton" type="submit" name="Save" class="collectorButton" style="display:none"></button> 

  <?php
    if (strcmp("$studySheetsInfo->thisSheetName.csv",'Conditions.csv')!=0){  ?>
      <input type="button" id="deleteSheetButton" name="DeleteSheetQuestion" class="collectorButton" value="Delete?">  
      <button id="deleteActivate" type="submit" name="DeleteSheet" class="collectorButton" value="Delete" style="display:none">No text needed</button>
  <?php    
    }  
  ?>      
  
</div>


  
  <div id="helperBar">
    <h1> Helper </h1>

    <h2 id="helpType">Select Cell</h2>
    
      <!-- Help Types -->
    
    <!-- Conditions -->
    <?php 
      // create list of all helper files
      
      $helper_files_dir   = "helper/Original/";
      $helper_files      = glob("$helper_files_dir*.txt"); 
      
      $filename_remove    = array(".txt",$helper_files_dir);

      $helper_files_clean = json_encode(str_ireplace($filename_remove,"",$helper_files));

      $backup_help        = array(); 
      
      foreach($helper_files as $helper_file){
        
        $helper_filename  = explode("/",$helper_file);
        $helper_filename  = $helper_filename[count($helper_filename)-1];
        $helper_name      = str_ireplace(".txt","",$helper_filename);
        
        if (file_exists("helper/UserCustom/$helper_filename")){
          $helper_contents = file_get_contents("helper/UserCustom/$helper_filename"); // use custom file if it exists
        } else {
          $helper_contents = file_get_contents($helper_file);
        }
        $backup_contents = file_get_contents($helper_file);                         // so that we can revert back to basic trial types

        echo  "<div class='helpType_Col' id='helpType_$helper_name' contentEditable='true' title='you can edit these notes'>";
        echo  "$helper_contents";
        echo  "</div>";
        ?>

        <br> <input id='helpType_<?=$helper_name?>Button' type='button' class='helpType_Button collectorButton' onclick='revertHelpFile("<?= $helper_name ?>")' value='Revert help to original' style="display:none">
        
        <?php
        
        echo  "<span id='help_type_backup_$helper_name' style='display:none'>$backup_contents</span>";
        
      }
        
    ?>
    
    <div class="helpType_Col" id="helpType_TrialType">
      
    
    <?php
      $trialTypes=getAllTrialTypeFiles();
    
      $customTrialTypesDir = $_PATH->get("Custom Trial Types");
      
      $trialTypesDir       = $_PATH->get("Trial Types");
      
      $trialTypes = array_keys($trialTypes);
      
      $trialTypesJson = json_encode($trialTypes);
      
      foreach($trialTypes as $trialType){
          echo " <h3 class='typeHeader' id='header$trialType' onclick='hideShow(\"detail$trialType\")'>$trialType</h3>";

        if(file_exists($trialTypesDir."/".$trialType."/help.txt")){
          echo "<div id='detail$trialType' style='display:none'><h4><em>Collector Trial Type</em></h4>".file_get_contents($trialTypesDir."/".$trialType."/help.txt")."</div>"; // the actual help
          
        } else {
          if(file_exists($customTrialTypesDir."/".$trialType."/help.txt")){
          
            echo "<div id='detail$trialType' style='display:none'>
            <h4><em>Custom Trial Type</em></h4>
            ".file_get_contents($customTrialTypesDir."/".$trialType."/help.txt")."</div>"; // the actual help
          
          }
          else {
            echo "<div id='detail$trialType' style='display:none'>No 'help.txt' file present. If this is a custom trial type you wrote please create one. Otherwise, please contact a.haffey@reading.ac.uk</div>";
          }
        } 
        
        
      }

    ?>
    
    </div>
    
    
    <!-- default !-->
    <div class="helpType_Col" id="helpTypeDefault">
      Select a cell to see more information about that column.
    </div>  
    
  </div>
  
</form>


<script type="text/javascript">



//  var help_files = <?= $helper_files_clean ?>;  // not currently used - delete when sure don't need it.
  
  var help_location = "helper/UserCustom/";
  
  var help_files = document.getElementsByClassName("helpType_Col");
    
  function updateCustomFile(i) {
    
    var custom_help = i.replace("helpType_","");
    custom_help     += ".txt"; 
    
    //ajax call here
    $.get(
      'HelpUpdate.php',
      { file    : custom_help,
        content : document.getElementById(i).innerHTML,
        location: help_location
      }      
    );
    
  }
  
  
  
  function revertHelpFile(helpSelected){
    //alert(helpSelected);
//    console.dir(helpSelected);
//    console.dir(String(helpSelected));
    
    
//    alert($("#helpType_"+helpSelected).html());
    $("#helpType_"+helpSelected).html($("#help_type_backup_"+helpSelected).html());
    updateCustomFile("helpType_"+helpSelected);
    
  }
  
  
  for (var i=0; i<help_files.length; i++){
    
    help_files[i].addEventListener('input', (function(i) {
      return function() {
        updateCustomFile(help_files[i].id);
      }
    })(i))

  }
    
    
  //importing json encoded lists from php
  listStudyNames  = <?=$listStudyNamesJson?>;
  listSheetsNames = <?=$jsonSheets?>;
  var stimData    = <?= $stimData ?>;
  trialTypesJson  = <?=$trialTypesJson ?>;
  
  
  
</script>

<script src="sheetsEditor.js"></script>
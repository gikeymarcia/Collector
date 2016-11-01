<?php
  
/*
   Experiment Editor:
   -Open and view all stimuli and procedure files for every experiment
   -Open condition sheet
   -change contents of sheet
   -copy/rename/delete sheets
   
   -start by scanning all experiments to find all sheets
   -display list of experiments
   -after experiment is selected, display list of sheets inside that experiment
   -alternatively, let them create a new exp
   -can open sheets, or copy to new sheet and start editing that
   -can also copy experiment to edit that
 */

  require "../../initiateTool.php";
  
  function read_csv_raw($file) {
    $data = array();
    $file_resource = fopen($file, 'r');
    
    while ($line = fgetcsv($file_resource)) {
      $data[] = $line;
    }
    fclose($file_resource);
    return $data;
  }
  
  $experiments = get_Collector_experiments($_FILES);
  
  $experiment_files = array();
  
  foreach ($experiments as $exp) {
    $_FILES->set_default('Current Experiment', $exp);
    $experiment_files[$exp]['Conditions'] = read_csv_raw($_FILES->get_path('Conditions'));
    $experiment_files[$exp]['Stimuli']    = $_FILES->read('Stimuli Dir');
    $experiment_files[$exp]['Procedures'] = $_FILES->read('Procedure Dir');
  }
$new_exp_json = file_get_contents('default_new_experiment.json');
?>

<script>
  var new_experiment_data = <?= $new_exp_json ?>;
</script>

  <div id="load_toolbar">
    <button type="button" id="new_experiment_button" class="collectorButton">New Experiment</button>
    
    <select id="experiment_select">
      <?php 
      foreach ($experiments as $experiment){
        echo "<option>$experiment</option>";
      }
      ?>
    </select>
    <button type="button" id="experiment_select_button" class="collectorButton">Load</button>
  </div>
  
  <div id="rest_of_interface">  
  </div>

  <script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
  <link rel="stylesheet" href="../handsontables/handsontables.full.css">
  <script src="../handsontables/handsontables.full.js"></script>

  

<div id="stimArea" class="tableArea">'
       '<div id="stimTable"></div>'
   '</div>'
</div>';


  
  <script>
  
    var experiment_files = <?= json_encode($experiment_files) ?>;
    console.dir(experiment_files);
    
    
    $("#new_experiment_button").on("click",function(){
      createHoT("stimTable",)
    });
    $("#experiment_select_button")
    
  
  </script>
  
  
  
  <?php
  
  var_dump($experiments);
  
  exit;

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

/*
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
    
      

      $_DATA['guiSheets']['studyName'] = editStudy($_DATA['guiSheets']['studyName'],$_POST['currStudyName']);  //file is in the process of being edited
      
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

*/
  
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
  <br>
  
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
  
</form>


<script type="text/javascript">

  //importing json encoded lists from php
  listStudyNames  = <?=$listStudyNamesJson?>;
  listSheetsNames = <?=$jsonSheets?>;
  var stimData = <?= $stimData ?>;
   
</script>

<script src="sheetsEditor.js"></script>
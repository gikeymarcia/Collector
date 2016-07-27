<?php

  require "../../../initiateTool.php";
  
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
 
 ?>

<link rel="stylesheet" href="sheetsEditor.css">
 
<?php
  
  
  
  
  
  !!! tyson addition
  
  <?php

$oldToken = isset($_SESSION['token']) ? $_SESSION['token'] : null;
$_SESSION['token'] = rand_string(24);

if (isset($_POST['token']) AND $_POST['token'] === $oldToken) {
    // execute code
}

?>

<form>
    
    <input type="hidden" name="token" value="<?= $_SESSION['token'] ?>">
</form>

!!!
  
  
  
  
  
  
  
  
  
  
  
  
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
  
  if(isset($_POST['DeleteSheet'])){//something is being deleted	
    unlink ("$thisDirInfo->studyDir/$studySheetsInfo->thisSheetFilename");
    $studySheetsInfo->thisSheetName='Conditions';
    $studySheetsInfo->thisSheetFolder='';
    $studySheetsInfo->thisSheetFilename="$studySheetsInfo->thisSheetFolder/Conditions.csv";
  }
  
  
  // creating new sheet //
  
  function createNewSheet($procStim,$studySheetsInfo){
    global $_PATH, $thisDirInfo;
    
    $newName  = 0;
    $newNo    = 0;
    
    if($procStim=="Procedure"){
      $thisSheetsList=$studySheetsInfo->procSheets;
    } else { // it is a stimuli sheet being created
      $thisSheetsList=$studySheetsInfo->stimSheets;
    }

    while ($newName==0){
      $newNo++;
      if(!in_array("$procStim$newNo.csv",$thisSheetsList)){
        $newName=1;
      }          
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
        $studySheetsInfo  = createNewSheet("Stimuli",$studySheetsInfo);
        break;
      case "proc":
        //identify what novel filename needs to be        
        $studySheetsInfo  = createNewSheet("Procedure",$studySheetsInfo);        
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

<form action='index.php' method='post'>
 
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
  
  <script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
  <link rel="stylesheet" href="../handsontables/handsontables.full.css">
  <script src="../handsontables/handsontables.full.js"></script>

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
       . '</div>';
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


//removing the current study's name from the list (because this list is to prevent duplication)
studyIndex  = listStudyNames.indexOf(currStudyName.value);
listStudyNames.splice(studyIndex,1);


// Checks for preventing repeating study names
var revertStudyName = currStudyName.value;
function checkName(){
  // check if member of array
  if($.inArray(currStudyName.value,listStudyNames)!=-1){
    alert("This is the same name of another study, reverting to unique name");
    $("#currStudyName").val(revertStudyName);
  } else{
    revertStudyName = $("#currStudyName").val();
  }
}


//removing the current study's name from the list (because this list is to prevent duplication)
if (typeof sheetName !== 'undefined'){                            //i.e. if this is not "conditions.csv"
  sheetIndex  = listSheetsNames.indexOf(sheetName.value+'.csv');  
  listSheetsNames.splice(sheetIndex,1);
  var revertSheetName = sheetName.value;
  function checkSheetName(){
    potentialSheetName=sheetName.value+'.csv';
    // check if member of array
    if($.inArray(potentialSheetName,listSheetsNames)!=-1){
      alert("This is the same name of another sheet, reverting to unique name");
      sheetName.value=revertSheetName;
    } else{
      revertSheetName=sheetName.value; // could delete to revert to name when page loaded
    }
    //put in a check to see if there are any illegal symbols here in future version - this is currently being checked after saving  
  }  
}

var stimTable;
    function isTrialTypeHeader(colHeader) {
        var isTrialTypeCol = false;
        
        if (colHeader === 'Trial Type') isTrialTypeCol = true;
        
        if (   colHeader.substr(0, 5) === 'Post '
            && colHeader.substr(-11)  === ' Trial Type'
        ) {
            postN = colHeader.substr(5, colHeader.length - 16);
            postN = parseInt(postN);
            if (!isNaN(postN) && postN != 0) {
                isTrialTypeCol = true;
            }
        }
        
        return isTrialTypeCol;
    }
    function isNumericHeader(colHeader) {
        var isNum = false;
        if (colHeader.substr(-4) === 'Item')     isNum = true;
        if (colHeader.substr(-8) === 'Max Time') isNum = true;
        if (colHeader.substr(-8) === 'Min Time') isNum = true;
        return isNum;
    }
    function isShuffleHeader(colHeader) {
        var isShuffle = false;
        if (colHeader.indexOf('Shuffle') !== -1) isShuffle = true;
        return isShuffle;
    }
    function firstRowRenderer(instance, td, row, col, prop, value, cellProperties) {
        Handsontable.renderers.TextRenderer.apply(this, arguments);
        td.style.fontWeight = 'bold';
        if (value == '') {
            $(td).addClass("htInvalid");
        }
    }
    function numericRenderer(instance, td, row, col, prop, value, cellProperties) {
        Handsontable.renderers.TextRenderer.apply(this, arguments);
        if (isNaN(value) || value === '') {
            td.style.background = '#D8F9FF';
        }
    }
    function shuffleRenderer(instance, td, row, col, prop, value, cellProperties) {
        Handsontable.renderers.TextRenderer.apply(this, arguments);
        if (value === '') {
            td.style.background = '#DDD';
        } else if (
            typeof value === 'string' 
         && (   value.indexOf('#') !== -1
             || value.toLowerCase() === 'off'
            )
        ) {
            td.style.background = '#DDD';
        }
    }
    function trialTypesRenderer(instance, td, row, col, prop, value, cellProperties) {
        Handsontable.renderers.AutocompleteRenderer.apply(this, arguments);
        if (value === 'Nothing' || value === '') {
            if (instance.getDataAtCell(0,col) === 'Trial Type') {
                $(td).addClass("htInvalid");
            } else {
                td.style.background = '#DDD';
            }
        }
    }
    function updateDimensions(hot, addWidth, addHeight) {
      var addW = addWidth  || 0;
      var addH = addHeight || 0;
      
      var container   = hot.container;
      var thisSizeBox = $(container).find(".wtHider");
      
      var thisWidth  = thisSizeBox.width()+22+addW;
      var thisHeight = thisSizeBox.height()+22+addH;
      
      var thisArea = $(container).closest(".tableArea");
      
      thisWidth  = Math.min(thisWidth,  thisArea.width());
      thisHeight = Math.min(thisHeight, 600);
      
      hot.updateSettings({
        width:  thisWidth,
        height: thisHeight
      });
    }
    function updateDimensionsDelayed(hot, addWidth, addHeight) {
        updateDimensions(hot, addWidth, addHeight);
        setTimeout(function() {
            updateDimensions(hot);
        }, 0);
    }
    function createHoT(container, data) {
        var table = new Handsontable(container, {
            data: data,
            width: 1,
            height: 1,
      
            afterChange: function(changes, source) {
                updateDimensions(this);  
        
        var middleColEmpty=0;
        var middleRowEmpty=0;
        var postEmptyCol=0; //identify if there is a used col after empty one
        var postEmptyRow=0; // same for rows

        //identify if repetition has occurred and adjusting value
        var topRow=[];
        for (var k=0; k<this.countCols()-1; k++){
          var cellValue=this.getDataAtCell(0,k);
          topRow[k]=this.getDataAtCell(0,k);
          for (l=0; l<k; l++){
            if (this.getDataAtCell(0,k)==this.getDataAtCell(0,l)){
              alert ('repetition has occurred!');
              this.setDataAtCell(0,k,this.getDataAtCell(0,k)+'*');
            }
          }
                  
        }
        
        //Removing Empty middle columns
        for (var k=0; k<this.countCols()-1; k++){
          if (this.isEmptyCol(k)){
            if (middleColEmpty==0){
              middleColEmpty=1;
            }
          }            
          if (!this.isEmptyCol(k) & middleColEmpty==1){
            postEmptyCol =1;
            alert ("You have an empty column in the middle - Being removed from table!");
            this.alter("remove_col",k-1); //delete column that is empty 
            middleColEmpty=0;
          }            
        }
        
        //Same thing for rows
        for (var k=0; k<this.countRows()-1; k++){
          if (this.isEmptyRow(k)){
            if (middleRowEmpty==0){
              middleRowEmpty=1;
            }
          }            
          if (!this.isEmptyRow(k) & middleRowEmpty==1){
            postEmptyRow =1;
            alert ("You have an empty row in the middle - Being removed from table!");
            this.alter("remove_row",k-1); //delete column that is empty
            middleRowEmpty=0;
          }            
        }        
        if(postEmptyCol != 1 ){
          while(this.countEmptyCols()>1){  
            this.alter("remove_col",this.countCols); //delete the last col
          }
        }
        if(postEmptyRow != 1){
          while(this.countEmptyRows()>1){  
            this.alter("remove_row",this.countRows);//delete the last row
          }
        }
      },
      afterInit: function() {
          updateDimensions(this);
      },
      afterCreateCol: function() {
          updateDimensionsDelayed(this, 55, 0);
      },
      afterCreateRow: function() {
          updateDimensionsDelayed(this, 0, 28);
      },
      afterRemoveCol: function() {
          updateDimensionsDelayed(this);
      },
      afterRemoveRow: function() {
          updateDimensionsDelayed(this);
      },
      rowHeaders: false,
      contextMenu: true,
      cells: function(row, col, prop) {
        var cellProperties = {};        
        if (row === 0) {
            // header row
            cellProperties.renderer = firstRowRenderer;
        } else {
            var thisHeader = this.instance.getDataAtCell(0,col);
            if (typeof thisHeader === 'string' && thisHeader != '') {
                if (isTrialTypeHeader(thisHeader)) {
                    cellProperties.type = 'dropdown';
                    cellProperties.source = trialTypes;
                    cellProperties.renderer = trialTypesRenderer;
                } else {
                    cellProperties.type = 'text';
                    if (isNumericHeader(thisHeader)) {
                        cellProperties.renderer = numericRenderer;
                    } else if (isShuffleHeader(thisHeader)) {
                        cellProperties.renderer = shuffleRenderer;
                    } else {
                        cellProperties.renderer = Handsontable.renderers.TextRenderer;
                    }
                }
            } else {
                cellProperties.renderer = Handsontable.renderers.TextRenderer;
            }
        }                
        return cellProperties;
      },
      minSpareCols: 1,
      minSpareRows: 1,
      manualColumnFreeze: true,
      fixedRowsTop: 0,
      colHeaders: false,
      cells: function (row, col, prop) {
      }
        });
        return table;
    }
    
  var stimContainer = document.getElementById("stimTable");
  var stimData = <?= $stimData ?>;
  stimTable = createHoT(stimContainer, stimData);
    
    // limit resize events to once every 100 ms
    var resizeTimer;
    
    $(window).resize(function() {
        window.clearTimeout(resizeTimer);
        resizeTimer = window.setTimeout(function() {
            updateDimensions(stimTable);
        }, 100);
    });
   
$("#submitButton").on("click", function() {
  $("input[name='stimTableInput']").val(JSON.stringify(stimTable.getData()));
});

$("#stimButton").on("click", function() {
  //$("#stimListDiv").show();
  var myWindow = window.open("stimList.php", "", "width=800, height=600");
});

$("#newStimButton").on("click", function() {
  alert("Creating new Stimuli sheet");
});

$("#newProcButton").on("click", function() {
  alert("Creating new Procedure sheet");
});

$("#deleteSheetButton").on("click", function() {
  delConf=confirm("Are you SURE you want to delete this file?");
  if (delConf== true){
    document.getElementById('deleteActivate').click();
  }  
});


// saving code //

var csvSelectedValue  = csvSelected.value; // this to prevent a bug resulting from a user saving after changing the spreadsheet selected

$("#saveButton").on("click", function() { //final checks before saving
  //are there too many empty column headers?
  emptyHeadCols = 0;
  for(i=0; i<stimTable.countCols();i++){
    if(stimTable.getDataAtCell(0,i)==''){
      emptyHeadCols++;
    }
  }
  
  if(emptyHeadCols>1){
    alert("You have an empty header - will not save. Fix before trying to save again.");
  } else {
    
    // dealing with bug that emerges if someone tries to save after changing the csvSelected value;
    if($("#csvSelected").val()!=csvSelectedValue){
      $("#csvSelected").val(csvSelectedValue); 
    }
    $('#submitButton').click();
  }
});

$(window).bind('keydown', function(event) {
    if (event.ctrlKey || event.metaKey) {
        switch (String.fromCharCode(event.which).toLowerCase()) {
        case 's':
            event.preventDefault();
            alert('Saving');
      stimTable.deselectCell();      
      $("#saveButton").click();
            break;
        case 'd':
            event.preventDefault();
      $("#deleteButton").click();
            break;
        }
    }
});
</script>


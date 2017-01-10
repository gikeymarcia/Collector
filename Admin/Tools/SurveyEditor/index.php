<?php

  require "../../initiateTool.php";


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
  
  <style>
  body { 
    color: black; 
    background-color: white; 
  }
  #header { 
    font-size: 180%; 
    text-align: center; 
    margin: 10px 0 40px; 
  }
  form {
    text-align: left;
  }
  .tableArea {
    display: block;
    width: 100%;
    box-sizing: border-box;
    padding: 10px 30px;
    vertical-align: top;
  }
  textarea { 
    border-radius: 5px;
    border-color: #E8E8E8  ;
  }
  .alert-success{
    position:       absolute; 
    right:          10%;
    width:          200px;
    top:            10%;
    bottom:         10%;
    padding:        10px;
    opacity:        .8;
    border-radius:  10px;
    z-index:        3;
  }
  
  .helpType_Col { display: none; }
  #helpTypeDefault { display: block; }
  
  
  
  #TableForm {
    display: inline-block;
    width: 75%;
    box-sizing: border-box;
  }
  .typeHeader{
    color:blue;
  }
  .typeHeader:hover{
    color:green ;
  }
</style>

<?php

  // requiring files and calling in classes
  require_once ('../guiFunctions.php');
  require('../guiClasses.php');
  $surveySheetsInfo = new surveySheetsInfo(); // calling in class for sheets information


  //checking whether the post is legitimate - this may change in Tyson's new Admins system
  $legitPostNames=array
    ( 'currentGuiSheetPage',
      'currSurveyName',
      'csvSelected',
      'eventName',
      'stimTableInput',
      'newSheet',
      'DeleteSheet',
      'Save');
  $illegalInputs=array('<?','{','}','/','\\'); // need to also exclude \
  
  //can this be improved with Tyson's illegal character thing here?:
    //preg_replace('([^ \\-0-9A-Za-z])', '', $_POST['u']);
  
  checkPost($_POST,$legitPostNames,$illegalInputs); // defined in guiFunctions

  
  
  /* * * * * * * * * *
  * File organisation
  * * * * * * * * * */

  // List csv files in the directories
  $surveySheetsInfo->surveySheets=getCsvsInDir($_FILES->get_path('Common')."/Surveys"); 
 
 // jumping in from index page //
  
  // having selected "edit" a page
  if(isset($_POST['editSurvey'])){
    $surveySheetsInfo->thisSurveyFilename=$_POST['editSurveyName'];
  }
  
  // having selected "edit" a page
  if(isset($_POST['createSurvey'])){
    $surveySheetsInfo->thisSurveyFilename=$_POST['newSurveyName'].".csv";
    $templateFilename=$_POST['createSurveyName'];
    /* this code can be taken from creating a survey below */
        
    copy($_PATH->get('Common')."/Surveys/$templateFilename",$_PATH->get('Common')."/Surveys/".$surveySheetsInfo->thisSurveyFilename);
        
  }

  // opening files
  if (isset($_POST['openButton'])){  //wrong - surveyPostName is only for opening files
    $surveySheetsInfo->thisSurveyFilename=$_POST['surveyPostName'];
  }

  $surveySheetsInfo->thisSurveyName=str_ireplace('.csv','',$surveySheetsInfo->thisSurveyFilename);

  
  // creating new Survey - works  
  if(isset($_POST['newSurvey'])){  //code for creating a new CSV sheet
    $newName=0;
    $newNo=0;
     //identify what novel filename needs to be          
    while ($newName==0){
      $newNo++;
      //print_r($surveySheetsInfo->surveySheets);
      if(!in_array("Survey$newNo.csv",$surveySheetsInfo->surveySheets)){
        $newName=1;
      }          
    }
    $surveySheetsInfo->thisSurveyName="Survey$newNo";
    $surveySheetsInfo->thisSurveyFilename="$surveySheetsInfo->thisSurveyName.csv";
    copy($_PATH->get('Common')."/Surveys/SurveyDemo.csv",$_PATH->get('Common')."/Surveys/".$surveySheetsInfo->thisSurveyFilename);
  }
  
  //Saving - working
  if (isset($_POST['Save'])){ 
    // renaming file if the user renamed it
    if (strcmp($_POST['currSurveyName'],$_DATA['guiSheets']['surveyName'])!=0){
      $illegalChars=array('  ',' ','.');
      foreach ($illegalChars as $illegalChar){
        $_POST['currSurveyName']=str_ireplace($illegalChar,'',$_POST['currSurveyName']);
      }
      $newFile=$_PATH->get('Common')."/Surveys/".$_POST['currSurveyName'].'.csv';
      $originalFile=$_PATH->get('Common')."/Surveys/".$_DATA['guiSheets']['surveyName'];
     
      if(file_exists($originalFile)){ // Will there ever be a case where there isn't an original file???
        copy($originalFile,$newFile);
        unlink($originalFile);        
      }  
      $surveySheetsInfo->thisSurveyName=$_POST['currSurveyName'];
      $surveySheetsInfo->thisSurveyFilename="$surveySheetsInfo->thisSurveyName.csv";        
    }
  
  	$stimTableArray=json_decode($_POST['stimTableInput'], true);    
  	writeHoT($_PATH->get('Common')."/Surveys/".$surveySheetsInfo->thisSurveyFilename,$stimTableArray);
    
  }      
  
  // use demo survey if working from scratch 
  if($surveySheetsInfo->thisSurveyFilename=="to be declared" ||  $surveySheetsInfo->thisSurveyFilename=='[No survey Selected]'){
    $surveySheetsInfo->thisSurveyFilename=="newSurvey.csv";
    $stimuli=$_FILES->read('Survey', "SurveyDemo.csv"); // this can be integrated with later code for tidying
  } 
  
  else // load current survey
  {
    $stimuli=getFromFile($_FILES->get_path('Common')."/Surveys/$surveySheetsInfo->thisSurveyFilename",false,',');
  }

  //preparing Stim data
  $stimData = array(array_keys(reset($stimuli)));   
  foreach ($stimuli as $row) {
    $stimData[] = array_values($row);
  }
  $stimData = json_encode($stimData);      
   
  //Storing this page's filename in order to compare if user renames
  $_DATA['guiSheets']['surveyName']=$surveySheetsInfo->thisSurveyFilename; //once page decided

  // update list of files after all file processing
  $surveySheetsInfo->surveySheets=$_FILES->read("Surveys");
  $jsonSheets=json_encode($surveySheetsInfo->sheetsList); 
?>


<?php
/* <!-- Bootstrap alerts !-->
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">
<script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script> */
?>

<form action='surveyEditor.php' method='post' id="TableForm">
 <input type="button" id="indexButton" class="collectorButton" value="Go back to Index" onclick="document.location.href = '../';" style=" position:absolute;
  right:20px;">
  
 <h1 style="padding:30px">
    <?php 
      if(strcmp($surveySheetsInfo->thisSurveyName,"[No survey Selected]")==0){
        ?>
          <textarea id="currSurveyName" name="currSurveyName" style="color:#069;" rows="1"
          onchange="checkName()" placeholder="<?=$surveySheetsInfo->thisSurveyName?>"></textarea>
        
        <?php 
        } else { ?>
          <textarea id="currSurveyName" name="currSurveyName" style="color:#069;" rows="1" 
          onchange="checkName()"><?=$surveySheetsInfo->thisSurveyName?></textarea>        
        <?php } ?>  
  </h1>
 
  
  <span style="padding:30px">
    <button name="newSurvey" value="stim" class="collectorButton" id="newSurveyButton"> new survey </button>

    <select name="surveyPostName" title="[filename]">

    <?php    

      
      foreach($surveySheetsInfo->surveySheets as $survey){
        echo "<option name='surveyPostName' value='$survey'>$survey</option>";
      }
        
    ?>    
    </select>
    <button type='submit' name="openButton" class='collectorButton' value='Select'>Open</button>
    <input id="saveButton" type="button" class="collectorButton" value="Save">  
    <button id="submitButton" type="submit" name="Save" class="collectorButton" style="display:none"></button> 
    <input type="button" id="deleteSheetButton" name="DeleteSheetQuestion" class="collectorButton" value="Delete?">  
    <button id="deleteActivate" type="submit" name="DeleteSheet" class="collectorButton" value="Delete" style="display:none">No text needed</button>
    <input id="helperButton" type="button" class="collectorButton" value="Hide Helper"> 
  
    
  </span>
  
  <script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
  <link rel="stylesheet" href="../handsontables/handsontables.full.css">
  <script src="../handsontables/handsontables.full.js"></script>

     

  <div>
    
  <?php
    // doing this in PHP to prevent whitespace
    echo '<div id="stimArea" class="tableArea">'
       .         '<div id="stimTable" class="expTable"></div>'
       .     '</div>'
       . '</div>';
  ?>
  <br>

  <input type="hidden" name="stimTableInput">
  
</form>

<!-- the helper bar !-->
  
<div id="helperBar">
  <h1> Helper </h1>
  <h2 id="helpType">Select Cell</h2>
  
  <!-- Help Types -->
  <div class="helpType_Col" id="helpType_Answers">
    Answers can take two forms. If you want a range of numbers, you can type:<br><br>
    <em>FirstNumber::LastNumber</em>
    <br><br><br>
    If you have a list of values you want the participant to choose from, you can list them with a pipe (|) in the middle. E.g.: <br><br>
    <em>Cat|Dog|Bird</em>
    
    <br><br><br>
    Alternatively, you can just leave this value empty if the participant isn't making a choice. For example, for <b>Text</b> or <b> Date</b> questions there are answers to choose from.
  </div>
  
  <div class="helpType_Col" id="helpType_Values">
    <b>Values</b> are stored for each answer given. This is useful if you want to have a score associated with each answer. You need the same number of <b>Values</b> as you have of <b>Answers</b>. This is because <b>Answers need to map onto Values</b>.
    
    <br><br><br>    
    As a result, like <b>Answers</B>, values can take two forms.
     
    If you want a range of numbers, you can type:<br><br>
    <em>FirstNumber::LastNumber</em>
    <br><br><br>
    If you have a list of values you want the participant to choose from, you can list them with a pipe (|) in the middle. E.g.: <br><br>
    <em>Cat|Dog|Bird</em>
    
    <br><br><br>
    Alternatively, you can just leave this value empty if the participant isn't making a choice. For example, for <b>Text</b> or <b> Date</b> questions there are answers to choose from.
  </div>
  
  <div class="helpType_Col" id="helpType_Question">
    Just type in the question in this column.
  </div>

  <div class="helpType_Col" id="helpType_QuestionName">
    Question names are used for storing the data, so each question name has to be unique. Question names are not required for page Breaks.    
  </div>
  
  <div class="helpType_Col" id="helpType_Required">
    Identify in this column whether or not the participant needs to complete this question to proceed. The default assumption is that they do not, but if you want to force a participant to respond to a question, type in "Yes".
  </div>

  <div class="helpType_Col" id="helpType_Shuffle">
    <b>Shuffling</b> is used to randomise the order of stimuli. The default assumption is that there is no shuffling. However, if you want to randomise the order of certain rows, then type in the same value for each of the rows you want shuffled. For example, if I want rows 1,3 and 5 shuffled, but not rows 2 and 4, I could put in these values in this column:
    <br><br>
    shuffle135<br>
    No<br>
    shuffle135<br>
    No<br>
    shuffle135
  </div>
  

  <div class="helpType_Col" id="helpType_Type">
    <?php
      $surveyTypes=getFromFile("surveyTypes.csv",",");
      $surveyTypes=array_slice($surveyTypes,2);
      
      $surveyTypeVector=[];
      foreach($surveyTypes as $surveyType){
        array_push($surveyTypeVector,$surveyType["surveyType"]);
        ?>
        <h3 class="typeHeader" id='header<?=$surveyType["surveyType"]?>' onclick='hideShow("detail<?=$surveyType["surveyType"]?>")'><?=$surveyType["surveyType"]?></h3>
        <div id='detail<?=$surveyType["surveyType"]?>' style='display:none'><?=$surveyType["surveyDetail"]?></div>
        <?php
      }
        $jsonSurveyVector=json_encode($surveyTypeVector);
    ?>
  </div>

  
  <div class="helpType_Col" id="helpTypeDefault">
    Select a cell to see more information about that column.
  </div>
  
</div>


<script type="text/javascript">

$("#helperButton").click(function(){
  if(helperButton.value=="Hide Helper"){
    helperButton.value="Show Helper";
    $("#helperBar").hide();
    TableForm.style.width="100%";//expand width of table
    updateDimensions(stimTable);
  } else {
    helperButton.value="Hide Helper";
    TableForm.style.width="75%";//expand width of table
    updateDimensions(stimTable);
    $("#helperBar").show();
  }
});

function hideShow(x){
  if($('#'+x).is(':visible')) {
    $('#'+x).hide();
  } else {
    $('#'+x).show();
  }
}

//importing json encoded lists from php
listSheetsNames=<?=$jsonSheets?>;
var surveyVector=<?=$jsonSurveyVector?>;

//removing the current study's name from the list (because this list is to prevent duplication)
// studyIndex=listStudyNames.indexOf(currSurveyName.value);
// listStudyNames.splice(studyIndex,1);

// Checks for preventing repeating study names
var revertStudyName=currSurveyName.value;
function checkName(){
  // check if member of array
  if($.inArray(currSurveyName.value,listStudyNames)!=-1){
    alert("This is the same name of another study, reverting to unique name");
    currSurveyName.value=revertStudyName;
  } else{
    revertStudyName=currSurveyName.value;
  }
}


//checks for preventing repeating sheet names

//removing the current study's name from the list (because this list is to prevent duplication)
if (typeof sheetName !== 'undefined'){
  sheetIndex=listSheetsNames.indexOf(sheetName.value+'.csv');
  listSheetsNames.splice(sheetIndex,1);
  var revertSheetName=sheetName.value;
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

// var perVar = {}; - probably can delete

function helperActivate(columnName, cellValue){
  $("#helpType").html(columnName);
  
  var columnCodeName = columnName.replace(/ /g, '');
  
  $("#helperBar").find(".helpType_Col").hide();

  if ($("#helperBar").find("#helpType_" + columnCodeName).length > 0) {
    $("#helperBar").find("#helpType_" + columnCodeName).show();
  } else {
    $("#helperBar").find("#helpTypeDefault").show();
  }
  
  // code for specific helper bars
  if(columnCodeName=="Type"){
    //compare if string is within string
    for(i=0;i<surveyVector.length;i++){
      //remove cases for comparisons
      var surveyValue=surveyVector[i].toLowerCase();
      if(surveyValue.indexOf(cellValue.toLowerCase())==-1){
        $("#header"+surveyVector[i]).hide();
      } else {
        $("#header"+surveyVector[i]).show(); // show header
      }
      
      // show details if only one item fits criterion
      if(surveyValue.localeCompare(cellValue.toLowerCase())==0){ 
        $("#detail"+surveyVector[i]).show();
      } else {
        $("#detail"+surveyVector[i]).hide();
      }
    }    
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
      
      afterSelectionEnd: function(){
        var coords        = this.getSelected();
        var column        = this.getDataAtCell(0,coords[1]);//stimTable.getDataAtCell(0,1); 
        var thisCellValue = this.getDataAtCell(coords[0],coords[1]);
        window['Current HoT Coordinates'] = coords;
        
        helperActivate(column, thisCellValue);
      //         alert(stimTable.getDataAtCell(0,1));
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


$("#newSurveyButton").on("click", function() {
  alert("Creating new Survey");
});


$("#deleteSheetButton").on("click", function() {
  delConf=confirm("Are you SURE you want to delete this file?");
  if (delConf== true){
    document.getElementById('deleteActivate').click();
  }  
});

$("#saveButton").on("click", function() { //final checks before saving
  //are there too many empty column headers?
  emptyHeadCols=0;
  for(i=0; i<stimTable.countCols();i++){
    if(stimTable.getDataAtCell(0,i)==''){
      emptyHeadCols++;
    }
  }
  if(emptyHeadCols>1){
    alert("You have an empty header - will not save. Fix before trying to save again.");
  } else {
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

// during typing into HandsOnTable, update the helper bar
$(document).on("input", ".handsontableInput", function() {
    var coord  = window['Current HoT Coordinates'];
    var y      = coord[1];
    var column = stimTable.getDataAtCell(0, y);
    helperActivate(column, this.value);
});


</script>


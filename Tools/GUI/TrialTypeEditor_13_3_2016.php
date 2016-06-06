<?php

/*
  	GUI - Trial type editor by Anthony Haffey

	Collector
    A program for running experiments on the web
    Copyright 2012-2015 Mikey Garcia & Nate Kornell

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License version 3 as published by
    the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>

    */
    
  //aquiring the structure of the typeInfo
    
  
    if(isset($_POST['elementArray'])){
      $trialTypeElementsPhp=json_decode($_POST['elementArray']);  
    } else {
      require ("guiClasses.php");
      $trialTypeElementsPhp = new trialTypeElements();
    }
    
    $jsontrialTypeElements = json_encode($trialTypeElementsPhp); //to use for javascript
  
  //loading saved info (if present)
    if(isset($_POST['saveButton'])){
        //using info from last saved element array          
      if(isset($_DATA['trialTypeEditor']['currentTrialTypeName'])){
        if(strcmp($_DATA['trialTypeEditor']['currentTrialTypeName'],$trialTypeElementsPhp->trialTypeName)!=0){ //i.e. a new trialType name
          if(file_exists("GUI/newTrialTypes/".$_DATA['trialTypeEditor']['currentTrialTypeName'].".txt")){
            unlink("GUI/newTrialTypes/".$_DATA['trialTypeEditor']['currentTrialTypeName'].".txt"); //Delete original file here            
          }
          $_DATA['trialTypeEditor']['currentTrialTypeName']=$trialTypeElementsPhp->trialTypeName; //identify correct name here
        }
        else {
          //names match - nothing to do there
        }
      } 
      else {
        $_DATA['trialTypeEditor']['currentTrialTypeName']="insertNameHere";
      }
        
    } else {
      if(isset($_POST['loadButton'])){
      $_DATA['trialTypeEditor']['currentTrialTypeName']=$_POST['trialTypeLoaded'];#
      // more implications of it being a load;
      }
      else { // starting from scratch if nothing is being saved or loaded
        $_DATA['trialTypeEditor']['currentTrialTypeName']='';
      }
    }

// resume here!!!

    
    
    $trialTypesList = scandir("GUI/newTrialTypes");
    $trialTypesList = array_slice($trialTypesList,2);
    print_r($trialTypesList);


//      $currentTrialTypeName=$_DATA['trialTypeEditor']['currentTrialTypeName'];  
      //identify whether file exists or not
      $_DATA['trialTypeEditor']['currentTrialTypeFilename']=$_DATA['trialTypeEditor']['currentTrialTypeName'].".txt";
      if(in_array($_DATA['trialTypeEditor']['currentTrialTypeFilename'],$trialTypesList)){
        //whatt???
      } else { //renaming file
        
        //renaming not done
        //file_put_contents("GUI/newTrialTypes/$currentTrialTypeFilename",$_POST['elementArray']);        
      
      }

      if(isset($_POST['elementArray'])){
        file_put_contents("GUI/newTrialTypes/".$_DATA['trialTypeEditor']['currentTrialTypeFilename'],$_POST['elementArray']);        
      }
      
      
      //should have saved the info to a blank file by now!!!
      
      
      //save elementArray into the file
      
      
    

     

    
    
    // saving file here
    
    if (isset($_POST['saveButton'])){ //Saving whichever csv you are currently working on
    // renaming file if the user renamed it

    
      file_put_contents('GUI/newTrialTypes/pilot.txt',$_POST['elementArray']);
    
  
/*
    if (strcmp($studySheetsInfo->thisSheetName,'Conditions')==0){
      // skip this renaming process
    } else {
      if (strcmp($_POST['eventName'],$studySheetsInfo->thisSheetName)!=0){
        $illegalChars=array('  ',' ','.');
        foreach ($illegalChars as $illegalChar){
          $_POST['eventName']=str_ireplace($illegalChar,'',$_POST['eventName']);
        }
        $newFile=$thisDirInfo->studyDir.'/'.$studySheetsInfo->thisSheetFolder.'/'.$_POST['eventName'].'.csv';
        $originalFile=$thisDirInfo->studyDir.'/'.$studySheetsInfo->thisSheetFilename;
        copy($originalFile,$newFile);
        unlink($originalFile);
        $studySheetsInfo->thisSheetName=$_POST['eventName'];
        // do not change $studySheetsInfo->thisSheetFolder it's the same folder
        $studySheetsInfo->thisSheetFilename="$studySheetsInfo->thisSheetFolder/$studySheetsInfo->thisSheetName.csv";        
      }
    }
    // converting raw table data into usable array
    //removing symbols
		$stimTableArray=json_decode($_POST['stimTableInput'], true);
		writeHoT("$thisDirInfo->studyDir/$studySheetsInfo->thisSheetFilename",$stimTableArray);
  */
  }
  
   
    
?>

<style>
  
  #configurationEditor{
    width:400px;
    height:500px;
    position:absolute;
    left:620px;
    top:300px;
    border:2px solid black;
    border-radius: 25px;
    padding:50px;
  }
  #configurationEditor:hover{
    border: 2px solid blue;
  }
  #elementArray{
    width:400px;
    height:500px;
    position:absolute;
    left:1040px;
    top:300px;
    border:2px solid black;
    border-radius: 25px;
    padding:50px;  
  }
  #elementList{
    position:absolute;
    left:100px;
    top:250px;
  }
  #keyboardResponses{
    position:relative;
    left:100px;
    top:800px;
    width:920px;
    height:50px;
    border: 2px solid black;
    border-radius: 25px;
  }
  #keyboardResponses:hover{
    border: 2px solid blue;
  }

  #loadDiv{
    position:absolute;
    top:275px;
    left:1200px;
  }
  
  #trialEditor{
    width:500px;
    height:500px;
    position:absolute;
    left:100px;
    top:300px;
    border:2px solid black;
    border-radius: 25px;
    padding:50px;
  }
  
  #trialTypeName{
    
    position:absolute;
    left: 100px;
    top: 200px;
    width: 600px;
    line-height: 1;
    font-size:30px;
    padding: 5px;
  }
  
  .elementButton{
    background-color:blue;
    color:white;
  }
  .elementButton:hover{
    background-color:white;
    color:black;
  }
  .elementButtonSelected{
    
    background-color:green;
    color:white;
  }
  .elementButtonSelected:hover{
    background-color:white;
    color:black;
  }
  
  .inputElement{
  }
  .inputElement:hover{
    background-color:#000099;
  }
  .inputElementSelected{
    background-color:green;
  }
  
  .mediaElement{
    color:blue;
    width:100px;
    height: 100px;
    line-height:70px;
    border: 2px solid blue;
    border-radius: 10px;
    padding:10px;    
  }

  .mediaElementSelected{
    color:green;
    width:100px;
    height: 100px;
    line-height:70px;
    border: 4px solid green;    
    border-radius: 10px;
    padding:10px;    

  }
  
  .textElement{
    font-size:30px;
    color:blue;
  }
  .textElement:hover{
    color:#003300;
    outline-style: dotted;
    outline-color: #00ff00;
  }
  .textElementSelected {
    color:green;
    font-size:30px;
    font-weight:bold;
    
  }
  <!--  	pointer-events: none; !-->

  
  
</style>
<form method="post">

  <textarea id="currentGuiSheetPage" name="currentGuiSheetPage" style="display:none">TrialTypeEditor</textarea>
  
  <textarea id="trialTypeName" placeholder="[insert name of trial type here]" onchange="updateTrialTypeElements()"><?php 
    
    echo $_DATA['trialTypeEditor']['currentTrialTypeName'];
    ?></textarea>
  
  <?php 
    if(count($trialTypesList)>0){
    ?>
      <div id="loadDiv">  
        <select id="trialTypeLoading" name="trialTypeLoaded">
          <option>-select a trial type-</option>
          <?php foreach($trialTypesList as $trialType){
            echo "<option>$trialType</option>";
          }
          ?>
        </select> 
        <input type="button" id="loadButton" class="collectorButton" value="Load">
        <input type="submit" id="loadButtonAction" name="loadButton" class="collectorButton" value="Load" style="display:none">
      </div>
    <?php
    }
  ?>
  
<div id="elementList">
  <br>
  <span>
    <input id="mediaButton" type="button" class="elementButton" value="Media" onclick="elementType('media')">
    <input id="textButton" type="button" class="elementButton" value="Text" onclick="elementType('text')">
    <input id="inputButton" type="button" class="elementButton" value="Input" onclick="elementType('input')">
    <input id="selectButton" type="button" class="elementButton" value="Select" onclick="elementType('select')">
  </span>
  <span style="position:relative; left:500px">
    <input type="button" class="collectorButton" value="Keyboard Responses" onclick="editKeyboardResponses()">
    <input type="submit" class="collectorButton" id="saveButton" name="saveButton" value="Save">
  </span>
</div>
<div id="trialEditor" onMouseMove="mouseMovingFunctions()" onclick="getPositions(); alertMouse()">

<?php
  //insert elements on the page
//  print_r ($trialTypeElementsPhp->elements);
  foreach($trialTypeElementsPhp->elements as $elementKey=>$element){
   // echo $elementKey."<br>";
   // print_r($element);
   // echo "<br>";
    
    echo "<div id='element$elementKey' class='".$element->inputType."Element' 
                 style='position:absolute;
                        width:".(5*$element->width)."px;
                        height:".(5*$element->height)."px;
                        left:".(5*$element->xPos)."px;
                        top:".(5*$element->yPosition)."px;
                 ' onclick='clickElement($elementKey)'
                 >".$element->stimulus."</div>";  
    
  }
  
  /*
  
        document.getElementById("trialEditor").innerHTML=document.getElementById("trialEditor").innerHTML+"<input class='inputElement' type='text' id='element"+elementNo+"' style='position: absolute; left:"+_mouseX+"px;top:"+_mouseY+"px' onclick='clickElement("+elementNo+")' name='"+inputElementType+"' readonly>";  
      
    } else {
      document.getElementById("trialEditor").innerHTML=document.getElementById("trialEditor").innerHTML+"<span class='"+inputElementType+"Element' id='element"+elementNo+"' style='position: absolute; left:"+_mouseX+"px;top:"+_mouseY+"px' onclick='clickElement("+elementNo+")' name='"+inputElementType+"'>"+inputElementType+"</span>";    

      
      */
  
  
  //count elements within object
  
  
  
  /*
  foreach($trialTypeElementsPhp->elements as $elementKey => $elementItem){
    //print_r($elementKey);
    if (strpos($elementKey,'rialTypeName') == false) { //probably a tidier way to avoid running through   
    
    
    echo "<div id='$elementKey' class='".$trialTypeElementsPhp->$elementKey->type."Element' 
                 style='position:absolute;
                        width:".(5*$trialTypeElementsPhp->$elementKey->width)."px;
                        height:".(5*$trialTypeElementsPhp->$elementKey->height)."px;
                        left:".(5*$trialTypeElementsPhp->$elementKey->xPos)."px;
                        top:".(5*$trialTypeElementsPhp->$elementKey->yPosition)."px;
                 '
                 >".$trialTypeElementsPhp->$elementKey->stimulus."</div>";  
    
    }
    
  }
  */
  
  
  ?>


</div> <!-- onMouseMove="getPositions();" !-->
<div id="keyboardResponses" style="display:none">
  keyboard responses? <input type="checkbox" onclick="displayHideKeyboard()">
  <span id="keyboardOptions" style="display:none">
  accepted keyboard response(s) <input name="acceptedKeyboardResponses">
  correct keyboard response(s) <input name="">
  </span>
</div>

<div id="configurationEditor">

  


  <h1 id="currentStimType">No Element Selected</h1>
  <table id="configurationSettings" style="display:none">
    <tr><td>Element Name</td><td><input type="text" id="elementNameValue" type="text" onkeyup="adjustElementName()"></td><tr>
    <div id="userInputSettings">
      <tr title="If you want to refer to a stimulus list then write '[stimulus1]' or '[stimulus2]' etc."><td>Input Type:</td>
        <td><select id="userInputTypeValue" onchange="changeUserInputType()">
          <option>Text</option>
          <!-- <option>Drop-Down List</option> !--> 
          <option>Button</option> <!-- we could with time offer different styles of these buttons !-->
          <option>Checkbox</option>
          <option>Radio</option> <!-- we could with time offer different styles of these buttons !-->
          </select>
        </td>
      </tr>
    </div>
    
    <tr title="If you want to refer to a stimulus list then write '[stimulus1]' or '[stimulus2]' etc."><td>Stimulus:</td><td><input id="stimInputValue" type="text" onkeyup="adjustStimulus()"></td></tr>
    <tr><td>Width:</td><td><input id="elementWidth" type="number" value="20" min="1" max="100" onchange="adjustWidth()">%</td><td></td></tr>
    <tr><td>Height:</td><td><input id="elementHeight" type="number" value="20" min="1" max="100" onchange="adjustHeight()">%</td><td></td></tr>
    <tr title="this position is based on the left of the element"><td>X-Position:</td><td><input id="xPosId" type="number" min="1" max="100" onchange="adjustXPos()">%</td><td></td></tr>
    <tr title="this position is based on the top of the element"><td>Y-Position:</td><td><input id="yPosId" type="number" min="1" max="100" onchange="adjustYPos()">%</td><td></td></tr>
    <tr title="change this value to bring the element to forward or backward (to allow it to be on top of or behind other elements"><td>Z-Position:</td><td><input id="zPosId" type="number" ></td></tr>
    <tr title="how long do you want until the element appears on screen?"><td>onset time:</td><td><input id="onsetId" type="time" value="00:00:00" step=".001" onchange="adjustOnsetTime()"></td></tr>
    <tr title="if you want the element to disappear after a certain amount of time, change from 00:00"><td>offset time:</td><td><input id="offsetId" type="time" value="00:00:00" step=".001" onchange="adjustOffsetTime()"></td></tr>
    <tr title="What actions to other elements do you want when clicking on this element? E.G. hide'(element1)'"><td>Click outcomes:</td><td><input id="clickOutcomesId" type="text" onkeyup="supportClickOutcomes()" onchange="adjustClickOutcomes()"></td></tr>
    
    <tr title="check this box if you want the participant to be able to use this element to respond"><td>Response click?</td><td><input id="responseId" type="checkBox"></td></tr>
    
    
    <tr><td><input id="deleteButton" type="button" value="delete" class="collectorButton"></td></tr>
    
  </table>
  
</div>

<?php

  if(isset($_DATA['trialTypeEditor']['currentTrialTypeName'])){
    if(file_exists("GUI/newTrialTypes/".$_DATA['trialTypeEditor']['currentTrialTypeName'].".txt")){
      $loadedContents=file_get_contents("GUI/newTrialTypes/".$_DATA['trialTypeEditor']['currentTrialTypeName'].".txt");
    } else {
      $loadedContents='';
    }
  }
  
?>

<textarea id="elementArray" name="elementArray"><?=$loadedContents?></textarea>

</form>
<br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>
<?php

  print_r($_POST);

?>
<script>

function supportClickOutcomes(){
  // this may be developed in version 2 to help users use this functionality; Need a list somewhere of all functions.   
}; 

var trialTypeElements = <?= $jsontrialTypeElements ?>;


var inputElementType;

// var spanArray = []; no longer needed

var elementNo = Object.size(trialTypeElements['elements'])-1;
alert (elementNo);


$("#deleteButton").on("click", function() {
  alert ("deleting!!!");
  var element = document.getElementById(currentElement);
  $("#configurationSettings").hide();
  element.parentNode.removeChild(element);
  currentStimType.innerHTML="No Element Selected";
  alert(currentElement);
});

$(window).bind('keydown', function(event) {
    if (event.ctrlKey || event.metaKey) {
        switch (String.fromCharCode(event.which).toLowerCase()) {
        case 's':
            event.preventDefault();
            alert('Saving');
            $("#saveButton").click();
            break;
        }
    }
});
/*
function arrowMove(){
  alert("hello");
}
*/

$("#loadButton").on("click",function(){
  //alert(trialTypeLoading.value);
  if(trialTypeLoading.value=="-select a trial type-"){
    alert ("You must select a trial type to proceed!!");
  } else {
    $("#loadButtonAction").click();
  }
});

function adjustStimulus(){
  var stimText=stimInputValue.value;
  document.getElementById("element"+currentElement).innerHTML=stimText;
  
//  if(stimText.indexOf('[') != -1 & stimText.indexOf(']') != -1 & stimText.indexOf('[') < stimText.indexOf(']')){ // may need to put checks on this to prevent e.g.s like "[stim]asdfadsf"
  if(stimText.indexOf('$') != -1 ){  // may need to put checks on this to prevent e.g.s like "[stim]asdfadsf"
    stimInputValue.style.color="blue";
  } else {
    stimInputValue.style.color="black";
  }
  
  //stimText=stimInputValue.value;

  trialTypeElements['elements'][currentElement]['stimulus']=stimText;//update trialTypeElements
  updateTrialTypeElements();

}

function adjustElementName(){
  var elementNameText=elementNameValue.value;
  document.getElementById("element"+currentElement).innerHTML=elementNameText;
  
  if(elementNameText.indexOf('$') != -1 ){  // may need to put checks on this to prevent e.g.s like "[stim]asdfadsf"
    elementNameValue.style.color="blue";
  } else {
    elementNameValue.style.color="black";
  }
//  elementNameText=stimInputValue.value;
  trialTypeElements['elements'][currentElement]['elementName']=elementNameText;//update trialTypeElements
  updateTrialTypeElements();
}

function adjustHeight(){
  //alert (currentElement);
  if(Number(yPosId.value) + Number(elementHeight.value) > 100){
    elementHeight.value = 100-yPosId.value; // temporary correction will still allow user to create something bigger than the screen
  }
  newHeight = 5*elementHeight.value;
  newHeight = newHeight +"px";
  document.getElementById("element"+currentElement).style.height = newHeight;
  trialTypeElements['elements'][currentElement]['height']=elementHeight.value;//update trialTypeElements
  updateTrialTypeElements();
}

function adjustWidth(){
  if( Number(xPosId.value) + Number(elementWidth.value) > 100){
    elementWidth.value = 100-xPosId.value; // temporary correction will still allow user to create something bigger than the screen
  }
  newWidth = 5*elementWidth.value;
  newWidth = newWidth +"px";
  document.getElementById("element"+currentElement).style.width = newWidth;
  trialTypeElements['elements'][currentElement]['width']=elementWidth.value;//update trialTypeElements
  updateTrialTypeElements();
}

function adjustXPos(){
  if( Number(xPosId.value) + Number(elementWidth.value) > 100){
    xPosId.value= 100- elementWidth.value; // temporary correction will still allow user to create something bigger than the screen
  }  
  newXPos=(Number(xPosId.value)*5) +"px";
  document.getElementById("element"+currentElement).style.left = newXPos; //-xPosId.value; // temporary correction will still allow user to create something bigger than the screen
  trialTypeElements['elements'][currentElement]['xPos']=xPosId.value;//update trialTypeElements
  updateTrialTypeElements();
}

function adjustYPos(){
  if( Number(yPosId.value) + Number(elementHeight.value) > 100){
    yPosId.value= 100- elementHeight.value; // temporary correction will still allow user to create something bigger than the screen
  }  
  newYPos=(Number(yPosId.value)*5) +"px";
  document.getElementById("element"+currentElement).style.top = newYPos; //-xPosId.value; // temporary correction will still allow user to create something bigger than the screen
  trialTypeElements['elements'][currentElement]['yPosition']=yPosId.value;//update trialTypeElements
  updateTrialTypeElements();
}

// Could probably consolidate this into a single function
function adjustOnsetTime(){
  trialTypeElements['elements'][currentElement]['onsetTime']=onsetId.value;//update trialTypeElements
  updateTrialTypeElements();
}
function adjustOffsetTime(){
  trialTypeElements['elements'][currentElement]['offsetTime']=offsetId.value;//update trialTypeElements
  updateTrialTypeElements();
}
function adjustClickOutcomes(){
  trialTypeElements['elements'][currentElement]['clickOutcomes']=clickOutcomesId.value;//update trialTypeElements
  updateTrialTypeElements();
}

/*
function adjustElementArray(x){
  alert(x);
  
}
*/

function alertMouse(){
  if(inputElementType!="select"){
    elementNo++;
    _mouseX=_mouseX-100; // this may need to be adjusted depending on the size of the element. Current size is 100px
    _mouseY=_mouseY-300; // same as above
    xPos=(_mouseX)/5;
    yPos=(_mouseY)/5;
//    spanArray[elementNo]={type:inputElementType};
                                       
      
      
      
    if(inputElementType=="input"){
      
      document.getElementById("trialEditor").innerHTML=document.getElementById("trialEditor").innerHTML+"<input class='inputElement' type='text' id='element"+elementNo+"' style='position: absolute; left:"+_mouseX+"px;top:"+_mouseY+"px' onclick='clickElement("+elementNo+")' name='"+inputElementType+"' readonly>";  
      
    } else {
      document.getElementById("trialEditor").innerHTML=document.getElementById("trialEditor").innerHTML+"<span class='"+inputElementType+"Element' id='element"+elementNo+"' style='position: absolute; left:"+_mouseX+"px;top:"+_mouseY+"px' onclick='clickElement("+elementNo+")' name='"+inputElementType+"'>"+inputElementType+"</span>";    
      
    }
  
  
  //alert(elementNo);
  
  
    trialTypeElements['elements'][elementNo] = {
     // type:inputElementType, this is repeating "inputType"
      width:20, 
      height:20,
      xPos: xPos,
      yPosition: yPos,
      elementName: 'element'+elementNo,
      stimulus: 'not yet added',
      response: false,
      inputType: inputElementType, // repetition here
      };
    updateTrialTypeElements();
  }
}

backupTrialTypeName=trialTypeName.value;

function changeUserInputType(){
//  alert ("changing input type");
//  alert (userInputTypeValue.value);
//  alert(currentElement);
  // delete and then recreate it... correctly
  //delete
  var element = document.getElementById("element"+currentElement);
  element.parentNode.removeChild(element);
  
  
// insert into style: left:"+trialTypeElements[currentElement]['xPos']+"px;top:"trialTypeElements[currentElement]['yPos']+"px
  
//  alert(trialTypeElements[currentElement]['xPos']);
  currentXPos=xPosId.value*5;
  currentYPos=yPosId.value*5;
  // insert into style: left:"+currentXPos+"px;top:"currentYPos+"px

  
  document.getElementById("trialEditor").innerHTML=document.getElementById("trialEditor").innerHTML+"<input class='inputElement' type='"+userInputTypeValue.value+"' id='"+currentElement+"' style='position: absolute;left:"+currentXPos+"px;top:"+currentYPos+"px' onclick='clickElement("+elementNo+")' name='"+currentElement+"' readonly>";  

}

var illegalChars=['.',' '];



function updateTrialTypeElements(){
  //check if trialTypeName is legit
  var illegalCharPresent = false;
  for (i=0; i<illegalChars.length; i++){

    if(trialTypeName.value.indexOf(illegalChars[i])!=-1){
      alert("Illegal character in name, reverting to acceptable version");
      illegalCharPresent = true;
      //revert to working title
      trialTypeName.value=backupTrialTypeName;
    }   
  }
  if(illegalCharPresent==false){
    backupTrialTypeName=trialTypeName.value;    
  }
  
  trialTypeElements['trialTypeName']=trialTypeName.value;
  document.getElementById("elementArray").value = JSON.stringify(trialTypeElements,  null, 2);
  inputElementType="select";
  elementType("select");
  
}


var currentElement = 0;
function clickElement(x){
  if(inputElementType=="select"){
    currentElement = x;
    thisElement="element"+x;
    for(i=0;i<=elementNo;i++){
      if(i==x){
        //alert (trialTypeElements[i]['inputType']+"ElementSelected");
        document.getElementById("element"+i).className=trialTypeElements['elements'][i]['inputType']+"ElementSelected";
      } else {
        document.getElementById("element"+i).className=trialTypeElements['elements'][i]['inputType']+"Element";
      }
    }
    

    $("#configurationSettings").hide();
    $("#userInputSettings").hide();

    // here's where you distinguish between different element types
    switch (trialTypeElements['elements'][x]['inputType']){
      case "media":
        $("#configurationSettings").show();
//        $("#userInputSettings").style.visibility="hidden";
//        alert(document.getElementById('userInputTypeValue').value);
        document.getElementById('userInputTypeValue').style.visibility="hidden";
        currentStimType.innerHTML="Media";
      break
      case "text":
        $("#configurationSettings").show();
        document.getElementById('userInputTypeValue').style.visibility="hidden";
        currentStimType.innerHTML="Text";

        break      
      case "input":      
        $("#configurationSettings").show();
        document.getElementById('userInputTypeValue').style.visibility="visible";
        currentStimType.innerHTML="Input";

      break      
    break
    }
  }
  loadConfigs();
}

function loadConfigs(){
    
  elementNameValue.value=trialTypeElements['elements'][currentElement].elementName;
  stimInputValue.value=trialTypeElements['elements'][currentElement].stimulus;
  elementWidth.value=trialTypeElements['elements'][currentElement].width;
  elementHeight.value=trialTypeElements['elements'][currentElement].height;
  xPosId.value=trialTypeElements['elements'][currentElement].xPos;
  yPosId.value=trialTypeElements['elements'][currentElement].yPosition;
  responseId.checked= trialTypeElements['elements'][currentElement].stimulus;
    
}



var keyboardShow = false;
var keyboardOptionsShow = false;

function displayHideKeyboard(){
  if(keyboardOptionsShow == false){
    $("#keyboardOptions").show();
    keyboardOptionsShow = true;
  } else {  
    $("#keyboardOptions").hide();
    keyboardOptionsShow = false;
  }
}

function editKeyboardResponses(){
  if(keyboardShow == false){
    $("#keyboardResponses").show();
    keyboardShow = true; 
  } else {
    $("#keyboardResponses").hide();
    keyboardShow = false;
  }
}



var inputButtonArray=["media","text","input","select"];

elementType('select');

function elementType(x){
  //alert (x);
  //alert(inputButtonArray.length);  
  inputElementType=x;
  for(i=0;i<inputButtonArray.length;i++){
    //alert (x);
    //alert (inputButtonArray);
    if(inputButtonArray[i]==x){
      document.getElementById(inputButtonArray[i]+"Button").className="elementButtonSelected";
    } else {
      document.getElementById(inputButtonArray[i]+"Button").className="elementButton";
    }
  }
  if(x!="select"){
    $("#configurationSettings").hide();
    $("#userInputTypeValue").value="n/a";

    currentStimType.innerHTML="No Element Selected ";
    // for all elements revert formatting to element
    for(i=0;i<=elementNo;i++){
      alert("element"+i);
      document.getElementById("element"+i).className=trialTypeElements['elements'][i]['inputType']+"Element";    
    }
  }  
}

function getPositions(ev) {
if (ev == null) { ev = window.event }
   _mouseX = ev.clientX;
   _mouseY = ev.clientY;
}





function mouseMovingFunctions(){
  if(inputElementType=="select"){
    var css = '.mediaElement:hover{ border-color: white; background-color:green; color:white }';
    style = document.createElement('style');

    if (style.styleSheet) {
        style.styleSheet.cssText = css;
    } else {
        style.appendChild(document.createTextNode(css));
    }
    document.getElementsByTagName('head')[0].appendChild(style);
  }
  

  if(inputElementType!="select"){
    //unset hover style
  }
  
  
}



// create hover class depending on whether select is on or not
/*
  .element:hover{
    border: 2px solid red;
  }
*/

  
  
</script>
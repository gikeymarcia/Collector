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
    

?>

<style>
  input[type="time"]::-webkit-clear-button {
    display: none;
  }
  
  #controlPanel {
    position:       fixed;
    border:         2px solid black;
    left:           73%;  
    width:          25%;
    top:            40%;
    height:         500px;
    padding:        10px;
    opacity:        .9;
    border-radius:  10px;
    z-index:        5;
  }
    

  
  #controlPanelRibbon {
    padding: 5px;
    border-bottom: 1px solid gray;
  }

  #controlPanelItems > div {
    display: none;
    height: 400px;
    border: 1px solid green;
  }
  
  #displayEditor{
    width:400px;
    border:2px solid black;
    border-radius: 25px;
    padding:25px;
  }
  
  #displayEditor:hover{
    border: 2px solid blue;
  }
  
  #elementArray{
    width:400px;
    position:absolute;
    border:2px solid black;
    border-radius: 25px;
    padding:50px;  
  }
  
  #elementTypeList{
    position:absolute;
    left:100px;
    top:250px;
  }
  #keyboardResponses{
    position:absolute;
    left:300px;
    top:820px;
    width:920px;
    height:50px;
    padding:10px;
    border: 2px solid black;
    border-radius: 25px;
  }  
  #keyboardResponses:hover{
    border: 2px solid blue;
  }
  #interactionEditor{
    width:400px;
    height:500px;
    position:absolute;
    border:2px solid black;
    border-radius: 25px;
    padding:25px;
  }
  
  #interactionEditor:hover{
    border: 2px solid blue;
  }
  
  #loadDiv{
    position:absolute;
    top:265px;
    left:1140px;
  }
  
  #trialEditor{
    width:100%;
    height:80%;
    position:absolute;
    top:300px;
    left:0px;
    border:2px solid black;
    border-radius: 25px;
    padding:50px;
  }
  #trialEditor:hover{
    border: 2px solid blue;
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
    background-color:transparent;
    color:black;
  }
  
  .inputElement{
    border:1px solid #cccccc;
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
  .onsetOffset{
    color:grey;
  }
  
  .textElement{
    font-size:30px;
    color:black;
  }

  .textElementSelected {
    color:black;
    font-size:30px;
    font-weight:bold;
  }
  
  <!--  	pointer-events: none; !-->
  
</style>

<?php     

  //sorting out whether we are working from scratch, have just saved a file, or are loading a file
  if(isset($_POST['loadButton'])){   //load first
    $file_contents=file_get_contents("GUI/newTrialTypes/".$_POST['trialTypeLoaded']);
    $trialTypeElementsPhp=json_decode($file_contents);
    $_DATA['trialTypeEditor']['currentTrialTypeName']=str_replace('.txt','',$_POST['trialTypeLoaded']);#
    
  } else {
    if(!empty($_POST['elementArray'])){ // have just saved
      $trialTypeElementsPhp=json_decode($_POST['elementArray']);  
      if(isset($_DATA['trialTypeEditor']['currentTrialTypeName'])){ // Renaming files if task name has changed 
        if(strcmp($_DATA['trialTypeEditor']['currentTrialTypeName'],$trialTypeElementsPhp->trialTypeName)!=0){ //i.e. a new trialType name
          if(file_exists("GUI/newTrialTypes/".$_DATA['trialTypeEditor']['currentTrialTypeName'].".txt")){
            unlink("GUI/newTrialTypes/".$_DATA['trialTypeEditor']['currentTrialTypeName'].".txt"); //Delete original file here            
            unlink("../Experiments/_Common/TrialTypes/".$_DATA['trialTypeEditor']['currentTrialTypeName']."/display.php"); //deleting php file
            rmdir("../Experiments/_Common/TrialTypes/".$_DATA['trialTypeEditor']['currentTrialTypeName']); //deleting directory
          }
          $_DATA['trialTypeEditor']['currentTrialTypeName']=$trialTypeElementsPhp->trialTypeName; //identify correct name here
        }  
      }
      
      // saving backup of task (.txt), schematic of task (.txt) and task (.php)
      file_put_contents('GUI/newTrialTypes/backup.txt',$_POST['elementArray']);  //creating backup
      file_put_contents("GUI/newTrialTypes/".$_DATA['trialTypeEditor']['currentTrialTypeName'].'.txt',$_POST['elementArray']); //actual act of saving
      require('createTrialType.php');

    } else { // creating a file from scratch 
      require ("guiClasses.php"); //not sure we need this;  maybe the guiClassed can be tidied up  
      $trialTypeElementsPhp = new trialTypeElements();        
      $_DATA['trialTypeEditor']['currentTrialTypeName']='';
    }
  }    
  $jsontrialTypeElements = json_encode($trialTypeElementsPhp); //to use for javascript manipulations
  $_DATA['trialTypeEditor']['currentTrialTypeFilename']=$_DATA['trialTypeEditor']['currentTrialTypeName'].".txt";
     
  // list of trial types the user can edit
  if (!is_dir("GUI/newTrialTypes")) {
      $trialTypesList = array();
  } else {
      $trialTypesList = scandir("GUI/newTrialTypes");
      $trialTypesList = array_slice($trialTypesList,2);
  }
  ?>

<form method="post">

  <textarea id="currentGuiSheetPage" name="currentGuiSheetPage" style="display:none">TrialTypeEditor</textarea>  
  <textarea id="trialTypeName" placeholder="[insert name of trial type here]" onkeyup="updateTrialTypeElements()"><?php     
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
  
<div id="elementTypeList">
  <br>
  <span>
    <input id="mediaButton" type="button" class="elementButton" value="Media" onclick="elementType('media')">
    <input id="textButton" type="button" class="elementButton" value="Text" onclick="elementType('text')">
    <input id="inputButton" type="button" class="elementButton" value="Input" onclick="elementType('input')">
    <input id="complexButton" type="button" class="elementButton" value="Complex" onclick="alert('This will include more code heavy elements, e.g. progress bars, and will be in a later release')">
    <input id="selectButton" type="button" class="elementButton" value="Select" onclick="elementType('select')">
  </span>
  <span style="position:relative; left:500px">
    <input type="button" class="collectorButton" value="Keyboard Responses" onclick="editKeyboardResponses()">
    <input type="submit" class="collectorButton" id="saveButton" name="saveButton" value="Save">
  </span>
</div>

<div id="trialEditor" onMouseMove="mouseMovingFunctions()" onclick="getPositions(); alertMouse()">
<?php
  foreach($trialTypeElementsPhp->elements as $elementKey=>$element){
    echo "<div id='element$elementKey' class='".$element->trialElementType."Element' 
             style='position:absolute;
              width:".(5*$element->width)."px;
              height:".(5*$element->height)."px;
              left:".(5*$element->xPos)."px;
              top:".(5*$element->yPosition)."px;
              ";
    if (isset($element->textColor)){
      echo "color:$element->textColor;
            font-family:$element->textFont;
            background-color:$element->textBack;
            font-size:".(3*$element->textSize)."px;"; // look into this when I've finalised spacing for interfaces
    }
    echo "' onclick='clickElement($elementKey)'
             >".$element->stimulus."</div>";      
  } 
  if(isset($_DATA['trialTypeEditor']['currentTrialTypeName'])){
    if(file_exists("GUI/newTrialTypes/".$_DATA['trialTypeEditor']['currentTrialTypeName'].".txt")){
      $loadedContents=file_get_contents("GUI/newTrialTypes/".$_DATA['trialTypeEditor']['currentTrialTypeName'].".txt");
    } else {
      $loadedContents='';
    }
  }
  
?>
</div>

<div id="keyboardResponses" style="display:none">
 <!-- keyboard responses? <input type="checkbox" onclick="displayHideKeyboard()">
  <span id="keyboardOptions" style="display:none"> !-->
  accepted keyboard response(s) <input id="acceptedKeyboardResponses" name="acceptedKeyboardResponses" onkeyup="adjustKeyboard()">
  correct keyboard response(s) <input type="checkbox" id= "proceedKeyboardResponses" name="proceedKeyboardResponses" onchange="adjustKeyboard()">
 <!-- </span> !-->
</div>

<!-- Bootstrap alerts !-->
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">
<script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>

<!-- the helper bar !-->
<div id="controlPanel" class="alert-success">
  <div id="controlPanelRibbon">
    <button type="button" class="collectorButton" value="#displayEditor">Display Editor</button>
    <button type="button" class="collectorButton" value="#interactionEditor">Interaction Editor</button>
    <button type="button" class="collectorButton" value="#elementArray">Element Array</button>
    <button type="button" class="collectorButton" id="hideShowControl" onclick="hideShowControlPanel()">X</button>
  </div>  
  <div id="controlPanelItems">
    <div id="displayEditor">
      <h1>Display editor <br><span style="font-size:20px" id="currentStimType">No Element Selected</span></h1>
      <table id="configurationSettings" style="display:none">
        <tr title="we may allow editing of element names in a later release but without careful coding it can make code break easily"><td>Element Name</td><td><input type="text" id="elementNameValue" onkeyup="adjustElementName()" readonly style="background-color:#d3d3d3"></td><tr>
        <div id="userInputSettings">
          <tr title="If you want to refer to a stimulus list then write '[stimulus1]' or '[stimulus2]' etc."><td id="inputStimTypeCell">Input Type:</td>
            <td id="inputStimSelectCell">
              <select id="userInputTypeValue" onchange="adjustUserInputType()">
                <option>Text</option>
                <option>Button</option> 
              </select>
            </td>
          </tr>
        </div>
        
        <tr title="If you want to refer to a stimulus list then write '[stimulus1]' or '[stimulus2]' etc.">
          <td>Stimulus:</td>
          <td><input id="stimInputValue" type="text" onkeyup="adjustStimulus()"></td>
        </tr>
        <tr>
          <td>Width:</td><td><input id="elementWidth" type="number" value="20" min="1" max="100" onchange="adjustWidth()">%</td>
          <td></td>
        </tr>
        <tr>
          <td>Height:</td><td><input id="elementHeight" type="number" value="20" min="1" max="100" onchange="adjustHeight()">%</td>
          <td></td>
        </tr>
        <tr title="this position is based on the left of the element">
          <td>X-Position:</td>
          <td><input id="xPosId" type="number" min="1" max="100" step="1" onchange="adjustXPos()">%</td>
          <td></td>
        </tr>
        <tr title="this position is based on the top of the element">
          <td>Y-Position:</td>
          <td><input id="yPosId" type="number" min="1" max="100" step="1" onchange="adjustYPos()">%</td>
          <td></td>
        </tr>
        <tr title="change this value to bring the element to forward or backward (to allow it to be on top of or behind other elements">
          <td>Z-Position:</td>
          <td><input id="zPosId" type="number" step="1" min="0" onchange="adjustZPos()"></td>
        </tr>
        <tr title="how long do you want until the element appears on screen?">
          <td>onset time:</td>
          <td><input id="onsetId" class="onsetOffset" type="time" value="00:00:00" step=".001" onchange="adjustOnsetTime()"></td>
        </tr>
        <tr title="if you want the element to disappear after a certain amount of time, change from 00:00">
          <td>offset time:</td>
          <td><input id="offsetId" class="onsetOffset" type="time" value="00:00:00" step=".001" onchange="adjustOffsetTime()"></td>
        </tr>
        <tr>
          <td><input id="deleteButton" type="button" value="delete" class="collectorButton"></td>
        </tr>       
      </table>
      
    </div>

    <div id="interactionEditor">
      <h1> Interaction Editor </h1>
       
      <div id="interactionEditorConfiguration"> 
        <table>
          <tr title="What actions to other elements do you want when clicking on this element? E.G. hide'(element1)'">
            <td>Click outcomes:</td>
            <td>
              <select id="clickOutcomesActionId" onchange="adjustClickOutcomes(); supportClickOutcomes()">
                <option></option>
                <option>show</option>
                <option>hide</option>
                <option title="if you want this element to be (part of) your response">response</option>
              </select>
              <select id="clickOutcomesElementId" onchange="adjustOutcomeElement()">
                <option></option>
              </select>
              <input id="responseValueId" placeholder="[insert desired value here]" style="display:none" onkeyup="adjustClickOutcomes()">
              <span id="respNoSpanId" title="If you want multiple elements to contribute to the response, order the responses in the order you want them in the output" style="display:none"> <br>Resp No <input id="responseNoId" type="number" min="0" value="1" style="width:50px" onchange="adjustResponseOrder(); adjustClickOutcomes() ">
              </span>
            </td>
          </tr>   
          <tr>
            <td> <input type="button" class="collectorButton" id="addDeleteFunctionButton0" value="Add Function" onclick="addDeleteFunction(0)"> </td>
          </tr>
          <tr>
            <td>Proceed Click</td>
            <td><input id="proceedId" title="if you want the trial to proceed when you click on this element, check this box" type="checkbox" onclick="adjustProceed()"></td>
          </tr>
        </table>
      </div>
    </div>


    <div id="elementArray" name="elementArray"><?=$loadedContents?></div>
  </div>
  <div style="position:absolute;top:800px;left:100px">responseInputs<br>
    <textarea name="responseValues" id="responseValuesId"></textarea>
  </div>
</div>
</form>
<script>

 $(document).ready(function() {
  $("#controlPanelRibbon button").click(function() {
    var targetElementID = this.value;
    
    $("#controlPanelItems > div").hide();
    
    $(targetElementID).show();
  });
});

function hideShowControlPanel(){
//  $("#controlPanelItems").hide();
}

function supportClickOutcomes(){
  console.dir ("test");
  // this may be developed in version 2 to help users use this functionality; Need a list somewhere of all functions.   
}; 

var trialTypeElements = <?= $jsontrialTypeElements ?>;


var inputElementType;

// var spanArray = []; no longer needed

var elementNo = Object.size(trialTypeElements['elements'])-1;


$("#deleteButton").on("click", function() {
  alert ("deleting!!!");
  var element = document.getElementById("element"+currentElement);
  $("#configurationSettings").hide();
  element.parentNode.removeChild(element);
  
  alert(currentElement);
  trialTypeElements['elements'].splice(currentElement,1);
  
  currentStimType.innerHTML="No Element Selected";
  alert(elementNo);
  updateTrialTypeElements();
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
  if(trialTypeLoading.value=="-select a trial type-"){
    alert ("You must select a trial type to proceed!!");
  } else {
    $("#loadButtonAction").click();
  }
});

function addDeleteFunction(x){
  alert ("The ability to have multiple click actions for a single element will be added in a later release");
  /* this is the beginning of the code needed to facilitate multiple actions for clicking an element
  
  if(document.getElementById("addDeleteFunctionButton"+x).value=="Add Function"){  
    document.getElementById("addDeleteFunctionButton"+x).value="Delete Function";
    
    // update "clickOutcomesAction" for element
      // check what elements are within it
      alert (trialTypeElements['elements'][currentElement]['clickOutcomesAction']); // this has only one element, need to restructure to have multiple elements within it.     
    
  } else {
    document.getElementById("addDeleteFunctionButton"+x).value="Add Function";    
  }
  */
}

function adjustKeyboard(){
  trialTypeElements['keyboard']={
    acceptedResponses:acceptedKeyboardResponses.value,
    proceed:proceedKeyboardResponses.checked,    
  }
  updateTrialTypeElements();
}

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

function adjustZPos(){
  trialTypeElements['elements'][currentElement]['zPosition']=zPosId.value;//update trialTypeElements
  
  //update style here!!!
  document.getElementById("element"+currentElement).style.zIndex = zPosId.value;
  
  updateTrialTypeElements();
}

function adjustUserInputType(){
  var element = document.getElementById("element"+currentElement);
  element.parentNode.removeChild(element);
  currentXPos=xPosId.value*5;
  currentYPos=yPosId.value*5;
  currentWidth=elementWidth.value*5;
  currentHeight=elementHeight.value*5;
  document.getElementById("trialEditor").innerHTML=document.getElementById("trialEditor").innerHTML+"<input class='inputElement' type='"+userInputTypeValue.value+"' id='element"+currentElement+"' style='position: absolute;left:"+currentXPos+"px;top:"+currentYPos+"px; width:"+currentWidth+"px; height:"+currentHeight+"px' onclick='clickElement("+elementNo+")' name='"+currentElement+"' value='"+stimInputValue.value+"' readonly>";  

  trialTypeElements['elements'][currentElement]['userInputType']=userInputTypeValue.value;
  updateTrialTypeElements();
}

// Could probably consolidate this into a single function
function adjustOnsetTime(){
  if(onsetId.value=="00:00"){
    onsetId.style.color="grey";
  } else {
    onsetId.style.color="blue";
  }
  trialTypeElements['elements'][currentElement]['onsetTime']=onsetId.value;//update trialTypeElements
  updateTrialTypeElements();
}
function adjustOffsetTime(){
  //alert(offsetId.value);
  if(offsetId.value=="00:00"){
    offsetId.style.color="grey";
  } else {
    offsetId.style.color="blue";
  }
  trialTypeElements['elements'][currentElement]['offsetTime']=offsetId.value;//update trialTypeElements
  updateTrialTypeElements();
}

function adjustProceed(){
  trialTypeElements['elements'][currentElement]['proceed']=proceedId.checked;//update trialTypeElements
  updateTrialTypeElements();
}

function adjustOutcomeElement(){
    trialTypeElements['elements'][currentElement]['clickOutcomesElement']=clickOutcomesElementId.value;
    updateTrialTypeElements();
}

function adjustClickOutcomes(){
  if(clickOutcomesActionId.value=="response"){
    $("#clickOutcomesElementId").hide();
    $("#responseValueId").show();
    $("#respNoSpanId").show();
    updateResponseValuesId();
  } else {
    $("#clickOutcomesElementId").show();
    populateClickElements();    
    $("#responseValueId").hide();
    $("#respNoSpanId").hide();
    trialTypeElements['elements'][currentElement]['clickOutcomesElement']=clickOutcomesElementId.value;
  }

  trialTypeElements['elements'][currentElement]['clickOutcomesAction']=clickOutcomesActionId.value;//update trialTypeElements
  updateTrialTypeElements();
  // could add preview of outcome e.g. temp fade of another element if that's the action
  currentResponseNo=responseNoId.value;

}
var responseArray=[];
var tempStartArray=[];
var noOfResponses = 0;
function updateResponseValuesId(){
  newRespElement=true;
  for(i=0; i<responseArray.length;i++){
    if(responseArray[i].indexOf(elementNameValue.value)!=-1){
      newRespElement=false;
    }
  }

  if(newRespElement==true){
    responseNoId.value=noOfResponses;
    //noOfResponses++;
    //will start as response zero
    
    if(typeof responseArray[0] != 'undefined' ){
      tempStartArray[responseArray[0].length]=elementNameValue.value;
    } else {
      tempStartArray[0]=elementNameValue.value;
    }
    responseArray[responseNoId.value]=tempStartArray;
    trialTypeElements['responses']=responseArray;
    updateTrialTypeElements();
  }
  responseValuesId.value=JSON.stringify(responseArray);
  
  trialTypeElements['elements'][currentElement]['responseValue']=responseValueId.value;//update trialTypeElements
  trialTypeElements['elements'][currentElement]['responseNo']=responseNoId.value;//update trialTypeElements
  updateTrialTypeElements();
  
}

var currentResponseNo=0; //this needs to be updated whenever you click on an element;

function adjustResponseOrder(){

  if(typeof responseArray[responseNoId.value] != 'undefined'){
    newPos=responseArray[responseNoId.value].length;
  } else {
    responseArray[responseNoId.value]=[];
    newPos=0;
  }
//  alert(newPos);
  
  //remove from original array
  
  for(i=0; i<responseArray.length;i++){
    if(responseArray[i].indexOf(elementNameValue.value)!=-1){
      responseArray[i][responseArray[i].indexOf(elementNameValue.value)] = null;
    }
  }
  
  responseArray[responseNoId.value][newPos]=elementNameValue.value;

  
  updateResponseValuesId();
  
}

function adjustTextBack(){
  document.getElementById("element"+currentElement).style.backgroundColor=textBackId.value;
  if(textBackId.value==""){
    trialTypeElements['elements'][currentElement]['textBack']="";
  } else {
    trialTypeElements['elements'][currentElement]['textBack']=textBackId.value;//update trialTypeElements
  }
  updateTrialTypeElements();
}

function adjustTextColor(){
  document.getElementById("element"+currentElement).style.color=textColorId.value;
  if(textColorId.value==""){
    trialTypeElements['elements'][currentElement]['textColor']="";
  } else {
    trialTypeElements['elements'][currentElement]['textColor']=textColorId.value;//update trialTypeElements
  }
  updateTrialTypeElements();
}

function adjustTextFont(){
  document.getElementById("element"+currentElement).style.fontFamily=textFontId.value;
  if(textFontId.value==""){
    trialTypeElements['elements'][currentElement]['textFont']="";
  } else {
    trialTypeElements['elements'][currentElement]['textFont']=textFontId.value;//update trialTypeElements
  }
  updateTrialTypeElements();
}

function adjustTextSize(){
  document.getElementById('element'+currentElement).style.fontSize=(textSizeId.value*3)+"px";
  
  trialTypeElements['elements'][currentElement]['textSize']=textSizeId.value;//update trialTypeElements
    
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
    //_mouseX=_mouseX-100; // this may need to be adjusted depending on the size of the element. Current size is 100px
    //_mouseY=_mouseY-300; // same as above
    
    /*
    if(_mouseX>400){ //replace with real value
      _mouseX=400;
    }
    if(_mouseY>400){ //replace with real value
      _mouseY=400;
    }
    */
    
   
    xPos=Math.round((_mouseX)); //    /5);
    yPos=Math.round((_mouseY)); //  /5);
    
    
    
    
//    spanArray[elementNo]={type:inputElementType};
      
    if(inputElementType=="input"){
      
      document.getElementById("trialEditor").innerHTML=document.getElementById("trialEditor").innerHTML+"<input class='inputElement' type='text' id='element"+elementNo+"' style='position: absolute; width:80px; left:"+_mouseX+"px;top:"+_mouseY+"px' onclick='clickElement("+elementNo+")' name='"+inputElementType+"' readonly>";  
      
    } else {
      document.getElementById("trialEditor").innerHTML=document.getElementById("trialEditor").innerHTML+"<span class='"+inputElementType+"Element' id='element"+elementNo+"' style='position: absolute; left:"+_mouseX+"px;top:"+_mouseY+"px; z-index:"+elementNo+"' onclick='clickElement("+elementNo+")' name='"+inputElementType+"'>"+inputElementType+"</span>";
    }
    trialTypeElements['elements'][elementNo] = {
      width:20, 
      height:20,
      xPos: xPos,
      yPosition: yPos,
      zPosition: elementNo,
      elementName: 'element'+elementNo,
      stimulus: 'not yet added',
      response: false,
      trialElementType: inputElementType, // repetition here
      clickOutcomesAction:'',
      clickOutcomesElement:'',
      proceed: false,
      };
      // add attributes depending on what type of element
      if(inputElementType=="media"){
        trialTypeElements['elements'][elementNo]['mediaType']="Pic";
      }
      if(inputElementType=="text" | inputElementType=="input"){
        trialTypeElements['elements'][elementNo]['textSize']=12;
        trialTypeElements['elements'][elementNo]['textColor']="";
        trialTypeElements['elements'][elementNo]['textFont']="";
        trialTypeElements['elements'][elementNo]['textBack']="";
        
      }
      if(inputElementType=="input"){
        trialTypeElements['elements'][elementNo]['userInputType']="text";
        trialTypeElements['elements'][elementNo]['height']="5"; //overwriting default height
      }
       
    updateTrialTypeElements();
  }
}

backupTrialTypeName=trialTypeName.value;


var illegalChars=['.',' '];

function updateTrialTypeElements(){
  var illegalCharPresent = false;
  for (i=0; i<illegalChars.length; i++){
    if(trialTypeName.value.indexOf(illegalChars[i])!=-1){
      alert("Illegal character in name, reverting to acceptable version");
      illegalCharPresent = true;
      trialTypeName.value=backupTrialTypeName;
    }   
  }
  if(illegalCharPresent==false){
    backupTrialTypeName=trialTypeName.value;    
  }
  trialTypeElements['trialTypeName']=trialTypeName.value;
  document.getElementById("elementArray").innerHTML = JSON.stringify(trialTypeElements,  null, 2);
  inputElementType="select";
  elementType("select");  
}

function changeMediaType(){
  trialTypeElements['elements'][currentElement]['mediaType']=userInputTypeValue.value;
  updateTrialTypeElements();

  // code here to change image cue if we include media images
}

var currentElement = 0;
function clickElement(x){
  if(inputElementType=="select"){
    currentElement = x;
    thisElement="element"+x;
    for(i=0;i<=elementNo;i++){
      if(i==x){
        //alert (trialTypeElements[i]['inputType']+"ElementSelected");
        document.getElementById("element"+i).className=trialTypeElements['elements'][i]['trialElementType']+"ElementSelected";
      } else {
        
        if (typeof trialTypeElements['elements'][i] != 'undefined') { //code to check whether the element exists or not
          document.getElementById("element"+i).className=trialTypeElements['elements'][i]['trialElementType']+"Element";
        }
      }
    }
    $("#configurationSettings").hide();
    $("#interactionEditorConfiguration").hide();
    $("#userInputSettings").hide();

    loadConfigs(); // this loads the configurations for the editor
    
    // here's where you distinguish between different element types
    switch (trialTypeElements['elements'][x]['trialElementType']){
      case "media":
        document.getElementById("inputStimTypeCell").innerHTML="Media Type";
        $("#configurationSettings").show();
        $("#interactionEditorConfiguration").show();
        document.getElementById('userInputTypeValue').style.visibility="hidden";
        currentStimType.innerHTML="Media";
        inputStimSelectCell.innerHTML='<select id="userInputTypeValue" onchange="changeMediaType()"><option>Pic</option><option>Audio</option><option>Video</option></select>';
      break

      case "text":
        $("#configurationSettings").show();
        $("#interactionEditorConfiguration").show();
        document.getElementById('userInputTypeValue').style.visibility="hidden";
        currentStimType.innerHTML="Text";
        document.getElementById("inputStimTypeCell").innerHTML="Text properties";
        // userInputTypeValue is being used for both media and input types - this could probably be tidier by keeping them separate
        inputStimSelectCell.innerHTML=
          '<input id="userInputTypeValue" style="display:none">'+
            '<td><input type="number" id="textSizeId" onchange="adjustTextSize()" value=12 min="1" style="width:50px"></td>'+
            '<td><input type="text" id="textColorId" onkeyup="adjustTextColor()" placeholder="color" style="width:50px"></td>'+
            '<td><input type="text" id="textFontId" onkeyup="adjustTextFont()" placeholder="font" style="width:50px" value=></td>'+
            '<td><input type="text" id="textBackId" onkeyup="adjustTextBack()" placeholder="background-color" style="width:50px" value=></td>';
               
        //rather than embed it in above text, i've listed these values below for improved legibility
        textFontId.value=trialTypeElements['elements'][currentElement].textFont;
        textColorId.value=trialTypeElements['elements'][currentElement].textColor;
        textSizeId.value=trialTypeElements['elements'][currentElement].textSize;
        textBackId.value=trialTypeElements['elements'][currentElement].textBack;
      break      

      case "input":      
        document.getElementById("inputStimTypeCell").innerHTML="Input Type";
        $("#configurationSettings").show();
        $("#interactionEditorConfiguration").show();
        document.getElementById('userInputTypeValue').style.visibility="visible";
        currentStimType.innerHTML="Input";
        inputStimSelectCell.innerHTML=
          '<select id="userInputTypeValue" onchange="adjustUserInputType()"><option>Text</option><option>Button</option></select>'+
          '</td><br>'+
          '<td>size<input type="number" id="textSizeId" onchange="adjustTextSize()" value=12 min="1" style="width:50px"></td>'+
          '<td><input type="text" id="textColorId" onkeyup="adjustTextColor()" placeholder="color" style="width:50px"></td>'+
          '<td><input type="text" id="textFontId" onkeyup="adjustTextFont()" placeholder="font" style="width:50px" value=></td>'+
          '<td><input type="text" id="textBackId" onkeyup="adjustTextBack()" placeholder="background-color" style="width:50px" value=></td>';

        //rather than embed it in above text, i've listed these values below for improved legibility
        textFontId.value=trialTypeElements['elements'][currentElement].textFont;
        textColorId.value=trialTypeElements['elements'][currentElement].textColor;
        textSizeId.value=trialTypeElements['elements'][currentElement].textSize;
        textBackId.value=trialTypeElements['elements'][currentElement].textBack;

          
        // might add check box and radio in a later release
      break      
    break //this break necessary?

    }
  }
}

function removeOptions(selectbox) // this solution was from Fabiano at http://stackoverflow.com/questions/3364493/how-do-i-clear-all-options-in-a-dropdown-box
  {
    var i;
    for(i=selectbox.options.length-1;i>=0;i--)
    {
        selectbox.remove(i);
    }
  }


function loadConfigs(){

  if (trialTypeElements['elements'][currentElement].clickOutcomesAction=="response"){
    $("#clickOutcomesElementId").hide();
    $("#responseValueId").show();
    $("#respNoSpanId").show();
    responseValueId.value=trialTypeElements['elements'][currentElement].responseValue;
    updateResponseValuesId();
    
  } else {
    $("#clickOutcomesElementId").show();
    $("#responseValueId").hide();
    $("#respNoSpanId").hide();
    populateClickElements();    
  }
    
  elementNameValue.value=trialTypeElements['elements'][currentElement].elementName;
  userInputTypeValue.value=trialTypeElements['elements'][currentElement].mediaType;
  stimInputValue.value=trialTypeElements['elements'][currentElement].stimulus;
  elementWidth.value=trialTypeElements['elements'][currentElement].width;
  elementHeight.value=trialTypeElements['elements'][currentElement].height;
  xPosId.value=trialTypeElements['elements'][currentElement].xPos;
  yPosId.value=trialTypeElements['elements'][currentElement].yPosition;
  zPosId.value=trialTypeElements['elements'][currentElement].zPosition;  
  clickOutcomesActionId.value=trialTypeElements['elements'][currentElement].clickOutcomesAction;
  
  
  //javascript code to select element
  document.getElementById('clickOutcomesElementId').value= trialTypeElements['elements'][currentElement].clickOutcomesElement; //2;
//  alert(document.getElementById('clickOutcomesElementId').value);  

}

function populateClickElements(){
  removeOptions(document.getElementById("clickOutcomesElementId"));   
  var option = document.createElement("option");
  option.text = '';
  document.getElementById("clickOutcomesElementId").add(option);
  clickOutcomesElementId.value= trialTypeElements['elements'][currentElement].clickOutcomesElement;  
  var arrayNo=0;
  var elementList = [];
  for(x in trialTypeElements['elements']){
    arrayNo++;
//    alert(trialTypeElements['elements'][x].elementName);
    elementList.push(trialTypeElements['elements'][x].elementName); //may become redundant
    var option = document.createElement("option");
    option.text = trialTypeElements['elements'][x].elementName;
    option.value=trialTypeElements['elements'][x].elementName; //arrayNo;
    document.getElementById("clickOutcomesElementId").add(option);

    //alert(elementList);
    // add x to element array
  }
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
  inputElementType=x;
  for(i=0;i<inputButtonArray.length;i++){
    if(inputButtonArray[i]==x){
      document.getElementById(inputButtonArray[i]+"Button").className="elementButtonSelected";
    } else {
        if (typeof ("element"+i) != 'undefined') { //code to check whether the element exists or not
          document.getElementById(inputButtonArray[i]+"Button").className="elementButton";
        }     
    }
  }
  if(x!="select"){
    $("#configurationSettings").hide();
    $("#userInputTypeValue").value="n/a";

    currentStimType.innerHTML="No Element Selected ";
    // for all elements revert formatting to element
    for(i=0;i<=elementNo;i++){
        if (typeof trialTypeElements['elements'][i] != 'undefined') { //code to check whether the element exists or not
          document.getElementById("element"+i).className=trialTypeElements['elements'][i]['trialElementType']+"Element";
        }      
    }
  }  
}


function getPositions(ev) {
if (ev == null) { ev = window.event }
  var offset = $("#trialEditor").offset(); 
  _mouseX = ev.pageX;
  _mouseY = ev.pageY;
  console.dir(_mouseX);
  console.dir(_mouseY);
  console.dir(offset);
 
  _mouseX -= offset.left;
  _mouseY -= offset.top;
   
}

function mouseMovingFunctions(){
  if(inputElementType=="select"){
    
    var css = '.textElement:hover{ border-color: black; background-color:transparent; text-shadow:-1px -1px 0 #000,1px -1px 0 #000,-1px 1px 0 #000,1px 1px 0 #000; }';
    style = document.createElement('style');

    if (style.styleSheet) {
        style.styleSheet.cssText = css;
    } else {
        style.appendChild(document.createTextNode(css));
    }
    document.getElementsByTagName('head')[0].appendChild(style);
    
    
    var css = '.mediaElement:hover{ border-color: white; background-color:green; color:white }';
    style = document.createElement('style');

    if (style.styleSheet) {
        style.styleSheet.cssText = css;
    } else {
        style.appendChild(document.createTextNode(css));
    }
    document.getElementsByTagName('head')[0].appendChild(style);
    
    var css = '.inputElement:hover{ border-color: green; background-color:green; color:blue }';
    style = document.createElement('style');

    if (style.styleSheet) {
        style.styleSheet.cssText = css;
    } else {
        style.appendChild(document.createTextNode(css));
    }
    document.getElementsByTagName('head')[0].appendChild(style);    
  }
  

  if(inputElementType!="select"){
    // change all elements to nonHover version
    var css = '.textElement:hover{ border-color: transparent; background-color:transparent; color:blue }';
    style = document.createElement('style');

    if (style.styleSheet) {
        style.styleSheet.cssText = css;
    } else {
        style.appendChild(document.createTextNode(css));
    }
    document.getElementsByTagName('head')[0].appendChild(style);




    var css = '.mediaElement:hover{ border-color: blue; background-color:transparent; color:blue }';
    style = document.createElement('style');

    if (style.styleSheet) {
        style.styleSheet.cssText = css;
    } else {
        style.appendChild(document.createTextNode(css));
    }
    document.getElementsByTagName('head')[0].appendChild(style);    //unset hover style
    
    
    var css = '.inputElement:hover{ border: 1px solid #cccccc; background-color:white; color:white }';
    style = document.createElement('style');

    if (style.styleSheet) {
        style.styleSheet.cssText = css;
    } else {
        style.appendChild(document.createTextNode(css));
    }
    document.getElementsByTagName('head')[0].appendChild(style);

  }
}

$("#requestButton").on("click", function() {
  //$("#stimListDiv").show();
  var myWindow = window.open("GUI/requestFunction.php", "", "width=800, height=600");
});

var showHideRequestInput=false;
$("#showRequestOptionsId").on("click", function(){
  if(showHideRequestInput==false){
    showHideRequestInput=true;
    $("#newFunctionTable").show();
  } else {
    showHideRequestInput=false;
    $("#newFunctionTable").hide();
  }
});
/*

  <!--
  .inputElement:hover{
    background-color:#000099;
  }
  !-->
    <!--
  .textElement:hover{
    color:#003300;
    outline-style: dotted;
    outline-color: #00ff00;
  }
  !-->
  
  */

// create hover class depending on whether select is on or not
/*
  .element:hover{
    border: 2px solid red;
  }
*/

  
  
</script>
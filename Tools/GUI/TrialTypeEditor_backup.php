<?php

/*
  	GUI - Anthony Haffey

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
    
    require ("guiClasses.php");
    
    $thisTrialTypeInfo = new trialTypeInfo();

    $jsonTrialTypeInfo = json_encode($thisTrialTypeInfo);

?>

<style>
  
  

  #configurationEditor{
    width:400px;
    height:500px;
    position:absolute;
    left:620px;
    top: 220px;
    border:2px solid black;
    border-radius: 25px;
    padding:50px;
  }
  #configurationEditor:hover{
    border: 2px solid blue;
  }
  #elementList{
    position:absolute;
    left: 100px;
  }
  #keyboardResponses{
    position:relative;
    left:100px;
    top:550px;
    width:920px;
    height:50px;
    border: 2px solid black;
    border-radius: 25px;
  }
  #keyboardResponses:hover{
    border: 2px solid blue;
  }

  
  #trialEditor{
    width:500px;
    height:500px;
    position:absolute;
    left:100px;
    top:220px;
    border:2px solid black;
    border-radius: 25px;
    padding:50px;
  }
  
  .element{
    color:blue;
    width:100px;
    height: 100px;
    line-height:70px;
    border: 2px solid blue;
    border-radius: 10px;
    padding:10px;    
  }

  .elementSelected{
    color:green;
    width:100px;
    height: 100px;
    line-height:70px;
    border: 4px solid green;    
    border-radius: 10px;
    padding:10px;    

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
  <!--  	pointer-events: none; !-->

  
  
</style>


<div id="elementList">
  <span>
    <input id="mediaButton" type="button" class="elementButton" value="Media" onclick="elementType('media')">
    <input id="textButton" type="button" class="elementButton" value="Text" onclick="elementType('text')">
    <input id="inputButton" type="button" class="elementButton" value="Input" onclick="elementType('input')">
    <input id="selectButton" type="button" class="elementButton" value="Select" onclick="elementType('select')">
  </span>
  <span style="position:relative; left:500px">
    <input type="button" class="collectorButton" value="Keyboard Responses" onclick="editKeyboardResponses()">
  </span>
</div>
<div id="trialEditor" onMouseMove="mouseMovingFunctions()" onclick="getPositions(); alertMouse()"></div> <!-- onMouseMove="getPositions();" !-->
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
    <tr title="If you want to refer to a stimulus list then write '[stimulus1]' or '[stimulus2]' etc."><td>Stimulus:</td><td><input id="stimInputValue" type="text" onkeyup="editText()"></td></tr>
    <tr><td>Width:</td><td><input id="elementWidth" type="number" value="20" min="1" max="100" onchange="adjustWidth()"></td><td>%</td></tr>
    <tr><td>Height:</td><td><input id="elementHeight" type="number" value="20" min="1" max="100" onchange="adjustHeight()"></td><td>%</td></tr>
    <tr title="this position is based on the left of the element"><td>X-Position:</td><td><input id="xPosId" type="number" min="1" max="100" onchange="adjustXPos()"></td><td>%</td></tr>
    <tr title="this position is based on the top of the element"><td>Y-Position:</td><td><input id="yPosId" type="number" min="1" max="100" onchange="adjustYPos()"></td><td>%</td></tr>
    <tr title="change this value to bring the element to forward or backward (to allow it to be on top of or behind other elements"><td>Z-Position:</td><td><input id="zPosId" type="number" min="1" max="100"></td></tr>
    <tr title="check this box if you want the participant to be able to click on this as a response"><td>Response click?</td><td><input id="responseId" type="checkBox"></td></tr>
    
    
    <tr><td><input id="deleteButton" type="button" value="delete" class="collectorButton"></td></tr>
    
  </table>
  
  <table id="inputSettings" style="display:none"> 
    <tr title="If you want to refer to a stimulus list then write '[stimulus1]' or '[stimulus2]' etc."><td>Input Type:</td>
      <td><select id="inputTypeValue">
        <option>Text</option>
        <!-- <option>Drop-Down List</option> !--> 
        <option>Button</option> <!-- we could with time offer different styles of these buttons !-->
        <option>Check-Box</option>
        <option>Radio-Button</option> <!-- we could with time offer different styles of these buttons !-->
        </select>
      </td>
    </tr>
    <tr title="If you want to refer to a stimulus list then write '[stimulus1]' or '[stimulus2]' etc."><td>Value(s):</td><td><input id="inputValues" type="text" onkeyup="editText()"></td></tr>
    <tr><td>Width:</td><td><input type="number" value="20" min="1" max="100"></td></tr>
    <tr><td>Height:</td><td><input type="number" value="20" min="1" max="100"></td></tr>
    <tr><td>X-Position:</td><td><input type="number" min="1" max="100"></td></tr>
    <tr><td>Y-Position:</td><td><input type="number" min="1" max="100"></td></tr>
    <tr title="check this box if you want the participant to be able to click on this as a response"><td>Response click?</td><td><input type="checkBox"></td></tr>
    <tr><td><input type="button" value="delete" class="collectorButton"></td></tr>
  </table>
  
  

</div>


<script>


var trialTypeInfo = <?= $jsonTrialTypeInfo ?>;


var inputElementType;

var spanArray = [];

var elementNo = -1;

function adjustHeight(){
  if(Number(yPosId.value) + Number(elementHeight.value) > 100){
    elementHeight.value = 100-yPosId.value; // temporary correction will still allow user to create something bigger than the screen
  }
  newHeight = 5*elementHeight.value;
  newHeight = newHeight +"px";
  document.getElementById(currentElement).style.height = newHeight;
}

function adjustWidth(){
  if( Number(xPosId.value) + Number(elementWidth.value) > 100){
    elementWidth.value = 100-xPosId.value; // temporary correction will still allow user to create something bigger than the screen
  }
  newWidth = 5*elementWidth.value;
  newWidth = newWidth +"px";
  document.getElementById(currentElement).style.width = newWidth;
}

function adjustXPos(){
  if( Number(xPosId.value) + Number(elementWidth.value) > 100){
    xPosId.value= 100- elementWidth.value; // temporary correction will still allow user to create something bigger than the screen
  }  
  newXPos=(Number(xPosId.value)*5) +"px";
  document.getElementById(currentElement).style.left = newXPos; //-xPosId.value; // temporary correction will still allow user to create something bigger than the screen
}

function adjustYPos(){
  if( Number(yPosId.value) + Number(elementHeight.value) > 100){
    yPosId.value= 100- elementHeight.value; // temporary correction will still allow user to create something bigger than the screen
  }  
  newYPos=(Number(yPosId.value)*5) +"px";
  document.getElementById(currentElement).style.top = newYPos; //-xPosId.value; // temporary correction will still allow user to create something bigger than the screen
}


function alertMouse(){
  if(inputElementType!="select"){
    _mouseX=_mouseX-150; // this may need to be adjusted depending on the size of the element. Current size is 100px
    _mouseY=_mouseY-270; // same as above
    xPos=(_mouseX)/5;
    yPos=(_mouseY)/5;
    
    elementNo++;
    spanArray[elementNo]=inputElementType;
    document.getElementById("trialEditor").innerHTML=document.getElementById("trialEditor").innerHTML+"<span class='element' id='element"+elementNo+"' style='position: absolute; left:"+_mouseX+"px;top:"+_mouseY+"px' onclick='clickElement("+elementNo+")' name='"+inputElementType+"'>"+inputElementType+"</span>";    
  }
  
  //alert(elementNo);
  
  
  trialTypeInfo['element'+elementNo] = {type:inputElementType, 
                                        width:trialTypeInfo['defaultWidth'], 
                                        height:trialTypeInfo['defaultHeight'],
                                        xPosition: xPos,
                                        yPosition: yPos,
                                        stimulus: 'not yet added',
                                        response: false,
                                        inputType: 'NA',
                                        };
  
  
  inputElementType="select";
  elementType("select");
  
  
}


var currentElement = 0;
function clickElement(x){
  if(inputElementType=="select"){
    currentElement = ("element"+x);
    thisElement="element"+x;
    for(i=0;i<=elementNo;i++){
      if(i==x){
        document.getElementById("element"+i).className="elementSelected";
      } else {
        document.getElementById("element"+i).className="element";
      }
    }
    

    $("#configurationSettings").hide();
    $("#inputSettings").hide();

    // here's where you distinguish between different element types
    switch (spanArray[x]){
      case "media":
        $("#configurationSettings").show();
        currentStimType.innerHTML="Media";
      break
      case "text":
        $("#configurationSettings").show();
        currentStimType.innerHTML="Text";
      break      
      case "input":      
        $("#inputSettings").show();
        currentStimType.innerHTML="Input";
      break      
    break
    }
  }
  loadConfigs();
}

function loadConfigs(){
    
  stimInputValue.value=trialTypeInfo[currentElement].stimulus;
  elementWidth.value=trialTypeInfo[currentElement].width;
  elementHeight.value=trialTypeInfo[currentElement].height;
  xPosId.value=trialTypeInfo[currentElement].xPosition;
  yPosId.value=trialTypeInfo[currentElement].yPosition;
  responseId.checked= trialTypeInfo[currentElement].stimulus;
    
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

function editText(){
  var stimText=stimInputValue.value;
  document.getElementById(currentElement).innerHTML=stimText;
  
//alert(stimText);
  if(stimText.indexOf('[') != -1 & stimText.indexOf(']') != -1 & stimText.indexOf('[') < stimText.indexOf(']')){ // may need to put checks on this to prevent e.g.s like "[stim]asdfadsf"
    stimInputValue.style.color="blue";
  } else {
    stimInputValue.style.color="black";
  }
  // there's duplication here to be tidied
  
  stimText=inputValues.value;
 
  if(stimText.indexOf('[') != -1 & stimText.indexOf(']') != -1 & stimText.indexOf('[') < stimText.indexOf(']')){ // may need to put checks on this to prevent e.g.s like "[stim]asdfadsf"
    inputValues.style.color="blue";
  } else {
    inputValues.style.color="black";
  }
 
  //<span>"+document.getElementById("textInputs").value+"</span>";
}

var inputButtonArray=["media","text","input","select"];

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
    $("#inputSettings").hide();

    currentStimType.innerHTML="No Element Selected ";
    // for all elements revert formatting to element
    for(i=0;i<=elementNo;i++){
      document.getElementById("element"+i).className="element";
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
    var css = '.element:hover{ border-color: white; background-color:green; color:white }';
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
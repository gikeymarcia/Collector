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
    position:       absolute;
    border:         2px solid black;
    left:           810px;  
    width:          500px;
    top:            300px;
    height:         800px;
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
    border: none;
  }
  
  #displayEditor{
    width:400px;
    border:2px solid black;
    border-radius: 25px;
    padding:25px;
  }
  #elementArray{
    width:400px;
    height: 800px;
    top: 300px;
    left: 1350px;
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
    width:400px;
    height:500px;
    position:absolute;
    padding:10px;
    border: 2px solid black;
    border-radius: 25px;
  }  
  #interactionEditor{
    width:400px;
    height:500px;
    position:absolute;
    border:2px solid black;
    border-radius: 25px;
    padding:25px;
  }
  
  #trialEditor{
    width:800px;
    height:800px;
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
    width:160px;
    height: 160px;
    line-height:70px;
    border: 2px solid blue;
    border-radius: 10px;
    padding:10px;    
  }

  .mediaElementSelected{
    color:green;
    width:160px;
    height: 160px;
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
  
  /* * * * * * * *
  * Configurations
  * * * * * * * */

  $elementScale   =   8; // as the interface for inserting elements if 800px x 800px, and we are scaling to a 100% height or width (800/100 = 8)
  
  // load function 
  // save function
  // rename function
  // else scratch 
  
  
  //sorting out whether we are working from scratch, have just saved a file, or are loading a file
  if(isset($_POST['loadButton'])){   //load first
    $file_contents                                    =   file_get_contents("GUI/newTrialTypes/".$_POST['trialTypeLoaded']);
    $trialTypeElementsPhp                             =   json_decode($file_contents);
    $_DATA['trialTypeEditor']['currentTrialTypeName'] =   str_replace('.txt','',$_POST['trialTypeLoaded']);#
    
  } else {
    if(!empty($_POST['elementArray'])){ // have just saved
    
      $trialTypeElementsPhp=json_decode($_POST['elementArray']);  
      if(isset($_DATA['trialTypeEditor']['currentTrialTypeName'])){ // Renaming files if task name has changed 
        if(strcmp($_DATA['trialTypeEditor']['currentTrialTypeName'], $trialTypeElementsPhp->trialTypeName)!=0){ //i.e. a new trialType name
          if(file_exists("GUI/newTrialTypes/"       .     $_DATA['trialTypeEditor']['currentTrialTypeName'] . ".txt")){
            unlink("GUI/newTrialTypes/"             .       $_DATA['trialTypeEditor']['currentTrialTypeName'] . ".txt"); //Delete original file here            
            unlink($_PATH->get('Custom Trial Types')."/".$_DATA['trialTypeEditor']['currentTrialTypeName']."/display.php"); //deleting php file
            rmdir($_PATH->get('Custom Trial Types') ."/".$_DATA['trialTypeEditor']['currentTrialTypeName']); //deleting directory
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
echo $_DATA['trialTypeEditor']['currentTrialTypeName']
?></textarea>
    
  <div id="elementTypeList">
    <br>
    <span>
      <input id="mediaButton"   type="button" class="elementButton" value="Media"   onclick="elementType('media')">
      <input id="textButton"    type="button" class="elementButton" value="Text"    onclick="elementType('text')">
      <input id="inputButton"   type="button" class="elementButton" value="Input"   onclick="elementType('input')">
      <input id="complexButton" type="button" class="elementButton" value="Complex" onclick="alert('This will include more code heavy elements, e.g. progress bars, and will be in a later release')">
      <input id="selectButton"  type="button" class="elementButton" value="Select"  onclick="elementType('select')">

    </span>
    <span style="position:relative; left:420px">

      <input  type="submit" class="collectorButton" id="saveButton" name="saveButton" value="Save">
      <button type="button" class="collectorButton" onclick="saveTextAsFile()">download JSON</button>
    <?php 
      if(count($trialTypesList)>0){
      ?>
          <select id="trialTypeLoading" name="trialTypeLoaded">
            <option>-select a trial type-</option>
            <?php foreach($trialTypesList as $trialType){
              echo "<option>$trialType</option>";
            }
            ?>
          </select> 
          <input type="button" id="loadButton" class="collectorButton" value="Load">
          <input type="submit" id="loadButtonAction" name="loadButton" class="collectorButton" value="Load" style="display:none">
      <?php
      }
    ?>  

      </span>

    </div>

  <div id="trialEditor" onMouseMove="mouseMovingFunctions()" onclick="getPositions(); alertMouse()">

<?php

  foreach($trialTypeElementsPhp->elements as $elementKey=>$element){
    if($element!=NULL){ //ideally I'll tidy it up so that there are no null elements 
      echo "<div id='element$elementKey' class='".$element->trialElementType."Element' 
               style='position:absolute;
                width:".($elementScale*$element->width)."px;
                height:".($elementScale*$element->height)."px;
                left:".($elementScale*$element->xPos)."px;
                top:".($elementScale*$element->yPosition)."px;
                ";
      if (isset($element->textColor)){
        echo "color:$element->textColor;
              font-family:$element->textFont;
              background-color:$element->textBack;
              font-size:".($element->textSize)."px;"; // look into this when I've finalised spacing for interfaces
      }
      echo "' onclick='clickElement($elementKey)'
               >".$element->stimulus."</div>";      
    }
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

<!-- Bootstrap alerts !-->
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css"> <!-- this is redundant, helper bar is a normal div !-->
<script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>

<!-- the helper bar !-->
<div id="controlPanel" class="alert-success">
  <div id="controlPanelRibbon">
    <button type="button" class="collectorButton" value="#displayEditor"      style="display:none"  id="displayEditorButton">Display </button>
    <button type="button" class="collectorButton" value="#interactionEditor"  style="display:none"  id="interactionEditorButton">Interaction </button>
    <button type="button" class="collectorButton" value="#keyboardResponses"                        >Keyboard           </button>
    <button type="button" class="collectorButton" value="#responseInputs"                           >Responses          </button>
  </div>  
  
  
  <div id="controlPanelItems">
  <div id = "responseInputs">
    <h2> <b>Click Responses</b> that you have coded </h2>
    <textarea name="responseValues" id="responseValuesId" style="display:none" readonly></textarea>
    <div id="responseValuesTidyId"></div>
  </div>

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
          <td><input id="onsetId" class="onsetOffset" type="time" value="00:00:00" step=".001" onchange="adjustTime('onset')"></td>
        </tr>
        <tr title="if you want the element to disappear after a certain amount of time, change from 00:00">
          <td>offset time:</td>
          <td><input id="offsetId" class="onsetOffset" type="time" value="00:00:00" step=".001" onchange="adjustTime('offset')"></td>
        </tr>
        <tr>
          <td><input id="deleteButton" type="button" value="delete" class="collectorButton"></td>
        </tr>       
      </table>
    </div>

    <div id="keyboardResponses">
      <h1>Keyboard responses</h1>
        accepted keyboard response(s) <input id="acceptedKeyboardResponses" name="acceptedKeyboardResponses" onkeyup="adjustKeyboard()"><br>
        proceed when an accepted key is pressed <input id="proceedKeyboardResponses" type="checkbox" onchange="adjustKeyboard()"> 
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
         <!--   <td> <input type="button" class="collectorButton" id="addDeleteFunctionButton0" value="Add Function" onclick="addDeleteFunction(0)"> </td> !-->
          </tr>
          <tr>
            <td>Proceed Click</td>
            <td><input id="clickProceedId" title="if you want the trial to proceed when you click on this element, check this box" type="checkbox" onclick="adjustClickProceed()"></td>
          </tr>
        </table>
      </div>
    </div>
    


  </div>  
</div>

  <textarea id="elementArray" name="elementArray"><?=$loadedContents?></textarea>

</form>

<script src="GUI/trialTypeFunctions.js"></script>

<script>

/* Configurations and preparing global variables */
var elementScale = 8; // config


var currentElement      =   0;                                              //assumes that we are working from scratch
var trialTypeElements   =   <?= $jsontrialTypeElements ?>;                  //the object containing all the trialTypeInformation
var inputElementType;                                                       //the type of element that is currently selected to be added to the task. "Select" also included
var elementNo           =   Object.size(trialTypeElements['elements'])-1;   //elements are numbered, e.g. "element0","element1"
var responseArray       =   trialTypeElements['responses'];
var currentResponseNo   =   0;                                              //this needs to be updated whenever you click on an element;
var inputButtonArray    =   ["media","text","input","select"];

elementType('select');                                                      // by default you are in select mode, not creating an element


/* * *
/ I may want to include more code within document.ready function?
* * */

$(document).ready(function() {
  $("#controlPanelRibbon button").click(function() { // when clicking on a button within the control panel ribbon
    var targetElementID = this.value;                // use the value of the clicked button
    
    $("#controlPanelItems > div").hide();            // hide all of the interface in the control panel
    
    $(targetElementID).show();                       // except the one for the clicked button
  });
});


/* structuring code

  have a file for function definitions
    - try to pass in objects through functions rather than refer to global variables

*/



/* * * * *
* button clicking functions
* * * * * */

$("#deleteButton").on("click", function() {
  delConf=confirm ("Are you sure you wish to delete?");
  if (delConf== true){
    var element = document.getElementById("element"+currentElement);
    $("#configurationSettings").hide();
    element.parentNode.removeChild(element);
    
    trialTypeElements['elements'].splice(currentElement,1);
    
    currentStimType.innerHTML="No Element Selected";
    updateTrialTypeElements();    

    
    $("#interactionEditorButton").hide();
    $("#displayEditorButton").hide();
  }
});

$("#loadButton").on("click",function(){
  if(trialTypeLoading.value=="-select a trial type-"){
    alert ("You must select a trial type to proceed!!");
  } else {
    $("#loadButtonAction").click();
  }
});

/* * * * 
* Saving using CTRL S - doesn't suppress event in Firefox
* * * */

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
function addDeleteFunction(x){
  alert ("The ability to have multiple click actions for a single element will be added in a later release");
  
  if(document.getElementById("addDeleteFunctionButton"+x).value=="Add Function"){  
    document.getElementById("addDeleteFunctionButton"+x).value="Delete Function";
    
    // update "clickOutcomesAction" for element
      // check what elements are within it
      alert (trialTypeElements['elements'][currentElement]['clickOutcomesAction']); // this has only one element, need to restructure to have multiple elements within it.     
    
  } else {
    document.getElementById("addDeleteFunctionButton"+x).value="Add Function";    
  }
  
}
*/


/* * * * 
*  loading inputs with trialTypeElements settings 
*/



  /* Keyboard */

  acceptedKeyboardResponses.value     =   trialTypeElements['keyboard'].acceptedResponses;
  proceedKeyboardResponses.checked    =   trialTypeElements['keyboard'].proceed;

  
  /* response Values code*/
  
  if(typeof(responseArray) != 'undefined'){ // if there is an array already
    updateClickResponseValues("initiate");  
  }

  
  // identifying which response clicking on the element contributes to, e.g. - whether clicking on element1 contributes to Response1,Response2 etc.
  function updateClickResponseValues(x){
    
    // assume that the element is part of a new response. This will be checked later.
    newRespElement=true; // by default
    
    var currentElementName = $("#elementNameValue").val();
    
    responseValuesTidyId.innerHTML=""; // wipe the list  
    for(i=0; i<responseArray.length;i++){
      if(responseArray[i].indexOf(currentElementName)!=-1){
        newRespElement=false;
      }
    } 
    
    /* new Element being added to an array */        
    if(x!="initiate"){                                                    // don't load this at startup
      
      if(newRespElement==true & currentElementName!=""){                  // or on first click of an element
        responseArray[0][responseArray[0].length]=currentElementName;     //add it to the end of the first array in responseArray
        responseNoId.value=0;                                             // reset response number to zero (as it is being added to the first array)
      }
    
    }
    
    
    /* clear responseArray of null values and write out array in legible form  */
    for(i=0; i<responseArray.length; i++){    
      /* tidying */
      responseArray[i]  =   removeNullValues(responseArray[i]);
      
      /* could add code here to remove blank arrays, but be careful - user may have a blank array in the middle of the response array, which - if deleted, will mess up the order of the arrays. You have been warned. */
      
      /* writing out array in Responses area in form that is legible to user */
      responseValuesTidyId.innerHTML  +=  "Response "+i+":" +responseArray[i]+"<br>";
      
    }
    
    
    /* update trialTypeElements with input values */
     
    if(x!="initiate"){ // not relevant when initiating page
      trialTypeElements['elements'][currentElement]['responseValue']  =   responseValueId.value;
      trialTypeElements['elements'][currentElement]['responseNo']     =   responseNoId.value;
      updateTrialTypeElements();    
    }
    
    $("#responseValuesId").val(JSON.stringify(responseArray));
  }


  /* adjust position of element within responseArray */
  function adjustResponseOrder(){

    var newPos; // the position the element will fit within the array selected. E.g. if the element is added to response 1, newPos will be at the end of response 1.
    
    /* adding to array that already exists */
    if(typeof responseArray[responseNoId.value] != 'undefined'){
      newPos=responseArray[responseNoId.value].length;
    } else {
    
    /* creating a new array within responseArray */    
      responseArray[responseNoId.value]=[];
      newPos=0;
    }
      
    /* place null value where the element used to be (before being moved). This is tidied later. */
    for(i=0; i<responseArray.length;i++){
      if(responseArray[i].indexOf(elementNameValue.value)!=-1){
        responseArray[i][responseArray[i].indexOf(elementNameValue.value)] = null;
      }
    }
    
    //now that the element's been removed from it's original position, we can add it to the array.
    responseArray[responseNoId.value][newPos]   =   elementNameValue.value;  
    updateClickResponseValues(); // check if   
  }

  /* adding elements to the trialType or clicking on them for editing */
  
  function alertMouse(){ // can I break this down into multiple functions

    if(inputElementType!="select"){
      elementNo++; // we're not selecting an element, so we're creating one, which means we need a new element number.
      
      xPos  =  Math.round((_mouseX)/elementScale);
      yPos  =  Math.round((_mouseY)/elementScale);
      
      if(inputElementType=="input"){
        
        document.getElementById("trialEditor").innerHTML+=
          "<input class='inputElement' type='text' id='element"+elementNo+"' style='position: absolute; width:80px; left:"+_mouseX+"px;top:"+_mouseY+"px' onclick='clickElement("+elementNo+")' name='"+inputElementType+"' readonly>";  
        
      } else {
        document.getElementById("trialEditor").innerHTML+=
          "<span class='"+inputElementType+"Element' id='element"+elementNo+"' style='position: absolute; left:"+_mouseX+"px;top:"+_mouseY+"px; z-index:"+elementNo+"' onclick='clickElement("+elementNo+")' name='"+inputElementType+"'>"+inputElementType+"</span>";
      }
      
      //could take this object out - maybe
      trialTypeElements['elements'][elementNo] = {
        width                 :   20, 
        height                :   20,
        xPos                  :   xPos,
        yPosition             :   yPos,
        zPosition             :   elementNo,
        elementName           :   'element'+elementNo,
        stimulus              :   'not yet added',
        response              :   false,
        trialElementType      :   inputElementType, // repetition here
        clickOutcomesAction   :   '',
        clickOutcomesElement  :   '',
        proceed               :   false,
      };
      
      
      var elemIndex=trialTypeElements['elements'][elementNo];
      
      /* add attributes depending on what type of element */
      
      if(inputElementType ==  "media"){
        elemIndex['mediaType']="Pic"; // default assumption
      }
      
      if(inputElementType ==  "text" | inputElementType=="input"){
         // to allow more concise coding of the variables
        elemIndex['textSize']    =    12;
        elemIndex['textColor']   =    '';
        elemIndex['textFont']    =    '';
        elemIndex['textBack']    =    '';
      }
      
      if(inputElementType=="input"){
        elemIndex['userInputType']="text";
        elemIndex['height']="5"; //overwriting default height
      }
         
      updateTrialTypeElements();
    }
  }


  /* updating the trialType */
  
  backupTrialTypeName   =   trialTypeName.value;    //in case the user tries an illegal name
  var illegalChars      =   ['.',' '];              // this probably should be expanded
 
  function updateTrialTypeElements(){
      
    $("#trialTypeName").val(trialTypeName.value.replace(/ /g,""));
    var trialName   =   $("#trialTypeName").val(); //apply this to later code in place of "trialTypeName.value";
    
    var illegalCharPresent  =   false;
    for (i  = 0; i  < illegalChars.length; i++){
      if(trialName.indexOf(illegalChars[i])   !=  -1){
        alert("Illegal character in name, reverting to acceptable version");
        illegalCharPresent  =   true;
        trialName           =   backupTrialTypeName;
      }   
    }
    if(illegalCharPresent   ==    false){
      backupTrialTypeName  =  trialTypeName.value;    
    }
    trialTypeElements['trialTypeName']                  =   trialName;
    document.getElementById("elementArray").innerHTML   =   JSON.stringify(trialTypeElements,  null, 2);
    inputElementType                                    =   "select";
    elementType("select");
  }

  function changeMediaType(){
    trialTypeElements['elements'][currentElement]['mediaType']  =   userInputTypeValue.value;
    updateTrialTypeElements();
    // code here to change image cue if we include media images
  }

  function clickElement(x){
    if(inputElementType=="select"){
      $("#displayEditorButton").show(1000);
      $("#interactionEditorButton").show(1000);
      
      currentElement =  x;            // this is in order to update the global variable "currentElement";
      thisElement    =  "element"+x;
      
      for(i=0;i<=elementNo;i++){
        if(i==x){
          document.getElementById("element"+i).className    =   trialTypeElements['elements'][i]['trialElementType']  +   "ElementSelected";
        } else {
          
          if (trialTypeElements['elements'][i] != null) { //code to check whether the element exists or not
            document.getElementById("element"+i).className  =   trialTypeElements['elements'][i]['trialElementType']  +   "Element";
          }
        }
      }
      $("#configurationSettings").hide();
      $("#interactionEditorConfiguration").hide();
      $("#userInputSettings").hide();
      var targetElementID = "#displayEditor";
      $("#controlPanelItems > div").hide();
      $(targetElementID).show();

      loadConfigs(); // this loads the configurations for the editor
         
      
      currentElementAttributes=trialTypeElements['elements'][x]; // to simplify later code
      
      switch (currentElementAttributes['trialElementType']){
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
            '<table>'+ 
              '<tr>'+
                '<td>font size</td>'+
                '<td><input type="number" id="textSizeId" onchange="adjustTextSize()" value=12 min="1" style="width:50px">px</td>'+
              '</tr>'+
              '<tr>'+
                '<td>color</td>'+
                '<td><input type="text" id="textColorId" onkeyup="adjustTextColor()" placeholder="color"></td>'+
              '</tr>'+
              '<tr>'+
                '<td>font</td>'+
                '<td><input type="text" id="textFontId" onkeyup="adjustTextFont()" placeholder="font"></td>'+
              '</tr>'+
              '<tr>'+
                '<td>background-color</td>'+
                '<td><input type="text" id="textBackId" onkeyup="adjustTextBack()" placeholder="background-color"></td>'+
              '</tr>'
            '</table>';
                 
          //rather than embed it in above text, i've listed these values below for improved legibility
          textFontId.value   =  currentElementAttributes.textFont;
          textColorId.value  =  currentElementAttributes.textColor;
          textSizeId.value   =  currentElementAttributes.textSize;
          textBackId.value   =  currentElementAttributes.textBack;
          
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
            '<table>'+
              '<tr>'+
                '<td>size</td>'+
                '<td><input type="number" id="textSizeId" onchange="adjustTextSize()" value=12 min="1" style="width:50px">px</td><br>'+
              '</tr>'+
              '<tr>'+
                '<td>color</td>'+
                '<td><input type="text" id="textColorId" onkeyup="adjustTextColor()" placeholder="color" ></td>'+
              '</tr>'+
              '<tr>'+
                '<td>font</td>'+
                '<td><input type="text" id="textFontId" onkeyup="adjustTextFont()" placeholder="font"></td>'+
              '</tr>'+
              '<tr>'+
                '<td>background-color</td>'+
                '<td><input type="text" id="textBackId" onkeyup="adjustTextBack()" placeholder="background-color"></td>'+
              '</tr>'+
            '</table>';

          //rather than embed it in above text, i've listed these values below for improved legibility
          textFontId.value          =   currentElementAttributes.textFont;
          textColorId.value         =   currentElementAttributes.textColor;
          textSizeId.value          =   currentElementAttributes.textSize;
          textBackId.value          =   currentElementAttributes.textBack;
          document.getElementById("userInputTypeValue").value  =   currentElementAttributes.userInputType;

            
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

  currentElementAttributes=trialTypeElements['elements'][currentElement]; //to make following code more concise

  /* deciding which part of interactionEditor to show  - this may need to be more flexible when more interactive features are added*/ 
  if (currentElementAttributes.clickOutcomesAction=="response"){
    $("#clickOutcomesElementId").hide();
    $("#responseValueId").show();
    $("#respNoSpanId").show();
    
    responseValueId.value = currentElementAttributes.responseValue;
    responseNoId.value    = currentElementAttributes.responseNo;
    updateClickResponseValues();
  
  } else {
    $("#clickOutcomesElementId").show();
    $("#responseValueId").hide();
    $("#respNoSpanId").hide();
    populateClickElements();    
  }

  elementNameValue.value        =   currentElementAttributes.elementName;
  userInputTypeValue.value      =   currentElementAttributes.mediaType;
  stimInputValue.value          =   currentElementAttributes.stimulus;
  elementWidth.value            =   currentElementAttributes.width;
  elementHeight.value           =   currentElementAttributes.height;
  
  /* positions */
  xPosId.value                  =   currentElementAttributes.xPos;
  yPosId.value                  =   currentElementAttributes.yPosition;
  zPosId.value                  =   currentElementAttributes.zPosition; 
  
  /* click events */
  clickOutcomesActionId.value   =   currentElementAttributes.clickOutcomesAction;
  clickOutcomesElementId.value  =   currentElementAttributes.clickOutcomesElement;

  /* Timings */
  if(typeof(currentElementAttributes.onsetTime) == 'undefined'){
    onsetId.value           =   "00:00:00.000";
    onsetId.style.color     =   "grey";
  } else {    
    onsetId.value           =   currentElementAttributes.onsetTime; 
    onsetId.style.color     =   "blue";    
  }
  
  if(typeof(currentElementAttributes.onsetTime) == 'undefined'){
    offsetId.value           =   "00:00:00.000";
    offsetId.style.color     =   "grey";
  } else {
    offsetId.value           =   currentElementAttributes.offsetTime; 
    offsetId.style.color     =   "blue";    
  }  
}



function populateClickElements(){
  removeOptions(document.getElementById("clickOutcomesElementId"));   

  var option                      =   document.createElement("option");
  option.text                     =   '';
  document.getElementById("clickOutcomesElementId").add(option);
  clickOutcomesElementId.value    =   trialTypeElements['elements'][currentElement].clickOutcomesElement;  
  var elementList                 =   [];

  
  console.dir(trialTypeElements['elements']);
  for(x in trialTypeElements['elements']){
    
    // here be the bug //
    
    if (trialTypeElements['elements'][x] != null){
      elementList.push(trialTypeElements['elements'][x].elementName); //may become redundant
      var option    =   document.createElement("option");
      option.text   =   trialTypeElements['elements'][x].elementName;
      option.value  =   trialTypeElements['elements'][x].elementName; 
      document.getElementById("clickOutcomesElementId").add(option);  
    }
  }
}

/* Which element type are you adding to the trial */
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

      currentStimType.innerHTML="No Element Selected";
      // for all elements revert formatting to element
      for(i=0;i<=elementNo;i++){
          if (typeof trialTypeElements['elements'][i] != 'undefined') { //code to check whether the element exists or not
            document.getElementById("element"+i).className=trialTypeElements['elements'][i]['trialElementType']+"Element";
          }      
      }
    }  
  }


  /* mouse functions */
  
  function getPositions(ev) { 
  if (ev == null) { ev = window.event }
    var offset = $("#trialEditor").offset(); 
    _mouseX = ev.pageX;
    _mouseY = ev.pageY;
    _mouseX -= offset.left;
    _mouseY -= offset.top;
     
  }

  function mouseMovingFunctions(){
    if(inputElementType=="select"){
      
      /* text element style */
      var css   =  '.textElement:hover{ border-color        :     black;'+
                                        'background-color   :     transparent;'+
                                        'text-shadow        :     -1px -1px 0 #000,1px -1px 0 #000,-1px 1px 0 #000,1px 1px 0 #000; }';
      
      applynewStyle();
 
      /* media element style */
      var css   =   '.mediaElement:hover{ border-color      :     white;'+
                                          'background-color :     green;'+
                                          'color            :     white }';
      
      applynewStyle();

      
      /* input elemnt style */
      var css   =   '.inputElement:hover{ border-color      : green;'+
                                          'background-color : green;'+
                                          'color            : blue }';
                                          
      applynewStyle();
   }     
      
     
    /* change all element styles to nonHover version */ 
    if(inputElementType!="select"){
      //text elements
      var css   =   '.textElement:hover{  border-color      :   transparent;'+
                                          'background-color :   transparent;'+
                                          'color            :   blue}';
      applynewStyle();

      //media elements
      var css   =   '.mediaElement:hover{ border-color      :   blue;'+
                                          'background-color :   transparent;'+
                                          'color:blue }';
      applynewStyle();
      
      //input elements
      var css   =   '.inputElement:hover{ border            :   1px solid #cccccc;'+
                                          'background-color :   white;'+
                                          'color            :   white}';
      applynewStyle();
    }
    //keeping this function local;
    function applynewStyle(){
      style = document.createElement('style');

      if (style.styleSheet) {
          style.styleSheet.cssText = css;
      } else {
          style.appendChild(document.createTextNode(css));
      }
      document.getElementsByTagName('head')[0].appendChild(style);  
    }
  }


  

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
</script>
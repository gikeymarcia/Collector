<?php

  require "../../../initiateTool.php";
  require_once("../guiFunctions.php");
  
  
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

<link rel="stylesheet" href="trialTypeEditor.css">
<?php     
  
  if(!isset($_DATA['trialTypeEditor']['trialTypeName'])){         //  If there is no trialType Name yet
    $_DATA['trialTypeEditor']['trialTypeName']  = '';             //  Create one
  }

  $trialTypeName =& $_DATA['trialTypeEditor']['trialTypeName'];   // for legibility

  define('TRIAL_DIR',"newTrialTypes");                            // creating a variable that is accessible within functions

  
  /* * * * * * * *
  * Configurations
  * * * * * * * */

  $elementScale   =   8; // as the interface for inserting elements if 800px x 800px, and we are scaling to a 100% height or width (800/100 = 8)
  
  
  $trialTypeElementsPhp;

  // load function //
  
  function loadTrialType($filename){
        
    $file_contents         =  file_get_contents(TRIAL_DIR."/".$filename);
    $trialTypeElementsPhp  =  json_decode($file_contents);
    return  $trialTypeElementsPhp;
  }

  // save function  //
  
  function saveTrialType($elementArray,$trialTypeName){
  
    global $_PATH;
    
    $trialTypeElementsPhp=json_decode($elementArray);  
            
    // Renaming files if task name has changed 
    if($trialTypeName !== ''){   // checking if there is there a long term value for the name to check against
      /* does the new name match the old name*/
      if($trialTypeName !== $trialTypeElementsPhp->trialTypeName){                          //i.e. a new trialType name
        if(file_exists(TRIAL_DIR."/"           .     $trialTypeName . ".txt")){
          unlink(TRIAL_DIR."/"                 .     $trialTypeName . ".txt");              //Delete original file here            
          unlink($_PATH->get('Custom Trial Types')."/". $trialTypeName . "/display.php");   //deleting php file
          $trialTypeCodeDir=$_PATH->get('Custom Trial Types')."/". $trialTypeName;          
          if(count(scandir($trialTypeCodeDir)) <= 2){                                       //if empty
            rmdir($_PATH->get('Custom Trial Types') ."/". $trialTypeName);                  //deleting directory. Don't do this if score.php or other file present.
          }
        }
        $trialTypeName  = $trialTypeElementsPhp->trialTypeName;                             //identify correct name here
      }  
    }
    
    // saving schematic of task (.txt) and task (.php)
    if(!is_dir(TRIAL_DIR)) mkdir($dir, 0777, true);
    
    file_put_contents(TRIAL_DIR."/".$trialTypeName.'.txt',$elementArray); //actual act of saving
    
    if(!isset($_DATA['trialTypeEditor']['currentTrialTypeName'])){
      $_DATA['trialTypeEditor']['currentTrialTypeName']=$trialTypeName;
    }      
    
    require('createTrialType.php');                                      //php file
    
    return $trialTypeElementsPhp;
  }
  
  
  /*sorting out whether we are working from scratch, have just saved a file, or are loading a file */
  
  /* loading */
  if(isset($_POST['loadButton']) || isset($_POST['editTrialType'])){   //load first

    if(isset($_POST['loadButton'])){
      $trialTypeElementsPhp   = loadTrialType($_POST['trialTypeLoaded']);
      $trialTypeName          = str_replace('.txt','',$_POST['trialTypeLoaded']);
    } 
    if(isset($_POST['editTrialType'])) {
      $trialTypeElementsPhp   = loadTrialType($_POST['editTrialTypeName']);
      $trialTypeName          = str_replace('.txt','',$_POST['editTrialTypeName']); 
    }
      
  } else {
    /* saving */
    if(!empty($_POST['elementArray'])){ // have just saved
       
      $trialTypeElementsPhp = saveTrialType($_POST['elementArray'],$trialTypeName);

    } else { 
      // creating a new file //
      if($_POST['createTrialTypeName']=="[Blank]"){
        require ("guiClasses.php"); //not sure we need this;  maybe the guiClasses can be tidied up  
        $trialTypeElementsPhp = new trialTypeElements();
        
      } else {
        // creating from template
        
        
      }
      
      $trialTypeName        = $_POST['newTrialTypeName'];
    }
  }    
  $jsontrialTypeElements  =   json_encode($trialTypeElementsPhp);   //to use for javascript manipulations
     
  // list of trial types the user can edit
  if (!is_dir(TRIAL_DIR)) {
      $trialTypesList = array();
  } else {
      $trialTypesList = scandir(TRIAL_DIR);
      $trialTypesList = array_slice($trialTypesList,2);
  }
  
  ?>

<form method="post" action="index.php">
  <textarea id="trialTypeName" placeholder="[insert name of trial type here]" onkeyup="updateTrialTypeElements()"><?php 
echo $trialTypeName
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
    <span style="position:relative; left:520px">

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
          <input type="submit" id="loadButton" name="loadButton" class="collectorButton" value="Load">
      <?php
      }
    ?>  

      </span>

    </div>

  <div id="trialEditor" onMouseMove="mouseMovingFunctions()" onclick="getPositions(); tryCreateElement()">

<?php

  foreach($trialTypeElementsPhp->elements as $elementKey=>$element){
    if($element!=NULL){ //ideally I'll tidy it up so that there are no null elements 
      // identify if deleted or not //
      $delete='';
      if(isset($element->delete)){
        $delete="display:none;";
      }
      
      /* identify if input or other type of element */
      if(isset($element->userInputType)){
                
        echo "<input id='element$elementKey' type='".$element->userInputType."'";
      } else {
        echo "<div id='element$elementKey' class='".$element->trialElementType."Element'";
      }
      echo "    style='position:absolute;
                width   : ".($elementScale*$element->width)."px;
                height  : ".($elementScale*$element->height)."px;
                left    : ".($elementScale*$element->xPosition)."px;
                top     : ".($elementScale*$element->yPosition)."px;
                $delete
                ";
      if (isset($element->textColor)){
        echo "color:$element->textColor;
              font-family:$element->textFont;
              background-color:$element->textBack;
              font-size:".($element->textSize)."px;"; // look into this when I've finalised spacing for interfaces
      }
      echo "'   onclick       =   'clickElement($elementKey)'";
      if(isset($element->userInputType)){
        if($element->userInputType=="Text"){
          echo "placeholder   =   '".$element->stimulus."' readonly>";
        } else {  // it's a "Button"
          echo "value         =   '".$element->stimulus."'>";
        }
      }else {
        // it's not an input, so it's a div we're writing
        echo ">".$element->stimulus."</div>";        
      }
    }
  }
  

  
?>
</div>

<!-- the helper bar !-->
<div id="controlPanel">
  <div id="controlPanelRibbon">
    <button type="button" class="collectorButton" value="#displayEditor"      style="display:none"  id="displayEditorButton">Display </button>
    <button type="button" class="collectorButton" value="#interactionEditor"  style="display:none"  id="interactionEditorButton">Interaction </button>
    <button type="button" class="collectorButton" value="#keyboardResponses"                        >Keyboard           </button>
    <button type="button" class="collectorButton" value="#responseInputs"                           >Responses          </button>
  </div>  
  
  
  <div id="controlPanelItems">
    <div id = "responseInputs">
      <h1> <b>Click Responses</b> that you have coded </h2>
      <textarea name="responseValues" id="responseValuesId" style="display:none"></textarea>
      <div id="responseValuesTidyId" class="elementProperty"></div>
    </div>

    <div id="displayEditor">
      <h1>Display editor <br><span style="font-size:20px" id="currentStimType">No Element Selected</span></h1>
      <table id="displaySettings" style="display:none">
        <tr title="we may allow editing of element names in a later release but without careful coding it can make code break easily">
          <td class="elementProperty">Element Name</td>
          <td><input type="text" id="elementNameValue" onkeyup="adjustElementName()" readonly style="background-color:#d3d3d3"></td>
        </tr>
        <div id="userInputSettings">
          <tr title="If you want to refer to a stimulus list then write '$cue' or '$answer' etc.">
            <td id="inputStimTypeCell" class="elementProperty">Input Type</td>
            <td id="inputStimSelectCell">
              <select style="padding:5px" id="mediaTypeValue" onchange="changeMediaType()">
                <option>Pic</option>
                <option>Audio</option>
                <option>Video</option>
              </select>
            
              <select id="userInputTypeValue" onchange="adjustUserInputType()">
                <option>Text</option>
                <option>Button</option> 
              </select>
            </td>
          </tr>
        </div>
        
        <tr title="If you want to refer to a stimulus list then write '[stimulus1]' or '[stimulus2]' etc.">
          <td class="elementProperty">Stimulus</td>
          <td><input id="stimInputValue" type="text" onkeyup="adjustStimulus()"></td>
        </tr>
        <tr>
          <td class="elementProperty">Width</td><td><input id="elementWidth" type="number" value="20" min="1" max="100" onchange="adjustWidth()">%</td>
          <td></td>
        </tr>
        <tr>
          <td class="elementProperty">Height</td><td><input id="elementHeight" type="number" value="20" min="1" max="100" onchange="adjustHeight()">%</td>
          <td></td>
        </tr>
        <tr title="this position is based on the left of the element">
          <td class="elementProperty">X-Position</td>
          <td><input id="xPosId" type="number" min="1" max="100" step="1" onchange="adjustXPos()">%</td>
          <td></td>
        </tr>
        <tr title="this position is based on the top of the element">
          <td class="elementProperty">Y-Position</td>
          <td><input id="yPosId" type="number" min="1" max="100" step="1" onchange="adjustYPos()">%</td>
          <td></td>
        </tr>
        <tr title="change this value to bring the element to forward or backward (to allow it to be on top of or behind other elements">
          <td class="elementProperty">Z-Position</td>
          <td><input id="zPosId" type="number" step="1" min="0" onchange="adjustZPos()"></td>
        </tr>
        <tr title="how long do you want until the element appears on screen?">
          <td class="elementProperty">Onset time</td>
          <td><input id="onsetId" placeholder="seconds" class="onsetOffset" type="text" onkeyup="adjustTime('onset')"></td>
        </tr>
        <tr title="if you want the element to disappear after a certain amount of time, change from 00:00">
          <td class="elementProperty">Offset time</td>
          <td><input id="offsetId" placeholder="seconds" class="onsetOffset" type="text" onkeyup="adjustTime('offset')"></td>
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
            <td class="elementProperty">Click outcomes:</td>
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
              <span id="respNoSpanId" title="If you want multiple elements to contribute to the response, order the responses in the order you want them in the output" style="display:none"> <br>Resp No <input id="responseNoId" type="number" min="0" value="1" style="width:50px" onchange="adjustResponseOrder(trialTypeElements['responses']); adjustClickOutcomes() ">
              </span>
            </td>
          </tr>   
          <tr>
         <!--   <td> <input type="button" class="collectorButton" id="addDeleteFunctionButton0" value="Add Function" onclick="addDeleteFunction(0)"> </td> !-->
          </tr>
          <tr>
            <td class="elementProperty">Proceed Click</td>
            <td><input id="clickProceedId" title="if you want the trial to proceed when you click on this element, check this box" type="checkbox" onclick="adjustClickProceed()"></td>
          </tr>
        </table>
      </div>
    </div>
    


  </div>  
</div>

  <textarea id="elementArray" name="elementArray"></textarea>

</form>

<script src="trialTypeFunctions.js"></script>
<script src="trialTypeEditor.js"></script>

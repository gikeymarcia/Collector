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
    // start the session, load our custom functions, and create $_PATH
    $title = 'Collector GUI';
    $branches = getCollectorExperiments();
  
  	unset ($_DATA['guiSheets']['csvSelected']);
    
    $gradient1="#ADD8E6";
    $gradient2="#E0FFFF";
    
?>
<style>
  .guiTasks { display: inline-block; }
  .guiTasks input {width: 120px; margin: 20px; }
  .buttonRows {text-align: right; max-width: 800px; margin: auto;}
  .editorType{
    background: <?=$gradient1?>                                                       ; /* For browsers that do not support gradients */
    background: -webkit-linear-gradient(left top, <?=$gradient1 ?>, <?=$gradient2?>)  ; /* For Safari 5.1 to 6.0 */
    background: -o-linear-gradient(bottom right, <?=$gradient1 ?>, <?=$gradient2?>)   ; /* For Opera 11.1 to 12.0 */
    background: -moz-linear-gradient(bottom right, <?=$gradient1 ?>, <?=$gradient2?>) ; /* For Firefox 3.6 to 15 */
    background: linear-gradient(to bottom right, <?=$gradient1 ?>, <?=$gradient2?>)   ; /* Standard syntax */
    padding           : 15px;
    border-radius     : 50px;
  }
</style>


<?php

  //create select lists
  
  $selectArray        = array();
  $illegalStudyNames  = array('.','..','New Experiment','Common');
  $branchStudyKey     = array();
  $editStudySelect    = '';
  $newStudySelect     = '';
  
  // creating lists for studies

  foreach ($branches as $study) {
    $branch           = $study;
    $illegalStudyName = true;
    
    if(in_array($study,$illegalStudyNames)  !=1 & stripos($study,'.') ==  false){    //don't look at studies that exist within illegalStudyNames array
      $illegalStudyName = false;
    }  
        
    $branchStudyKey[$study] = $branch;
        
    if( $illegalStudyName == false) {   $editStudySelect  = $editStudySelect."<option>$study</option>";   }
    $newStudySelect   = $newStudySelect."<option>$study</option>";
        
  }
 
 // creating list for surveys

  $surveySelect = '';
  foreach (glob($_PATH->get("Common")."/Surveys/*.csv") as $survey) {
    $survey = str_ireplace($_PATH->get("Common")."/Surveys/","",$survey);         // remove directory from filename
        
    $surveySelect = $surveySelect."<option>$survey</option>";  
  }

  // creating list for trial types

  $trialTypeSelect = '';
  foreach (glob($_PATH->get("Tools")."/GUI/trialTypeEditor/newTrialTypes/*.txt") as $trialType) {
    $trialType = str_ireplace($_PATH->get("Tools")."/GUI/trialTypeEditor/newTrialTypes/","",$trialType);   // remove directory from filename

    $trialTypeSelect = $trialTypeSelect."<option>$trialType</option>";  
  }
  
  $branchStudyKeyJson=json_encode($branchStudyKey);

?>

<div class="buttonRows">
  <div class = "guiTasks">
  <form action="sheetsEditor/index.php" method="post">
    <div class="editorType">
      <div class="buttonRows">
        Which study do you want to edit?
        <select name="editStudyName" onchange="updateGuiStudyName()">
          <?= $editStudySelect ?>
        </select>
      
        <input class="collectorButton" type="submit"  value="Edit Study" name="editStudy" title="This tool edits the spreadsheets for your study">
      </div>
      
      <div class="buttonRows">
        Or select a study to base a NEW study on:
        <select name="createStudyName">
          <?= $newStudySelect ?>
        </select>
        <input id="newStudy" class="collectorButton" type="submit" value="New Study" name="createStudy" title="This tool edits the spreadsheets for your study">
      </div>
    </div>
    <textarea id="newStudyName" name="newStudyName" style="display:none"></textarea>

  </form>
  
  <br><br>
  
  <form class="editorType" action="surveyEditor/index.php" method="post">
  
      <div>
        Do you want to use the Survey/Questionnaire editor?        
        <select name="editSurveyName">
          <?= $surveySelect ?>
        </select>
        <input type="submit" name="editSurvey" value="Edit Survey" class="collectorButton" >
      </div>

      <div>
        Or select a Survey/Questionnaire to base a NEW survey on:        
        <select name="createSurveyName">
          <?= $surveySelect ?>
        </select>
        <input id="newSurvey" name="createSurvey" type="submit" value="Create Survey" class="collectorButton" >
      </div>
    <textarea id="newSurveyName" name="newSurveyName" style="display:none"></textarea>
  </form>
  
  <br>
  <br>
  
  <form action="trialTypeEditor/index.php" method="post">  
    <div class="editorType">
      <div class= "buttonRows">
        Do you want to edit a trial type?
        <select name="editTrialTypeName">
          <?= $trialTypeSelect ?>
        </select>
        <input type="submit" name="editTrialType" value="Edit" class="collectorButton">
      </div>
    

      <div class= "buttonRows">
        Or select a trial type to base a NEW trial type on:
        <select name="createTrialTypeName">
          <option>[Blank]</option>
          <?= $trialTypeSelect ?>
        </select>
        <input id="newTrialType" type="submit" name="createTrialType" value="Create" class="collectorButton">
      </div>
    </div>
    <textarea id="newTrialTypeName" name="newTrialTypeName" style="display:none"></textarea>

  </form>
</div>

<script>

$("#newStudy").on("click",function(event){
  newName(event,"newStudyName");
});

$("#newSurvey").on("click",function(event){
  newName(event,"newSurveyName");
});

$("#newTrialType").on("click",function(event){
  newName(event,"newTrialTypeName");
});


function newName(event,nameType){
  var newName = prompt("What would you like to name this?", "");
  if (newName != null && newName.length  > 0) {
    document.getElementById(nameType).value =  newName;
  } else {
    event.preventDefault();
  }
}
</script>
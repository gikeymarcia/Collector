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
    // start the session, load our custom functions, and create $_PATH
    require '../Code/initiateCollector.php';
    	
    $title = 'Collector GUI';
    require $_PATH->get('Header');


//updating gui.txt based on instructions passed

$guiFileLoc = "../Experiments/".$_SESSION['studyName'].'/gui.txt';
$jsonGUI=file_get_contents($guiFileLoc);
$guiArray=json_decode($jsonGUI);

//acquire list of keys
$postKeys = array_keys($_POST);

// instructions will be selected event
$groups =$guiArray->studyGroups;
$selectedEvent = "$guiArray->selectedEvent";
$eventInfo=$guiArray->$selectedEvent;
echo "<br>";
$eventInfo->eventName=$_POST['eventName']; //changing name if the user changed name

//identify groups in post keys
$countGroups=0;
foreach ($postKeys as $postKey){
	if(strpos($postKey,'GroupName')!==false){
		$countGroups++;
	}
}
//echo $countGroups."<br>";

$postGroups=array();
$postRadioSelections=array();
for ($i=1;$i<=$countGroups; $i++){
	//working out what the current name of each group is
	$thisGroupNameInput = "GroupName".$i;
	$thisGroupName=$_POST[$thisGroupNameInput];
	//$postGroups[$i]=$thisGroupName;
	
	//working out what the selection of each group is
	$thisGroupRadioInput="group".$i;
	$thisGroupRadio=$_POST[$thisGroupRadioInput];
	//$postGroupRadios[$i]=$thisGroupRadio;
	
	array_push($postGroups,$thisGroupName);
	array_push($postRadioSelections,$thisGroupRadio);
	
	
}

$guiArray->studyGroups=$postGroups;
$eventInfo->eventDetails->groupSelected=$postRadioSelections;

//$eventInfo->
// add instructions into eventInfo

$InstructionKeys=array();
foreach ($postKeys as $postKey){
	if(strpos($postKey,'instructionsText')!==false){
		array_push($InstructionKeys,$postKey);
	}
}

$postInstructions=array();
foreach  ($InstructionKeys as $instructionKey){
	
	//working out what the current name of each group is
	$thisInstruction=$_POST[$instructionKey];
	array_push($postInstructions,$thisInstruction);
	
}

// add $eventInfo into guiarray
$eventInfo->eventDetails->instructions=$postInstructions;
$guiArray->$selectedEvent=$eventInfo;


//save file

$jsonGUI=json_encode($guiArray);

file_put_contents($guiFileLoc,$jsonGUI);


//proceed with first task creation



?>
<form action="postFirstTask.php" method="post">
<select name="trialType"><option>Cue</option><option>Dropdown list of trial types </option><select>
<div> preview of task here </div>
<div><?php require ("cue.php") ?></div>
   <button class="collectorButton" id="submitButton" type="button">Submit</button>
 <!--   <button class="collectorButton" id="resetButton"  type="button">Reset</button> !-->
 
</form>

<script>

// Code to submit or reset stuff
    
    $("#submitButton").on("click", function() {
        $("input[name='stimTableInput']").val(JSON.stringify(stimTable.getData()));
        $("form").submit();
    });
    $("#resetButton").on("click", function() {
        $("input[name='stimTableInput']").remove();
        $("form").submit();
    });

</script>
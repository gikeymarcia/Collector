 <style>
    .studyList { position: absolute; top: 50px; left: 10px; }
    .trialList { position: absolute; top: 10px}
	body { flex-flow: row; }
    .leftCol { padding-right: 100px; }
	.eventDiv {
		width:80%;
		float:right;
	}
	textarea {
		resize: none;
	}
</style>
<?php 
/*  

To Do List:
- allow user to go straight to this page
	-this involves allowing them to save a json-encoded file



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
    
    // now lets get a list of possible experiments to edit
    $branches = getCollectorExperiments();
	
	
    $title = 'Collector GUI';
    require $_PATH->get('Header');
	
	//print_r($_POST);
	
	if (isset($_POST['guiStudyName'])){
		$studyName=$_POST['guiStudyName'];
	} else {
		$studyName=$_SESSION['studyName'];
	}
 	$guiFileLoc = "../Experiments/".$studyName.'/gui.txt';
	
	$jsonGUI=file_get_contents($guiFileLoc, false);
	$guiArray=json_decode($jsonGUI);
	
	//print_r($guiArray);
//	$_SESSION('groups')=$guiArray->studyGroups;
	
	
	$trialTypeList=["Instruction","Cue"];// put in Tyson's code listing all posstible trial types!!!
	
	//identify each event
	$listingEventsComplete=0;
	$eventList=array();
	$eventNo=0;
	while ($listingEventsComplete==0){
		$eventNo++;
		$checkedEvent = "event".$eventNo;
		if (isset($guiArray->$checkedEvent)){
			array_push($eventList,$checkedEvent);
		} else {
			$listingEventsComplete=1;
		}
	}
	$eventsJson = json_encode($eventList);
	
	// create event flow
	$eventFlow=array();
	foreach($eventList as $event){
		array_push($eventFlow,$guiArray->$event->eventName);
	}
	
	
	
	?>
<form action="guiSave.php" method="post">

<div class="studyList">
	<div>

	<textarea name="StudyName" rows='1' style="  border-radius: 4% 4% 4%; opacity: .95; background:#A9D0F5;   text-align:center; color:#069; font-size:40px; font-weight:bold; width:210;  "><?=$studyName ?></textarea>	
	</div>
	<ul id="sortable">
		<?php 
			$eventNo = 0;
			foreach($eventFlow as $thisRow){
				$eventNo++;
				echo "<li class='collectorButton changeScreen' id='event".$eventNo."' >".$thisRow."</li>";
			}
		?>
	</ul>
  

	<button class='collectorButton' id="newTask" > + </button>
</div>

<input type="button" class="collectorButton" style="position:absolute; right: 10px; top: 10px" value="Preview" onclick="previewAction()">




<div id="mainInterface"> 

<?php 

// for each event, create a div that includes the event information, and only show the event which is the selected event
	//print_r($eventList);
	
	foreach($eventList as $event){
		$trialTypeRequired=$guiArray->$event->chosenTrialType;
		$trialTypeRequired=$trialTypeRequired.'.php';
		$eventInfo=$guiArray->$event;
		echo "<div class='eventDiv' id='".$event."div'";
		
		//workout here what the problem is!!!
		
		
		
		if ($event==$guiArray->selectedEvent){
			echo ">";
		} else {
			echo "style='display:none;' >";
		}		
		//echo $event;
		
		require($trialTypeRequired);
		echo "</div>";		
	}	
?>


<button id="submitButton" class="collectorButton">Save</submit>
</form>

	</div>
</div>

 
<script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
 <!-- including the following for excel functionality !-->
 

 <script type="text/javascript">
//hide trial info as default



$("#submitButton").on("click", function() {
	//include foreach code here for each event to capture!//
	$("input[name='stimTableInput']").val(JSON.stringify(stimTable.getData()));
	$("form").submit();
});

$(function() {
    $( "#sortable" ).sortable();
    //$( "#sortable" ).disableSelection();
  });
$("#newTask").click (function(){
	
		var newTaskName = prompt("What do you want to call this task?");
		// insert task at bottom of list
		document.getElementById("sortable").innerHTML=document.getElementById("sortable").innerHTML + "<li class='collectorButton changeScreen' id='"+newTaskName+"' >" +newTaskName + "</li>" ;
		
		for (i=0; i<eventList.length; i++){
			idToHide="#"+eventList[i]+'div';
			//alert("hide "+idToHide);
			$(idToHide).hide();
		}
		var mainInterfaceText = document.getElementById("mainInterface").innerHTML
		document.getElementById("mainInterface").innerHTML=(mainInterfaceText+"<div id=test>Select which trial type you would like the event to be: <br><select name ='newTrialTypeSelect' ><option>instructions</option><option>cue</option></select></div><input type='button' class='collectorButton' value='Select' onclick='newTrialType()'></button>");
		
	 
});

function newTrialType(){
	alert ("selecting new trial type now");
	//delete everything in the new div, and create again with information required for trial type
};

// need to list the events here!
var eventList = <?= $eventsJson ?>;

var jsonGui = <?= $jsonGUI ?>;

	
function previewAction(){
	alert ("this will open a new window with a preview of whichever trial type is being edited");
}

//// RESUME HERE WITH HIDING WORK!!! ///



$(".changeScreen").click(function(){	
	for (i=0; i<eventList.length; i++){
		if(eventList[i]==this.id){
			idToShow="#"+eventList[i]+'div';
			$(idToShow).show();
		} else {
			idToHide="#"+eventList[i]+'div';
			//alert("hide "+idToHide);
			$(idToHide).hide();
		}
	}	
});
$("#trialType").change (function(){
	if(this.value=="Instruction"){
		var internalHTML = "<textarea> </textarea>" + "<input type='checkBox' />";
	}
	if(this.value=="Cue"){
		var internalHTML = "cue input will go here. To go list - excel sheets for stimuli";
	}
		document.getElementById("mainInterface").innerHTML = internalHTML;
	
	
});
//$("#cueTrialInfo").hide();
</script>


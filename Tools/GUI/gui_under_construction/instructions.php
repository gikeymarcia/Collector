<style>
.instrucChosen {
width: 30px;
height: 20px;
background: green;
border-radius: 5px;
padding-right:10px;
padding-left:10px;
color: white;
}
.instrucNonChosen {
width: 30px;
background: #069;
height: 20px;
border-radius: 5px;
padding-right:10px;
padding-left:10px;
color: #069;
}
.instrucNonChosen:hover {
	background: green;
	color: white;
}
textarea { border: none; }
</style>
<?php 

	$groups =$guiArray->studyGroups;
	$selectedEvent = "$guiArray->selectedEvent";


if (!isset ($event)){
	$eventInfo=$guiArray->$selectedEvent;
}


 #list conditions horizontally 

//confirm that the selected event is an instruction 
/* if(strcmp($eventInfo->chosenTrialType,"instructions") != 0){
	die("this is not the correct trial type"); // should never get here
} */

$groupNo=0;

?>
<h1 contenteditable='TRUE' id="eventHeader"></h1>
<h1><textarea name="eventName" style="color:#069" rows="1"><?php echo $eventInfo->eventName ?></textarea></h1>
<br><br> 

What instructions do you want your participants to see? <br>
<table style="width:100%;text-align:center" id="instructionTable"> 
<tr id="groupRow">
<td></td>


<?php 
foreach($groups as $group){
	$groupNo++;
	$group=str_replace(' ','',$group);
	echo "<td><h3><textarea name='GroupName".$groupNo."' style='text-align:center; color:#069' id='".$group."' rows='1' cols='".(strlen($group)+4)."' onkeyup='textAreaAdjust(this)' name='".$group."'>".$group."</textarea></h3></td>";
}
echo '<td><input type="button" class="newGroup collectorButton" id="addGroup" value=" + "/></td></tr>';

$instructions=$eventInfo->eventDetails->instructions;

//insert previously inserted instructions if referring to an old file	
if(empty($instructions)){
	$instructions=array("");
	$rowNo=1; //for some reason, putting in a 1 doesn't work.
	echo '<tr id="tr'.$rowNo.'"><td><textarea name="instructionsText1" id="Instructions 1" onkeyup="textAreaAdjust(this)" placeholder="[type your instructions here]"></textarea></td>';
	$groupPos=-1; //this is in place of an i variable
	foreach($groups as $group){
		$group=str_replace(' ','',$group);
		$groupPos++;
		echo  "<td><label> <input type='radio' name='group".($groupPos+1)."' value='".$rowNo."' id='".$group.$rowNo."' onclick='changeRadio(".$groupPos.",".$rowNo.")' style='display:none;'><span class='instrucNonChosen' id='label".$group.$rowNo."' >&#10003</span></label></td>";		
		}
	echo "</tr>";
} else {
	$rowLine=-1;
	
	//create an array of unique row numbers - this deals with when a row has been deleted
	$rowUnique=array_unique($eventInfo->eventDetails->groupSelected);
	$rowUnique=array_values($rowUnique); // otherwise the array keys may jump e.g. from 0 to 2.	
	
	//print_r($rowUnique);
	
	
	foreach($instructions as $instruction){
		$rowLine++;
		$rowNo=$rowUnique[$rowLine];
		
		//rowNo needs to be defined here based on instruction no.
		
		//inserting previously requested instructions and delete button when multiple instructions
		echo '<tr id="tr'.$rowNo.'"><td><textarea name="instructionsText'.$rowNo.'" id="Instructions'.$rowNo.'"  onkeyup="textAreaAdjust(this)">'.$instruction.'</textarea></td>';
		## list the key variables
		$groupPos=-1; //this is in place of an i variable
		foreach($groups as $group){
			$group=str_replace(' ','',$group);
			$groupPos++;
			$groupSelectedArray=$eventInfo->eventDetails->groupSelected;
			
			if ($rowNo == $groupPos+1){
				echo  "<td><label> <input type='radio' name='group".($groupPos+1)."' value='".$rowNo."' id='".$group.$rowNo."' onclick='changeRadio(".$groupPos.",".$rowNo.")' checked style='display:none;'><span class='instrucChosen' id='label".$group.$rowNo."' >&#10003</span></label></td>"; 
			} else {
				echo  "<td><label> <input type='radio' name='group".($groupPos+1)."' id='".$group.$rowNo."' value='".$rowNo."' onclick='changeRadio(".$groupPos.",".$rowNo.")' style='display:none;' ><span class='instrucNonChosen' id='label".$group.$rowNo."' >&#10003</span></label></td>";  			
			}
		}
		if ($rowNo>1){
			echo '<td> <input type="button" class="collectorButton deleteButton" value="delete" onclick="deleteFunction(\'tr'.$rowNo.'\')"> </td>';
			
		}
		echo "</tr>";	
	}	
}?>
</tr>
</table>
<input type="button" class="addInstructions collectorButton" value="Add Instruction?">
<br><input type="submit" class="collectorButton" name="proceedInstructions" value="Proceed">	


<script type="text/javascript">



function textAreaAdjust(o) {
    o.style.height = "1px";
    o.style.height = (o.scrollHeight)+"px";
} // solution provided by Alsciende on http://stackoverflow.com/questions/995168/textarea-to-resize-based-on-content-length

function changeRadio (groupVar,rowVar) {
	for (i=1; i<=maxRow; i++){
		checkedId=("#tr"+i);
		if ($(checkedId).length>0){
			if(i==rowVar){
				//alert("label"+groups[groupVar]+i);
				document.getElementById("label"+groups[groupVar]+i).className="instrucChosen"; //activate row
			} else {
				//alert("label"+groups[groupVar]+i);	
				document.getElementById("label"+groups[groupVar]+i).className="instrucNonChosen"; //deactivate row
			}
		}
	}
}; 

function deleteFunction (rowX) {
	rowX.toString();
	variableRows=variableRows-1; //add function needs number of rows
	var row = document.getElementById(rowX);
    var table = row.parentNode;
    while ( table && table.tagName != 'TABLE' )
        table = table.parentNode;
    if ( !table )
        return;
    table.deleteRow(row.rowIndex);
}; // solution given by Vilx on http://stackoverflow.com/questions/4967223/delete-a-row-from-a-table-by-id


Object.size = function(obj) {
	var size = 0, key;
	for (key in obj) {
		if (obj.hasOwnProperty(key)) size++;
	}
	return size;
}; //this code has been taken off stackoverflow: http://stackoverflow.com/questions/5223/length-of-a-javascript-object-that-is-associative-array

var table = document.getElementById('instructionTable');	
var guiArray = <?php echo json_encode($jsonGUI,JSON_PRETTY_PRINT); ?>;
var groups = (<?php  echo json_encode($groups,JSON_PRETTY_PRINT); ?>);


for (i=0; i<groups.length ; i++){
	groups[i]=groups[i].replace(" ","");
}

var instructions = <?php echo json_encode ($instructions); ?>;
//var guiArray = $.map(guiArray, function(el) { return el }); // converting from json to array
var groups = $.map(groups, function(el) { return el }); // converting from json to array
var noNewGroups=0;
var variableRows=instructions.length;
var maxRow=instructions.length;

$("#addGroup").click(function(){
	noNewGroups=noNewGroups+1;
	var row = document.getElementById("groupRow");//insertCell(groups.length);
	var newGroupCell = row.insertCell(groups.length+1);
	newGroupCellID="#newGroup"+noNewGroups;
	//alert(newGroupCellID);
	newGroupCell.id = "newGroup"+noNewGroups;
	var newGroupName = prompt ("What is the name of your new group?","");
	if (newGroupName != null) {
		newGroupName=newGroupName.replace(" ","");
		$(newGroupCellID).append("<h3><textarea name='GroupName"+(groups.length+1)+"' style='text-align:center; color:#069' id='"+newGroupName+"' rows='1' cols='"+(newGroupName.length+4)+"' onkeyup='textAreaAdjust(this)' name='"+newGroupName+"'>"+newGroupName+"</textarea></h3>");		
		groups.push(newGroupName);
		//for each set of instructions, add a radio button
		whileRow=0;
		while (whileRow<maxRow){
			whileRow=whileRow+1;
			//identify whether row exists or not
			checkedId=("#tr"+whileRow);
			if ($(checkedId).length>0){
				var rowNo = whileRow;
				var row = document.getElementById("tr"+rowNo);//insertCell(groups.length);
				//alert (row);
				var newGroupCell = row.insertCell(groups.length);
				newGroupCell.id = "newRadio"+noNewGroups+rowNo;
				$("#newRadio"+noNewGroups+rowNo).append("<label><input type='radio' name='group"+groups.length+"' value='"+rowNo+"'  id='"+newGroupName+rowNo+"' onclick='changeRadio("+(groups.length-1)+","+rowNo+")' style='display:none;'><span class='instrucNonChosen' id='label"+newGroupName+rowNo+"'>&#10003</span></label></td>");
			}
		}
	} 	
});
//need this to identify length from script
$(".addInstructions").click(function(){
	variableRows++	
	maxRow=maxRow+1; //relates to most recent row number i.e. includes deleted rows. This is necessary for maintaining order of rows after deleting a row.
	var thisVarRow=variableRows; // i think I can delete this -not used!
	var row = table.insertRow(variableRows);
	row.id = "tr"+maxRow;
	var cell1 = row.insertCell(0);
	cell1.id = "cell1id"+maxRow;
	
	//instructions
	$("#cell1id"+maxRow).append('<textarea type="text" id="cell1input" placeholder="[type your instructions here]" onkeyup="textAreaAdjust(this)">');
	document.getElementById("cell1input").setAttribute('id',"cell1input"+(maxRow));
	document.getElementById("cell1input"+(maxRow)).setAttribute('name',"instructionsText"+(maxRow));
	for (i =0; i<Object.size(groups); i++){			
		var cellNo = i+1; //messy fix otherwise gives 01 rather than 1 etc.	
		var cellX = row.insertCell(cellNo);
		cellNo = i+2; //messy fix otherwise gives 02 rather than 2 etc.
		cellX.id = "cell"+cellNo+"id"+maxRow;		
		$("#cell"+(cellNo)+"id"+maxRow).append("<label><input type='radio' value='"+maxRow+"' name='group"+(i+1)+"' id='"+groups[i]+maxRow+"' style='display:none;'><span class='instrucNonChosen' id='label"+groups[i]+maxRow+"' onclick='changeRadio("+i+","+maxRow+")' >&#10003</span></label></td>");
	}
	//inserting delete button
	var cellNo = i+1; //messy fix - otherwise gives 01 rather than 1 etc.
	var cellX = row.insertCell(cellNo);
	cellNo = i+2; //messy fix - otherwise gives 02 rather than 2 etc.
	cellX.id = "cell"+cellNo+"id"+maxRow;
	$("#cell"+(cellNo)+"id"+maxRow).append('<input type="button" class="collectorButton deleteButton" value="delete" onclick="deleteFunction(\'tr'+maxRow+'\')">');	
});

</script>


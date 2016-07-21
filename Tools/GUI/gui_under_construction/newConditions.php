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
    //require '../Code/initiateCollector.php';
    
	$_DATA['guiSheets']['studyName']=$_POST['studyName'];
	$title = 'Collector GUI';
  require $_PATH->get('Header');
	require('guiFunctions.php');
		
	if($_POST["newStudySubmit"]=="Create!"){
		#create a new study
		$studySource="../Experiments/New Experiment/";
		$studyDest="../Experiments/".$_POST["studyName"];
		recurse_copy($studySource,$studyDest);
	}
		
	
?>
<style>
    /* .studyList { position: absolute; top: 50px; left: 10px; } */
    body { flex-flow: row; }
    .leftCol { padding-right: 100px; }
</style>
<div class="mainCol">
<form action="index.php" method="post">
	<textarea id="currentGuiSheetPage" name="currentGuiSheetPage" style="display:none">firstInstructions</textarea>
    What participant groups are there? <br><br>
	<table style="width:100%;text-align:center" id="groupTable">
	<tr>
	<td>
	Group name <input name="conditionName1" id="condition1" type="text" placeholder="Group name" />
	</td>
	<td>
	Description <input name="conditionDetails1" id="conditionDetail1" type="text" placeholder="Group description" />
	</td>
	</tr>
	</table>
	<br>
	<input type="button" class="newGroup collectorButton" value="Add Group"/>
	<br><br>
	Once you have decided which groups you want, please press "Proceed" <br><br>
	<input type="submit" class="collectorButton" value="Proceed"/>
	</form>
</div>

<script>


// consider below after lunch break
variableRows=0	
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

var maxRow=0;

$(".newGroup").click(function(){
	variableRows++;	//relates to actual number of rows
	maxRow=maxRow+1; //relates to most recent row number i.e. includes deleted rows. This is necessary for maintaining order of rows after deleting a row.
	var table = document.getElementById('groupTable');	
	var row = table.insertRow(variableRows);
	row.id=("row"+maxRow);
	
	var cell1 = row.insertCell(0);
	var cell2 = row.insertCell(1);
	var cell3 = row.insertCell(2);
	cell1.innerHTML = "Group name ";
	cell1.id = "cell1id"+maxRow;
	cell2.innerHTML = "Description ";
	cell2.id = "cell2id"+maxRow;
	cell3.id = "cell3id"+maxRow;		

	//group name
	$("#cell1id"+maxRow).append('<input type="text" id="cell1input" placeholder="Group name">'); //adding input
	document.getElementById("cell1input").setAttribute('id',"conditionName"+(maxRow+1));
	document.getElementById("conditionName"+(maxRow+1)).setAttribute('name',"conditionName"+(maxRow+1));

	//group description		
	$("#cell2id"+maxRow).append('<input type="text"  id="cell2input" placeholder="Group description">');
	document.getElementById("cell2input").setAttribute('id',"conditionDetails"+(maxRow+1));
	document.getElementById("conditionDetails"+(maxRow+1)).setAttribute('name',"conditionDetails"+(maxRow+1));
	
	//delete button			
	$("#cell3id"+maxRow).append('<input type="button" class="deleteButton collectorButton" id="cell3input" onclick="deleteFunction(\''+row.id+'\')" value="delete">');
});





</script>


<?php
    require $_PATH->get('Footer');

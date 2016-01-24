<?php adminOnly();
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
	  
	// now lets get a list of possible experiments to edit
	$branches = getCollectorExperiments();
	$title = 'Collector GUI';
	require $_PATH->get('Header');
	//$key=array_search('New Experiment',$branches);
	//unset($branches[$key]);
	
	// get a list of all the filenames to allow checking for duplications
	$branchNames=array();
	$branchKey=array();
	foreach($branches as $branch){
		if(file_exists("../Experiments/$branch/name.txt")){
			array_push($branchNames,file_get_contents("../Experiments/$branch/name.txt"));
		} else {
			array_push($branchNames,$branch);
		}
	}
	$listStudyFilenamesJson=json_encode($branches);
	$listStudyNamesJson=json_encode($branchNames);
	
?>

<form action="index.php" id="newCSVForm" method="post">
	<textarea id="currentGuiSheetPage" name="currentGuiSheetPage" style="display:none">copySheets</textarea>
	
	<div> Which experiment you would like to base your new experiment on 
		<select id="templateExperiment">
			<?php 
				foreach ($branchNames as $branchName){
					echo "<option>$branchName</option>";
				}
			?>
		</select>
	</div>
	<br>
	What is the name of your new experiment? <input type="text" placeholder="name here" name="csvPostName" id="studyNameID" onkeyup="checkName()">
	<br><br>
	<input type="button" value="Create new experiment" class="collectorButton" onclick="return validateMyForm()">
	<textarea id="copyStudyFilename" name="templateExperiment" style="display:none"></textarea>
</form>

<script type="text/javascript">
 
	listStudyFileNames=<?=$listStudyFilenamesJson?>; 
	listStudyNames=<?=$listStudyNamesJson?>; 
	var revertName=studyNameID.value;
	function checkName(){
		// check if member of array
		if($.inArray(studyNameID.value,listStudyNames)!=-1){
			alert("This is the same name of another study, reverting to unique name");
			studyNameID.value=revertName;
		} else{
			revertName=studyNameID.value;
		}
	}
	
	var nameLength=0;
	var minNameLength=4;

	copyStudyFilename.value=templateExperiment.value;
	$("#templateExperiment").change(function(){
		studyIndex=listStudyNames.indexOf(templateExperiment.value);
		copyStudyFilename.value=listStudyFileNames[studyIndex];
	});
	$("input").change(function(){
			nameLength=document.getElementById("studyNameID").value.length;
	});

	function validateMyForm(){
		if(nameLength > minNameLength)
		{ 
			document.getElementById("newCSVForm").submit();
		}
		else 
		{
				alert("Name must be more than " + minNameLength + " characters");
		}
	}

</script>
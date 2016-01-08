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
    
    // now lets get a list of possible experiments to edit
    $branches = getCollectorExperiments();

	$title = 'Collector GUI';
    require $_PATH->get('Header');
	
	$key=array_search('New Experiment',$branches);
	unset($branches[$key]);
	//print_r($branches);
?>

<form action="copyCSVs.php" id="newCSVForm" method="post"> 
	<div> Which experiment you would like to base your new experiment on 
		<select name="templateExperiment">
			<option>New Experiment</option>
			<?php 
				foreach ($branches as $branch){
					echo "<option>$branch</option>";
				}
			?>
		</select>
	</div>
	<br>
	What is the name of your new experiment? <input type="text" placeholder="name here" name="studyName" id="studyNameID">
	<br><br>
	<input type="button" value="Create new experiment" class="collectorButton" onclick="return validateMyForm()">
</form>

<script type="text/javascript">
var nameLength=0;
var minNameLength=4;

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
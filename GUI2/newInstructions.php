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
	
	#summarising the passed variables
	$groups=[];
	## list the key variables
	$condInfo=array_keys($_POST);
	$allCondListed=0;
	$condNum=0;
	
	
	#list all keys that refer to groups
	foreach ($condInfo as $condI){
		//echo (strcmp("conditionName",$condI));
		if (strcmp("conditionName",substr($condI,0,13))==0){
			$condNum++;
			array_push($groups,$_POST[$condI]);
		}
	}
	
	
	
	include_once("guiStruc.php");
	$guiStruc = new guiStruc;
	
	$guiStruc->studyName = $_SESSION['studyName'];
	$guiStruc->studyGroups = $groups;
	

	//Saving GUI structure
	$jsonGUI = json_encode($guiStruc,JSON_PRETTY_PRINT);
	$guiFileLoc = "../Experiments/".$_SESSION['studyName'].'/gui.txt';
	
	
	
	
	//for piloting, I am adding code to detect whether a gui file already exists
	//echo (file_exists ($guiFileLoc));
	
	if(file_exists ($guiFileLoc)==1){
		//do nothing, as instructions.php already reads the file.
	} else {
		file_put_contents($guiFileLoc,$jsonGUI);

	}
	//$guiFileLoc = "../Experiments/".$_SESSION['studyName'].'/gui.txt';
	//$jsonGUI=file_get_contents($guiFileLoc);
	$guiArray=json_decode($jsonGUI);
	
?>
<style>
     body 		{	 flex-flow: row; }
    .leftCol 	{ padding-right: 100px; }
	button      { margin: 40px; }
</style>

<form action="firstTask.php" method="post" onsubmit="return getContent()">


<div id="mainInformationSpace"> 

<?php require ("instructions.php") ?></div>

</form>


<!-- <div id="cueTrialInfo"></div>	!-->

<script>



</script>




<?php
    require $_PATH->get('Footer');
?>

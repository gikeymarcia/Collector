<style>
	.stimDiv {
		position:relative;
		float:right;
		width:50%;
		height:90%;
		border-radius:10px;
		text-align:center;
	}
	form{
		position:initial;
	}
	.listDiv{
		position:relative;
		float:left;
		width:50%;
		height:90%;
		border-radius:10px;
		text-align:center;
	}
	.stimText {
		color:blue;
	}
	.stimText:hover{
		color:red;
	}
	.stimCenter{
		vertical-align:top;
		position:relative;
		top:35%;
		width:90%;
		height:50%
	}
</style>

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
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>

 */

  // start the session, load our custom functions, and create $_PATH
  require '../../Code/initiateCollector.php';
	$title = 'Collector GUI';
  require $_PATH->get('Header'); 
 
	// need to match 
	$acceptedFileTypes = array("jpg","png","jpeg","gif","mp4"); //this is copied in the upload file - may want to reduce this to a session variable.
    	
	?>

<form action="stimList.php" method="post">
	<div id="stimDivArea" class="stimDiv"></div>
</form>

<?php
	
	//deleting files
	if(isset($_POST['delete'])){
		//prevent automatic deletion by pressing backspace onto the page
		$deleteOK=1;
		if(isset($_SESSION['uploadedFiles'])){
			foreach($_SESSION['uploadedFiles'] as $recentUpload){
				if(strcmp($_POST['delete'],$recentUpload)==1){
					$deleteOK=0;				
				}
			}
		}
		$deletePath="../../Experiments/Common/".$_POST['delete'];
		$deletePath=str_ireplace('%20',' ',$deletePath);		
		if ($deleteOK==1 & file_exists($deletePath)){
			unlink($deletePath);
		}
	} else {
		unset($_SESSION['uploadedFiles']);
	}
	$commonFiles = scandir ("../../Experiments/Common");
	$stimFiles=array();
	$spaceUsed=0;
	for ($i=0; $i<count($commonFiles); $i++){
		$acceptedFile=0;
		foreach ($acceptedFileTypes as $acceptedFileType){
			if (strpos($commonFiles[$i],$acceptedFileType) >0){
				array_push($stimFiles,$commonFiles[$i]);
			}
		}
	}
	?>
	<div class='listDiv'>
		<div class="stimCenter">

	<?php
	$stimNo=0;
	foreach ($stimFiles as $stimFile){
		$stimNo++;
			echo "<span class='stimText' id='stim$stimNo' onclick='displayStim(\"$stimFile,$stimNo\")'>$stimFile
			</span>
			<br>";
		$spaceUsed=$spaceUsed+filesize("../../Experiments/Common/$stimFile");
	}
	$stimNoJson = json_encode($stimNo);
	$spaceUsed=$spaceUsed/1000000;
	echo round($spaceUsed,2)."MB/100MB available";
?>

<!DOCTYPE html>
		<html>
			<body>
				<form action="upload.php" method="post" enctype="multipart/form-data">
					Select files to upload:
					<input type="file" name="fileToUpload[]" id="fileToUpload" multiple>
					<input type="submit" class="collectorButton" value="Upload files" name="submit">
				</form>
			</div>
		</body>		
	</div>
</html>
<script>

var stimNo = <?= $stimNoJson ?>;

function displayStim(x){
	xSplit=x.split(','); //to separate the filename and the span id
	y=xSplit[0].replace(/ /g,"%20");
	stimDivArea.innerHTML="<div class='stimCenter'><embed src="+"'../../Experiments/Common/"+y+"' width='100%'" + ">" + "<br><button class='collectorButton' type='submit'  name='delete' id='deleteButton' value="+y+" style='display:none'>No Text Needed</button><input type='button' onclick='confirmDelete()' class='collectorButton' value='Delete?'></div>";
	//now highlight/bold text for selected image
	document.getElementById('stim'+xSplit[1]).style.color="green";

	for(i=1;i<stimNo;i++){ 
		if(i==xSplit[1]){
			document.getElementById('stim'+i).style.fontWeight="bold";
		} else {
			document.getElementById('stim'+i).style.fontWeight="normal";
		}
	}	
}

function confirmDelete(){
	delConf=confirm("Are you SURE you want to delete this file?");
	if (delConf== true){
		document.getElementById('deleteButton').click();
	}
}	
	
</script>
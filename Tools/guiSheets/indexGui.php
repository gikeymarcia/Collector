<?php
adminOnly();
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
    $title = 'Collector GUI';
    require $_PATH->get('Header');	
		$branches = getCollectorExperiments();
		
		if(isset($_SESSION['csvSelected'])){
			unset ($_SESSION['csvSelected']);
		}
		
?>
<style>
  .guiTasks { display: inline-block; }
  .guiTasks input {width: 120px; margin: 20px; }
  .buttonRows {text-align: right; max-width: 800px; margin: auto;}
</style>

<div class="buttonRows">
  <form class="guiTasks" action="index.php" method="post">
    <div class="buttonRows">
			Which study do you want to edit?
			<select id="studyName" name="studyName" onchange="updateGuiStudyName()">
      <?php
			$guiArray=array();
			$selectArray=array();
			$illegalStudyNames=array('.','..','New Experiment','Common'); //think this is redundant
			$branchStudyKey=array();
			
			foreach ($branches as $study) {
				$branch=$study;
				//don't look at studies that exist within illegalStudyNames array
				if(in_array($study,$illegalStudyNames)!=1 & stripos($study,'.')==false){
					if(file_exists("../Experiments/".$study."/name.txt")==1){
						$study=file_get_contents("../Experiments/$study/name.txt");
					}
					$branchStudyKey[$study]=$branch;
					$branchStudyKeyJson=json_encode($branchStudyKey);
					echo "<option>$study</option>";
					array_push($selectArray,$study);
					if(file_exists("../Experiments/$study/gui.txt")==1){	
						array_push($guiArray,$study);
					} 
				}
			}
			
			$guiArrayJson=json_encode($guiArray);
      ?>
			</select>
		
			<input name="submitButton" class="collectorButton" type="button" onclick="selectingPage('sheetsEditor')" value="Edit Sheets" title="GUI can only be used to edit studies edited using ONLY the GUI">
			<textarea name="csvPostName" id="csvPostName" style="display:none"></textarea>  
			<input name="guiEdit" id="editGuiButton" class="collectorButton" type="button" onclick="alert('The GUI feature is under Construction')" value="Edit GUI" title="under construction">
		<div class="buttonRows">
			Or do you want to create a new study?
			<input type="button" onclick="selectingPage('newSheet')" value="Using Sheets" class="collectorButton" >
			<input type="button" onclick="alert('The GUI feature is under Construction')" value="Using GUI">
		</div>
		<textarea id="currentGuiSheetPage" name="currentGuiSheetPage" style="display:none">sheetsEditor</textarea>
		<input id="changePage" type="submit" style="display:none">
  </form>
</div>

<script>
	function selectingPage(x){
		currentGuiSheetPage.value=x;
		$("#changePage").click();
	}

	branchStudyKey=<?=$branchStudyKeyJson?>;
  studyName.value=studyName.value;
	csvPostName.value=branchStudyKey[studyName.value];
  function updateGuiStudyName(){
		//studyName.value=branchStudyKey[studyName.value];
		csvPostName.value=branchStudyKey[studyName.value];
		if($.inArray(studyName.value,guiArray)==-1){
			$('#editGuiButton').hide(1000);
		} else {
			$('#editGuiButton').show(1000);
		}
  }
  guiArray=<?=$guiArrayJson?>;
	var showEditGui=0;
	if($.inArray(studyName.value,guiArray)==-1){
		$('#editGuiButton').hide(); //reinstate speed 1000 when gui is running
	} else {
		$('#editGuiButton').show(); //reinstate speed 1000 when gui is running
	}

</script>

<?php
    require $_PATH->get('Footer');

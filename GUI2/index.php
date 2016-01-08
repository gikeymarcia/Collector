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
    //$branches = getCollectorExperiments();
 
 
  # list of all studies
  $branches = scandir("../Experiments");
  //$branches = array_slice($branches,2);
  #print_r($branches);

	
    $title = 'Collector GUI';
    require $_PATH->get('Header');
?>
<style>
  .guiTasks { display: inline-block; }
  .guiTasks input {width: 100px; margin: 20px; }
  .formRows {text-align: right; max-width: 800px; margin: auto;}
</style>

<div class="formRows">
  <form class="guiTasks" action="csvRoar.php" method="post">
    Which study do you want to edit?
    <select id="studyName" name="studyName" onchange="updateGuiStudyName()">
      <?php
		$guiArray=array();
        foreach ($branches as $study) {
			if(strcmp($study,'New Experiment')!=0){
				//detect whether there is a name file in the folder
				//echo "../Experiments/$study/name.txt";
				if(file_exists("../Experiments/".$study."/name.txt")==1){
					$study=file_get_contents("../Experiments/$study/name.txt");
				}
				echo "<option value='$study'>$study</option>";

				//code to identify whether there is a gui file in each folder explored
				if(file_exists("../Experiments/$study/gui.txt")==1){	
					array_push($guiArray,$study);
				}
			}
        }
		$guiArrayJson=json_encode($guiArray);
      ?>
    </select>
    <input name="submitButton" class="collectorButton" type="submit" value="Edit CSV">
  </form>
  
  <form class="guiTasks" action="gui.php" method="post">
    <input name="guiEdit" class="collectorButton" type="submit" value="Edit GUI" >
    <textarea name="guiStudyName" id="guiStudyName" style="display:none"></textarea>
  </form>
</div>

<div class="formRows">
  <form class="guiTasks" action="newCSV.php" method="post">
    Or do you want to create a new study?
    <input type="submit" value="Using CSV" class="collectorButton" >
  </form>
  <form class="guiTasks" action="newStudy.php" method="post">
    <input type="submit" value="Using GUI" class="collectorButton">
  </form>
</div>

<script>
  guiStudyName.value=studyName.value;
  function updateGuiStudyName(){
	guiStudyName.value=studyName.value;
  }
  alert(guiStudyName.value);
  guiArray=<?=$guiArrayJson?>;
  for (i=0;i<guiArray.length;i++){
	alert(guiArray[i]);
  }

</script>

<?php
    require $_PATH->get('Footer');

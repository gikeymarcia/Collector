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
 
 /*
  # list of all studies
  $branches = scandir("../Experiments");
  $branches = array_slice($branches,2);
  #print_r($branches);
*/
	
    $title = 'Collector GUI';
    require $_PATH->get('Header');
	
	$newFlow=readCsv('flowTemplate.csv');
	
	
	
#	print_r ($_POST);
	
	#summarising the passed variables
	$allCondListed=0;
	$condNum=0;
	$groups=[];
	## list the key variables
	$condInfo=array_keys($_POST);
	while ($allCondListed==0){
		$condNum++;
		if(isset($_POST['condition'.$condNum.'Name'])){
			#echo $_POST['condition'.$condNum.'Name'];
			$groups[$condNum]=$_POST['condition'.$condNum.'Name'];
		} else {
			#echo $_POST['condition'.$condNum.'Name'];
			$allCondListed=1;
		}
	}
	#print_r($groups);
	
	
?>

<style>
     body { flex-flow: row; }
    .leftCol { padding-right: 100px; }
</style>
<div class="leftCol">
	<div>

Start <br>
<?php foreach($newFlow as $thisRow){
	echo "--".$thisRow[0]."<br>";
}

?>
End
	</div>
</div>

<div>
<form action="studyGUI.php" method="post">

	To select stimuli to select you may need to upload some files. Click "upload files" to do so.
	<input type="button" value="upload files"/>
	<br><br><br>

    What type of trial would you like your participants to complete? <br><br><br>
	<table style="width:100%;text-align:center" id="instructionTable"> 
	<tr>
	<td>trial type selected in previous screen </td>
	<td>Group 1 (tick or no tick)</td>
	<td>Group 2 (tick or no tick)</td>
	<td>list of cues</td>
	<td>list of answers</td>
	</tr>
	<tr> <td>

	</td>
	<?php #give tick boxes for each condition
	$allCondListed=0;
	$condNum=0;
	## list the key variables
	$condInfo=array_keys($_POST);
	foreach($groups as $group){
		echo "<td> <input type='checkbox' name=".$group." checked></td>";
	}
	
#	echo json_encode($groups);
	
	?>
	</tr>
	</table>
	<br><br><br>
	<input type="button" class="addInstructions" value="Add Trial Type?">
	<br><br><br>
	Once you are ready, click "Preview Study Design" to proceed (you will be able to edit and expand your study after this phase) <br>
	<input type="submit" name="instructions1" value="Preview Study Design">	
</form>

<script type="text/javascript">






</script>



<?php
    require $_PATH->get('Footer');
?>

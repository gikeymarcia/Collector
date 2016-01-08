<?php
/*  

	TO DO LIST!!!
	
	- at the moment, I'm not sure the instructions are being saved to the correct groups

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
    session_start();	
    $title = 'Collector GUI';
    //require $_PATH->get('Header');
	
	$guiFileLoc = "../Experiments/".$_SESSION['studyName'].'/gui.txt';
	$jsonGUI=file_get_contents($guiFileLoc);
	$guiArray=json_decode($jsonGUI);
	
	//open csv files
	
	$conditionsLoc = "../Experiments/".$_SESSION['studyName'].'/Conditions.csv';
	
	
	
	$conditionsArray = file_get_contents($conditionsLoc);
	
	require "csv_to_array.php";
	

$conditionsArray=csv_to_array($conditionsLoc);
	
	
	//setting up an array for both procedure and stimuli
	$conditions=$guiArray->studyGroups;
	foreach ($conditions as $group){ //tidy up, "conditions" and "groups" are the same
		$stimArray[$group]=[];
		$procArray[$group]=[];
	}
	if (!isset($stimArray[$group])){
						
						//create a new array for group stim
						//$stimArray[$group]
					} else {
						//need to add onto already existing array
						//there may not be any action required
	}
	
	
	// code here to update guiArray
	//creating code for each group
	
	$rowNo=-1;
	foreach ($conditions as $condition){
		$rowNo++;
		// identify group name
		$conditionsArray[$rowNo]['Description']=$condition;
		$conditionsArray[$rowNo]['Notes']='more detailed notes here';
		$conditionsArray[$rowNo]['Stimuli 1']=$condition.'.csv'; //this needs to be dynamic
		$conditionsArray[$rowNo]['Procedure 1']=$condition.'.csv';
		
		
		
		
		//create the relevant proc files if they do not yet exist!
	$procFileLocation='../Experiments/'.$_SESSION['studyName'].'/Procedure/'.$condition.'.csv';
	
	
	copy ('../Experiments/'.$_SESSION['studyName'].'/Procedure/Procedure.csv',$procFileLocation);
	
	}
	
	
	//convert array to csv
	// extract keys as first row
	$csvKeys=array_keys($conditionsArray[0]);
	
	$csvKeys=implode(',',$csvKeys);
	$csvKeys=$csvKeys.'
';
	$csvArray=$csvKeys;
	foreach ($conditionsArray as $conditionsRow){
		$conditionsRow=implode(',',$conditionsRow);
		$conditionsRow=$conditionsRow.'
';
		$csvArray=$csvArray.$conditionsRow;
	}
	$testFileName='../Experiments/'.$_SESSION['studyName'].'/Conditions.csv';
	file_put_contents($testFileName,$csvArray);

	$eventNo=0;
	$allEventsProcessed=0;
	while ($allEventsProcessed==0) {
		$eventNo++;
		$thisEvent="event".$eventNo;
		if (isset($guiArray->$thisEvent)){
			$eventInfo=$guiArray->$thisEvent;
			//this code will be moved to separate PhP files for cleanliness//
			if ($guiArray->$thisEvent->chosenTrialType=="instructions"){
				// update instructions for groups that have them
				for ($i=0; $i<count($eventInfo->eventDetails->groupSelected); $i++){
					$groupProcFileName='../Experiments/'.$_SESSION['studyName'].'/Procedure/'.$guiArray->studyGroups[$i].'.csv';
					//open csv for each group procedure file
					$groupProcFileArray=csv_to_array($groupProcFileName);
					
					$thisRow=count($groupProcFileArray);
					if (count($groupProcFileArray)==1){
						$thisRow=0;
					} 
					$groupProcFileArray[$thisRow]['Text']=$guiArray->$thisEvent->eventDetails->instructions[$i];
					
					// need to do more; but just checking if I can save into csv easily
					
					//convert array to csv
					// extract keys as first row
					$csvKeys=array_keys($groupProcFileArray[0]);
					
					$csvKeys=implode(',',$csvKeys);
					$csvKeys=$csvKeys.'
';
					$csvArray=$csvKeys;
					foreach ($groupProcFileArray as $procRow){
						$procRow=implode(',',$procRow);
						$procRow=$procRow.'
						';
						$csvArray=$csvArray.$procRow;
					}
					
					$procArray[$guiArray->studyGroups[$i]]=[$groupProcFileArray[0]];
					
					
					
				}
			}
			if ($guiArray->$thisEvent->chosenTrialType=="Cue"){
				//print_r($_POST);
				
				//convert $_POST['stimTableInput'] into array
				$stimTableArray=str_replace('[','',$_POST['stimTableInput']);
				$stimTableArray=str_replace('"','',$stimTableArray);

				$stimTableArray=explode(']',$stimTableArray);
				
				$stimKeys=explode(',',$stimTableArray[0]);
				//print_r($stimKeys);
				
				for ($i=1; $i<=count($stimTableArray); $i++){
					$stimTableArray[$i]=explode(',',$stimTableArray[$i]);
					if (empty($stimTableArray[$i][0])){
						unset($stimTableArray[$i][0]);
					}
					if (count($stimTableArray[$i])==count($stimKeys)){
						$stimTableArray[$i]=array_combine($stimKeys,$stimTableArray[$i]);
					} else {
						unset ($stimTableArray[$i]);
					}
				}
				
				
				//print_r($stimTableArray);
				
				
				//need to create a stim array for each group (if not already present)
				foreach ($guiArray->studyGroups as $group){
					//echo $group;
					//identify which rows to add for group
					$groupStimArray=array();
					//echo count($stimTableArray);
					//print_r($_POST['stimTableInput']);
					
					
					
					
					
					//may need more distinct names than stimTableArray vs. stimArray
					
					for ($i =1; $i<count($stimTableArray); $i++){
						
					if($stimTableArray[$i]['groups']=='all'|strpos($stimTableArray[$i]['groups'],$group)!== false){
							//add this rows information into the groups Stimuli Array
							
										
						$stimArray[$group][count($stimArray[$group])]=$stimTableArray[$i];
						
						//tidying up columns that will not be relevant for stimuli file
						//unset($stimArray[$group][count($stimArray[$group])-1]['groups']);
						//unset($stimArray[$group][count($stimArray[$group])-1]['Shuffle']);
						//print_r($stimArray);
						
						//updating procedure array//
						
						
					
						
						} else {
						//	echo "fail";
						// may be able to delete this line altogether!	
						}
						
					}
					$itemNo=-1;
					foreach ($stimArray[$group] as $stimRow){
						$itemNo++;
						$procArray[$group][count($procArray[$group])]['Item']=count($procArray[$group])+1;
						$procArray[$group][count($procArray[$group])-1]['Trial Type']="Cue";
						$procArray[$group][count($procArray[$group])-1]['Max Time']="user";
						$procArray[$group][count($procArray[$group])-1]['Text']=''; //$stimRow['Cue'];
						$procArray[$group][count($procArray[$group])-1]['Settings']='';
						$procArray[$group][count($procArray[$group])-1]['Shuffle']=$stimRow['Shuffle'];						
						
						
						}
				}				
			}			
		}
		else {
			$allEventsProcessed=1;
		}
	}
	
//proceed with saving of task//


foreach ($guiArray->studyGroups as $group){//save the stim file for each group

	//echo $group;

	
	//convert Array into csv format
	
	$thisFileName='../Experiments/'.$_SESSION['studyName'].'/Stimuli/'.$group.'.csv';
	$fp = fopen($thisFileName, 'w');

	$stimKeys=$stimArray[$group][0];
		
	unset($stimKeys['groups']);
	unset($stimKeys['Shuffle']);
	$stimKeys=array_keys($stimKeys);
	
	//print_r($stimKeys);
	$stimKeysArray=array_combine($stimKeys,$stimKeys);
	fputcsv($fp, $stimKeys);
	foreach ($stimArray[$group] as $fields) {
		unset($fields['groups']);
		unset($fields['Shuffle']);
		//print_r($fields);
		//$fields=array_merge($stimKeysArray,$fields);
		fputcsv($fp, $fields);
	}

	fclose($fp);
	
	//proc files
	
	$thisFileName='../Experiments/'.$_SESSION['studyName'].'/Procedure/'.$group.'.csv';
	$fp = fopen($thisFileName, 'w');

	$procKeys=$procArray[$group][0];
		
	//unset($procKeys['groups']);
	//unset($procKeys['Shuffle']);
	$procKeys=array_keys($procKeys);
	
	$stimKeysArray=array_combine($procKeys,$procKeys);
	fputcsv($fp, $procKeys);
	foreach ($procArray[$group] as $fields) {
		fputcsv($fp, $fields);
	}

	fclose($fp);
	
	
} 



					


	//update jsonencoded gui file - writing over the original//
	
	include_once("guiStruc.php");
	$guiStruc = new guiStruc;
	
	$guiStruc->studyName = $_POST['StudyName'];
	print_r($_SESSION);
	echo "<br><br><br>";
	$guiStruc->studyGroups = $_SESSION['groupInfo'];
	
	print_r($_POST);
	
	echo "<br><br><br>";
	
	print_r($guiStruc);


/*
	$stimuli   = json_decode($_POST['stimTableInput']);
	
	$guiArray-> event2 -> eventName = $_POST['eventName'];
	$guiArray-> event2 -> chosenTrialType = $_POST['trialType'];
	$guiArray-> event2 -> eventDetails = $stimuli;
	
	
	$jsonGUI=json_encode($guiArray);

	file_put_contents($guiFileLoc,$jsonGUI);

	header ("Location: gui.php");
*/


//proceed with converting saved file in the experimental files

	//STARTING HERE!!! */
	

?> 

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
    session_start();	
    $title = 'Collector GUI';
    //require $_PATH->get('Header');
	
	$guiFileLoc = "../Experiments/".$_DATA['guiSheets']['studyName'].'/gui.txt';
	$jsonGUI=file_get_contents($guiFileLoc);
	$guiArray=json_decode($jsonGUI);
	

//proceed with saving of task

	$stimuli   = json_decode($_POST['stimTableInput']);
	
	$guiArray-> event2 -> eventName = $_POST['eventName'];
	$guiArray-> event2 -> chosenTrialType = $_POST['trialType'];
	$guiArray-> event2 -> eventDetails = $stimuli;
		
	$jsonGUI=json_encode($guiArray);

	file_put_contents($guiFileLoc,$jsonGUI);

	
	print_r($_POST);
	header ("Location: gui.php");

//preview below to see what has been saved


?> 

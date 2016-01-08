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
	
?>

<form action="newConditions.php" method="post">
    What do you want to call your study?
    <input name="studyName" type="text" placeholder="insert name here">
	<input type="submit" class="collectorButton" name="newStudySubmit" value="Create!">
</form>



<?php
    require $_PATH->get('Footer');

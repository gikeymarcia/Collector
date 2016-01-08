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
	require ('guiFunctions.php'); // these need to be incorporated with other functions. 
		
?>

<style>
    body { color: black; background-color: white; }
    #header { font-size: 180%; text-align: center; margin: 10px 0 40px; }
    form {
        text-align: center;
        margin: 30px;
    }
    .tableArea {
        display: inline-block;
        width: 50%;
        box-sizing: border-box;
        padding: 10px 30px;
        vertical-align: top;
    }
	textarea { border: none; }
</style>


<?php
				
	
	$_POST['csvSelected']=str_ireplace('.csv','',$_POST['csvSelected']);

	
	//Convert all relevant $_SESSION and $_POST variables into standardised $variables 
	
	{//$thisDir
		if(isset($_SESSION['studyName'])){
			$thisDir="../Experiments/".$_SESSION['studyName'];
			$studyName=$_SESSION['studyName'];
		}
		else {
			if (isset($_POST['studyName'])){
				$thisDir="../Experiments/".$_POST['studyName'];		
				$_SESSION['thisDir']=$thisDir;
				$_SESSION['studyName']=$_POST['studyName'];
				$studyName=$_SESSION['studyName'];
			}	
			else {
				$thisDir=$_SESSION['thisDir'];
			}
		}
	}
	{//$thisFile and $thisLocation - duplications???
		if (isset($_POST['csvSelected']) & !isset($_POST['Save'])){
			$selectedCSV=$_POST['csvSelected'];
			$_SESSION['csvSelected']=$selectedCSV;
		} else {
			if (isset($_SESSION['csvSelected'])){
				$selectedCSV=$_SESSION['csvSelected'];
			} else {
				$selectedCSV=$condFiles[0];
			}
		}
		$csvFile=explode(',',$selectedCSV);
		if (isset($csvFile[1])){
			$csvLocation=$csvFile[1];
		} else {
			$csvLocation='';
		}
		$csvFile=$csvFile[0].'.csv';
		if ($csvFile==''){
			$csvFile=$condFiles[0];
		}
	}		
	if(isset($_POST['Delete'])){//check if anything should be deleted
		$deleteStim=explode(',',$_POST['csvSelected']);
		$deleteFile=$deleteStim[0];
		$deleteLocation=$thisDir.'/'.$deleteStim[1].'/'.$deleteStim[0].'.csv';
		unlink ($deleteLocation);
		unset($_POST['eventName']);
		unset($_POST['csvSelected']);
		unset($_SESSION['csvSelected']);
	}
	{ // updating study name if necessary
		file_put_contents($thisDir.'/name.txt',$_POST['currStudyName']);
		$studyName=file_get_contents($thisDir.'/name.txt');
	}
	{// List csv files in the directories
		$condFiles=getCsvsInDir($thisDir);
		$stimFiles=getCsvsInDir($thisDir.'/Stimuli/');
		$procFiles=getCsvsInDir($thisDir.'/Procedure/');
	}
	if(isset($_POST['newSheet'])){	//code for creating a new CSV sheet
		$newName=0;
		$newNo=0;
		switch ($_POST['newSheet']){
			case "stim":
				//identify what novel filename needs to be					
				while ($newName==0){
					$newNo++;
					$newFail=0;//have I identified a novel name
					foreach($stimFiles as $stimFile){
						if(strcmp($stimFile,"newStim$newNo.csv")==0){
							$newFail=1;
						}
					}
					if($newFail==0){
						$newName=1;
					}
				}
				copy("../Experiments/New Experiment/Stimuli/Stimuli.csv",$thisDir."/Stimuli/newStim$newNo.csv");
				break;
				
			case "proc":					
				while ($newName==0){
					$newNo++;
					$newFail=0;//have I identified a novel name
					foreach($procFiles as $procFile){
						if(strcmp($procFile,"newProc$newNo.csv")==0){
							$newFail=1;
						}
					}
					if($newFail==0){
						$newName=1;
					}
				}
				copy("../Experiments/New Experiment/Procedure/Procedure.csv",$thisDir."/Procedure/newProc$newNo.csv");
				break;
		}
	}
	if (isset($_POST['Save'])){ //Saving whichever csv you are currently working on
		{// this is potentially duplicated code
			$csvFileName=explode(",",$_POST['csvSelected']);
			$csvFileName=$csvFileName[0];
			$csvFileName=str_ireplace('.csv','',$csvFileName);		
		}
		{// renaming file if the user renamed it
			if (strcmp($_POST['eventName'],$csvFileName)!=0){
				$newFile=$thisDir.'/'.$csvLocation.'/'.$_POST['eventName'].'.csv';
				$originalFile=$thisDir.'/'.$csvLocation.'/'.$csvFileName.'.csv';
				$illegalChars=array('	',' ','.');
				foreach ($illegalChars as $illegalChar){
					$destFile=str_ireplace('	','',$newFile);
				}
				rename($originalFile,$newFile);
				$_SESSION['csvSelected']=$_POST['eventName'].','.$csvLocation;
			}
		}
		//Note that there is some duplicate code below - needs to be tidied
		{// this is potentially duplicated code
			$csvFileName=explode(",",$_POST['csvSelected']);
			$csvFileName=$csvFileName[0];
			$csvFileName=str_ireplace('.csv','',$csvFileName);		
		}
		{// converting raw table data into usable array
			//removing symbols
			$stimTableArray=str_replace('[','',$_POST['stimTableInput']);
			$stimTableArray=str_replace('"','',$stimTableArray);
			$stimTableArray=str_replace('null','',$stimTableArray);
			// exploding into an array
			$stimTableArray=explode(']',$stimTableArray);		
			$stimKeys=explode(',',$stimTableArray[0]);	
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
		}
		{//this is duplicated from above!!!
			$explodeEventName=explode(',',$_SESSION['csvSelected']);
			$folderLoc=$explodeEventName[1]; 
			$filename=$explodeEventName[0];
			$thisFileName=$thisDir.'/'.$folderLoc.'/'.$filename.'.csv';
		}			
		{//Save table as a .csv file;
			$fp = fopen($thisFileName, 'w');
			$stimTableArray[0]=explode(',',$stimTableArray[0]);
			fputcsv($fp, $stimTableArray[0]);
			for($i=1; $i<count($stimTableArray); $i++){
				fputcsv($fp, $stimTableArray[$i]);
			}
			fclose($fp);	
		}			
	}
	{//confirming csvFileName and Title (i.e. without .csv at end) - this needs to be done in case a file has been renamed during SAVE		
		
		/*
		print_r($_POST);
		if(!isset($_POST['eventName'])){
			
		} else {
			$fileTitle = $_POST['eventName'];
		}
		*/
		$fileTitle = explode (',',$_SESSION['csvSelected']);
		$fileTitle=$fileTitle[0];
		$fileName="$fileTitle.csv";
		$fileName=$csvFile;
	}
	{// extract table from csv file
		echo $fileName;
		$stimuli   = csv_to_array("$thisDir/$csvLocation/$fileName");
		$stimKeys = array_keys($stimuli[0]);	
		$stimData = array(array_keys(reset($stimuli)));
		foreach ($stimuli as $row) {
			$stimData[] = array_values($row);
		}
		$stimData = json_encode($stimData);			
	}	
	{//list all csv files
		$condFiles=getCsvsInDir($thisDir);
		$stimFiles=getCsvsInDir($thisDir.'/Stimuli/');
		$procFiles=getCsvsInDir($thisDir.'/Procedure/');
	}
			
?>

<form action="index.php">
	<button class="collectorButton"> return to index </button>	 
</form>
<form action='csvRoar.php' method='post'>
	<h1>
		<textarea name="currStudyName" style="color:#069;" rows="1"
			><?=$studyName?></textarea>
	</h1>

	<span>
		<button class="collectorButton" id="stimButton"> list of stimuli </button>
		<button name="newSheet" value="stim" class="collectorButton" id="newStimButton"> new stimuli sheet </button>
		<button name="newSheet" value="proc" class="collectorButton" id="newProcButton"> new procedure sheet </button>
	</span>
	<br>	
	<br>

		<div>
			<select name=csvSelected title="[filename],[folder]">	
		<?php
		if (!$fileTitle==''){ 
			echo"<option name='csvSelected' value='$fileName,$csvLocation'>$fileName,$csvLocation</option>";			
			} 
			
			foreach ($condFiles as $condFile){
				if($condFile != $fileName){
					echo "<option name='csvSelected' value='$condFile,'>$condFile,</option>";
				}
			}
			
			foreach ($procFiles as $procFile){
				if($procFile != $fileName){
					echo "<option name='csvSelected' value='$procFile,Procedure'>$procFile,Procedure</option>";
				}	
			}
			
			foreach ($stimFiles as $stimFile){
				if($stimFile != $fileName){
					echo "<option name='csvSelected' value='$stimFile,Stimuli'>$stimFile,Stimuli</option>";	
				}
			}
	?>		
		</select>
		<button type='submit' class='collectorButton' value='Select'>Select</button>
	</div>		
	<script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
	<link rel="stylesheet" href="handsontables/handsontables.full.css">
	<script src="handsontables/handsontables.full.js"></script>

	<?php
		if (strcmp($fileTitle,'Conditions.csv')==0){ ?>
			<h2 title="You cannot edit the Conditions.csv filename or delete the file.">Conditions.csv</h2>
		<?php 		
		} else { 
		
			$viewFileTitle=str_ireplace('.csv','',$fileTitle);
		
		?>
			<h2>
				<textarea name="eventName" style="color:#069;" rows="1"><?=$viewFileTitle?></textarea>
			</h2>
			<?php		
		}	
	?>

	<div>   
		
	<?php
		// doing this in PHP to prevent whitespace
		echo '<div id="stimArea" class="tableArea">'
		   .         '<div id="stimTable" class="expTable"></div>'
		   .     '</div>'
		   . '</div>';
	?>

		<br>
		
	<button id="submitButton" type="submit" name="Save" class="collectorButton" value="Save">Save</button> 

	<?php
		if (strcmp($fileTitle,'Conditions.csv')!=0){  ?>
			<input type="button" id="deleteButton" name="Delete" class="collectorButton" value="Delete?">	
			<button id="deleteActivate" type="submit" name="Delete" class="collectorButton" value="Delete" style="display:none">No text needed</button>
		<?php		
		}	
	?>

	</div>

	<input type="hidden" name="stimTableInput">
	
</form>


<script type="text/javascript">

var stimTable;
    function isTrialTypeHeader(colHeader) {
        var isTrialTypeCol = false;
        
        if (colHeader === 'Trial Type') isTrialTypeCol = true;
        
        if (   colHeader.substr(0, 5) === 'Post '
            && colHeader.substr(-11)  === ' Trial Type'
        ) {
            postN = colHeader.substr(5, colHeader.length - 16);
            postN = parseInt(postN);
            if (!isNaN(postN) && postN != 0) {
                isTrialTypeCol = true;
            }
        }
        
        return isTrialTypeCol;
    }
    function isNumericHeader(colHeader) {
        var isNum = false;
        if (colHeader.substr(-4) === 'Item')     isNum = true;
        if (colHeader.substr(-8) === 'Max Time') isNum = true;
        if (colHeader.substr(-8) === 'Min Time') isNum = true;
        return isNum;
    }
    function isShuffleHeader(colHeader) {
        var isShuffle = false;
        if (colHeader.indexOf('Shuffle') !== -1) isShuffle = true;
        return isShuffle;
    }
    function firstRowRenderer(instance, td, row, col, prop, value, cellProperties) {
        Handsontable.renderers.TextRenderer.apply(this, arguments);
        td.style.fontWeight = 'bold';
        if (value == '') {
            $(td).addClass("htInvalid");
        }
    }
    function numericRenderer(instance, td, row, col, prop, value, cellProperties) {
        Handsontable.renderers.TextRenderer.apply(this, arguments);
        if (isNaN(value) || value === '') {
            td.style.background = '#D8F9FF';
        }
    }
    function shuffleRenderer(instance, td, row, col, prop, value, cellProperties) {
        Handsontable.renderers.TextRenderer.apply(this, arguments);
        if (value === '') {
            td.style.background = '#DDD';
        } else if (
            typeof value === 'string' 
         && (   value.indexOf('#') !== -1
             || value.toLowerCase() === 'off'
            )
        ) {
            td.style.background = '#DDD';
        }
    }
    function trialTypesRenderer(instance, td, row, col, prop, value, cellProperties) {
        Handsontable.renderers.AutocompleteRenderer.apply(this, arguments);
        if (value === 'Nothing' || value === '') {
            if (instance.getDataAtCell(0,col) === 'Trial Type') {
                $(td).addClass("htInvalid");
            } else {
                td.style.background = '#DDD';
            }
        }
    }
    function updateDimensions(hot, addWidth, addHeight) {
        var addW = addWidth  || 0;
        var addH = addHeight || 0;
        
        var container   = hot.container;
        var thisSizeBox = $(container).find(".wtHider");
        
        var thisWidth  = thisSizeBox.width()+22+addW;
        var thisHeight = thisSizeBox.height()+22+addH;
        
        var thisArea = $(container).closest(".tableArea");
        
        thisWidth  = Math.min(thisWidth,  thisArea.width());
        thisHeight = Math.min(thisHeight, 600);
        
        hot.updateSettings({
            width:  1000, //thisWidth,
            height: thisHeight
        });
    }
    function updateDimensionsDelayed(hot, addWidth, addHeight) {
        updateDimensions(hot, addWidth, addHeight);
        setTimeout(function() {
            updateDimensions(hot);
        }, 0);
    }
    function createHoT(container, data) {
        var table = new Handsontable(container, {
            data: data,
            width: 1,
            height: 1,
			
            afterChange: function(changes, source) {
                updateDimensions(this);	
				
				var middleColEmpty=0;
				var middleRowEmpty=0;
				var postEmptyCol=0; //identify if there is a used col after empty one
				var postEmptyRow=0; // same for rows

				//identify if repetition has occurred and adjusting value
				var topRow=[];
				for (var k=0; k<this.countCols()-1; k++){
					var cellValue=this.getDataAtCell(0,k);
					topRow[k]=this.getDataAtCell(0,k);
					for (l=0; l<k; l++){
						if (this.getDataAtCell(0,k)==this.getDataAtCell(0,l)){
							alert ('repetition has occurred!');
							this.setDataAtCell(0,k,this.getDataAtCell(0,k)+'*');
						}
					}
									
				}
				
				//Removing Empty middle columns
				for (var k=0; k<this.countCols()-1; k++){
					if (this.isEmptyCol(k)){
						if (middleColEmpty==0){
							middleColEmpty=1;
						}
					}						
					if (!this.isEmptyCol(k) & middleColEmpty==1){
						postEmptyCol =1;
						alert ("You have an empty column in the middle - Being removed from table!");
						this.alter("remove_col",k-1); //delete column that is empty						
					}						
				}
				
				//Same thing for rows
				for (var k=0; k<this.countRows()-1; k++){
					if (this.isEmptyRow(k)){
						if (middleRowEmpty==0){
							middleRowEmpty=1;
						}
					}						
					if (!this.isEmptyRow(k) & middleRowEmpty==1){
						postEmptyRow =1;
						alert ("You have an empty row in the middle - Please fix this!");
						this.alter("remove_row",k-1); //delete column that is empty
					}						
				}				
				if(postEmptyCol != 1 ){
					while(this.countEmptyCols()>1){  
						this.alter("remove_col",this.countCols); //delete the last col
					}
				}
				if(postEmptyRow != 1){
					while(this.countEmptyRows()>1){  
						this.alter("remove_row",this.countRows);//delete the last row
					}
				}
            },
            afterInit: function() {
                updateDimensions(this);
            },
            afterCreateCol: function() {
                updateDimensionsDelayed(this, 55, 0);
            },
            afterCreateRow: function() {
                updateDimensionsDelayed(this, 0, 28);
            },
            afterRemoveCol: function() {
                updateDimensionsDelayed(this);
            },
            afterRemoveRow: function() {
                updateDimensionsDelayed(this);
            },
            rowHeaders: false,
            contextMenu: true,
            cells: function(row, col, prop) {
                var cellProperties = {};
                
                if (row === 0) {
                    // header row
                    cellProperties.renderer = firstRowRenderer;
                } else {
                    var thisHeader = this.instance.getDataAtCell(0,col);
                    if (typeof thisHeader === 'string' && thisHeader != '') {
                        if (isTrialTypeHeader(thisHeader)) {
                            cellProperties.type = 'dropdown';
                            cellProperties.source = trialTypes;
                            cellProperties.renderer = trialTypesRenderer;
                        } else {
                            cellProperties.type = 'text';
                            if (isNumericHeader(thisHeader)) {
                                cellProperties.renderer = numericRenderer;
                            } else if (isShuffleHeader(thisHeader)) {
                                cellProperties.renderer = shuffleRenderer;
                            } else {
                                cellProperties.renderer = Handsontable.renderers.TextRenderer;
                            }
                        }
                    } else {
                        cellProperties.renderer = Handsontable.renderers.TextRenderer;
                    }
                }                
                return cellProperties;
            },
            minSpareCols: 1,
            minSpareRows: 1,
            manualColumnFreeze: true,
            fixedRowsTop: 0,
			colHeaders: false,
			cells: function (row, col, prop) {
			}
        });
        return table;
    }
    
	var stimContainer = document.getElementById("stimTable");
	var stimData = <?= $stimData ?>;
	stimTable = createHoT(stimContainer, stimData);        
    
    // limit resize events to once every 100 ms
    var resizeTimer;
    
    $(window).resize(function() {
        window.clearTimeout(resizeTimer);
        resizeTimer = window.setTimeout(function() {
            updateDimensions(stimTable);
        }, 100);
    });
   
$("#submitButton").on("click", function() {
	$("input[name='stimTableInput']").val(JSON.stringify(stimTable.getData()));
});

$("#stimButton").on("click", function() {
	var myWindow = window.open("stimList.php", "", "width=400, height=1000");
});

$("#newStimButton").on("click", function() {
	alert("creating new Stim File");
});

$("#newProcButton").on("click", function() {
	alert("creating new Proc File");
});

$("#deleteButton").on("click", function() {
	delConf=confirm("Are you SURE you want to delete this file?");
	if (delConf== true){
		document.getElementById('deleteActivate').click();
	}	
});

$(window).bind('keydown', function(event) {
    if (event.ctrlKey || event.metaKey) {
        switch (String.fromCharCode(event.which).toLowerCase()) {
        case 's':
            event.preventDefault();
            alert('Saving');
			stimTable.deselectCell();			
			$("#submitButton").click();
            break;
        case 'd':
            event.preventDefault();
			$("#deleteButton").click();
            break;
        }
    }
});
</script>
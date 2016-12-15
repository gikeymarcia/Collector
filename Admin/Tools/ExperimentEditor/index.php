<?php
  
/*
   Experiment Editor:
   -Open and view all stimuli and procedure files for every experiment
   -Open condition sheet
   -change contents of sheet
   -copy/rename/delete sheets
   
   -start by scanning all experiments to find all sheets
   -display list of experiments
   -after experiment is selected, display list of sheets inside that experiment
   -alternatively, let them create a new exp
   -can open sheets, or copy to new sheet and start editing that
   -can also copy experiment to edit that
 */

  require "../../initiateTool.php";
  require 'fileReadingFunctions.php';
  

  
  $experiments = get_Collector_experiments($_FILES);
  
  $experiment_files = array();
  
  foreach ($experiments as $exp) {
    $_FILES->set_default('Current Experiment', $exp);
    $experiment_files[$exp]['Conditions'] = read_csv_raw($_FILES->get_path('Conditions'));
    $experiment_files[$exp]['Stimuli']    = $_FILES->read('Stimuli Dir');
    $experiment_files[$exp]['Procedures'] = $_FILES->read('Procedure Dir');
  }
  
  $new_exp_json = file_get_contents('default_new_experiment.json');
?>

<style>
  #interface{
    display:none;
  }

  .condOption         { background-color: #DFD; }
  .stimOptions        { background-color: #BBF; }
  .stimOptions option { background-color: #DDF; }
  .procOptions        { background-color: #FBB; }
  .procOptions option { background-color: #FDD; }
</style>

<script src="../HandsontableFunctions.js"></script>

<script>
  var new_experiment_data = <?= $new_exp_json ?>;
</script>

<div id="load_toolbar">
  <button type="button" id="new_experiment_button" class="collectorButton">New Experiment</button>
  
  <select id="experiment_select">
    <option value="" hidden disabled selected>Select an experiment</option>
    <?php 
    foreach ($experiments as $experiment){
      echo "<option>$experiment</option>";
    }
    ?>
  </select>
</div>

<div id="rest_of_interface">  
</div>

<script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
<link rel="stylesheet" href="../handsontables/handsontables.full.css">
<script src="../handsontables/handsontables.full.js"></script>

  
<div id="interface">
  <input type="text" id="experiment_name" placeholder="Experiment Name">
  <br>
  <select id="spreadsheet_selection"></select>
  <br>
  <button type="button" id="new_stim_button" class="collectorButton">New Stimuli Sheet</button>
  <button type="button" id="new_proc_button" class="collectorButton">New Procedure Sheet</button>
  <div><button id="save_btn" class="collectorButton">Save Current Sheet</button></div>
  <div id="sheetArea">
    <div id="sheetTable"></div>
  </div>
</div>


  
  <script>
  
    var handsOnTable;
    
    function createExpEditorHoT(data) {
        $("#sheetArea").html("");
        var container = $("<div>").appendTo($("#sheetArea"))[0];
        
        handsOnTable = createHoT(container, JSON.parse(JSON.stringify(data)));
    }
    
    function get_HoT_data() {
        var data = JSON.parse(JSON.stringify(handsOnTable.getData()));
        
        // remove last column and last row
        data.pop();
        
        for (var i=0; i<data.length; ++i) {
            data[i].pop();
            
            for (var j=0; j<data[i].length; ++j) {
                if (data[i][j] === null) {
                    data[i][j] = '';
                }
            }
        }
        
        // check for unique headers
        var unique_headers = [];
        
        for (var i=0; i<data[0].length; ++i) {
            while (unique_headers.indexOf(data[0][i]) > -1) {
                data[0][i] += '*';
            }
            
            unique_headers.push(data[0][i]);
        }
        
        return data;
    }
    
    function get_current_sheet_path() {
        return $("#experiment_select").val() 
             + '/' 
             + $("#spreadsheet_selection").val();
    }
    
    function custom_alert(msg) {
        create_alerts_container();
        
        var el = $("<div>");
        el.html(msg);
        
        el.css("opacity", "0");
        
        $("#alerts").append(el).show();
        
        el.animate({opacity: "1"}, 600, "swing", function() {
            $(this).delay(5600).animate({height: "0px"}, 800, "swing", function() {
                $(this).remove();
                
                if ($("#alerts").html() === '') {
                    $("#alerts").hide();
                }
            });
        });
    }
    
    var alerts_ready = false;
    
    function create_alerts_container() {
        if (alerts_ready) return;
        
        var el = $("<div>");
        el.css({
            position: "fixed",
            top: "10px",
            left: "10px",
            right: "10px",
            backgroundColor: "#ffc8c8",
            borderRadius: "6px",
            border: "1px solid #DAA",
            color: "#800"
        });
        
        el.attr("id", "alerts");
        
        $("body").append(el);
        
        var style = $("<style>");
        style.html("#alerts > div { margin: 10px 5px; }");
        
        $("body").append(style);
        
        alerts_ready = true;
    }
    
    function save_current_sheet() {
        var data = JSON.stringify(get_HoT_data());
        var file = get_current_sheet_path();
        
        $.post(
            'saveSpreadsheet.php',
            {
                file: file,
                data: data
            },
            custom_alert,
            'text'
        );
    }
  
    function update_spreadsheet_selection() {
      var current_experiment = $("#experiment_name").val();
      
      var exp_data = experiment_files[current_experiment];
      
      var select_html = '<option class="condOption" value="Conditions.csv">Conditions</option>';
      
      select_html += '<optgroup label="Stimuli" class="stimOptions">';
      
      for (var i=0; i<exp_data['Stimuli'].length; ++i) {
        var file = exp_data['Stimuli'][i];
        select_html += '<option value="Stimuli/' + file + '">' + file + '</option>';
      }
      
      select_html += '</optgroup>';
      
      select_html += '<optgroup label="Procedures" class="procOptions">';
      
      for (var i=0; i<exp_data['Procedures'].length; ++i) {
        var file = exp_data['Procedures'][i];
        select_html += '<option value="Procedure/' + file + '">' + file + '</option>';
      }
      
      select_html += '</optgroup>';
      
      $("#spreadsheet_selection").html(select_html);
    }
  
    function create_new_experiment(exp_name) {
      $("#experiment_name").val(exp_name);
      
      var experiment_names = Object.keys(experiment_files);
      experiment_names.push(exp_name);
      
      var options_html ="<option>"+experiment_names.join("</option><option>")+"</option>";
      
      $("#experiment_select").html(options_html);
      
      $('#experiment_select').val(exp_name);
      
      var procedure_options_html ="<option>- select a PROCEDURE file to load it -</option><option>"+Object.keys(new_experiment_data['Procedure']).join("</option><option>")+"</option>";
      
      $("#proc_list").html(procedure_options_html);
      
      var stimuli_options_html ="<option>- select a STIMULI file to load it -</option><option>"+Object.keys(new_experiment_data['Stimuli']).join("</option><option>")+"</option>";
      
      $("#stim_list").html(stimuli_options_html);
      
      experiment_files[exp_name] = {
        Conditions: new_experiment_data['Conditions.csv'],
        Stimuli: Object.keys(new_experiment_data['Stimuli']),
        Procedures: Object.keys(new_experiment_data['Procedure'])
      }
      
      update_spreadsheet_selection();
    }
  
    var experiment_files = <?= json_encode($experiment_files) ?>;
  
    
    
    stim_list_options="<option></option>"
    
    $("#new_experiment_button").on("click",function(){
      var new_name = prompt("What do you want to call your new experient?");
      
      if (typeof experiment_files[new_name] !== "undefined") {
        alert("That name already exists - choose another one");
        $("#new_experiment_button").click();
      } else {
        // contact server to create new structure
        $.post(
          "AjaxNewExperiment.php",
          {
            new_name: new_name
          },
          function(returned_data){
            console.dir(returned_data);
            
            if (returned_data === 'success') {
              create_new_experiment(new_name);
            }
          }
        );
        
        // add new_experiment_data to experiment_files for new experiment name
      }
      
      createExpEditorHoT(new_experiment_data['Conditions.csv']);
      $("#sheet_name_header").val("Conditions");
      $("#interface").show();
      
      
      
    });
    
    $("#experiment_select_button").on("click",function(){
        
    });
    
    $("#experiment_select").on("change",function(){
        $("#experiment_name").val(this.value);
        update_spreadsheet_selection();
        $("#interface").show();
        
        // continue updating the rest of the interface...
    });
    
    $("#save_btn").on("click", save_current_sheet);
    
    var spreadsheets = {};
    
    $("#spreadsheet_selection").on("change", function() {
      var exp_name = $("#experiment_name").val();
      
      if (typeof spreadsheets[exp_name] === "undefined")
        spreadsheets[exp_name] = {};
      
      var sheet_name = this.value;
      if (typeof spreadsheets[exp_name][sheet_name] === 'undefined') {
        $.get(
          'spreadsheetAjax.php',
          {
            sheet: exp_name + '/' + sheet_name
          },
          function(spreadsheet_request_response) {
            if (spreadsheet_request_response.substring(0, 9) === 'success: ') {
              var data = spreadsheet_request_response.substring(9);
              spreadsheets[exp_name][sheet_name] = JSON.parse(data);
              load_spreadsheet(spreadsheets[exp_name][sheet_name]);
            } else {
              console.dir(spreadsheet_request_response);
            }
          }
        );
      } else {
        load_spreadsheet(spreadsheets[exp_name][sheet_name]);
      }
    });
    
    function load_spreadsheet(sheet) {
      createExpEditorHoT(sheet);
    }
  
  </script>
  
  
  
  <?php
  
  exit;

?>

<link rel="stylesheet" href="sheetsEditor.css">
 
<?php
   
  // requiring files and calling in classes
  $thisDirInfo      = new csvDirInfo(); // calling in class for directory information
  $studySheetsInfo  = new csvSheetsInfo(); // calling in class for sheets information
  
  
  // functions //
  
  function copyStudy($source,$dest){
    /*
      $illegalInputs=array('<?','{','}','/','.',"'",',') ; // need to also exclude \
      $legitPosts=array('templateExperiment',
                        'csvPostName');    
      checkPost($_POST,$legitPosts,$illegalInputs);
    */
    global $_DATA,$_PATH;
        
    #create a new study
    $expFolder  = $_PATH->get('Experiments');
    
    $studySource  = $expFolder."/".$source; //$_POST["createStudyName"];
    $studyDest    = $expFolder."/".$dest;   //$_POST["newStudyName"];
    recurse_copy($studySource,$studyDest);
    $_DATA['guiSheets']['thisDir']    = $expFolder."/".$_POST['newStudyName'];
    $_DATA['guiSheets']['studyName']  = $_POST['newStudyName'];
    
    return $_DATA['guiSheets']['studyName'];
    
  }  
  
  function editStudy($originalName,$newName){
    
    global $_PATH,$thisDirInfo;
    
    $thisDirInfo->studyDir      =   $_PATH->get("Experiments")."/".$newName;
    if($newName  !=  $originalName){
      
      //rename folder
      $oldDir = $_PATH->get("Experiments")."/".$originalName;
      $newDir = $_PATH->get("Experiments")."/".$newName;
      rename($oldDir,$newDir);                
    }
    return $newName;
  }
  
  // set filename and copy if necessary

/*
  if(isset($_POST['editStudy'])){
    //  if file has been selected for editing (from higher level index file in GUI folder)
    $thisDirInfo->studyDir            = $_PATH->get("Experiments")."/".$_POST['editStudyName'];    
    $_DATA['guiSheets']['studyName']  = $_POST['editStudyName'];
  } else {    
    //if file has just been created
    if(isset($_POST['createStudy'])){
      $_DATA['guiSheets']['studyName']  = copyStudy($_POST["createStudyName"],$_POST["newStudyName"]);
      $thisDirInfo->studyDir            = $_PATH->get("Experiments")."/".$_POST['newStudyName'];          
    }
    else {  
    
      

      $_DATA['guiSheets']['studyName'] = editStudy($_DATA['guiSheets']['studyName'],$_POST['currStudyName']);  //file is in the process of being edited
      
      $_DATA['guiSheets']['csvSelected']  = $_POST['csvSelected'];    // in case the user is not working from the default ("conditions.csv") spreadsheet
    }
  }
  
  //updating study name
  $thisDirInfo->studyName=$_DATA['guiSheets']['studyName'];

  
  // loading either default csv files, or whichever file was loaded //
  if(!isset($_DATA['guiSheets']['csvSelected'])){ //i.e. if this page has just been opened
    $studySheetsInfo->thisSheetName='Conditions';
    $studySheetsInfo->thisSheetFilename='Conditions.csv';
    $studySheetsInfo->thisSheetFolder='';
  } else { // checking whether browsing to "Conditions.csv";
    if(strcmp($_DATA['guiSheets']['csvSelected'],'Conditions.csv,')==0){
      $studySheetsInfo->thisSheetName='Conditions';
      $studySheetsInfo->thisSheetFolder='';
      $studySheetsInfo->thisSheetFilename="$studySheetsInfo->thisSheetFolder/Conditions.csv";
    }  else {
      $studySheetsInfo->postSheetInfo($_DATA['guiSheets']['csvSelected']);  
    }  
  }

  
  // List csv files in the directories
  $studySheetsInfo->stimSheets  = getCsvsInDir($thisDirInfo->studyDir.'/Stimuli/');
  $studySheetsInfo->procSheets  = getCsvsInDir($thisDirInfo->studyDir.'/Procedure/');
  $studySheetsInfo->legitSheets = array_merge($studySheetsInfo->stimSheets,$studySheetsInfo->procSheets); //note that new sheet and conditions is not in this array  
  

  //checking whether the post is legitimate
  $legitPostNames=array
    ( 'currentGuiSheetPage',
      'currStudyName',
      'csvSelected',
      'sheetName',
      'stimTableInput',
      'newSheet',
      'DeleteSheet',
      'Save');
  $illegalInputs=array('<?','{','}','/','\\'); // need to also exclude \
  
  //insert Tyson's illegal character thing here
    //preg_replace('([^ \\-0-9A-Za-z])', '', $_POST['u']);
   
  checkPost($_POST,$legitPostNames,$illegalInputs); // defined in guiFunctions
         
  
  $branches = scandir($_PATH->get("Experiments"));
  $listStudyNames = array();
  foreach ($branches as $branch) {
    if ($branch === '.' or $branch === '..' or $branch === "_Common") continue;

    if (is_dir($_PATH->get("Experiments") . '/' . $branch)) {
        array_push($listStudyNames,$branch);
    }
  } 
  
  $listStudyNamesJson=json_encode($listStudyNames);
  
  if(isset($_POST['DeleteSheet'])){      //something is being deleted and page isn't being refreshed
    if($refreshSkip == false) {unlink ("$thisDirInfo->studyDir/$studySheetsInfo->thisSheetFilename");}
    $studySheetsInfo->thisSheetName     = 'Conditions';
    $studySheetsInfo->thisSheetFolder   = '';
    $studySheetsInfo->thisSheetFilename = "$studySheetsInfo->thisSheetFolder/Conditions.csv";
  }
  
  
  // creating new sheet //
  
  function createNewSheet($procStim,$studySheetsInfo,$refreshSkip){
    global $_PATH, $thisDirInfo,$_DATA;
    
    $newName  = 0;
    $newNo    = 0;
    
    if($procStim=="Procedure"){
      $thisSheetsList=$studySheetsInfo->procSheets;
    } else { // it is a stimuli sheet being created
      $thisSheetsList=$studySheetsInfo->stimSheets;
    }
    
    if($refreshSkip){                 //  if refresh
      $newNo  = $_DATA['refreshNo'];  //  then load number calculated last time
    } else {    
      while ($newName==0){            //  calculate new number from scratch
        $newNo++;
        if(!in_array("$procStim$newNo.csv",$thisSheetsList)){
          $newName=1;
        }          
      }
      $_DATA['refreshNo'] = $newNo;   // and store in case of refresh
    }
    
    $studySheetsInfo->thisSheetName     = "$procStim$newNo";
    $studySheetsInfo->thisSheetFolder   = "$procStim";
    $studySheetsInfo->thisSheetFilename = "$studySheetsInfo->thisSheetFolder/$studySheetsInfo->thisSheetName.csv";
    copy($_PATH->get("Experiments")."/New Experiment/$procStim/$procStim.csv","$thisDirInfo->studyDir/$studySheetsInfo->thisSheetFilename");   
    return $studySheetsInfo;
  }
  
  if(isset($_POST['newSheet'])){  
    switch ($_POST['newSheet']){
      case "stim":
        //identify what novel filename needs to be          
        $studySheetsInfo  = createNewSheet("Stimuli",$studySheetsInfo,$refreshSkip);
        break;
      case "proc":
        //identify what novel filename needs to be        
        $studySheetsInfo  = createNewSheet("Procedure",$studySheetsInfo,$refreshSkip);
        break;
    }
  }
  
  //Saving whichever csv you are currently working on
  if (isset($_POST['Save'])){ 
    
    // renaming spreadsheet if the user renamed it
    if ($studySheetsInfo->thisSheetName!='Conditions' && strcmp($_POST['sheetName'],$studySheetsInfo->thisSheetName)!=0){      
      $illegalChars = array('  ',' ','.');
      foreach ($illegalChars as $illegalChar){
        $_POST['sheetName'] = str_ireplace($illegalChar,'',$_POST['sheetName']);
      }
      $newFile      = $thisDirInfo->studyDir.'/'.$studySheetsInfo->thisSheetFolder.'/'.$_POST['sheetName'].'.csv';
      $originalFile = $thisDirInfo->studyDir.'/'.$studySheetsInfo->thisSheetFilename;
      copy($originalFile,$newFile);
      unlink($originalFile);
      $studySheetsInfo->thisSheetName     = $_POST['sheetName'];
      $studySheetsInfo->thisSheetFilename = "$studySheetsInfo->thisSheetFolder/$studySheetsInfo->thisSheetName.csv";        
    }

    $stimTableArray = json_decode($_POST['stimTableInput'], true);                            // converting json from POST into array table data into array
		writeHoT("$thisDirInfo->studyDir/$studySheetsInfo->thisSheetFilename",$stimTableArray);   // saving array into HoT format
  }      
  
  // extract table from csv file
  $stimuli  = getFromFile("$thisDirInfo->studyDir/$studySheetsInfo->thisSheetFilename",false,',');  // reading csv file into array
  $stimData = array(array_keys(reset($stimuli)));                                                   // storing header into array
  
  foreach ($stimuli as $row) {
    
    $stimData[] = array_values($row);   //  adding new row to stimData array;
    
  }
  $stimData = json_encode($stimData);   //  json encoding stimData
  
  
  //list all csv files - should this be a function within $studySheetsInfo?
  $studySheetsInfo->stimSheets=getCsvsInDir($thisDirInfo->studyDir.'/Stimuli/');      //  stim sheets
  $studySheetsInfo->procSheets=getCsvsInDir($thisDirInfo->studyDir.'/Procedure/');    //  proc sheets
  $sheetsList=array_merge($studySheetsInfo->stimSheets,$studySheetsInfo->procSheets); //  merging the two together
  
  $jsonSheets=json_encode($sheetsList);

*/
  
?>

<form id="sheetsForm" action='index.php' method='post'>
  <input type="hidden" name="token" value="<?= $_SESSION['token'] ?>"> 
  <input type="button" id="indexButton" class="collectorButton" value="Go back to Index" onclick="document.location.href = '../';" style=" position:absolute;
  right:20px;">
  <h1>
    <textarea id="currStudyName" name="currStudyName" style="color:#069;" rows="1" onkeyup="checkName()"><?=$thisDirInfo->studyName?></textarea>
  </h1>
  
  <span>
    <button name="newSheet" value="stim" class="collectorButton" id="newStimButton"> new stimuli sheet </button>
    <button name="newSheet" value="proc" class="collectorButton" id="newProcButton"> new procedure sheet </button>
    <button type ="button" class ="collectorButton" id="stimButton"> list of stimuli files</button>
  </span>
  <br>  
  <br>
  
  <div>
    
    <select id="csvSelected" name="csvSelected" title="[filename],[folder]">

      <?php  
        // what is the first item in list - either the current sheet or the default sheet ("conditions");
        
        if ($studySheetsInfo->thisSheetFilename !=  ''){            // current sheet
        
          echo"<option  value='$studySheetsInfo->thisSheetName.csv,$studySheetsInfo->thisSheetFolder'>$studySheetsInfo->thisSheetName.csv,$studySheetsInfo->thisSheetFolder</option>";      
          
        } 

        if('Conditions' != $studySheetsInfo->thisSheetName){        // conditions sheet
        
          echo "<option value='Conditions.csv,'>Conditions.csv,</option>";
        
        }      
        
        // rest of the list
        foreach ($studySheetsInfo->procSheets as $procFile){      // procedure files
          if($procFile != "$studySheetsInfo->thisSheetName.csv"){
            echo "<option value='$procFile,Procedure'>$procFile,Procedure</option>";
          }  
        }      
        
        foreach ($studySheetsInfo->stimSheets as $stimFile){      // stimuli files
          if($stimFile != "$studySheetsInfo->thisSheetName.csv"){
            echo "<option value='$stimFile,Stimuli'>$stimFile,Stimuli</option>";  
          }
        }
      ?>    
    </select>
  
    <button type='submit' class='collectorButton' value='Select'>Open</button>
  
  </div>    
  
  
  <?php
    if (strcmp("$studySheetsInfo->thisSheetName.csv","Conditions.csv")==0){ ?>
      <h2 title="You cannot edit the Conditions.csv filename or delete the file.">Conditions.csv</h2>
  <?php    
    }  else {
  ?>
      
      <h2>
        <textarea id="sheetName" name="sheetName" style="color:#069;" rows="1" onchange="checkSheetName()"><?=$studySheetsInfo->thisSheetName?></textarea>
      </h2>
  <?php 
    }

?>
    <br>

  <input type="hidden" name="stimTableInput">
    
  <input id="saveButton" type="button" class="collectorButton" value="Save">  
  <button id="submitButton" type="submit" name="Save" class="collectorButton" style="display:none"></button> 

  <?php
    if (strcmp("$studySheetsInfo->thisSheetName.csv",'Conditions.csv')!=0){  ?>
      <input type="button" id="deleteSheetButton" name="DeleteSheetQuestion" class="collectorButton" value="Delete?">  
      <button id="deleteActivate" type="submit" name="DeleteSheet" class="collectorButton" value="Delete" style="display:none">No text needed</button>
  <?php    
    }  
  ?>      
  
</form>


<script type="text/javascript">

  //importing json encoded lists from php
  listStudyNames  = <?=$listStudyNamesJson?>;
  listSheetsNames = <?=$jsonSheets?>;
  var stimData = <?= $stimData ?>;
   
</script>

<script src="sheetsEditor.js"></script>
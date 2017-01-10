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
  
  #helperBar {
    position: absolute;
    left: 75%;
    top: 0%;
    display: inline-block;
    width: 20%;
    background-color: #EFE;
    border: 2px solid #6D6;
    border-radius: 8px;
    box-sizing: border-box;
    padding: 10px;
    vertical-align: top;
    margin-top: 180px;
  }
  
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

<div> <?php require("HelperBar.php"); ?> </div>

  
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
    
    $("#experiment_select").on("change",function(){
        $("#experiment_name").val(this.value);
        update_spreadsheet_selection();
        $("#interface").show();
        
        $("#spreadsheet_selection").val("Conditions.csv");
        $("#spreadsheet_selection").change();
        
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
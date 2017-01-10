<?php
  
/*
   Survey Editor:
   -Open and view all surveys
   -change contents of sheet
   -copy/rename/delete sheets
   
   -start by scanning all surveys to find all sheets
   -display list of surveys
   -alternatively, let them create a new survey
   -can open sheets, or copy to new sheet and start editing that
   -can also copy survey to edit that
 */

  require "../../initiateTool.php";
  require 'fileReadingFunctions.php';
  
  require_once ('../guiFunctions.php');
  require('../guiClasses.php');


  $archived_surveys = file_get_contents("archived_files.txt");
  $archived_surveys = explode(",",$archived_surveys);
  $archived_surveys_json = json_encode($archived_surveys);
  
  $surveys = getCsvsInDir($_FILES->get_path('Common')."/Surveys"); 
  
  $new_exp_json = file_get_contents('default_new_experiment.json'); // may delete
?>

<style>
  #interface{
    display:none;
    width: 70%;
  }
  
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

  .condOption         { background-color: #DFD; }
  .archived_survey{
    display:none;
  }
  
</style>

<script src="../HandsontableFunctions.js"></script>

<script>
  var new_experiment_data = <?= $new_exp_json ?>; // may delete
</script>

<div id="load_toolbar">
  <button type="button" id="new_survey_button" class="collectorButton">New Survey</button>
  <span id="survey_span">    
  </span>
  
  <button type="button" id="copy_survey_button" class="collectorButton">Copy</button>
  <button type="button" id="archive_survey_button" class="collectorButton">Archive</button>
  <button type="button" id="unarchive_survey_button" class="collectorButton" style="display:none">Unarchive</button>
  <button type="button" id="delete_survey_button" class="collectorButton" style="display:none">Delete</button>
  <label>
    Show archived files
    <input type="radio" class="archive_radio" value="show_archive" name="show_hide_archive">
  </label>
  <label>
    Hide archived files
    <input type="radio" class="archive_radio" value="hide_archive" name="show_hide_archive" checked>
  </label>
</div>

<script>

  
  var survey_files = <?= json_encode($surveys) ?>;
  console.dir(survey_files);
  var archived_surveys = <?= $archived_surveys_json ?>;
  console.dir(archived_surveys);

  
  update_survey_span('<option value="" hidden disabled selected>Select a Survey</option>');
  
  function update_survey_span(default_option){
    var current_survey = $("#survey_select").val();
    survey_span_content = "<select id='survey_select'>"+default_option;
    for(i=0; i<survey_files.length;i++){
      
      //$("input[name=show_hide_archive]:checked").val()== "show_archive"
      
      
      if(archived_surveys.indexOf(survey_files[i]) == -1){ 
        survey_span_content += "<option>"+survey_files[i]+"</option>";
      } else {
        if($("input[name=show_hide_archive]:checked").val()== "show_archive"){
          survey_span_content += "<option class='archived_survey' style='display:initial'>"+survey_files[i]+"</option>"; 
        } else {
          survey_span_content += "<option class='archived_survey' >"+survey_files[i]+"</option>"; 
          
        }
      }
    }
    survey_span_content += "</select>";
    $("#survey_span").html(survey_span_content);
    $("#survey_select").val(current_survey);
    $("#survey_select").on("change",function(){
        $("#survey_name").val(this.value);
        $("#interface").show();
        
        sheet_selection_function();
        
        archive_unarchive_buttons_show_hide(this.value);
       
        // continue updating the rest of the interface...
    });
    
  }


  function archive_unarchive_buttons_show_hide(current_survey){
      if(archived_surveys.indexOf(current_survey) !== -1){
        $("#unarchive_survey_button").show();
        $("#archive_survey_button").hide();
        $("#delete_survey_button").show();
        
      } else {
        $("#archive_survey_button").show();
        $("#unarchive_survey_button").hide();
        $("#delete_survey_button").hide();
        
      }
    }
  
  $(".archive_radio").on("click",function(){
    console.dir(this.value);
    if(this.value=="show_archive"){
      $(".archived_survey").show();
    } else {
      $(".archived_survey").hide();
    }
  });
  
  
  </script>

<div id="rest_of_interface">  <!-- delete??? -->
</div>

<script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
<link rel="stylesheet" href="../handsontables/handsontables.full.css">
<script src="../handsontables/handsontables.full.js"></script>

  
<div id="interface">
  <input type="text" id="survey_name" placeholder="Experiment Name">
  
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
        return $("#survey_select").val()              
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
        var filename = $("#survey_name").val();
        
        $.post(
            'saveSpreadsheet.php',
            {
                file: file,
                data: data,
                survey_name: filename
            },
            custom_alert,
            'text'
        );
    }
  
    function create_new_experiment(survey_name) {
      $("#survey_name").val(survey_name);
      
      var survey_names = Object.keys(survey_files);
      survey_names.push(survey_name);
      
      var options_html ="<option>"+survey_names.join("</option><option>")+"</option>";
      
      $("#survey_select").html(options_html);
      
      $('#survey_select').val(survey_name);
      
      var procedure_options_html ="<option>- select a PROCEDURE file to load it -</option><option>"+Object.keys(new_experiment_data['Procedure']).join("</option><option>")+"</option>";
      
      $("#proc_list").html(procedure_options_html);
      
      var stimuli_options_html ="<option>- select a STIMULI file to load it -</option><option>"+Object.keys(new_experiment_data['Stimuli']).join("</option><option>")+"</option>";
      
      $("#stim_list").html(stimuli_options_html);
      
      survey_files[survey_name] = {
        /* Conditions: new_experiment_data['Conditions.csv'],
        Stimuli: Object.keys(new_experiment_data['Stimuli']),
        Procedures: Object.keys(new_experiment_data['Procedure']) */
      }
      
    }
  
    
    stim_list_options="<option></option>"
    
    $("#new_survey_button").on("click",function(){
      var new_name = prompt("What do you want to call your new experient?");
      
      if (typeof survey_files[new_name] !== "undefined") {
        alert("That name already exists - choose another one");
        $("#new_survey_button").click();
      } else {
        // contact server to create new structure
        $.post(
          "AjaxNewSurvey.php",
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
        
        // add new_experiment_data to survey_files for new experiment name
      }
      
      createExpEditorHoT(new_experiment_data['Conditions.csv']);
      $("#sheet_name_header").val("Conditions");
      $("#interface").show();
      
    });
    
    
    $("#copy_survey_button").on("click",function(){
      var new_name = prompt("What do you want to call your new experient?");
      var old_survey = $("#survey_select").val();
      
      if (typeof survey_files[new_name] !== "undefined") {
        alert("That name already exists - choose another one");
        $("#copy_survey_button").click();
      } else {
        if($("#survey_select").val()==null){
          
          alert("Please select a survey to copy from");
          
        } else {
          console.dir(new_name);
          if(new_name == ""){ 
            
          } else {
          
            // checks here that name is acceptable assuming they are:
          
              survey_files.push(new_name+".csv"); // unless they were giving an unnacceptable name
            update_survey_span();
          
            // contact server to create new structure
            $.post(
              "AjaxCopySurvey.php",
              {
                new_name: new_name,
                old_survey: old_survey
              },
              function(returned_data){
                console.dir(returned_data);
                
                if (returned_data === 'success') {
                  create_new_experiment(new_name);
                }
              }
            );
          }
        }
        
      }
      
      
    });
    
    
    $("#unarchive_survey_button").on("click",function(){
      if($("#survey_select").val()==null){
        alert("Please select a survey to unarchive");
      } else {
        archived_survey = $("#survey_select").val();
        // contact server to create new structure
        $.post(
          "Unarchive_Survey.php",
          {
            archived_survey: archived_survey
          },
          function(returned_data){
            console.dir(returned_data);
            
          }
        );
      }
      var splice_index = archived_surveys.indexOf(archived_survey);
      archived_surveys.splice(splice_index,1);
      $("#unarchive_survey_button").hide();
      $("#archive_survey_button").show();
      $("#delete_survey_button").hide();
      update_survey_span();

    });

    
    $("#archive_survey_button").on("click",function(){
      if($("#survey_select").val()==null){
        alert("Please select a survey to archive");
      } else {
        archived_survey = $("#survey_select").val();
        console.dir(archived_survey);
        // contact server to create new structure
        $.post(
          "Archive_Survey.php",
          {
            archived_survey: archived_survey
          },
          function(returned_data){
            console.dir(returned_data);
            
          }
        );
        archived_surveys.push(archived_survey)
        $("#unarchive_survey_button").show();
        $("#archive_survey_button").hide();
        $("#delete_survey_button").show();
        update_survey_span();
      }
      
    });
    
    $("#delete_survey_button").on("click",function(){
      var confirm_delete = confirm("Are you sure you want to delete this survey? This cannot be reversed!");
      if(confirm_delete == true){
        var deleted_survey = $("#survey_select").val();
        $.post(
          "DeleteSurvey.php",
          {
            deleted_survey: deleted_survey
          },
          function(returned_data){
            console.dir(returned_data);            
          }
          
        );
        var archive_splice_index = archived_surveys.indexOf(deleted_survey);
        archived_surveys.splice(archive_splice_index,1);

        var delete_splice_index = survey_files.indexOf(deleted_survey);
        survey_files.splice(delete_splice_index,1);
          
        update_survey_span('<option value="" hidden disabled selected>Select a Survey</option>');
        $("#survey_name").val("");
        $("#sheetArea").hide();

      }
    });

    
    
    
    $("#save_btn").on("click", save_current_sheet);
    
    var spreadsheets = {};
    
    function sheet_selection_function(){
      var survey_name = $("#survey_name").val();
      
      if (typeof spreadsheets[survey_name] === "undefined")
        spreadsheets[survey_name] = {};
      
      var sheet_name = this.value;
      if (typeof spreadsheets[survey_name][sheet_name] === 'undefined') {
       
        $.get(
          'spreadsheetAjax.php',
          {
            sheet: survey_name
          },
          function(spreadsheet_request_response) {
/*             if (spreadsheet_request_response.substring(0, 9) === 'success: ') {
              var data = spreadsheet_request_response.substring(9);
              spreadsheets[survey_name] = JSON.parse(data);
              load_spreadsheet(spreadsheets[survey_name]);
 Anthony's shitty fix to problem*/
            var success_output = spreadsheet_request_response.split("success: ");
            if(success_output.length>1){
              var data = success_output[1];
              spreadsheets[survey_name] = JSON.parse(data);
              load_spreadsheet(spreadsheets[survey_name]);
            } else {
              console.dir("error:"+spreadsheet_request_response);
            }
          }
        );
      } else {
        load_spreadsheet(spreadsheets[survey_name]);
      }
      $("#sheetArea").show();
    }
    
    
    
    function load_spreadsheet(sheet) {
      createExpEditorHoT(sheet);
    }
  
  </script>
  
 
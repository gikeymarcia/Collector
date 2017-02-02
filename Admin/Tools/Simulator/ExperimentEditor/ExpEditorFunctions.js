function new_proc_stim_sheet(data,filetype){
  var file = prompt("What would you like the name of the new procedure sheet to be?");
  
  var exp_name = $("#experiment_select").val();
  
  $.post(
    'ExperimentEditor/newExpFile.php',
      {
        file: file,
        data: data,
        filetype: filetype,
        exp_name: exp_name
      },
      custom_alert,
      'text'
  );
  if(filetype == "Stimuli"){
    $("#stim_select").append("<option>"+file+".csv</option>");
    
    experiment_files[exp_name]["Stimuli"].push(file+".csv");
    
    stim_proc_selection("Stimuli",file+".csv"); 
    
    $("#stim_select").val(file+".csv");
    
    //load_spreadsheet(stim_spreadsheet,"Stimuli");
  } else if (filetype == "Procedure"){
    $("#proc_select").append("<option>"+file+".csv</option>");
    
    experiment_files[exp_name]["Procedures"].push(file+".csv");
    
    stim_proc_selection("Procedure",file+".csv"); 
    
    $("#proc_select").val(file+".csv");
  } else {
      // something has gone wrong
  }
}

function createExpEditorHoT(sheet,selected_handsonTable) {
  
  if(selected_handsonTable == "Conditions"){
    // Conditions
    $("#conditionsArea").html("");
    var container = $("<div>").appendTo($("#conditionsArea"))[0];
    handsOnTable_Conditions = createHoT(container, JSON.parse(JSON.stringify(sheet)));        
  }
  if(selected_handsonTable == "Stimuli"){
    // Stim
    $("#stimsArea").html("");
    var container = $("<div>").appendTo($("#stimsArea"))[0];
    handsOnTable_Stimuli = createHoT(container, JSON.parse(JSON.stringify(sheet)));        
  }
  if(selected_handsonTable == "Procedure"){
    // Proc
    $("#procsArea").html("");
    var container = $("<div>").appendTo($("#procsArea"))[0];
    handsOnTable_Procedure = createHoT(container, JSON.parse(JSON.stringify(sheet)));
    
  }
    
}

function get_HoT_data(current_sheet) { // needs to be adjusted for 
    
    var data = JSON.parse(JSON.stringify(current_sheet.getData()));
    
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

function get_current_sheet_path(sheet_type,selected_stim,selected_proc) {
  var sheet_path;
  if(sheet_type == handsOnTable_Conditions){
    sheet_path = "Conditions.csv";
  }
  if(sheet_type == handsOnTable_Stimuli){
    sheet_path = "Stimuli/"+selected_stim;
  }
  if(sheet_type == handsOnTable_Procedure){
    sheet_path = "Procedure/"+selected_proc;        
  }
  return $("#experiment_select").val() 
       + '/' 
       + sheet_path;
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

function save_current_sheet(selected_stim,selected_proc) {
  // loop through all open sheets
  
  var handsontables_list = [handsOnTable_Conditions,handsOnTable_Stimuli,handsOnTable_Procedure];
        
  for(i=0;i<handsontables_list.length;i++){
    var data = JSON.stringify(get_HoT_data(handsontables_list[i]));
    
    var file = get_current_sheet_path(handsontables_list[i],selected_stim,selected_proc);
    
    $.post(
        'ExperimentEditor/saveSpreadsheet.php',
        {
            file: file,
            data: data
        },
        custom_alert,
        'text'
    );
  }
    
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
  
  //$("#spreadsheet_selection").html(select_html);
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

function save_current_sheets(){
  var current_stim_sheet = $("#stim_select").val();
  var current_proc_sheet = $("#proc_select").val();
  save_current_sheet(current_stim_sheet,current_proc_sheet); 
}


function stim_proc_selection(stim_proc,sheet_selected){
  var exp_name = $("#experiment_select").val();
  
  if (typeof spreadsheets[exp_name] === "undefined")
    spreadsheets[exp_name] = {};
  
  if(stim_proc == "Conditions"){
    sheet_name = sheet_selected;
  } else {
    var sheet_name = stim_proc+"/"+sheet_selected;
  }
  if (typeof spreadsheets[exp_name][sheet_name] === 'undefined') {
    $.get(
      'ExperimentEditor/spreadsheetAjax.php',
      {
        sheet: exp_name + '/' + sheet_name
      },
      function(spreadsheet_request_response) {
        if (spreadsheet_request_response.substring(0, 9) === 'success: ') {
          var data = spreadsheet_request_response.substring(9);
          createExpEditorHoT(JSON.parse(data),stim_proc);              
        } else {
          console.dir(spreadsheet_request_response);
        }
      }
    );
  } else {
    createExpEditorHoT(spreadsheets[exp_name],stim_proc);
  }
}
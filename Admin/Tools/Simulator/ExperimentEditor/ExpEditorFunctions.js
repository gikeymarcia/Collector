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

function createExpEditorHoT(sheet,selected_handsonTable, sheet_name) {
  if (selected_handsonTable == "Conditions") {
    var area = $("#conditionsArea");
    var table_name = 'handsOnTable_Conditions';
  } else if (selected_handsonTable == "Stimuli") {
    var area = $("#stimsArea");
    var table_name = 'handsOnTable_Stimuli';
  } else {
    var area = $("#procsArea");
    var table_name = 'handsOnTable_Procedure';
  }
  
  if (window[table_name] !== null) {
    save_current_sheet(
        area.find(".sheet_name").html(),
        get_HoT_data(window[table_name])
    );
  }
  
  area.html("<span class='sheet_name' style='display: none'>" + sheet_name + "</span>");
  var container = $("<div>").appendTo(area)[0];
  window[table_name] = createHoT(container, JSON.parse(JSON.stringify(sheet)));
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
  var files = {
    "Conditions": ["#conditionsArea", handsOnTable_Conditions],
    "Stimuli":    ["#stimsArea",      handsOnTable_Stimuli],
    "Procedure":  ["#procsArea",      handsOnTable_Procedure]
  }
  
    if(files["Conditions"][1] !== null){
        for (var file_type in files) {
            var selector = files[file_type][0];
            var table    = files[file_type][1];
            
            var file_path = $(selector).find(".sheet_name").html();
            var file_data = get_HoT_data(table);
            
            save_current_sheet(file_path, file_data);
        }      
    } else {
        custom_alert("You don't have an experiment loaded to save");
    }
}

function save_current_sheet(file_path, file_data) {
  $.post(
    'ExperimentEditor/saveSpreadsheet.php',
    {
      file: file_path,
      data: JSON.stringify(file_data)
    },
    custom_alert,
    'text'
  );
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
  
  sheet_name = exp_name + '/' + sheet_name;
  
  $.get(
    'ExperimentEditor/spreadsheetAjax.php',
    {
      sheet: sheet_name
    },
    function(spreadsheet_request_response) {
      if (spreadsheet_request_response.substring(0, 9) === 'success: ') {
        var data = spreadsheet_request_response.substring(9);
        createExpEditorHoT(JSON.parse(data),stim_proc, sheet_name);              
      } else {
        console.dir(spreadsheet_request_response);
      }
    }
  );
}
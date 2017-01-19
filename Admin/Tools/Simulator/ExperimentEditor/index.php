
<div id="rest_of_interface">  
</div>

<script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
<link rel="stylesheet" href="../handsontables/handsontables.full.css">
<script src="../handsontables/handsontables.full.js"></script>

 

<table id="exp_data">
  <tr>
    <div id="Conditions" class="hide_show_elements"> 
      <h3>Conditions</h3>
      <div id="conditionsArea"> Select study
        <!-- <div id="sheetTable"></div> -->
      </div>
    </div>
  </tr>
  <tr>
    <td id="Stimuli" class="hide_show_elements">
      <h3>Stimuli</h3>
      <select id="stim_select"></select>
      <button type="button" id="new_stim_button" class="collectorButton">New Stimuli Sheet</button>

        
      <span id="stimsArea">
        <div id="stimsheetTable"></div> 
      </span>

    </td>
    
    <td id="Procedure" class="hide_show_elements">
      <h3>Procedure</h3>
      <select id="proc_select"></select>
      <button type="button" id="new_proc_button" class="collectorButton">New Procedure Sheet</button>
    
      <span id="procsArea">
      <div id="procSheetTable"></div>
      </span>
    </td>
    
    <td id="resp_area">
        <select style="visibility: hidden"><option>Select</option></select>
        <div id="resp_data" class="custom_table"></div>
    </td>
  </tr>
</table>

<script>
  $("#new_proc_button").on("click",function(){
    var data = new_experiment_data["Procedure"]["Procedure.csv"];
    var filetype = "Procedure";
    new_proc_stim_sheet(data,filetype);
  });  
  
  $("#new_stim_button").on("click",function(){
    var data = new_experiment_data["Stimuli"]["Stimuli.csv"];
    var filetype = "Stimuli";
    new_proc_stim_sheet(data,filetype);
  });
  
  function new_proc_stim_sheet(data,filetype){
    var file = prompt("What would you like the name of the new procedure sheet to be?");
    
    var exp_name = $("#experiment_select").val();
    
    console.dir(data);
    
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

</script>


<!-- can delete following divs?? --> 
<div id="interface">
  
  
  
  <div id="stim_proc_area">
    
    
    
  </div>
</div>

<script>

  var experiment_files = <?= json_encode($experiment_files) ?>;

  $("#experiment_select").on("change",function(){
    
    current_stim_list = experiment_files[this.value]['Stimuli'];
    $("#stim_select").html('');
    for(i=0; i<current_stim_list.length;i++){
      $("#stim_select").append("<option>"+current_stim_list[i]+"</option>");      
    }
    
    $("#stim_select").on("change",function(){
      stim_proc_selection("Stimuli",this.value);
    });
    
    
    
    current_proc_list = experiment_files[this.value]['Procedures'];
    $("#proc_select").html('');
    for(i=0; i<current_proc_list.length;i++){
      $("#proc_select").append("<option>"+current_proc_list[i]+"</option>");      
    }
    
    $("#proc_select").on("change",function(){
      stim_proc_selection("Procedure",this.value);
    });

           
    stim_proc_selection("Conditions","Conditions.csv");
    stim_proc_selection("Stimuli",current_stim_list[0]); //  auto open first file in stim list
    stim_proc_selection("Procedure",current_proc_list[0]); //  auto open first file in proc list
    
    
    (function (){
        
      $("#stim_select, #proc_select").on("focus",function(){
        
        previous_stim = $("#stim_select").val();
        previous_proc = $("#proc_select").val();
      }).change(function(){
        
        save_current_sheet(previous_stim,previous_proc);      
      
      });
      
    })();
    
  });


</script>
 

<div> <?php require("HelperBar.php"); ?> </div>

  
  <script>
  
  
    var handsOnTable;
    
    
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
    
    function save_current_sheet(selected_stim,selected_proc) {
      // loop through all open sheets
      
      var handsontables_list = [handsOnTable_Conditions,handsOnTable_Stimuli,handsOnTable_Procedure];
            
      for(i=0;i<handsontables_list.length;i++){
        var data = JSON.stringify(get_HoT_data(handsontables_list[i]));
        
        console.dir(selected_stim);
        console.dir(selected_proc);
        
        var file = get_current_sheet_path(handsontables_list[i],selected_stim,selected_proc);
        
        console.dir(file);
        
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
  
    
    stim_list_options="<option></option>"
    
    $("#new_experiment_button").on("click",function(){
      var new_name = prompt("What do you want to call your new experient?");
      
      if (typeof experiment_files[new_name] !== "undefined") {
        alert("That name already exists - choose another one");
        $("#new_experiment_button").click();
      } else {
        // contact server to create new structure
        $.post(
          "ExperimentEditor/AjaxNewExperiment.php",
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
      
      var conditions_data = new_experiment_data['Conditions.csv'];
      var stim_data       = new_experiment_data['Stimuli']['Stimuli.csv']; // this needs to just open the first sheet in list
      
      var proc_data       = new_experiment_data['Procedure']['Procedure.csv']; // this needs to just open the first sheet in list
      
        
      createExpEditorHoT(conditions_data,"Conditions");
      createExpEditorHoT(stim_data,"Stimuli");      
      createExpEditorHoT(proc_data,"Procedure");
      
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
    
    $("#save_btn").on("click", function(){
      var current_stim_sheet = $("#stim_select").val();
      var current_proc_sheet = $("#proc_select").val();
      save_current_sheet(current_stim_sheet,current_proc_sheet);
    });
    
    var spreadsheets = {};
    
    function stim_proc_selection(stim_proc,sheet_selected){
      var exp_name = $("#experiment_select").val();
      
      if (typeof spreadsheets[exp_name] === "undefined")
        spreadsheets[exp_name] = {};
      
      if(stim_proc == "Conditions"){
        sheet_name = sheet_selected;
      } else {
        var sheet_name = stim_proc+"/"+sheet_selected;
      }
      console.dir(sheet_name);
      if (typeof spreadsheets[exp_name][sheet_name] === 'undefined') {
        $.get(
          'ExperimentEditor/spreadsheetAjax.php',
          {
            sheet: exp_name + '/' + sheet_name
          },
          function(spreadsheet_request_response) {
            if (spreadsheet_request_response.substring(0, 9) === 'success: ') {
              var data = spreadsheet_request_response.substring(9);
              //spreadsheets[exp_name][sheet_name] = JSON.parse(data);
              //load_spreadsheet(spreadsheets[exp_name][sheet_name],stim_proc);
              load_spreadsheet(JSON.parse(data),stim_proc);
              console.dir(spreadsheets[exp_name][sheet_name]);
              
              
            } else {
              console.dir(spreadsheet_request_response);
            }
          }
        );
      } else {
        load_spreadsheet(spreadsheets[exp_name],stim_proc);
      
      }
    }
    
    function load_spreadsheet(sheet,selected_handsonTable) {
      
      // this function appears to be mostly redundant, and only relaying to another function
      
      createExpEditorHoT(sheet,selected_handsonTable);
      
    }
  
  </script>
function save_trial_types() {
    $("#trial_type_data .modified").each(function() {
        var trial_type = $(this).closest(".trial_type_row").find(".trial_type_name").html();
        var file = $(this).data("file");
        var code = this.value;
        
        save_trial_type(trial_type, file, code);
        
        $(this).removeClass("modified");
    });
}

function save_trial_type(trial_type, file, script){
    $.post(
        'TrialTypeEditor/saveTrialType.php',
        {
            file: trial_type + "/" + file,
            data: script
        },
        custom_alert,
        'text'
    );
};

// use the normal Experiment object as a prototype so we can modify
// how the object saves data to the server
var Gui_Experiment = function() {
    Experiment.apply(this, arguments);
}

Gui_Experiment.prototype = Object.create(Experiment.prototype);

Gui_Experiment.prototype.record_to_server = function(trial_sets) {
    trial_sets.forEach(function(set) {
        set.forEach(function(trial) {
            trial.recorded = true;
        });
    });
    
    // do nothing else, do not actually ajax data to server
}

// re-run the experiment every time a relevant table is changed
var Collector_Experiment = null;

function simulate_experiment() {
    container = $("#ExperimentContainer");
    container.html("");
    // collect the current definitions of the experiment data and trial types, and create an experiment
    Collector_Experiment = new Gui_Experiment(
        get_exp_data(),
        container,
        trial_page,
        get_trial_types(),
        server_paths
    );
    
    // run the first trial of the new experiment
    Collector_Experiment.run_trial();
}

function exp_is_ready_to_simulate() {
    if (   $("#stim_data").children().length > 0
        && $("#proc_data").children().length > 0
    ) {
        return true;
    } else {
        return false;
    }
}

function get_exp_data() {
  
    var stim = get_HoT_data(handsOnTable_Stimuli); // 
    stim = associate_data(stim);
    
    var proc = get_HoT_data(handsOnTable_Procedure); // 
    proc = associate_data(proc);
    
    var glob = {
        position: [0,0]
    };
    
    return {
        stimuli:   stim,
        procedure: proc,
        globals:   glob,
        responses: {}
    };
}

function get_trial_types() {
    var raw_data = get_table_data($("#trial_type_data")[0]);
    
    var trial_types = {};
    
    for (var i=0; i<raw_data.length; ++i) {
        trial_types[raw_data[i]["Trial Type"]] = {
            template:       raw_data[i]["Template"],
            scoring:        raw_data[i]["Scoring"],
            prepare_inputs: raw_data[i]["Prepare Inputs"]
        }
    }
    
    return trial_types;
}

// load the data onto the tables

function create_trial_type(name, data) {
    data = data || { template: "", scoring: "", prepare_inputs: "" };
    
    var row = $("<div class='trial_type_row'>");
    
    row.append("<div class='trial_type_name'>" + name + "</div>");
    
    for (var file in data) {
        var val = data[file];
        
        if (val === null) val = '';
        
        row.append("<div class='textareaDiv' id='"+name+file+"_id'>"
            + "<textarea id='"+name+file+"_textarea' data-file='"+file+"'>"
                + val.replace(/</g, '&lt;')
            + "</textarea></div>"
        );
        
    }
    
    $("#trial_type_data").append(row);
    $("#trial_type_select").append("<option>"+name+"</option>");
    
}

for (var trial_type in trial_types) {
    create_trial_type(trial_type, trial_types[trial_type]);
}

function show_trial_type(trial_type,file){
  $("#trial_type_data > div > div").hide();
  $("#"+trial_type+file+"_id").show();
  
}

// Sample Data
var raw_stim_data = [
    ['Cue', 'Answer'],
    ['AAA', 'Apple' ],
    ['BBB', 'Banana'],
    ['CCC', 'Cat'   ],
    ['DDD', 'Dog'   ],
    ['EEE', 'Ent'   ],
    ['FFF', 'Fairy' ]
];

var raw_proc_data = [
    ['Item', 'Trial Type', 'Max Time', 'Text'],
    ['0',    'instruct',   'user',     'this is an instruct'],
    ['2',    'cue',        'user',     ''],
    ['3',    'cue',        'user',     ''],
    ['4',    'wordpair',   'user',     ''],
    ['5',    'wordpair',   'user',     ''],
    ['0',    'cuedRecall', 'user',     ''],
];

function populate_table($table, data) {
    var headers = true;
    
    data.forEach(function(row) {
        var row_div = $("<div>");
        
        if (headers) {
            row_div.addClass("headers");
            headers = false;
        }
        
        row.forEach(function(cell) {
            row_div.append("<div>" + cell + "</div>");
        });
        
        row_div.children().prop("contenteditable", true);
        
        $table.append(row_div);
    });
}

populate_table($("#stim_data"), raw_stim_data);
populate_table($("#proc_data"), raw_proc_data);


function get_table_data(table) {
    return associate_data(get_raw_table_data(table));
}

function get_raw_table_data(table) {
    var rowI, rowN = table.children.length,
        colI, colN = table.children[0].children.length;
    var output = new Array(rowN), row;
    var cell;
    
    for (rowI=0; rowI<rowN; ++rowI) {
        row = new Array(colN);
        for (colI=0; colI<colN; ++colI) {
            cell = table.children[rowI].children[colI];
            
            if (cell.isContentEditable) {
                row[colI] = cell.innerHTML.replace(/<br>/g,  "\n")
                                          .replace(/&lt;/g,  '<')
                                          .replace(/&gt;/g,  '>')
                                          .replace(/&amp;/g, '&');
            } else if (cell.children.length === 0) {
                row[colI] = cell.innerHTML;
            } else {
                row[colI] = cell.children[0].value;
            }
        }
        output[rowI] = row;
    }
    
    return output;
}

function associate_data(data) {
    var rowI, rowN = data.length,
        colI, colN = data[0].length;
    var output = new Array(rowN-1), row;
    
    for (rowI=1; rowI<rowN; ++rowI) {
        row = {};
        
        for (colI=0; colI<colN; ++colI) {
            row[data[0][colI]] = data[rowI][colI];
        }
        
        output[rowI-1] = row;
    }
    
    return output;
}

function trialtype_to_canvas(current_trialtype_template){
  
    // wipe temp_GUI_Var details from previous trialtype
    if(typeof(temp_GUI_Var) !== "undefined"){
        temp_GUI_Var='';            
    }
   
   
  var new_iframe = $("<iframe id='canvas_iframe'>");  
  $("#canvas").html(""); //wipe canvas
  new_iframe.appendTo("#canvas");
  
  // detect and remove scripts here
  
  scriptless_trialtype_template = current_trialtype_template.replace(/<script>/g,"<script>___script___")
  
  scriptless_trialtype_template = scriptless_trialtype_template.split(/<script>|<\/script>/g);
  
  var iframe_width = $("iFrame").width();
  var mouseover_mouseout = "onmouseover='this.style.color=\"black\"' "+
                           "onmouseout='this.style.color=\"white\"' ";
  
  script_style="style='position:absolute;bottom:0px;left:0px;width:"+iframe_width+"px;background-color:blue;color:white;opacity:90%;padding:0px;text-align:center;display:none'";
  
  var no_scripts = 0;
  
  for(var i=0;i<scriptless_trialtype_template.length;i++){
    if(scriptless_trialtype_template[i].indexOf("___script___")!==-1){
      if(no_scripts == 0){
        no_scripts++;
        var this_script = scriptless_trialtype_template[i].replace("___script___","");
        var script_no = 0; // though this can be tidied up also
        interaction_manager.current_trial_type_script=this_script;
        // the first part of this string includes script      
        scriptless_trialtype_template[i] = "<span "+mouseover_mouseout+" "+script_style+" onclick='edit_script("+script_no+")' class='script_element' id='gui_script'>___script"+script_no+"___</span>";
      } else {
        no_scripts++;
        alert("For use of the GUI, you must only load trialtypes with only one script. This trialtype as at least "+no_scripts+". Only loading the first script.");
      }      
    }
  }
  
  
  var doc = new_iframe[0].contentDocument;

  var header =  '<!DOCTYPE html>'+
                '<html>'+
                '<head>'+
                 ' <title>Tests</title>'+
                 ' <meta charset="utf-8">'+
                 ' <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script>'+
                 '</head>'+
                 '<div id="canvas_in_iframe">';
  var footer = '</div>';
  var file_version = (typeof canvas_file_mod === "undefined")
                    ? Date.now() //
                    : canvas_file_mode; //
  var canvas_script = '<script src="GUI/canvas_iframe.js?v='+file_version+'"></script>'; 
  
  // insert promise here
  
  function write_canvas(processed_template){
    doc.open();
    doc.write(header+processed_template+canvas_script+footer); 
    doc.close();    
  }
  
  function after_write_canvas(){
    element_management.canvas_elements_update();
    element_management.update_lists(); // rename object and function    
  }
  
  // could store everything into a temp div, use javascript to replace pics etc. with divs, capture everything in the div and then proceed
  
  $("#preprocessing_trialType").html(scriptless_trialtype_template);
  
  // process each element type

  
  these_divs = $("#preprocessing_trialType").find("div");
  for(var i=0;i<these_divs.length;i++){
    these_divs[i].className = "text_element";
  }
  these_spans = $("#preprocessing_trialType").find("span");
  for(var i=0;i<these_spans.length;i++){
    
    if(these_spans[i].className !== "script_element"){
      these_spans[i].className = "text_element";
    }
  }
  
  
    // processing media which are converted into spans

    these_images = $("#preprocessing_trialType").find("img");  
    these_videos = $("#preprocessing_trialType").find("video");  
    these_audios = $("#preprocessing_trialType").find("audio");  

    trialtype_to_canvas_stimuli(these_images,"image");
    trialtype_to_canvas_stimuli(these_videos,"video");
    trialtype_to_canvas_stimuli(these_audios,"audio");
    
    processed_template=$("#preprocessing_trialType").html();
  
    // following use of Ajax only runs after_write_canvas AFTER the canvas has been written. Solution by Kio2212 on http://stackoverflow.com/questions/5000415/call-a-function-after-previous-function-is-complete
    $.ajax({
        url:write_canvas(processed_template),
        success:function(){
            after_write_canvas();
            for(i=0;i<element_management.canvas_elements.length;i++){
                if(element_management.canvas_elements[i].id == ""){
                  element_management.canvas_elements[i].id = $("iFrame")[0].contentWindow.generate_new_id();
                }        
            }
            trial_management.update_temp_trial_type_template();
            //load contents for script editor
            if($('iFrame').contents().find('#gui_script').length !== 0){
                $('iFrame').contents().find('#gui_script').click();
            } else {
                // what to do if there's no script
                $("#interactive_gui").html("");
            }
            $(".GUI_divs").hide();
            interaction_manager.update_buttons();
        }
    })
  
  canvas_drawing.activate_canvas_mouseframe();
  
}


function trialtype_to_canvas_stimuli(these_stimuli,stimuli_type){
  var relevant_stimuli_properties = ["src","position","left","top","height","width"];
  
  var default_properties = {
    src:      "none",
    position: "absolute",
    left:     "0px",
    top:      "0px",
    height:   "50px",
    width:    "50px"
  }
  
  for(i=0;i<these_stimuli.length;i++){    
    this_stimuli_props = {};
    for(j=1;j<relevant_stimuli_properties.length;j++){
      this_relevant_property = relevant_stimuli_properties[j];
      if(these_stimuli[i].style[this_relevant_property]==""){ // i.e. nothing is set
        these_stimuli[i].style[this_relevant_property] = default_properties[this_relevant_property];
      }
      this_stimuli_props[this_relevant_property]=these_stimuli[i].style[this_relevant_property];
    }
    
    this_stimuli_props["src"]=these_stimuli[i].src;
    
    if(these_stimuli[i].id==""){
      these_stimuli[i].id = generate_new_id();
    }     
    $("#preprocessing_trialType").find(these_stimuli[i]).remove();
    
    var clean_source = these_stimuli[i]["src"].replace(window.location.href,"");
    
    if(stimuli_type == "image"){
      this_border_color="blue";
    }
    if(stimuli_type == "video"){
      this_border_color="purple";
    }
    if(stimuli_type == "audio"){
      this_border_color="red";
    }
    
    var this_style = "style=  'position:"+this_stimuli_props["position"]  +";"+
                               "left:"+    this_stimuli_props["left"]     +";"+
                               "top:"+     this_stimuli_props["top"]      +";"+
                               "height:"+  this_stimuli_props["height"]   +";"+
                               "width:"+   this_stimuli_props["width"]    +";"+  
                               "border-color:"+ this_border_color         +";"+  
                               "border-style:     solid;"+
                               "border-width:     2px;'";
    var new_span ="<span id='"+ these_stimuli[i].id         +
                  "'class='"  + stimuli_type  +"_element' " +
                  this_style  +">"+clean_source+"</span>";
                                           
   $("#preprocessing_trialType").append(new_span);
  }  
}


function generate_new_id() { // this function is duplicated in canvas_iframe.js
  var count = 0;
  
  while (document.getElementById("element" + count) !== null) {
      ++count;
  }
  
  return "element" + count;
}
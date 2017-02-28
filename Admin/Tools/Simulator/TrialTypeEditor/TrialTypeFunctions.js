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
  
  // capture globals
   
  var new_iframe = $("<iframe>");
  new_iframe.id="canvas_iframe";
  $("#canvas").html(""); //wipe canvas
  new_iframe.appendTo("#canvas");
  
  // detect and remove scripts here
  
  scriptless_trialtype_template = current_trialtype_template.replace(/<script>/g,"<script>___script___")
  
  scriptless_trialtype_template = scriptless_trialtype_template.split(/<script>|<\/script>/g);
  
  current_trial_types_script_array = [];
  
  for(var i=0;i<scriptless_trialtype_template.length;i++){
    if(scriptless_trialtype_template[i].indexOf("___script___")!==-1){
      var this_script = scriptless_trialtype_template[i].replace("___script___","");
      var script_no = current_trial_types_script_array.length;
      current_trial_types_script_array.push(this_script);
      // the first part of this string includes script      
      scriptless_trialtype_template[i] = "<span onclick='edit_script("+script_no+")'>___script"+script_no+"___</span>";
      
    }
  }
  
  scriptless_trialtype_template = scriptless_trialtype_template.join("");
  
  
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
  var canvas_script = '<script src="GUI/canvas_iframe.js"></script>'
  
  // insert promise here
  
  function write_canvas(){
    doc.open();
    doc.write(header+scriptless_trialtype_template+footer+canvas_script);
    doc.close();    
  }
  
  function after_write_canvas(){
    element_management.canvas_elements_update();
    element_management.update_lists(); // rename object and function    
  }
  
  // following use of Ajax only runs after_write_canvas AFTER the canvas has been written. Solution by Kio2212 on http://stackoverflow.com/questions/5000415/call-a-function-after-previous-function-is-complete
  $.ajax({
    url:write_canvas(),
    success:function(){
      after_write_canvas();
    }
  })
  
}
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
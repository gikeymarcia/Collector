<?php
    require '../../initiateTool.php';

    $exp_js = $_FILES->get_path("Experiment JS");     // header.php is loading these
    echo "<script src='$exp_js'></script>";

    // load data required for the experiment
    // $user_data       = load_user_data($_FILES); this will be filled out or selected later on the actual page
    $trial_page      = get_trial_page($_FILES);
    $trial_type_data = get_all_trial_type_data($_FILES);

    //setting up varialbes new Experiment.js needs
    $media_path = $_FILES->get_path("Media Dir");
    $root_path  = $_FILES->get_path("Root");

    require("../ExperimentEditor/LoadExperiment.php");
    
    ?>

<style>
    #ToolsNavBar { margin: 0; }
    
    #ExperimentContainer {
        height: 50%;
        
        border: 0px solid black;
        border-width: 2px 0 2px;
        
        background-color: #DDD;
    }
    
    #ExperimentContainer > iframe {
        background-color: #FFF;
        height: 100%;
    }
    
    #ExperimentContainer, #ExperimentContainer > iframe {
        width: 100%;
        margin: 0;
        padding: 0;
        display: block;
    }
        
    .custom_table { display: table; margin: auto; border-collapse: collapse; }
    .custom_table > div { display: table-row; }
    .custom_table > div > div { display: table-cell; padding: 2px 4px; max-width: 320px; vertical-align: top; }
    
    .custom_table > div > .textareaDiv { padding: 10px; width: 500px; max-width: 1200px; height: 20em; }
    .custom_table > div > .textareaDiv > textarea {
        padding: 1px;
        width: 100%;
        min-width: 100%;
        max-width: 100%;
        height: 20em;
        box-sizing: border-box;
        border: 0; margin: 0;
        vertical-align: bottom;
        background-color: #E0E0E0;
    }

    .custom_table > div:not(.headers):hover { background-color: #CFC; }
    .custom_table > div.headers > div { text-align: center; font-weight: bold; }
    
    #exp_data td { text-align: center; padding: 15px; }
    #trial_type_selectors {
      text-align:center;
      
    }
</style>

<div id="ExperimentContainer" class="hide_show_elements">Select your stim and proc files below to start the stimulation.</div>

<div class="textcenter"><button type="button" id="run_button">Run Simulation</button></div>

<?php require ("../ExperimentEditor/index.php"); ?>

<!-- <table id="exp_data"><tr>
    <td id="stim_area">
        <select id="stim_select">
            <option>Stimuli</option>
        </select>
        
        <div id="stim_data" class="custom_table"></div>
    </td>
    
    <td id="proc_area">
        <select id="proc_select">
            <option>Procedure</option>
        </select>
        
        <div id="proc_data" class="custom_table"></div>
    </td>
    
    <td id="resp_area">
        <select style="visibility: hidden"><option>Select</option></select>
        <div id="resp_data" class="custom_table"></div>
    </td>
</tr></table> -->

<div id="TrialTypes" class="hide_show_elements">
  
  <div id="trial_type_selectors">
    <select id="trial_type_select"></select>
    <select id="trial_type_file_select">
      <option value='template'>Template</option>
      <option value='scoring'>Scoring</option>
      <option value='prepare_inputs'>Prepare Inputs</option>
   </select>
  </div>
    <div id="trial_type_data" class="custom_table">
        <div> <div>Trial Type</div> <div>Template</div> <div>Scoring</div> <div>Prepare Inputs</div> </div>
    </div>
</div>

<script>

  $("#trial_type_select,#trial_type_file_select").on("change",function(){
    var trial_type = $("#trial_type_select").val();
    var file = $("#trial_type_file_select").val();
    
    show_trial_type(trial_type,file);
  });


// set up default information for the experiment object to use
var User_Data = {
    Username:   "Admin",
    ID:         "Admin",
    Debug_Mode: true,
    Experiment_Data: {stimuli: {}, procedure: {}, globals: {}, responses: {}}
}

var trial_page   = <?= json_encode($trial_page) ?>;
var trial_types  = <?= json_encode($trial_type_data) ?>;
var server_paths = {
    media_path: '<?= $media_path ?>',
    root_path:  '<?= $root_path ?>',
};


function upate_trialtype_select(){
  $("#trial_type_select").html(
  "<option>" + Object.keys(trial_types).join("</option><option>") + "</option>"
  ); 
  var current_trial_type = $("#trial_type_select").val();
  show_trial_type(current_trial_type,"template");

}




// use the normal Experiment object as a prototype so we can modify
// how the object saves data to the server
/* 
var Gui_Experiment = function() {}

Gui_Experiment.prototype = Experiment.prototype;

Gui_Experiment.prototype.record_to_server = function() {
    // do nothing
    // make this add the new data to #resp_data
}
 */

// re-run the experiment every time a relevant table is changed
var Collector_Experiment = null;

function simulate_experiment() {
    container = $("#ExperimentContainer");
    container.html("");
    // collect the current definitions of the experiment data and trial types, and create an experiment
    Collector_Experiment = new Experiment(
        get_exp_data(),
        container,
        trial_page,
        get_trial_types(),
        server_paths
    );
    
    // run the first trial of the new experiment
    Collector_Experiment.run_trial();
}


/* 
$("#stim_data, #proc_data, #trial_type_data").on("change input", "div", function() {
    // check to make sure that a stim and proc file have been selected before trying to simulate an experiment
    if (!exp_is_ready_to_simulate()) return;
    
    simulate_experiment();
});
 */
 
$("#run_button").on("click", function() {
    // check to make sure that a stim and proc file have been selected before trying to simulate an experiment
    //if (!exp_is_ready_to_simulate()) return;
    
    simulate_experiment();
});

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

for (var trial_type in trial_types) {
    var row = $("<div>");
    
    row.append("<div>" + trial_type + "</div>");
    row.children("div").prop("contenteditable", true);
    
    for (var file in trial_types[trial_type]) {
        var val = trial_types[trial_type][file];
        
        if (val === null) val = '';
        
        row.append("<div class='textareaDiv' id='"+trial_type+file+"_id'><textarea>" + val.replace(/</g, '&lt;') + "</textarea></div>");
    }
    
    $("#trial_type_data").append(row);
}


function show_trial_type(trial_type,file){
  $("#trial_type_data > div > div").hide();
  console.dir(trial_type);
  console.dir(file);
  $("#"+trial_type+file+"_id").show();
}

upate_trialtype_select();


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
</script>
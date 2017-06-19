<div id="TrialTypes" class="hide_show_elements">
  
    <div id="trial_type_selectors">
        <select id="trial_type_select">
            <option hidden disabled selected>Select a trial type</option>
        </select>
        <select id="trial_type_file_select" style="display:none">
            <option value='template'>Template</option>
            <option value='scoring'>Scoring</option>
            <option value='prepare_inputs'>Prepare Inputs</option>
        </select>
        <button id="new_trial_type_button" class="collectorButton">New Trial Type</button>
        <button id="rename_trial_type_button" class="collectorButton" style="display:none">Rename Trial Type</button>
    </div>
        
    
    <div id="trial_type_data" class="custom_table">
        <div> 
          <div>Trial Type</div> 
          <div>Template</div> 
          <div>Scoring</div> 
          <div>Prepare Inputs</div>
        </div>
    </div>
    <style type="text/css" media="screen">
        #ACE_editor { 
            height:500px;
        }
    </style>
    <h6><em>ACE (https://ace.c9.io/)</em> is used for editing code</h6>  
    <div id="ACE_editor" style="display:none"></div>
    
    <script src="https://cdn.jsdelivr.net/ace/1.2.6/min/ace.js" type="text/javascript" charset="utf-8"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.2.7/ext-language_tools.js" type="text/javascript" charset="utf-8"></script>
    
    <style>    
    </style>
    
    <script>
    
        $("#trial_type_select").on("change",function(){
           $("#ACE_editor").show(); 
           $("#trial_type_file_select").show(500); 
           $("#rename_trial_type_button").show(500); 
        });
        
    
    trial_management.gui_edit_warning = false;
    trial_management.warning_code = "<!-- Warn if try to edit with GUI !-->";
    
    
    var editor = ace.edit("ACE_editor");
    editor.setTheme("ace/theme/chrome");       
    editor.getSession().setMode("ace/mode/html");
    $("#ACE_editor").on("keyup input",function(){
        var ace_content = editor.getValue();
        // identify it's been edited            
        ace_content.replace(trial_management.warning_code,'');
        ace_content += trial_management.warning_code;
        
        trial_management.gui_edit_warning = true;
        
        $("#"+trial_management.current_trialtype_textarea).val(ace_content);
        // add the class "modified"
        $("#"+trial_management.current_trialtype_textarea).addClass("modified");
    });
    
    editor.setOptions({
        enableBasicAutocompletion: true,
        enableSnippets: false,
        enableLiveAutocompletion: true
    });
    
    langTools = ace.require('ace/ext/language_tools'); 
    
    
    editor.completers.push({
        getCompletions: function(editor, session, pos, prefix, callback) {
            callback(null, [
                {value: "get_new_data", score: 1000, meta: "COAST"},
                {value: "load_json_index_file", score: 1000, meta: "COAST"}
            ]);
        }
    })
        
        
        
        
        
        
        
        
        
        
        $("#gui_table").on("mouseenter",function(){
            if(trial_management.gui_edit_warning == true){
                custom_alert("<b> WARNING </b> This trial has edits in the script editor. Any edits you make to this trialtype using the GUI will delete the edits made in the script editor.");trial_management.gui_edit_warning = false ;                   
            }
        });    
        
        
        
    </script>

  
  
</div>

<!-- Create a simple CodeMirror instance -->
<link rel="stylesheet" href="http://www.uoropen.org/Software/codemirror-5.25.0/lib/codemirror.css">
<script src="http://www.uoropen.org/Software/codemirror-5.25.0/lib/codemirror.js"></script>

<?php


    $js_files = array('TrialTypeEditor/TrialTypeFunctions.js', 'TrialTypeEditor/TrialTypeActions.js');
    
    foreach ($js_files as $file) {
        $file_mod_time = filemtime($file);
        echo "<script src='$file?v=$file_mod_time'></script>";
    }

/* 
  $script_mod_time  = filemtime("TrialTypeFunctions.js");
  $script_src       = "TrialTypeFunctions.js?v=$script_mod_time"; */
?>


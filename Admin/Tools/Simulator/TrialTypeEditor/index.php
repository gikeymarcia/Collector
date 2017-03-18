<div id="TrialTypes" class="hide_show_elements">
  
  <div id="trial_type_selectors">
    <select id="trial_type_select">
      <option hidden disabled selected>Select a trial type</option>
    </select>
    <select id="trial_type_file_select">
      <option value='template'>Template</option>
      <option value='scoring'>Scoring</option>
      <option value='prepare_inputs'>Prepare Inputs</option>
    </select>
    <button id="new_trial_type_button" class="collectorButton">New Trial Type</button>
  </div>
  <div id="trial_type_data" class="custom_table">
    <div> 
      <div>Trial Type</div> 
      <div>Template</div> 
      <div>Scoring</div> 
      <div>Prepare Inputs</div>
    </div>
  </div>
</div>

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


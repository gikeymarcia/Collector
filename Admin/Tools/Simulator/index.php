<?php
    require '../../initiateTool.php';

    $exp_js = $FILE_SYS->get_path("Experiment JS");     // header.php is loading these
    echo "<script src='$exp_js'></script>";

    // load data required for the experiment
    // $user_data       = load_user_data($FILE_SYS); this will be filled out or selected later on the actual page
    $trial_page      = get_trial_page($FILE_SYS);
    $trial_type_data = get_all_trial_type_data($FILE_SYS);

    //setting up varialbes new Experiment.js needs
    $media_path = $FILE_SYS->get_path("Media Dir");
    $root_path  = $FILE_SYS->get_path("Root");
    $ajax_json_path = $FILE_SYS->get_path("Ajax Json");
    
    
    require("ExperimentEditor/LoadExperiment.php");
    
    ?>

<link rel="stylesheet" type="text/css" href="SimulatorStyle.css" media="screen" />

<div id="preview_gui">
  <table>
    <tr>
      <td><input type="button" class="gui_preview_buttons" value="preview"></td>
      <td rowspan="2">
        <div id="Preview" class="hide_show_elements" >
          <div id="ExperimentContainer">Select your stim and proc files below to start the stimulation.</div>

          <div id="run_stop_buttons" class="textcenter" style="display:none">
            <button type="button" id="run_button" class="collectorButton">Run Simulation</button>
            <button type="button" id="stop_button" class="collectorButton">Stop Simulation</button>
          </div>
        </div>
      </td>
    </tr>
    <tr><td><input type="button" class="gui_preview_buttons" value="GUI"></td></tr>
  </table>
</div>
  
<script>
  
  function show_run_stop_buttons(){
    $("#run_stop_buttons").show();    
  }
  $("#stop_button").on("click",function(){
    $("#ExperimentContainer").html("Simulation Stopped. Click 'Run Simulation' to restart.");
  });
</script>
  
<?php require ("ExperimentEditor/index.php"); ?>

<script>

var User_Data = {
    Username:   "Admin:Simulator",
    ID:         "Admin:Simulator",
    Debug_Mode: true,
    Experiment_Data: {stimuli: {}, procedure: {}, globals: {}, responses: {}}
}

var trial_page   = <?= json_encode($trial_page) ?>;
var trial_types  = <?= json_encode($trial_type_data) ?>;
var server_paths = {
    media_path: '<?= $media_path ?>',
    root_path:  '<?= $root_path ?>',
    ajax_tools: '<?= $ajax_json_path ?>'
};


</script>

<?php require ("TrialTypeEditor/index.php"); ?>


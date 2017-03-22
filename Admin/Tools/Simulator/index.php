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

  <table id="support_bar"> 
    <tr>
      <td><?php require("SupportBars/tutorial.php");   ?></td>
      <td><?php require("SupportBars/Interfaces.php"); ?></td>
      <td><?php require("SupportBars/HelperBar.php");  ?></td>
    </tr>
  </table>

<div id="preview_gui" class="hide_show_elements">
  <table id="preview_gui_table">
    <tr>
      <td style="vertical-align:middle;">
        <input type="button" class="collectorButton gui_preview_buttons" value="Preview">
        <br>
        <input type="button" class="collectorButton gui_preview_buttons" value="GUI">
      </td>
      
      <td>
        <div id="Preview_area" class="preview_gui_class">
          <div id="ExperimentContainer">Select an experiment to be able to start a preview.</div>

          <div id="run_stop_buttons" class="textcenter" style="display:none">
            <button type="button" id="run_button" class="collectorButton">Run Simulation</button>            
            <button type="button" id="stop_button" class="collectorButton">Stop Simulation</button>
            <button type="button" id="largescreen_button" class="collectorButton">Enlarge Screen Size</button>
            <button type="button" id="smallscreen_button" class="collectorButton" style="display:none">Shrink Screen Size</button>
          </div>
        </div>
        <div id="GUI_area" class="preview_gui_class">
          <?= require("GUI/index.php") ?>
        </div>
      </td>
    </tr>
  </table>
</div>
  
<script>
  
  function show_run_stop_buttons(){
    $("#run_stop_buttons").show();    
  }
  $("#largescreen_button").on("click",function(){
    $("#Preview_area").height($("#Preview_area").height()*2);
    $("#Preview_area").width($("#Preview_area").width()*2);
    $("#smallscreen_button").show();
    $("#largescreen_button").hide();
  });
  $("#smallscreen_button").on("click",function(){
    $("#Preview_area").height($("#Preview_area").height()/2);
    $("#Preview_area").width($("#Preview_area").width()/2);
    $("#largescreen_button").show();
    $("#smallscreen_button").hide();
  });
  $("#stop_button").on("click",function(){
    $("#ExperimentContainer").html("Simulation Stopped. Click 'Run Simulation' to restart.");
  });
  $(".gui_preview_buttons").on("click",function(){
    $(".preview_gui_class").hide();
    $("#"+this.value+"_area").show();
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

<?php
  
/*
   Experiment Editor:
   -Open and view all stimuli and procedure files for every experiment
   -Open condition sheet
   -change contents of sheet
   -copy/rename/delete sheets
   
   -start by scanning all experiments to find all sheets
   -display list of experiments
   -after experiment is selected, display list of sheets inside that experiment
   -alternatively, let them create a new exp
   -can open sheets, or copy to new sheet and start editing that
   -can also copy experiment to edit that
 */

  require_once "../../initiateTool.php";
  require 'fileReadingFunctions.php';
  

  
  $experiments = get_Collector_experiments($FILE_SYS);
  
  $experiment_files = array();
  
  foreach ($experiments as $exp) {
    $FILE_SYS->set_default('Current Experiment', $exp);
    $experiment_files[$exp]['Conditions'] = read_csv_raw($FILE_SYS->get_path('Conditions'));
    $experiment_files[$exp]['Stimuli']    = $FILE_SYS->read('Stimuli Dir');
    $experiment_files[$exp]['Procedures'] = $FILE_SYS->read('Procedure Dir');
  }
  
  $new_exp_json = file_get_contents('ExperimentEditor/default_new_experiment.json');
?>

<style>
  #interface{
    display:none;
  }
  #load_toolbar{
    padding:5px;
  }

  .condOption         { background-color: #DFD; }
  .stimOptions        { background-color: #BBF; }
  .stimOptions option { background-color: #DDF; }
  .procOptions        { background-color: #FBB; }
  .procOptions option { background-color: #FDD; }
  
  
  
  
  
</style>

<script src="../HandsontableFunctions.js"></script>

<script>
  var new_experiment_data = <?= $new_exp_json ?>;
</script>

<div id="load_toolbar">
  <button type="button" id="new_experiment_button" class="collectorButton">New Experiment</button>
  
  <select id="experiment_select">
    <option value="" hidden disabled selected>Select an experiment</option>
    <?php 
    foreach ($experiments as $experiment){
      echo "<option>$experiment</option>";
    }
    ?>
  </select>
  <input type="text" id="experiment_name" placeholder="Experiment Name">
  <button id="save_btn" class="collectorButton">Save</button>

</div>

<script>

$(window).bind('keydown', function(event) {
    if (event.ctrlKey || event.metaKey) {
        switch (String.fromCharCode(event.which).toLowerCase()) {
            case 's':
                event.preventDefault();
                $("#save_btn").click();
            break;
        }
    }
  
});

</script>


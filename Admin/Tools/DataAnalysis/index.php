<?php
    require '../../initiateTool.php';
    
    // default not to load any data
?>

<link rel="stylesheet" href="DataAnalysis.css">
<script src="jstat.min.js"></script>

<div id="main_options">

  <?php require 'analysisLoader.php'; ?>
  
  <span id="data_analysis_block">
    <button type="button" id="data_button" class="collectorButton">Data</button>
    <button type="button" id="analysis_button" class="collectorButton">Analysis</button>
    <textarea id="analysis_json_textarea"></textarea>
  </span>
  
</div>

<?php require 'dataArea.php'; ?>
<?php require 'analysisArea.php'; ?>


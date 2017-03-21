<script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
<link rel="stylesheet" href="../handsontables/handsontables.full.css">
<script src="../handsontables/handsontables.full.js"></script>

<table id="exp_data">
  <tr>
    <div id="Conditions" class="hide_show_elements"> 
      <h3>Conditions</h3>
      <div id="conditionsArea"> Select study
        <!-- <div id="sheetTable"></div> -->
      </div>
    </div>
  </tr>
  <tr>
    <td id="Stimuli" class="hide_show_elements">
      <h3>Stimuli</h3>
      <select id="stim_select"></select>
      <button type="button" id="new_stim_button" class="collectorButton">New Stimuli Sheet</button>
      <span id="stimsArea">
        <div id="stimsheetTable"></div> 
      </span>
    </td>
    
    <td id="Procedure" class="hide_show_elements">
      <h3>Procedure</h3>
      <select id="proc_select"></select>
      <button type="button" id="new_proc_button" class="collectorButton">New Procedure Sheet</button>
      <span id="procsArea">
        <div id="procSheetTable"></div>
      </span>
    </td>
    
    <td id="resp_area">
        <select style="visibility: hidden"><option>Select</option></select>
        <div id="resp_data" class="custom_table"></div>
    </td>
  </tr>
</table>

<script src="ExperimentEditor/ExpEditorActions.js"></script>
<script src="ExperimentEditor/ExpEditorFunctions.js"></script>

<script>

var experiment_files = <?= json_encode($experiment_files); ?>;
var spreadsheets = {};
var alerts_ready = false;

var handsOnTable_Conditions = null;
var handsOnTable_Stimuli    = null;
var handsOnTable_Procedure  = null;

</script>


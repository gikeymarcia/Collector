
<div id="example" name="exampleTable"></div>
<button class="collectorButton">upload files for stimuli</button>

<!-- <script src="../GUI2/handsontable-0.19.0/dist/handsontable.full.js"></script> !-->

<script src="http://handsontable.com/dist/handsontable.full.js"></script>
<link rel="stylesheet" media="screen" href="http://handsontable.com/dist/handsontable.full.css">
 <script src="moment.js"</script>	
 <script src="//cdnjs.cloudflare.com/ajax/libs/numeral.js/1.4.5/numeral.min.js"></script>
<!-- <script src="../GUI2/zeroclipboard-2.2.0/Gruntfile.js"></script> !-->

<button type="Submit" class="collectorButton" value="UpdateTask">Update Task</button>

<input type="hidden" name="stimTableInput">
<input type="hidden" name="procTableInput">
<button id="submitButton" type="button">Submit</button>
<button id="resetButton"  type="button">Reset</button>


<script type="text/javascript">

var groups = (<?php  echo json_encode($guiArray->studyGroups,JSON_PRETTY_PRINT); ?>);

var data = [
  ["a", groups],
  ["b", groups],
  ["c", groups]
];

var container = document.getElementById('example');
container.name="tableName";

var hot = new Handsontable(container, {
  data: data,
  minSpareRows: 1,
  rowHeaders: true,
  colHeaders: true,
  contextMenu: true,
  colHeaders: ['Cues','Group(s)']
});
hot.name="hotName";
 $("#submitButton").on("click", function() {
        $("input[name='stimTableInput']").val(JSON.stringify(example.getData()));
        $("input[name='procTableInput']").val(JSON.stringify(procTable.getData()));
        $("form").submit();
    });

</script>
<div id="example"></div>

<!-- <script src="../GUI2/handsontable-0.19.0/dist/handsontable.full.js"></script> !-->

<script src="http://handsontable.com/dist/handsontable.full.js"></script>
<link rel="stylesheet" media="screen" href="http://handsontable.com/dist/handsontable.full.css">
 <script src="moment.js"</script>	
 <script src="//cdnjs.cloudflare.com/ajax/libs/numeral.js/1.4.5/numeral.min.js"></script>
<!-- <script src="../GUI2/zeroclipboard-2.2.0/Gruntfile.js"></script> !-->
<script type="text/javascript">

var data = [
  ["Cue", "Answer"],
  ["a", "apple"],
  ["b", "banana"],
  ["c", "carrot"]
];
var container = document.getElementById('example');
var hot = new Handsontable(container, {
  data: data,
  minSpareRows: 1,
  rowHeaders: true,
  colHeaders: true,
  contextMenu: true
});
</script>
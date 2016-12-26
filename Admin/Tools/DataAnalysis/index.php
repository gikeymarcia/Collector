<?php
    require '../../initiateTool.php';
    
    // default not to load any data
?>

<?php
  $file = 'DataAnalysis.css';
  $v    = filemtime('DataAnalysis.css');
?>
<link rel="stylesheet" href="<?= $file ?>?v=<?= $v ?>">

<script src="jstat.min.js"></script>
<script src="DataAnalysisFunctions.js"></script>

<div id="main_options">

  <?php require 'analysisLoader.php'; ?>
  <textarea id="analysis_json_textarea"></textarea>
  
</div>

<br>
<?php
    function get_data() {
      $csv_data = fsDataType_CSV::read('temp/responses.csv');
      $raw_data = array();
      $raw_data[] = array_keys($csv_data[0]);
      
      foreach ($csv_data as $row) {
        $raw_data[] = array_values($row);
      }
      
      return json_encode($raw_data);
    }
  ?>

<script>
    Collector_data_raw = <?= get_data(); ?>;
    
    function raw_table_to_columns(data) {
      var output = {};
      var headers = data[0];
      
      // create empty arrays for each column header
      for (var col_index=0; col_index<headers.length; ++col_index) {
        // for example, set output["Username"] to empty array
        output[headers[col_index]] = [];
      }
      
      for (var row_index=1; row_index<data.length; ++row_index) {
        for (var col_index=0; col_index<headers.length; ++col_index) {
          output[headers[col_index]].push(data[row_index][col_index])
        }
      }
      
      return output;
    }
    
    function reformat_columns(columns) {
      for (var column in columns) {
        var is_numeric = true;
        
        for (var i=0; i<columns[column].length; ++i) {
          if (!$.isNumeric(columns[column][i])) {
            is_numeric = false;
            break;
          }
        }
        
        if (is_numeric) {
          console.dir(column + " is numeric");
          for (var i=0; i<columns[column].length; ++i) {
            columns[column][i] = parseFloat(columns[column][i]);
          }
        }
      }
    }
    
    data_by_columns = raw_table_to_columns(Collector_data_raw);
    
    reformat_columns(data_by_columns);
    
    function update_column_list() {
      var list = [];
      
      for (var column in data_by_columns) {
        list.push(column);
      }
      
      $("#column_list").html(
        "<div>" + list.join("</div><div>") + "</div>"
      );
    }
    
    $(document).ready(function() {
      update_column_list();
    });

</script>

<?php require 'analysisArea.php'; ?>


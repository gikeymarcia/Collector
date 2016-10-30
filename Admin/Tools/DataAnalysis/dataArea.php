<div id="data_area">
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
  <table id="data_table"></table>
  <script>
    var Collector_data_raw = <?= get_data(); ?>
    
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
    
    var data_by_columns = raw_table_to_columns(Collector_data_raw);
    
    console.dir(data_by_columns);
  </script>
</div>

<?php

  require '../newGetData/CollectorData.php';

  $all_available_data = CollectorData::find_available_data();
  

?>
<script>

    var Collector_data = <?php
    CollectorData::get_data_as_javascript_array(
        $all_available_data['Usernames'],
        $all_available_data['Columns']
    );
?>

</script>
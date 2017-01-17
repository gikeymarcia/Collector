<?php

require '../../initiateTool.php';

require 'CollectorData.php';

?>

<style>
    th, td { border: 1px solid #666; }
</style>

<div><form action="download.php" target="_blank">
    <button type="submit">Download</button>
</form></div>

<?php

$all_available_data = CollectorData::find_available_data();

CollectorData::get_data_as_html_table(
    $all_available_data['Usernames'],
    $all_available_data['Columns']
);

?><script>

var Collector_data = <?php
    CollectorData::get_data_as_javascript_array(
        $all_available_data['Usernames'],
        $all_available_data['Columns']
    );
?>;

</script>

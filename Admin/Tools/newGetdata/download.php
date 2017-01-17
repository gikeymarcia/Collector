<?php

require '../../initiateTool.php';

require 'CollectorData.php';

ob_end_clean();

$all_available_data = CollectorData::find_available_data();

CollectorData::get_data_as_csv(
    $all_available_data['Usernames'],
    $all_available_data['Columns']
);

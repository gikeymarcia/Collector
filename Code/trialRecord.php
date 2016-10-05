<?php
    if (!isset($_POST['trial_data'], $_POST['globals'])) exit;

    require 'initiateCollector.php';

    $trial_data = $_POST['trial_data'];
    $trial_data = json_decode($trial_data);

    $globals    = $_POST['globals'];
    $globals    = json_decode($globals);


    $_FILES->write_many('User Responses', $trial_data);
    $_FILES->overwrite('User Globals',    $globals);
?>

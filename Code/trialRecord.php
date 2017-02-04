<?php

if (!isset($_POST['trial_data'], $_POST['globals'])) exit;

require 'initiateCollector.php';
ini_set('html_errors', false);

$trial_data = $_POST['trial_data'];
$trial_data = json_decode($trial_data);

$globals    = $_POST['globals'];
$globals    = json_decode($globals);

$FILE_SYS->write_many('User Responses', $trial_data);
$FILE_SYS->overwrite('User Globals',    $globals);

echo 'success';

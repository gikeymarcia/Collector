<?php

require '../../initiateTool.php';
ob_end_clean(); // no need to transmit useless data

header('Content-type: text/plain; charset=utf-8');

$new_name = $_POST['new_name'];

// Tyson resume here?
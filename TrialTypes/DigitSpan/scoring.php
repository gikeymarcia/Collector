<?php

$data = $_POST;
$resp = json_decode($_POST['Response'], true);

// score is the length of the last correct response
do {
    $last = array_pop($resp);
} while ($last['score'] === 0);
$data['Score'] = strlen($last['sequence']);

<?php

$data = $_POST;
$dataTypes = array('Choice', 'ChoiceCode', 'Score', 'dmtRT');
$trialData = array();
foreach ($dataTypes as $col) {
    $postcol = filter_input(INPUT_POST, $col, FILTER_SANITIZE_STRING);
    $trialData[$col] = explode(',', $postcol);
}

$rounds = count($trialData[ $dataTypes[0] ]);

for ($i = 0; $i < $rounds; ++$i) {
    $extraData = array('DMT_Round' => $i + 1);

    foreach ($dataTypes as $col) {
        $extraData['DMT_'.$col] = $trialData[$col][$i];
    }

    recordTrial($extraData, false, false);
}

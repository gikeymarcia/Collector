<?php
/**
 *  Code that saves and scores information when user input is given.
 */
$data = $_POST;

// if there is a response given then do scoring
$response = filter_input(INPUT_POST, 'Response', FILTER_SANITIZE_STRING);
if ($response !== null) {
    // clean up response and answer (for later comparisons)
    $response = trim(strtolower($response));

    // if there is a range of answers, just use the first one for scoring
    $answers = explode('|', $answer);
    $correctAns = trim(strtolower(array_shift($answers)));

    // determine text similarity (accuracy) and store as $Acc
    $Acc = null;
    similar_text($response, $correctAns, $Acc);
    $data['Accuracy'] = $Acc;

    // perform strict scoring
    $data['strictAcc'] = ($Acc === 100) ? 1 : 0;

    // perform lenient scoring
    $data['lenientAcc'] = ($Acc >= $_SETTINGS->lenient_criteria) ? 1 : 0;
}

<?php
$stemLength = 2; // default value
if (is_numeric($_trialSettings->stem)) $stemLength = (int) $_trialSettings->stem;

// to make sure that everything is recorded, just throw all of POST into
// $data, which will eventually be recorded into the output file.
// To create new columns, simply assign the new data you want to record
// to a new key in $data.
// For example,    $data[ 'New Column' ] = "Hello";    would make a new
// column titled "New Column" in the output, and every row for this trial
// type would have the value "Hello".
$data = $_POST;

$answerClean = trim(strtolower($_EXPT->get('answer')));
$response = filter_input(INPUT_POST, 'Response', FILTER_SANITIZE_STRING);
$responseClean = substr($answerClean, 0, $stemLength).trim(strtolower($response));
$Acc1 = null;
$Acc2 = null;

// store the "complete" answer here, which is both the two-letter cue and
// the rest of the word that they typed in.
$data['ResponseComplete'] = $responseClean;

/*
 *  Calculating and saving accuracy for trials with user input
 */
// determine text similarity and store as $Acc
similar_text($responseClean, $answerClean, $Acc);
similar_text($responseClean, trim(strtolower($response)), $Acc2);
$Acc = max($Acc1, $Acc2);
$data['Accuracy'] = $Acc;

/*
 *  Scoring and saving scores
 */
// strict scoring
$data['strictAcc'] = ($Acc == 100) ? 1 : 0;

// lenient scoring
$data['lenientAcc'] = ($Acc >= $_SETTINGS->lenient_criteria) ? 1 : 0;

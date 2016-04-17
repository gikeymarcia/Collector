<?php
// determine if we need to run the trial validator
$doTrialValidation = true;

// find the mod time of all the files for this experiment
clearstatcache();

$allExpFiles = array_merge(
    Collector\Helpers::scanDirRecursively($_PATH->get('Current Experiment')),
    Collector\Helpers::scanDirRecursively($_PATH->get('Common'))
);

$fileModTimes = array();

foreach ($allExpFiles as $file) {
    $fileModTimes[] = filemtime($file);
}

$fileModTimesString = implode(',', $fileModTimes);

// compare against the recorded mod time of the last successful validation
$trialValidationTimeFile = $_PATH->get('Trial Validation Scan Time');

if (is_file($trialValidationTimeFile)) {
    $trialValidationTime = file_get_contents($trialValidationTimeFile);

    if ($trialValidationTime === $fileModTimesString) {
        $doTrialValidation = false;
    }
}

// if we need to validate, use the validation class
// if successful, record the mod times of the current scan
if ($doTrialValidation) {
    $trialValidator = new Collector\TrialValidator($stimuli, $procedure, $errors);

    if ($trialValidator->isValid()) {
        $dir = dirname($trialValidationTimeFile);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        file_put_contents($trialValidationTimeFile, $fileModTimesString);
    }
}

<?php
    // determine if we need to run the trial validator
    $doTrialValidation = true;
    
    // find the mod time of all the files for this experiment
    clearstatcache();
    
    $allExpFiles = array_merge(
        scanDirRecursively($_PATH->get('Current Experiment')),
        scanDirRecursively($_PATH->get('Common'))
    );
    
    $fileModTimes = array();
    
    foreach ($allExpFiles as $file) {
        $fileModTimes[] = filemtime($file);
    }
    
    $fileModTimes = implode(',', $fileModTimes);
    
    // compare the mod time against the recorded mod time of the last successful validation
    $trialValidationTimeFile = $_PATH->get('Trial Validation Scan Time');
    
    if (is_file($trialValidationTimeFile)) {
        $trialValidationTime = file_get_contents($trialValidationTimeFile);
        
        if ($trialValidationTime === $fileModTimes) {
            $doTrialValidation = false;
        }
    }
    
    // if we need to validate, use the validation class
    // if successful, record the mod times of the current scan
    if ($doTrialValidation) {
        
        $trialValidator = new TrialValidator($stimuli, $procedure, $errors);
        
        if ($trialValidator->isValid()) {
            $dir = dirname($trialValidationTimeFile);
            if (!is_dir($dir)) {
                mkdir($dir, 0777, true);
            }
            file_put_contents($trialValidationTimeFile, $fileModTimes);
        }
    }

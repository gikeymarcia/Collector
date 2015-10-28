<?php
    return function($trialValues) {
        $errors = array();
        
        $requiredColumns = array('Cue', 'Answer');
        
        foreach ($requiredColumns as $reqCol) {
            if (!isset($trialValues[$reqCol])) {
                $errors[] = "this trial type requires a column that is missing: '<b>$reqCol</b>'";
            }
        }
        
        return $errors;
    };

<?php return function($trial) {
    $trialtype = $trial->get('trial type');

    $requiredColumns = array('Cue', 'Answer');
    foreach ($requiredColumns as $col) {
        $var = $trial->get($col);
        if (!isset($var)) {
            $errors[] = $col;
        }
    }

    $message = "The {$trialtype} trial type requires the following column(s): ";
    foreach ($errors as $error) {
        $message .= $error . ', ';
    }

    return rtrim($message, ', ');
};

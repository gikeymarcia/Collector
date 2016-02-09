<?php 

return function($trialValues) {
    $errors = array();
    if (!isset($trialValues['Font Size'])) {
        $errors[] = "Missing column 'Font Size'";
    } elseif (empty($trialValues['Font Size'])) {
        $errors[] = 'Your "Font Size" column is empty and needs a value such as: "32px", "120%", "2em", or "16pt"';
    }
    return $errors;
}

?> 
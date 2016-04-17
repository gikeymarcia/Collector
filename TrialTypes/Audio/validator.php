<?php
return function($trialValues) {
    $errors = array();
    if (!isset($trialValues['Cue'])) {
        $errors[] = "Your 'Audio' trial tyle needs a 'Cue' value.";
    }
    if (Collector\Helpers::show($trialValues['Cue']) == $trialValues['Cue']) {
        $errors[] = "Your 'Audio' trial type's 'Cue' value does not point to a " .
                    "filename with an extension. For example,'Crystal Glass.mp3'. Your " .
                    "'Cue' is: {$trialValues['Cue']}";
    }
    return $errors;
};

?>
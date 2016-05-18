<?php
return function($trialValues) {
    $errors = array();
    if (!isset($trialValues['Cue'])) {
        $errors[] = "Your 'CopyPic' trial type needs a 'Cue' value.";
    }
    if (show($trialValues['Cue']) == $trialValues['Cue']) {
        $errors[] = "Your 'CopyPic' trial type's 'Cue' value does not point to a " .
                    "filename with an extension. For example,'picture.jpg'. Your " .
                    "'Cue' is: {$trialValues['Cue']}";
    }
    return $errors;
};
​
?>
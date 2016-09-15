<?php return function($trial) {
    $cue = $trial->get('cue');
    if (!isset($cue) || show($cue) === $cue) {
        return "Your 'CopyPic' trial type needs a 'Cue' value that points to a"
            . " filename with an extension. For example,'picture.jpg'. Your "
            . "'Cue' is: {$cue}";
    }
};
​
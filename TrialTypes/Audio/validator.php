<?php return function($trial) {
    $cue = $trial->get('cue');
    $trialtype = $trial->get('trial type');
    
    if (!$cue) {
        return "{$trialtype} trials require a 'Cue' value.";
    }
    
    if (show($cue) === $cue) {
        return "The 'Cue' for {$trialtype} trials must point to an audio file.";
    }
};

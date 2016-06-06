<?php return function($trial) {
    $item = $trial->get('cue');
    $cue = isset($item) ? $item : null;
    $trialtype = $trial->get('trial type');
    
    if (!$item) {
        return "{$trialtype} trials require a 'Cue' value.";
    }
    
    if (show($cue) === $cue) {
        return "The 'Cue' for {$trialtype} trials must point to a picture file.";
    }
};

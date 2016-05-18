<?php return function($trial) {
    $var = $trial->get('Font Size');
    $trialtype = $trial->get('trial type');
    if (empty($var)) {
        return "The {$trialtype} requires a 'Font Size' column with a value "
        . "such as: '32px', '120%', '2em', or '16pt'";
    }
};

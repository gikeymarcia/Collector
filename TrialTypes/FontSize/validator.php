<?php 

return function($trial) {
    $_trialSettings = new TrialSettings($trial->get('settings');
    $errors = array();
    $fontsize = $_trialSettings->fontsize;
    if (empty($fontsize)) {
        $errors[] = 'You need a Settings column with a value such as: "fontsize = 32px", "fontsize = 120%", "fontsize = 2em", or "fontsize = 16pt"';
    }
};

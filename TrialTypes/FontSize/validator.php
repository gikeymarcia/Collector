<?php return function($trial) {
    $errors = array();
    if (empty($trial->settings->fontsize)) {
        $errors[] = 'You need a Settings column with a value such as: '
            . '"fontsize = 32px", "fontsize = 120%", "fontsize = 2em", or '
            . '"fontsize = 16pt"';
    }
};

<?php return function($trial) {
    if (!$trial->get('key')) {
        return 'Failed!';
    }
};

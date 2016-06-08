<?php return function($trial) {
    if (!$trial->get('key1', true)) {
        return 'Failed!';
    }
};

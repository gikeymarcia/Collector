<?php return function($trial) {
    if (!$trial->get('key2')) {
        return 'Failed!';
    }
};

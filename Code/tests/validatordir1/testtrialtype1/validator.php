<?php return function($trial) {
    if (!$trial->get('key1')) {
        return 'Failed!';
    }
};

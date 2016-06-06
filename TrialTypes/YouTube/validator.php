<?php return function($trial) {
    $string = $trial->get('cue');
    
    if (!Collector\Helpers::isLocal($string)
        && (stripos($string, 'youtube') !== false || stripos($string, 'youtu.be') !== false)
    ) {
        return true;
    }
    
    return "The given video source is not supported. The cue \"{$string}\" is not"
        . " recognized as a valid YouTube URL.";
};

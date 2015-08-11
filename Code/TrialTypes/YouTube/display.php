<?php 
    /***************************************************************************
     * README
     * 
     * How to use:
     * In the Settings column, separate parameters by pipe |
     * Parameters;
     *     submitOnDone
     *         set to false to prevent auto-submission upon video completion
     *     preventEarlySubmit
     *         set to false to disable the submit button until video completion
     **************************************************************************/
    
    
    /**
     * Determines if the video is a valid YouTube link.
     * @param string $string The path to check.
     * @return boolean
     */
    function isValidYouTube($string) {
        if (isLocal($string)) {
            return false;
        }
        if (false !== stripos($string, 'youtube')) {
            return true;
        }
        if (false !== stripos($string, 'youtu.be')) {
            return true;
        }
        return false;
    }
    
    if (!isValidYouTube($cue)) {
        throw new InvalidArgumentException('The given video source is not '
            . 'supported. The cue should be a YouTube URL.');
    }
    
    // extract submitOnDone and preventEarlySubmit settings from $settings
    $submitOnDone       = true;
    $preventEarlySubmit = true;
    $settings = explode('|', $settings);
    foreach ($settings as $setting) {
        $settingSubmit  = removeLabel($setting, 'submitOnDone');
        $settingPrevent = removeLabel($setting, 'preventEarlySubmit');
        if ($settingSubmit === 'false') {
            $submitOnDone = false;
        }
        if ($settingPrevent === 'false') {
            $preventEarlySubmit = false;
        }
    }
    
    // get video ID
    $videoId = youtubeUrlCleaner($cue, true);
    
    // get start and end time from stim file columns
    if (!isset($startTime) OR !is_numeric($startTime)) { 
        $startTime = 0; 
    }
    if (!isset($endTime)   OR !is_numeric($endTime)) { 
        $endTime   = 'na'; 
    }
    
    // set parameters to append to the URL (https://developers.google.com/youtube/player_parameters)
    $parameters = array(
        'autoplay'       => 1,           // 1: starts the video immediately
        'modestbranding' => 1,           // 1: removes logo from controls
        'controls'       => 0,           // 0: hides the controls entirely
        'rel'            => 0,           // 0: does not show related videos
        'showinfo'       => 0,           // 0: does not show info like title
        'iv_load_policy' => 3,           // 3: removes annotations
        'start'          => $startTime,  // start time in seconds
        'end'            => $endTime     // end time in seconds
    );
    
?>

<div class="textcenter">
    <div id="player"></div>
</div>

<!-- include form to collect RT and advance page -->
<div><?php echo $text; ?></div>
<div class="textcenter">
    <button class="collectorButton collectorAdvance" id="FormSubmitButton">Next</button>
</div>

<script>
    var player;
    var submitOnDone       = <?= $submitOnDone       ? 'true' : 'false' ?>;
    var preventEarlySubmit = <?= $preventEarlySubmit ? 'true' : 'false' ?>;
    
    if (preventEarlySubmit) { $("#FormSubmitButton").addClass("invisible"); }
    
    function onYouTubeIframeAPIReady() {
        player = new YT.Player("player", {
            height     : "315",
            width      : "420",
            videoId    : "<?= $videoId ?>",
            events     : {
                           "onStateChange" : onPlayerStateChange
                         },
            playerVars : {
                           <?php
                             foreach ($parameters as $parameter => $value) {
                               echo '"' . $parameter . '" : "' . $value . '",' . "\r\n";
                             }
                           ?>
                         }
        });
    }
    
    function onPlayerStateChange(e) {
        if (e.data === YT.PlayerState.ENDED) {
            if (submitOnDone) {
                $("#FormSubmitButton").click();
            }
            if (preventEarlySubmit) {
                $("#FormSubmitButton").removeClass("invisible");
            }
        }
    }
</script>
<script src="https://www.youtube.com/iframe_api"></script>

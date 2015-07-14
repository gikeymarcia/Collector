<?php 
    /* @TODO JS functions for seek/start/stop to set start and stop times. */
    /* @TODO JS not needed for YouTube vids, use ?start=10&end=14 */
    
    /**
     * How to use:
     * In the Settings column, separate parameters by pipe |
     * Parameters;
     *     submitOnDone
     *         set to false to prevent auto-submission upon video completion
     *     preventEarlySubmit
     *         set to false to disable the submit button until video completion
     */
    
    function isValidYouTube($string) {
        if (isLocal($string)) return false;
        if (false !== stripos($string, 'youtube')) return true;
        if (false !== stripos($string, 'youtu.be')) return true;
        return false;
    }
    
    if (!isValidYouTube($cue)) {
        throw new InvalidArgumentException('The given video source is not '
            . 'supported. The cue should be a YouTube URL.');
    }
    
    $submitOnDone       = true;
    $preventEarlySubmit = true;
    $settings = explode('|', $settings);
    foreach ($settings as $setting) {
        if ($test = removeLabel($setting, 'submitOnDone')) {
            if ($test === 'false') {
                $submitOnDone = false;
            }
        } elseif ($test = removeLabel($setting, 'preventEarlySubmit')) {
            if ($test === 'false') {
                $preventEarlySubmit = false;
            }
        }
    }
    
    // YouTube URL
    $videoId = youtubeUrlCleaner($cue, true);
    
    if (!isset($startTime) OR !is_numeric($startTime)) { $startTime = 0; }
    if (!isset($endTime)   OR !is_numeric($endTime))   { $endTime   = 'na'; }
    
    $parameters = array(
        'autoplay'       => 1,
        'modestbranding' => 1,
        'controls'       => 0,
        'rel'            => 0,
        'showinfo'       => 0,
        'iv_load_policy' => 3,
        'start'          => $startTime,
        'end'            => $endTime
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

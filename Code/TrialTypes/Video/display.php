<?php 
    /* @TODO JS functions for seek/start/stop to set start and stop times. */
    /* @TODO JS not needed for YouTube vids, use ?start=10&end=14 */
    
    /**
     * Warning! This is a very primitive version of the Video trial type.
     * We expect to make improvements in the future, but in the mean time,
     * try using the YouTube trial type instead, and load your videos to
     * YouTube rather than your server.
     **/

    if (!isLocal($cue)) {
        // video is not a local file
        if (false !== strpos($cue, 'youtube') 
            || false !== strpos($cue, 'youtu.be')
        ) {
            // YouTube URL
            $vidSource = youtubeUrlCleaner($cue);
            $parameters = 'autoplay=1&modestbranding=1&controls=0&rel=0&showinfo=0&iv_load_policy=3';
        } else if (strpos($cue, 'vimeo')) {
            // Vimeo URL
            $vidSource = vimeoUrlCleaner($cue);
            $parameters = 'autoplay=1&badge=0&byline=0&portrait=0&title=0';
        } else {
            // Unsupported URL
            throw new InvalidArgumentException('The given video source is not '
                . 'supported. Please use Vimeo, YouTube, or a local file.');
        }
        $source = $vidSource.'?'.$parameters;
    } else {
        // video is a local file
        $source = "{$_PATH->experiment}/{$cue}";
    }
?>

<div class="textcenter">
    <iframe width="420" height="315" frameborder="0" 
            webkitallowfullscreen mozallowfullscreen allowfullscreen
            src="<?php echo $source; ?>" >
    </iframe>
</div>

<!-- include form to collect RT and advance page -->
<div><?php echo $text; ?></div>
<div class="textcenter">
    <button class="collectorButton collectorAdvance" id="FormSubmitButton">Next</button>
</div>

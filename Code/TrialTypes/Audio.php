<?php
    $compTime = 5;    // time in seconds to use for 'computer' timing
?>
<div class="precache textcenter">
    <audio src="<?php echo $expFiles.$cue; ?>" autoplay>
        <p>Your browser does not support the audio element.</p>
    </audio>
</div>

<!-- include form to collect RT and advance page -->
<div><?php echo $text; ?></div>
<div class="precache textcenter">
    <input class="collector-button collector-button-advance" id="FormSubmitButton" type="submit" value="Next">
</div>
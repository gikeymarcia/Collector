<?php
	$compTime = 60;					// time in seconds to use for 'computer' timing
?>

    <div><?php echo $text; ?></div>
    <div class="precache"> <?php echo $cue; ?> </div>
    <h3 class="precache textcenter">End of Passage</h3>

    <!-- include form to collect RT and advance page -->
    <div class="precache textright">
        <input class="button button-trial-advance" id="FormSubmitButton" type="submit" value="Next" />
    </div>

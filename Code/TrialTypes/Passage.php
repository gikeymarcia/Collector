<?php
	$compTime = 60;					// time in seconds to use for 'computer' timing
	trialTiming();					// determines timing and user/computer timing mode
?>

    <div class="precache"> <?php echo fixBadChars($cue); ?> </div>
    <h3 class="precache textcenter">End of Passage</h3>

    <!-- include form to collect RT and advance page -->
    <div class="precache textright">
        <form class="<?php echo $formClass; ?>" action="<?php echo $postTo; ?>" method="post">
            <input class="hidden" id="RT" name="RT" type="text" value="" />
            <input class="button button-trial-advance" id="FormSubmitButton" type="submit" value="Next" />
        </form>
    </div>

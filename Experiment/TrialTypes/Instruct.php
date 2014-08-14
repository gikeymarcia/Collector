<?php
    /**
     * I'm a custom modification of the normal instruct trial type!
     *
     * As long as I exist, the "instruct" trial type in the code folder will be overwritten with this trial type
     * I am going to make the instructions appear 10% bigger, so that they are easier to read.
     */
	$compTime = 5;        // time in seconds to use for 'computer' timing
?>
    <div style="font-size: 110%;"><?php echo $text; ?></div>

	<!-- include form to collect RT and advance page -->
	<div class="precache textright">
		<input class="button button-trial-advance" id="FormSubmitButton" type="submit" value="Next" />
	</div>
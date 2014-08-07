<?php
	$compTime = 5;					// time in seconds to use for 'computer' timing
?>
    <div><?php echo $text; ?></div>
	<div class='study precache'>
		<span class="study-left">  <?php echo $cue; ?>    </span>
		<span class="study-divider">         :            </span>
		<span class="study-right"> <?php echo $answer; ?> </span>
	</div>

    <!-- include form to collect RT and advance page -->
    <div class="precache textcenter">
        <input class="button button-trial-advance" id="FormSubmitButton" type="submit" value="Next" />
    </div>

<?php
	$compTime = 5;					// time in seconds to use for 'computer' timing
	trialTiming();					// determines timing and user/computer timing mode

?>
	<div class="precache textcenter">
		<audio src="<?php echo $expFiles.$cue; ?>" autoplay>
			<p>Your browser does not support the audio element.</p>
		</audio>
	</div>

    <!-- include form to collect RT and advance page -->
    <div class="precache textright">
        <form class='<?php echo $formClass; ?>' action='<?php echo $postTo; ?>' method=post>
            <input class=hidden id=RT name=RT type=text value='' />
            <input class='button button-trial-advance' id=FormSubmitButton type=submit value='Next' />
        </form>
    </div>
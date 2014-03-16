<?php
	$compTime = 5;					// time in seconds to use for 'computer' timing
	trialTiming();					// determines timing and user/computer timing mode
	echo '<div class="WordWrap PreCache">
			<audio src="'.$expFiles.$cue.'" autoplay>
				<p>Your browser does not support the audio element.</p>
			</audio>
		  </div>';

    // include form to collect RT and advance page
    echo '<div class="precache textright">
            <form class="'.$formClass.'" action="postTrial.php" method=post>
                <input class=hidden id=RT name=RT type=text value="" />
                <input class="button button-trial-advance" id=FormSubmitButton type=submit value="Next"" />
            </form>
          </div>';
<?php
	$compTime = 60;					// time in seconds to use for 'computer' timing
	trialTiming();					// determines timing and user/computer timing mode

	echo '<div class="passage PreCache">'.fixBadChars($cue).'</div>
		  <div id="end" class="PreCache">End of Passage</div>';
		  $formClass = $formClass.' center';

    // include form to collect RT and advance page
    echo '<div class="precache textright">
            <form class="'.$formClass.'" action="postTrial.php" method=post>
                <input class=hidden id=RT name=RT type=text value="" />
                <input class="button button-trial-advance" id=FormSubmitButton type=submit value="Next"" />
            </form>
          </div>';
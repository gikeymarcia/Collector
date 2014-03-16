<?php
	// determines timing and user/computer timing mode
	trialTiming();

	// show stim
	echo '<div>'. $currentTrial['Procedure']['Procedure Notes'].'</div>';

	// include form to collect RT and advance page
	echo '<div class="precache textright">
			<form class="'.$formClass.'" action="postTrial.php" method=post>
				<input class=hidden id=RT name=RT type=text value="" />
				<input class="button button-trial-advance" id=FormSubmitButton type=submit value="Next"" />
			</form>
		  </div>';
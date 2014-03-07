<?php
	$compTime = 5;					// time in seconds to use for 'computer' timing
	trialTiming();					// determines timing and user/computer timing mode
	echo '<div class="WordWrap PreCache">
			<audio autoplay><source src="'.$up.$expFiles.$cue.'" /></audio>
		  </div>';
	// the hidden form below collects RT and displays the 'Done' button for user timed trials
	echo '<div id="buttPos" class="PreCache">
			<form class="'.$formClass.'" action="postTrial.php" method="post">
				<input	name="RT"	type="text"		value=""	class="RT Hidden"	/>
				<input	id="FormSubmitButton"	type="submit"	value="Done"	/>
			</form>
		  </div>';
?>
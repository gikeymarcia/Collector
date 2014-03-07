<?php
	trialTiming();					// determines timing and user/computer timing mode
	
	echo '<div id="centerContent" class="PreCache">
			<div class="Instruct">'. $currentTrial['Procedure']['Procedure Notes'].'</div>
		  </div>';
	// the hidden form below collects RT and displays the 'Done' button for user timed trials
	echo '<div id="buttPos" class="PreCache">
			<form class="'.$formClass.'" action="postTrial.php" method="post">
				<input	name="RT"	type="text"		value=""	class="RT Hidden"	/>
				<input	id="FormSubmitButton"	type="submit"	value="Done"	/>
			</form>
		  </div>';
?>
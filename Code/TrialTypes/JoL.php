<?php
	$compTime = 8;					// time in seconds to use for 'computer' timing
	trialTiming();					// determines timing and user/computer timing mode

	echo '<div id="JOLpos" class="PreCache">
			<div id="jol">How likely are you to correctly remember this item on a later test?</div>
			<div id="subpoint" class="gray">Type your response on a scale from 0-100 using the entire range of the scale</div>';

	echo '<form class="'.$formClass.'"  autocomplete="off"  action="postTrial.php"  method="post">
			<input	name="Response"	type="text"	value=""			class="Textbox"			autocomplete="off" />	<br />
			<input	name="RT"		type="text"	value="RT"			class="RT Hidden"		/>
			<input	name="RTkey"	type="text"	value="no press"	class="RTkey Hidden" 	/>
			<input	name="RTlast"	type="text"	value="no press"	class="RTlast Hidden" 	/>
			<input	id="FormSubmitButton"	type="submit"	value="Submit"	/>
		  </form>
		 </div>';
?>
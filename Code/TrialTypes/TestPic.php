<?php
	$compTime = 8;					// time in seconds to use for 'computer' timing
	trialTiming();					// determines timing and user/computer timing mode
	
	echo '<div class="pic PreCache">
			'. show($cue).
		 '</div>';
	$formClass = $formClass.' center';
	
	echo '<form class="'.$formClass.' PreCache"  autocomplete="off"  action="postTrial.php"  method="post" class="PreCache">
			<input  name="Response" type="text" value=""			class="Textbox picWord PreCache" autocomplete="off" />	<br />
			<input	name="RT"		type="text"	value="RT"			class="RT Hidden"		/>
			<input	name="RTkey"	type="text"	value="no press"	class="RTkey Hidden" 	/>
			<input	name="RTlast"	type="text"	value="no press"	class="RTlast Hidden" 	/>
			<input	id="FormSubmitButton"	type="submit"	value="Submit"	/>
		  </form>';
?>
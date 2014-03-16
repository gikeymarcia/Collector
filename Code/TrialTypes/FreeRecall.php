<?php
	$compTime = 60;					// time in seconds to use for 'computer' timing
	trialTiming();					// determines timing and user/computer timing mode
	
	$prompt =& $currentTrial['Procedure']['Procedure Notes'];
	echo '<div id="centerContent" class="PreCache">
		<div class="Prompt">' . $prompt . '</div>
			<form class="'.$formClass.'"  autocomplete="off"  action="'.$postTo.'"  method="post">
				<textarea rows="20" cols="60" name="Response" class="PreCache" wrap="physical" value=""></textarea>	<br />
				<input	name="RT"		type="text"	value="RT"			class="RT Hidden"		/>
				<input	name="RTkey"	type="text"	value="no press"	class="RTkey Hidden" 	/>
				<input	name="RTlast"	type="text"	value="no press"	class="RTlast Hidden" 	/>
				<input	id="FormSubmitButton"	type="submit"	value="Submit"	/>
			</form>
			</div>';
?>
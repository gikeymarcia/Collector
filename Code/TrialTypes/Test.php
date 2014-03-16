<?php
	$compTime = 8;					// time in seconds to use for 'computer' timing
	trialTiming();					// determines timing and user/computer timing mode
	
	echo '<div class="WordWrap PreCache">
			<span class="leftcopy">'.$cue.'</span>
			<span class="dividercopy">:</span>
			<form class="'.$formClass.' leftfloat"  autocomplete="off"  action="'.$postTo.'"  method="post">
				<input	name="Response"	type="text"	value=""			class="Textbox Right PreCache"	autocomplete="off" />
				<input	name="RT"		type="text"	value="RT"			class="RT Hidden"		/>
				<input	name="RTkey"	type="text"	value="no press"	class="RTkey Hidden" 	/>
				<input	name="RTlast"	type="text"	value="no press"	class="RTlast Hidden" 	/>
				<input	id="FormSubmitButton"	type="submit"	value="Submit"	/>
			</form>
		  </div>';
?>
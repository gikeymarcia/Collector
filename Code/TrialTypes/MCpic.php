<?php
	$compTime = 8;					// time in seconds to use for 'computer' timing
	trialTiming();					// determines timing and user/computer timing mode
	
	$MCbuttons 		= $MultiChoiceButtons;
	$itemsPerRow	= 4;
	if($_SESSION['MCbutton'] == FALSE) {			// shuffle button position before presenting for the 1st time
		shuffle($MCbuttons);						// turn this line off to maintain the same choice order between-subjects
		$_SESSION['MCbutton'] = $MCbuttons;
	}
	// show the image
	echo '<div class="pic PreCache">
			'. show($cue).
		 '</div>';		
	// display the MC button choices
	$count = 0;
	echo '<div id="ButtonArea">';
	foreach ($_SESSION['MCbutton'] as $aButton) {
		echo '<span class="TestMC PreCache">'.$aButton.'</span>';
		$count++;
		if ($count == $itemsPerRow) {
			echo '<br style="clear: both;"/>';
			$count = 0;
		}
	}
	echo '</div>';
	
	$formClass = $formClass.' center';
	echo '<form class="'.$formClass.'" action="postTrial.php" method="post">
			<input	name="Response"	type="text"	value="no press"	class="Textbox Hidden"	/>
			<input	name="RTkey"	type="text"	value="no press"	class="RTkey Hidden" 	/>
			<input	name="RTlast"	type="text"	value="no press"	class="RTlast Hidden" 	/>
			<input	name="RT"		type="text"	value="RT"	class="RT Hidden"		/>
		  </form>';
?>
<?php
	$compTime = 5;					// time in seconds to use for 'computer' timing
	trialTiming();					// determines timing and user/computer timing mode
?>
	<style>
		.cueContainer	{	text-align: center;	font-size: 300%;	}
	</style>
	<form class="<?php echo $formClass; ?> collector-form" action="<?php echo $postTo; ?>" method="post">
		<div class="cueContainer">
			<div><?= $cue ?></div>
			<input class="hidden" id="RT" name="RT" type="text" value="" />
			<input class="button button-trial-advance" id="FormSubmitButton" type="submit" value="Next" />
		</div>
	</form>

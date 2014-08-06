<?php
	$compTime = 60;					// time in seconds to use for 'computer' timing

	$prompt = explode('|', $currentTrial['Procedure']['Procedure Notes']);
?>
    <div class="prompt"><?= trim($prompt[0]) ?></div>
	<?php
		if (isset($prompt[1])) {
			$stimPrompt = trim($prompt[1]);
			$cues = explode('|', $cue);
			$answers = explode('|', $answer);
			foreach ($cues as $i => $thisCue) {
				echo str_replace(array('$cue', '$answer'), array($thisCue, $answers[$i]), $stimPrompt);
			}
		}
	?>
	<textarea rows="20" cols="55" name="Response" class="precache" wrap="physical" value=""></textarea>
	<div class="textleft">
		<input class="button button-trial-advance" id="FormSubmitButton" type="submit" value="Submit"   />
	</div>
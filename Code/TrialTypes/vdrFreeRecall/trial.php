<?php
	$compTime = 60;					// time in seconds to use for 'computer' timing

	$prompt = str_replace(array($cue, $answer), array('$cue', '$answer'), $text);      // undo this change, since we are doing something a little non-standard here
    $prompts = explode('|', $prompt);
?>
    <div class="prompt"><?= trim($prompts[0]) ?></div>
	<?php
		if (isset($prompts[1])) {
			$cues = explode('|', $cue);
			$answers = explode('|', $answer);
			foreach ($cues as $i => $thisCue) {
				echo str_replace(array('$cue', '$answer'), array($thisCue, $answers[$i]), $prompts[1]);
			}
		}
	?>
	<textarea rows="20" cols="55" name="Response" class="precache" wrap="physical" value=""></textarea>
	<div class="textleft">
		<input class="button button-trial-advance" id="FormSubmitButton" type="submit" value="Submit"   />
	</div>
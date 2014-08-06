<?php
	$compTime = 8;					// time in seconds to use for 'computer' timing
	
	$firstTrialType = trim(strtolower($currentTrial['Procedure']['Trial Type']));
	
	// picture trial version of feedback
	if($firstTrialType == 'studypic' OR $firstTrialType == 'testpic' OR $firstTrialType == 'mcpic') {
?>

    <!-- show the image -->
    <div class="precache pic">
        <?php echo show($cue); ?>
    </div>
    <!-- show the answer -->
	<h2>The correct answer was:</h2>
	<h1 class="precache textcenter"> <?php echo show($answer); ?></h1>
	
    <!-- include form to collect RT and advance page -->
    <div class="precache textcenter">
        <input class="button button-trial-advance" id="FormSubmitButton" type="submit" value="Next" />
    </div>
	
<?php } else { 
		?><h2>The correct answer was:</h2><?php
		$cues = explode('|', $cue);
		$answers = explode('|', $answer);
		foreach( $cues as $i => $thisCue ) {
			$thisAnswer = $answers[$i];
			?>

	<div class="study precache">
		<span class="study-left">  <?php echo $thisCue; ?>    </span>
		<span class="study-divider">         :            </span>
		<span class="study-right"> <?php echo $thisAnswer; ?> </span>
	</div>
			<?php
		}
		?>

    <div class="precache textcenter">
        <input class="button button-trial-advance" id="FormSubmitButton" type="submit" value="Next" />
    </div>

	<?php
	}	?>

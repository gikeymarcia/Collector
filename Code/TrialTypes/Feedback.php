<?php
	$compTime = 8;					// time in seconds to use for 'computer' timing
	trialTiming();					// determines timing and user/computer timing mode
	
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
        <form class="<?php echo $formClass; ?> collector-form" action="<?php echo $postTo; ?>" method="post">
            <input class="hidden" id="RT" name="RT" type="text" value="" />
            <input class="button button-trial-advance" id="FormSubmitButton" type="submit" value="Next" />
        </form>
    </div>
	
<?php } else { ?>

	<h2>The correct answer was:</h2>
	<div class="study precache">
		<span class="study-left">  <?php echo $cue; ?>    </span>
		<span class="study-divider">         :            </span>
		<span class="study-right"> <?php echo $answer; ?> </span>
	</div>

    <!-- include form to collect RT and advance page -->
    <div class="precache textcenter">
        <form class="<?php echo $formClass; ?> collector-form" action="<?php echo $postTo; ?>" method="post">
            <input class="hidden" id="RT" name="RT" type="text" value="" />
            <input class="button button-trial-advance" id="FormSubmitButton" type="submit" value="Next" />
        </form>
    </div>

<?php } ?>

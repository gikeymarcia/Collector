<?php
	$compTime = 8;					// time in seconds to use for 'computer' timing
    
    include $expFiles . $custTTF . 'skipCode.php';
?>
    <div><?php echo $text; ?></div>
    <div class="study precache">
        <span class="study-left">  <?php echo $cue; ?>    </span>
        <span class="study-divider">         :            </span>
		<div class="study-right">
			<input name="Response" type="text" value="" class="copybox" autocomplete="off" />
		</div>
		<div class="textcenter">
			<input class="collector-button collector-button-advance" id="FormSubmitButton" type="submit" value="Submit"   />
		</div>
    </div>
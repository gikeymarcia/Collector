<?php
	$compTime = 8;					// time in seconds to use for 'computer' timing
    
    include $expFiles . $custTTF . 'skipCode.php';
?>
    <div><?php echo $text; ?></div>
    <div class="study alignToInput precache">
        <span class="study-left">  <?php echo $cue; ?>    </span>
        <span class="study-divider">         :            </span>
		<div class="study-right">
			<input name="Response" type="text" value="" class="copybox collectorInput" autocomplete="off" />
		</div>

    </div>
    <div class="textcenter">
        <button class="collectorButton collectorAdvance" id="FormSubmitButton">Submit</button>
    </div>
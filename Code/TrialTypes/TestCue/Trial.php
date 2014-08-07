<?php
	$compTime = 8;					// time in seconds to use for 'computer' timing
?>
	<style>
		.testCueRight	{	white-space: nowrap;	}
		.testCueRight input	{	width: 300px;	padding: 0px 1px;	font-family: "Roboto", "Open Sans", Arial, Helvetica, sans-serif;	}
	</style>
    <div><?php echo $text; ?></div>
    <div class="study precache">
        <span class=study-left>  <?php echo $cue; ?>    </span>
        <span class=study-divider>         :            </span>
		<div class="testCueRight">
			<?= substr($answer,0,2) ?><input name=Response type=text value="" autocomplete="off" />
		</div>
		<div class=textcenter>
			<input class="button button-trial-advance" id=FormSubmitButton type=submit value="Submit"  />
		</div>
    </div>
<?php
	trialTiming();         // determines timing and user/computer timing mode
?>
	<div> <?php echo $currentTrial['Procedure']['Procedure Notes']; ?> </div>

	<!-- include form to collect RT and advance page -->
	<div class="precache textright">
	   <form class="<?php echo $formClass; ?>" action="<?php echo $postTo; ?>" method=post>
            <input class=hidden id=RT name=RT type=text value="" />
			<input class="button button-trial-advance" id=FormSubmitButton type=submit value="Next" />
		</form>
	</div>
<?php
	$compTime = 8;					// time in seconds to use for 'computer' timing
	trialTiming();					// determines timing and user/computer timing mode
?>
    <div class="study precache">
        <span class=study-left>  <?php echo $cue; ?>    </span>
        <span class=study-divider>         :            </span>
        <span class=study-right>         &nbsp;         </span>
    </div>

    <div class="study precache">
        <span class=study-left>  <?php echo $cue; ?>    </span>
        <span class=study-divider>         :            </span>
        <form class="<?php echo $formClass; ?> collector-form"  autocomplete="off"  action="<?php echo $postTo; ?>"  method="post">
            <div class=study-right>
                <input name=Response type=text value="" class=copybox autocomplete="off" />
            </div>
            <input class=hidden  id=RT     name=RT       type=text value="RT"       />
            <input class=hidden  id=RTkey  name=RTkey    type=text value="no press" />
            <input class=hidden  id=RTlast name=RTlast   type=text value="no press" />
            <div class=textcenter>
                <input class="button button-trial-advance" id=FormSubmitButton type=submit value="Submit" />
            </div>
        </form>
    </div>
	
	<script>
		COLLECTOR.trial.delaycopy = function() {
			COLLECTOR.timer( 3, function() {
				$(".study-right").first().html( "<?= $answer; ?>" );
			} );
		}
	</script>
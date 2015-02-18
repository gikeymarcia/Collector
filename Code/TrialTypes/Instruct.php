<?php
    /* WARNING!  Any changes you make to this Instruct trial type will not change the experiment becasuse
     * there is another version of 'Instruct' within the '/Experiment/TrialTypes/` folder.
     * 
     * This was done as an example of how you can copy trial types from '/Code/TrialTypes/'
     * into '/Experiment/TrialTypes/' and override the default trial display. This is a
     * feature meant to keep all of your modification in one place '/Experiment/'.
     * 
     * You can also make your own new trial types inside of the '/Experiment/TrialTypes/' folder and will have
     * access to them in your experiment. The real benefit of developing experiments this way is that when new
     * version of Collector come out with features you want you will be able to download the new version and copy
     * your /Experiment/ folder into the new version so you can take advantage of new features without having
     * to completely port your experiment to the new version.
     */
	$compTime = 5;        // time in seconds to use for 'computer' timing
	
	// use the `Cue` if a valid one is called and there is no `Text` set in the procedure
	if (($cue != '')
        AND (trim($text) == '')
    ){
        $text = $cue;
    }
?>
    <div><?php echo $text; ?></div>

	<div class="precache textright">
		<input class="button button-trial-advance" id="FormSubmitButton" type="submit" value="Next" />
	</div>
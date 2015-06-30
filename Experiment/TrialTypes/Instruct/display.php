<?php
    /**
     * This is an example of a trial type in the Experiment/ folder overwriting
     * the trial type found in the Code/ folder.  Any changes to this Instruct
     * will be used throughout the program, so if you make any modifications,
     * such as increasing font-size, you can still download the latest Code/
     * folder of the Collector without worrying about accidentally losing
     * your changes.
     */
    
    // use the `Cue` if a valid one is called and there is no `Text` set in the procedure
    if (($cue != '')
        AND (trim($text) == '')
    ){
        $text = $cue;
    }
?>
<div><?php echo $text; ?></div>

<div class="textright">
    <button class="collectorButton collectorAdvance" id="FormSubmitButton">Next</button>
</div>
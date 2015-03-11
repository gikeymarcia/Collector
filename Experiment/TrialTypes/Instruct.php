<?php
    /**
     * This is an example of a trial type in the Experiment/ folder overwriting
     * the trial type found in the Code/ folder.  Any changes to this Instruct
     * will be used throughout the program, so if you make any modifications,
     * such as increasing font-size, you can still download the latest Code/
     * folder of the Collector without worrying about accidentally losing
     * your changes.
     */
     
    $compTime = 5;        // time in seconds to use for 'computer' timing
    
    // use the `Cue` if a valid one is called and there is no `Text` set in the procedure
    if (($cue != '')
        AND (trim($text) == '')
    ){
        $text = $cue;
    }
?>
<section class="vcenter">
  <div><?php echo $text; ?></div>

  <div class="collector-form-element precache textright">
    <input class="collector-button collector-button-advance" id="FormSubmitButton" type="submit" value="Next">
  </div>
</section>
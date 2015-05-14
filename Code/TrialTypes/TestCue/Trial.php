<?php
    $compTime = 8;    // time in seconds to use for 'computer' timing
?>

<style>
    /*sets size of the trial content window*/
    #content {
        width: 90%;
        max-width: 850px;
    }
    
    /*makes the cue and divider line up with the response box*/
    .study-left, .study-divider {
        margin-top: 4px;
    }
</style>

<!-- optional text -->
<div><?php echo $text; ?></div>

<!-- stimulus -->
<div class="study test-cue precache">
    <span class="study-left"><?php echo $cue; ?></span>
    <span class="study-divider">         :      </span>
    <span class="study-right"
      ><?php echo substr($answer,0,2); ?><input name="Response" type="text" value="" autocomplete="off" class="cInput"></span>
</div>
  
<div class="textcenter">
    <button class="collector-button collector-button-advance" id="FormSubmitButton">Submit</button>
</div>
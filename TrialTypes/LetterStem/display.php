<?php
    $stemLength = 2; // default value
    if (is_numeric($_trialSettings->stem)) $stemLength = (int) $_trialSettings->stem;
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
<div> <?= $_EXPT->get('text') ?> </div>

<!-- stimulus -->
<div class="study test-cue">
  <span class="study-left"> <?= $_EXPT->get('cue') ?> </span>
  <span class="study-divider"> : </span>
  <span class="study-right">
    <?= substr($_EXPT->get('answer'), 0, $stemLength) ?><input name="Response" type="text" value="" class="collectorInput">
  </span>
</div>
  
<div class="textcenter">
  <button class="collectorButton collectorAdvance" id="FormSubmitButton">Submit</button>
</div>

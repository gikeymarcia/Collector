<?php 
    if (empty($font_size)) {
        trigger_error("Your 'Font Size' column needs a value.", E_USER_ERROR);
    }
?>


<div>
  <?= $_EXPT->get('text'); ?>
</div>

<div class="textcenter" style="font-size:<?= $font_size ?>">
  <?= Collector\Helpers::show($_EXPT->get('cue')) ?>
</div>

<div class="textcenter">
  <button class="collectorButton collectorAdvance" id="FormSubmitButton">Next</button>
</div>
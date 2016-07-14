<?php
/**
 * Font Size trial type can take multiple types of fontsize values
 * in its settings column
 *
 *     Settings          Value
 *     fontsize          20px
 *     fontsize          110%
 *     fontsize          2em
 *     fontsize          16pt
 */
?>


<div>
  <?= $_EXPT->get('text'); ?>
</div>

<div class="textcenter" style="font-size:<?= $_TRIAL->settings->fontsize ?>;">
  <?= show($_EXPT->get('cue')) ?>
</div>

<div class="textcenter">
  <button class="collectorButton collectorAdvance" id="FormSubmitButton">Next</button>
</div>
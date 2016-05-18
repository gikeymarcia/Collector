<?php
/**
 * PicFeedback
 *   The settings below have not yet been implemented
 *   Settings          Values
 *   noHeader
 *   noAnswer
 *
 * These settings will be implemented once we move the
 * changes of the /trialSettings into /dev
 */

if (!isset($text) || $text === '') {
    $text = 'The correct answer was:';
}

?>
<!-- Show the image -->
<div class="pic">
    <?php show($cue); ?>
</div>

<div class="textcenter">
    <h3><?php $text; ?></h3>
</div>

<h2 class="textcenter">
    <?php show($answer); ?>
</h2>

<!-- include form to collect RT and advance page -->
<div class="textcenter">
  <button class="collectorButton collectorAdvance" id="FormSubmitButton" autofocus >Next</button>
</div>

<style>
  #content {
    width: 90%;
    max-width: 850px;
    min-width: 600px;
  }
</style>
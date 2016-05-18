<?php
if (empty($_EXPT->get('text'))) {
    $_EXPT->update('text', 'How likely are you to correctly recall this item on'
        . 'a later test?|Type your response on a scale from 0-100.');
}
$texts = explode('|', $_EXPT->get('text'));
$mainText = array_shift($texts);
?>

<div class="textcenter">
  <h3><?= $mainText ?></h3>
  
  <?php foreach ($texts as $t): ?>
  <p><?= $t ?></p>
  <?php endforeach; ?>
</div>
  
<div class="textcenter">
  <input name="JOL" type="text" value="" class="forceNumeric textcenter collectorInput">
  <button class="collectorButton" id="FormSubmitButton">Submit</button>
</div>

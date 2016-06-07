<?php
$mainTrialType = $_EXPT->get('trial type', true);

if (empty($_EXPT->get('text'))) {
    $_EXPT->update('text', 'The correct answer was:');
}

// picture trial version of feedback
if (strrpos($mainTrialType, 'pic') === strlen($mainTrialType) - 3): ?>
    <!-- show the image -->
    <div class="pic"><?= show($cue) ?> </div>

    <!-- show the answer -->
    <div class="textcenter"><h3> <?= $_EXPT->get('text') ?> </h3></div>
    <h2 class="textcenter"> <?= show($_EXPT->get('answer')) ?> </h2>

<?php // text feeback trials 
else: ?>
    <h2 class="textcenter"> <?= $_EXPT->get('text') ?> </h2>
    <?php $cues = explode('|', $_EXPT->get('cue'));
          $answers = explode('|', $_EXPT->get('answer'));
          foreach ($cues as $i => $cue): ?>
    <div class="study">
      <span class="study-left">    <?= $cue; ?>       </span>
      <span class="study-divider">       :            </span>
      <span class="study-right"> <?= $answers[$i]; ?> </span>
    </div>
    <?php endforeach; ?>
<?php endif; ?>

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

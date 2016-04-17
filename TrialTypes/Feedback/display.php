<?php
$firstTrial = $_EXPT->getTrialProcedure($currentPos, 0);
$firstTrialType = strtolower($firstTrial['Trial Type']);
if (!isset($text) || $text === '') {
    $text = 'The correct answer was:';
}

// picture trial version of feedback
if ($firstTrialType === 'cuepic'
    || $firstTrialType === 'cuedrecallpic'
    || $firstTrialType === 'multiplechoicepic'
): ?>
    <!-- show the image -->
    <div class="pic"><?= Collector\Helpers::show($cue) ?> </div>

    <!-- show the answer -->
    <div class="textcenter"><h3><?= $text ?> </h3></div>
    <h2 class="textcenter"> <?= Collector\Helpers::show($answer) ?> </h2>

<?php 

// text feeback trials 
else: ?>
    <h2 class="textcenter"><?php echo $text; ?></h2>
    <?php $cues = explode('|', $cue);
          $answers = explode('|', $answer);
          foreach ($cues as $i => $thisCue): ?>
    <div class="study">
      <span class="study-left">  <?php echo $thisCue; ?>     </span>
      <span class="study-divider">           :               </span>
      <span class="study-right"> <?php echo $answers[$i]; ?> </span>
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
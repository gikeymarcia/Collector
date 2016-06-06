<?php
// determine the # of rounds by entering "rounds = 100" in your settings column
$default = 150;
$rounds  = is_numeric($_TRIAL->settings->rounds) ? $_TRIAL->settings->rounds : $default;
$options = array('A', 'B');
?>

<table class="dmtTable">
  <tr class="textRow">
    <td colspan="<?= count($options) ?>"><?= $_EXPT->get('text') ?></td>
    <td><div>Current</div><div class="currentPoints">&nbsp;</div></td>
    <td><div>Cumulative</div><div class="cumulativePoints">&nbsp;</div></td>
  </tr>
  <tr class="displayRow">
    <td class="imgHolder" colspan="<?= count($options) ?>">
      <?= Collector\Helpers::show($_EXPT->get('cue')) ?>
    </td>
    <td class="tankHolder" rowspan="2">
      <div class="currentTank dmtTank">
        <div class="currentLevel dmtLevel"></div>
      </div>
    </td>
    <td class="tankHolder" rowspan="2">
      <div class="cumulativeTank dmtTank">
        <div class="cumulativeLevel dmtLevel"></div>
        <div class="goalBar"></div>
      </div>
    </td>
  </tr>
  <tr class="optionRow">
    <?php foreach ($options as $opt): ?>
    <td><button type="button" class="dmtOption"><?= $opt ?></button></td>
    <?php endforeach; ?>
  </tr>
</table>
    
<div class="textcenter hidden">
  <input class="button button-trial-advance" id="FormSubmitButton" type="submit" value="Submit"   />
</div>
    
    
<script>
trialBegin = function () {
    $(":focus").blur();
    DMT.rounds = <?= $rounds ?>;
    DMT.begin();
};
</script>

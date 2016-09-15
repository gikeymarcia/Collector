<?php
$cues    = explode('|', $_EXPT->get('cue'));
$answers = explode('|', $_EXPT->get('answer'));
$prompts = explode('|', $_EXPT->get('text'));
?>

<div class="prompt"> <?= trim($prompts[0]) ?> </div>

<?php
if (isset($prompts[1])) {
    foreach ($cues as $i => $cue) {
        echo str_replace(
            array('$cue', '$answer'),
            array($cue, $answers[$i]),
            $prompts[1]
        );
    }
}

$input = 'one';

$settings = explode('|', $_EXPT->get('settings'));
foreach ($settings as $setting) {
    $test = removeLabel($setting, 'input');
    if ($test !== false) {
        $input = strtolower($test);
        if (($test !== 'one') && ($test !== 'many') && !is_numeric($test)) {
            $tt = $_EXPT->get('trial type');
            exit("Error: invalid 'input' setting for trial type '{$tt}', "
                . "on trial '{$_EXPT->position}'");
        }
    }
}

if ($input === 'one'): ?>
<div class="pad">
  <textarea rows="20" cols="55" name="Response" class="collectorInput" wrap="physical" value=""></textarea>
  <br><button class="collectorButton collectorAdvance" id="FormSubmitButton" autofocus>Submit</button>
</div>

<?php else:
$comparison = ($input === 'many') ? $input : (substr_count($answer, '|') + 1);
?>
<style>
  .freeRecallArea {
    display:inline-block;
    width:850px;
    text-align:left;
  }
  .freeRecallArea input {
    width:192px;
    margin:4px;
    padding:4px;
  }
</style>

<div class="textcenter pad">
  <div class="freeRecallArea">
    <?php for ($i = 1; $i <= $compare; ++$i): ?>
    <input type="text" name="Response<?= $i ?>" class="noEnter"/>
    <?php endfor; ?>
  </div>
  <br><button class="collectorButton collectorAdvance" id="FormSubmitButton" autofocus>Submit</button>
</div>
<?php endif; ?>

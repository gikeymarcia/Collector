<?php 
/**
 * Attention Check-- how to use it
 * - This trial is designed to be a like an 'Instruct' that verifies whether
 * - or not the participant understood the question
 * 
 * Main text of the 'Instruct' goes into the 'Text' column of the procedure
 * 
 * 
 * The options for the multiple choice text go into the 'Settings' column
 *     
 *     Setting         Value
 *     
 *     Question        Who watches the Watchmen?
 *     Correct         Text for the correction option goes here
 *     Alternatives    option1; option2; option 3; etc...
 * 
 */

// reading and saving settings
$question     = $_TRIAL->settings->question;
$correct      = $_TRIAL->settings->correct;
$alternatives = $_TRIAL->settings->alternatives;

// using default text if none is provided
$text = isset($text) ? $text : "See <code>/TrialTypes/AttentionCheck/display.php</code> for ".
                               "instructions on how to change this text. Pay close attention.";
?>

<div class="alert alert-instructions">Please carefully read the instructions again.</div>
<section class="instructions">
  <h2 class="textcenter">Task Instructions</h2>
  <?php echo $text; ?>
  <div class="textcenter">
    <button id="revealRC" class="collectorButton" type="button">Advance</button>
  </div>
</section>

<?php
// Trial Type default values and input cleanup
if ($question === false) {
    $question = "Should you pay close attention? (Hint: Answer is in the instructions)";
}
if ($correct === false) {
    $correct = "Yes";
}
if ($alternatives === false) {
    $alternatives = array(
        "I don't think so",
        "Nope",
        "I can't read",
    );
}
// if a user only specified one alternative option then we turn it into an array
if (!is_array($alternatives)) {
    $alternatives = array("$alternatives");
}

// pseudo-shuffle into a random order but correct is never first
$answerPos = rand(1, count($alternatives));
shuffle($alternatives);
array_splice($alternatives, $answerPos, 0, $correct);
?>

<div class="readcheck">
  <?= $question ?>
  <ul>
    <?php foreach($alternatives as $i => $answer): ?>
    <li class="MCbutton" <?= $i === $answerPos ? 'id="correct"' : null ?>><?= $answer ?></li>
    <?php endforeach; ?>
  </ul>
</div>

<input type="hidden" name="Fails" id="Fails" value="0">

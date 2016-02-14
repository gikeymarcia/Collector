<div class="alert alert-instructions">Please carefully read the instructions again.</div>
<section class="instructions">
  <h2 class="textcenter">Task Instructions</h2>
  <p>In this study you will be studying some stuff then you will need to recall that stuff.
     After each bunch of stuff there will be some kind of memory task.</p>
  <p>Please pay close attention to the things we are showing you.</p>
  <p>As many paragraphs as you would like can go here.
     Instructions are done.Time for you to move onto the experiment </p>

  <div class="textcenter">
    <button id="revealRC" class="collectorButton" type="button">Advance</button>
  </div>
</section>

<?php
/* SET
 * This ensures that participants read your instructions.
 * Participants must correctly answer something about the procedure
 */
$question = 'Should you pay close attention?  (Hint: Answer is in the instructions)';
$correct = 'Yes';
$alternatives = array(
    "I don't think so",
    "Nope",
    "I can't read",
);

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

<?php
    if (empty($text)) {
        $text = "Listen carefully.";
    }
    $mediaPath = $_PATH->get("Media");
?>

<div class="textcenter">
  <audio src="<?= "{$mediaPath}/{$cue}" ?>" autoplay>
    <p>Your browser does not support the audio element.</p>
  </audio>
</div>

<!-- include form to collect RT and advance page -->
<div><?= $text ?></div>

<div class="textcenter">
  <button class="collectorButton collectorAdvance" id="FormSubmitButton">Next</button>
</div>

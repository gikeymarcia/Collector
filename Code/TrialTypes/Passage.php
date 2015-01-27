<?php
    $compTime = 60;    // time in seconds to use for 'computer' timing
?>
<section>
  <div><?php echo $text; ?></div>
  <div class="precache"><?php echo $cue; ?></div>
  <h3 class="precache textcenter">End of Passage</h3>

  <!-- include form to collect RT and advance page -->
  <div class="precache textright">
    <input class="collector-button collector-button-advance" id="FormSubmitButton" type="submit" value="Next">
  </div>
</section>
<?php
    $compTime = 5;    // time in seconds to use for 'computer' timing
?>
<section class="vcenter">    
  <div><?php echo $text; ?></div>
  
  <!-- show the image -->
  <div class="precache pic">
    <?php echo show($cue); ?>
  </div>
  
  <!-- show the answer -->
  <h2 class="precache textcenter"><?php echo $answer; ?></h2>

  <!-- response and RT form -->
  <div class="precache textcenter">
    <input class="collector-button collector-button-advance" id="FormSubmitButton" type="submit" value="Next">
  </div>
</section>
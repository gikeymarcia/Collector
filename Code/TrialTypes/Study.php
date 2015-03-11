<?php
    $compTime = 5;    // time in seconds to use for 'computer' timing
?>
<section class="vcenter precache">
  <div><?php echo isset($text) ? $text : ""; ?></div>
  <div class='study clearfix'>
    <span class="study-left">  <?php echo $cue; ?>    </span>
    <span class="study-divider">         :            </span>
    <span class="study-right"> <?php echo $answer; ?> </span>
  </div>

  <!-- include form to collect RT and advance page -->
  <div class="textcenter">
    <input class="collector-button collector-button-advance" id="FormSubmitButton" type="submit" value="Next" autofocus="">
  </div>
</section>
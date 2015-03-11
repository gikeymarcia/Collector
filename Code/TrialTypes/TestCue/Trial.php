<?php
    $compTime = 8;    // time in seconds to use for 'computer' timing
?>
<section class="vcenter">
  <!-- optional text -->
  <div><?php echo $text; ?></div>

  <!-- stimulus -->
  <div class="collector-form-element study test-cue precache">
    <span class=study-left><?php echo $cue; ?></span>
    <span class=study-divider>         :            </span>
    <span class=study-right
      ><?php echo substr($answer,0,2); ?><input name=Response type=text value="" autocomplete="off"></span>
  </div>
  
  <div class="collector-form-element textcenter">
    <input class="collector-button collector-button-advance" id=FormSubmitButton type=submit value="Submit">
  </div>
</section>
<?php
/**
 * CopyPic.
 * 
 * Show the correct answer with picture above and force participants to copy the
 * answer before continuing.
 * 
 * @author Paulo Carvalho
*/

// picture trial version of feedback
if (empty($_EXPT->get('text'))) {
    $_EXPT->update('text', 'The correct answer was:');
}
?>

<!-- show the image -->
<div class="pic">
  <?= show($_EXPT->get('cue')) ?>
</div>

<!-- show the answer -->
<div class="textcenter"><h2><?= $_EXPT->get('text') ?></h2></div>
<h1 class="textcenter"><?= show($_EXPT->get('answer')) ?></h1>

<!-- copy the answer -->
<div class="textcenter pad">
   <input name="Response" type="text" value="" class="copybox collectorInput" id="Response"/>
</div>

<!-- include form to collect RT and advance page -->
<div class="textcenter">
  <button class="collectorButton collectorAdvance" id="FormSubmitButton">Next</button>
</div>

<!-- force participants to copy the correct answer before they can continue -->
<script>
$("#FormSubmitButton").click(function(e){
  if(document.getElementById('Response').value === ''){
    e.stopImmediatePropagation();
    alert('You MUST copy the answer to continue');
    return false;
  }
});

// This script measures the width of the image you use
// and changes the trial type width to fit the image size
$("img").load(function(){       // when the image loads
    var imgW = $("img").width();    // save it's width
    $("form").width(imgW);          // resize the form to be same as img
});
</script>

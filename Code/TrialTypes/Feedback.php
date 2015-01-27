<?php
    $compTime = 8;    // time in seconds to use for 'computer' timing
    $firstTrialType = trim(strtolower($currentTrial['Procedure']['Trial Type']));
    if (!isset($text) || $text === '') { $text = 'The correct answer was:'; }
    
    // picture trial version of feedback
    if ($firstTrialType == 'studypic' OR $firstTrialType == 'testpic' OR $firstTrialType == 'mcpic'):
?>
<section class="vcenter">
  <!-- show the image -->
  <div class="precache pic"><?php echo show($cue); ?></div>

  <!-- show the answer -->
  <div class="textcenter"><h2><?php echo $text; ?></h2></div>
  <h1 class="precache textcenter"> <?php echo show($answer); ?></h1>
        
<?php else: // text feeback trials?> 
<section class="vcenter">
  <h2><?php echo $text; ?></h2>
  
<?php
    $cues = explode('|', $cue);
    $answers = explode('|', $answer);
    foreach( $cues as $i => $thisCue ):
        $thisAnswer = $answers[$i];?>
  <div class="study precache">
    <span class="study-left">  <?php echo $thisCue; ?>    </span>
    <span class="study-divider">         :            </span>
    <span class="study-right"> <?php echo $thisAnswer; ?> </span>
  </div>
<?php endforeach; ?>

<?php endif; ?>
    
  <!-- include form to collect RT and advance page -->
  <div class="collector-form-element precache textcenter">
    <input class="collector-button collector-button-advance" id="FormSubmitButton" type="submit" value="Next">
  </div>
</section>
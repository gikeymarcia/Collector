<?php
    $firstTrialType = trim(strtolower($currentTrial['Procedure']['Trial Type']));
    if (!isset($text) || $text === '') {
        $text = 'The correct answer was:';
    }
    
    // picture trial version of feedback
    if ($firstTrialType == 'studypic' OR $firstTrialType == 'testpic' OR $firstTrialType == 'mcpic'):
    ?>
        <!-- show the image -->
        <div class="pic"><?php echo show($cue); ?></div>
        
        <!-- show the answer -->
        <div class="textcenter"><h3><?php echo $text; ?></h3></div>
        <h2 class="textcenter"> <?php echo show($answer); ?></h2>
        
    <?php else: // text feeback trials?>
        <h2 class="textcenter"><?php echo $text; ?></h2>
    <?php
        $cues = explode('|', $cue);
        $answers = explode('|', $answer);
        foreach( $cues as $i => $thisCue ):
            $thisAnswer = $answers[$i];?>
            <div class="study">
                <span class="study-left">  <?php echo $thisCue; ?>    </span>
                <span class="study-divider">           :              </span>
                <span class="study-right"> <?php echo $thisAnswer; ?> </span>
            </div>
        <?php endforeach; ?>

    <?php endif; ?>
    
    <!-- include form to collect RT and advance page -->
    <div class="textcenter">
        <button class="collectorButton collectorAdvance" id="FormSubmitButton" autofocus >Next</button>
    </div>
    
    <style>
        #content {
            width: 90%;
            max-width: 850px;
            min-width: 600px;
        }
    </style>
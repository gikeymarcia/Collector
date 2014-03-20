<?php
    $compTime = 5;                  // time in seconds to use for 'computer' timing
    trialTiming();                  // determines timing and user/computer timing mode
?>
    <!-- show the image -->
    <div class='precache pic'>
        <?php echo show($cue); ?>
    </div>
    <!-- show the target -->
    <h2 class="precache textcenter"><?php echo $target; ?></h2>

    <!-- response and RT form -->
    <div class="precache textcenter">
        <form class="<?php echo $formClass; ?>" <?php echo $postTo; ?> method=post>
            <input class=hidden id=RT name=RT type=text value="" />
            <input class="button button-trial-advance" id=FormSubmitButton type=submit value="Next"" />
        </form>
    </div>
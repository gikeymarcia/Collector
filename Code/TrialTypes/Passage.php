<?php
    $compTime = 60;    // time in seconds to use for 'computer' timing
?>

<div><?php echo $text; ?></div>                   <!-- Show contents of "Text" column above passage -->
<div class="passage"><?php echo $cue; ?></div>    <!-- Show the "Cue" contents and use the "Passage" style defined below -->
<h3 class="textcenter">End of Passage</h3>

<!-- include form to collect RT and advance page -->
<div class="textright">
    <button class="collectorButton collectorAdvance" id="FormSubmitButton">Next</button>
</div>

<style>
    #content { width: 750px; }

    .passage * {
        text-rendering: optimizeLegibility;
        font-size: 1.05em;
        line-height: 2.2em;
    }
    
    #flexBody {
        justify-content: flex-start;
        /*Make the body align flex children to top of page*/
    }
    
</style>
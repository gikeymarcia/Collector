<?php
    /*  PicFeedbackCopy
        Show the correct answer with picture above and force participants to copy the answer before continuing
        Trial type created by Paulo Carvalho
    */

    $compTime = 8;					// time in seconds to use for 'computer' timing
	
    
    // $firstTrialType = trim(strtolower($currentTrial['Procedure']['Trial Type']));
	
	// picture trial version of feedback
    if ($text === '') { $text = 'The correct answer was:'; }
    
?>

    <!-- show the image -->
    <div class="precache pic">
        <?php echo show($cue); ?>
    </div>
    <!-- show the answer -->
	<div class="textcenter"><h2><?php echo $text; ?></h2></div>
	<h1 class="precache textcenter"> <?php echo show($answer); ?></h1>
	
	<!-- copy the answer -->
	<div class="textcenter pad">
	   <input name="Response" type="text" value="" class="copybox collectorInput" autocomplete="off" id="Response"/>
	</div>
	
    <!-- include form to collect RT and advance page -->
    <div class=""precache textcenter"">
        <button class="collectorButton collectorAdvance" id="FormSubmitButton">Next</button>
    </div>
    

    <!-- force participants to copy the correct answer before they can continue -->
    <script>
    $("#FormSubmitButton").click(function(e){
    	if(document.getElementById('Response').value == ''){
    		e.stopImmediatePropagation();
    		alert('You MUST copy the answer to continue');
    		return false;
    	}
    });
    </script>
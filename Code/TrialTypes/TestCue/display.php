<style>
    /*sets size of the trial content window*/
    #content {
        width: 90%;
        max-width: 850px;
    }
    
    /*makes the cue and divider line up with the response box*/
    .study-left, .study-divider {
        margin-top: 4px;
    }
</style>

<!-- optional text -->
<div><?php echo $text; ?></div>

<!-- stimulus -->
<div class="study test-cue">
    <span class="study-left"   ><?php echo $cue; ?></span>
    <span class="study-divider"><?php echo ":";  ?></span>
    <span class="study-right"
      ><?php echo substr($answer,0,2); ?><input name="Response" type="text" value="" autocomplete="off" class="collectorInput"></span>
</div>
  
<div class="textcenter">
    <button class="collectorButton collectorAdvance" id="FormSubmitButton">Submit</button>
</div>
<style>
    #content {
        width: 90%;
        max-width: 800px;
    }
</style>


<div><?php echo isset($text) ? $text : ""; ?></div>
<div class="study alignToInput">
    <span class="study-left"   ><?php echo $cue; ?></span>
    <span class="study-divider"><?php echo ":";  ?></span>
    <div class="study-right"   >
        <input name="Response" type="text" value="" class="copybox collectorInput" autocomplete="off">
    </div>
</div>
  
<div class="textcenter">
    <button class="collectorButton collectorAdvance" id="FormSubmitButton">Submit</button>
</div>
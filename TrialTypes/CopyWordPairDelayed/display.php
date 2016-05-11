<style>
  #content {
    width: 90%;
    max-width: 800px;
  }
</style>

<div><?= $text; ?></div>

<div class="study">
  <span class="study-left">  <?= $cue; ?>  </span>
  <span class="study-divider">     :       </span>
  <span class="study-right">     &nbsp;    </span>
</div>

<div class="study alignToInput">
  <span class="study-left">  <?= $cue; ?>  </span>
  <span class="study-divider">     :       </span>
  <div class="study-right">
    <input name="Response" type="text" value="" class="copybox collectorInput">
  </div>
</div>
  
<div class="textcenter">
  <button class="collectorButton collectorAdvance" id="FormSubmitButton">Submit</button>
</div>


<script>
trialBegin = function() {
    COLLECTOR.timer( 3, function() {
        $(".study-right").first().html( "<?= $answer; ?>" );
    });
});
</script>
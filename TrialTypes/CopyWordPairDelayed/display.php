<style>
  #content {
    width: 90%;
    max-width: 800px;
  }
</style>

<div><?= $_EXPT->get('text'); ?></div>

<div class="study">
  <span class="study-left">  <?= $_EXPT->get('cue'); ?>  </span>
  <span class="study-divider">     :       </span>
  <span class="study-right">     &nbsp;    </span>
</div>

<div class="study alignToInput">
  <span class="study-left">  <?= $_EXPT->get('cue'); ?>  </span>
  <span class="study-divider">     :       </span>
  <div class="study-right">
    <input name="Response" type="text" value="" class="copybox collectorInput">
  </div>
</div>

<div class="textcenter">
  <button class="collectorButton collectorAdvance" id="FormSubmitButton">Submit</button>
</div>


<script>
  Collector.start = function() {
    Collector.setTimeout (3, function() {
      $(".study-right").first().html( "<?= $_EXPT->get('answer'); ?>" );
    });
};
</script>
<div>
  <?= $_EXPT->get('text'); ?>
</div>

<div class="study textcenter">
  <?= Collector\Helpers::show($_EXPT->get('cue')) ?>
</div>

<div class="textcenter">
  <button class="collectorButton collectorAdvance" id="FormSubmitButton">Next</button>
</div>
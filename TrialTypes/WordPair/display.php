<style>
  #content {	width: 90%;  max-width: 800px;  }
</style>

<div><?= $_EXPT->get('text') ?></div>
<div class="study">
  <span class="study-left">  <?= $_EXPT->get('cue') ?>    </span>
  <span class="study-divider">     :        </span>
  <span class="study-right"> <?= $_EXPT->get('answer') ?> </span>
</div>
<!-- include form to collect RT and advance page -->
<div class="textcenter">
  <button class="collectorButton collectorAdvance" 
  id="FormSubmitButton" type="submit" autofocus>Next</button>
</div>

<style>
  #content {	width: 90%;  max-width: 800px;  }
</style>

<div><?= isset($text) ? $text : ''; ?></div>
<div class="study">
  <span class="study-left">  <?= $cue ?>    </span>
  <span class="study-divider">     :        </span>
  <span class="study-right"> <?= $answer ?> </span>
</div>
<!-- include form to collect RT and advance page -->
<div class="textcenter">
  <button class="collectorButton collectorAdvance" 
  id="FormSubmitButton" type="submit" autofocus>Next</button>
</div>
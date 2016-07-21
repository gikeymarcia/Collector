<div>
  <?= $_EXPT->get('text'); ?>
</div>

<div>
  <?= show($_EXPT->get('cue')) ?>
</div>

<div class="textcenter">
  <button class="collectorButton collectorAdvance" id="FormSubmitButton">Next</button>
</div>

<script type="text/javascript">
// This script measures the width of the image you use
// and changes the trial type width to fit the image size
$("img").load(function(){       // when the image loads
    var imgW = $("img").width();    // save it's width
    $("form").width(imgW);          // resize the form to be same as img
});
</script>
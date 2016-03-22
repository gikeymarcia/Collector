<?php
    $text = (empty($text)) ? "Listen carefully." : $text;
    $current = $_PATH->get('Trial Types', 'url').'/DigitSpanCommon';

    $cues = [];
    for ($i = 1; $i < 10; ++$i) {
        $cues[] = "${current}/audio/{$i}.wav";
    }
    $beepFile = "{$current}/audio/beep.wav";
?>

<!-- Additional text to show above the trial -->
<div class="textcenter">
  <p><?= $text ?></p>
</div>

<!-- include form to collect RT and advance page -->
<div class="textcenter hidden" id="inputdiv">
  <input id="inputbox" class="collectorInput collectorResponse forceNumeric textcenter" name="Response" value="">
  <button id="advanceButton" class="collectorButton" type="button">Next</button>
</div>

<!-- Javascript necessary for running the trial -->
<script> 
  /**
   * The paths to the audio files to use for the cues.
   * @type {array}
   */
  var trackcues = <?= json_encode($cues) ?>;
  var beepFile = "<?= $beepFile ?>";

  /* These variables and functions must be set for this trial type to run properly.
   *****************************************************************************/
  /**
   * Unhides the div where the user inputs responses.
   */
  function showInput() 
  {
    $("#inputdiv").show();
    $("input[name=Response]").focus();
  }

  /**
   * Updates the response box to include all of the data and then submits the form.
   * @param  {string}  record The JSON stringified record to submit.
   */
  function fsubmit(record)
  {
    $("input[name=Response]").val(record);
    $("form").submit();
  }
</script>

<script type="text/javascript" src="<?= "{$current}/digitspan.js" ?>"></script>

<script>
  // Pre-load all the possible players for digits
  var digitTracks = [];
  while (trackcues.length > 0) {
    var player = new Track(trackcues.shift());
    digitTracks.push(player);
  }
</script>
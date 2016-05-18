<?php
if (empty($_EXPT->get('text'))) {
    $_EXPT->update('text', "Listen carefully.");
}
$settings = $_EXPT->get('settings');

$direction = (strtolower($settings) === 'reverse' || strtolower($settings) === 'backwards')
           ? -1
           : 1;

$current = $_PATH->get('Trial Types', 'url').'/DigitSpan';

$cues = array();
for ($i = 1; $i < 10; ++$i) {
    $cues[] = "{$current}/audio/{$i}.wav";
}
$beepFile = "{$current}/audio/beep.wav";
?>

<!-- Additional text to show above the trial -->
<div class="textcenter">
  <p><?= $_EXPT->get('text') ?></p>
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

<script>
  /**
   * Scoring method for this task.
   * @param  {string} sequence The presented sequence.
   * @param  {string} response The user's response sequence.
   * @return {number}          Returns 1 for a match, else false.
   */
  Record.score = function(sequence, response) {
    <?php if ($direction === 1): ?>  
    return (sequence === response) ? 1 : 0;

    <?php else: ?>
    return (sequence === response.split("").reverse().join("")) ? 1 : 0;

    <?php endif; ?>
  }
  


  /* Functional area
   ***************************************************************************/
  $(document).ready(function () {
    $("#inputdiv").removeClass('hidden').hide();

    var task = new Task(digitTracks);

    // Rebind the advance button to trigger DigitSpan advance instead of Collector advance    
    $("#advanceButton").click(function () {
      $("#inputdiv").hide();
      task.recordResponse('inputbox');
      task.run();
    });

    // rebind the enter key to click the advance button
    $("#inputbox").keydown(function (e) {
      if (e.which === 13) {
        e.preventDefault();
        $("#advanceButton").click();
      }
    });

    task.run();
  });
</script>

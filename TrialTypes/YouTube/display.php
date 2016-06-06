<?php
/***************************************************************************
 * README
 *
 * How to use:
 * In the Settings column, separate parameters by pipe |
 *   e.g.: submitOnDone = false | preventEarlySubmit = false
 *   or  : submitOnDone = true  | preventEarlySubmit = true
 * Parameters;
 *     submitOnDone
 *         set to false to prevent auto-submission upon video completion
 *     preventEarlySubmit
 *         set to false to disable the submit button until video completion
 **************************************************************************/

// set parameters to append to the URL (https://developers.google.com/youtube/player_parameters)
$parameters = array(
    'autoplay' => 1,           // 1: starts the video immediately
    'modestbranding' => 1,     // 1: removes logo from controls
    'controls' => 0,           // 0: hides the controls entirely
    'rel' => 0,                // 0: does not show related videos
    'showinfo' => 0,           // 0: does not show info like title
    'iv_load_policy' => 3,     // 3: removes annotations
 // 'start'          => see line 71, // start time in seconds
 // 'end'            => see line 71  // end time in seconds
);

// extract submitOnDone and preventEarlySubmit settings from $settings
$submitOnDone = true;
$preventEarlySubmit = true;

if ($_TRIAL->settings->submitOnDone       === 'false') $submitOnDone       = false;
if ($_TRIAL->settings->preventEarlySubmit === 'false') $preventEarlySubmit = false;

// get video ID
$videoId = youtubeUrlCleaner($_EXPT->get('cue'), true);

// get start and end time from stim file columns
if (!isset($startTime) || !is_numeric($startTime)) {
    $startTime = 0;
}
if (!isset($endTime)   || !is_numeric($endTime)) {
    $endTime = 'na';
}

$parameters['start'] = $startTime;
$parameters['end'] = $endTime;

?>

<div class="textcenter">
  <div id="player"></div>
</div>

<!-- include form to collect RT and advance page -->
<div><?= $_EXPT->get('text') ?></div>
<div class="textcenter">
  <button class="collectorButton collectorAdvance" id="FormSubmitButton">Next</button>
</div>

<script>
  var player;
  var submitOnDone       = <?= $submitOnDone       ? 'true' : 'false' ?>;
  var preventEarlySubmit = <?= $preventEarlySubmit ? 'true' : 'false' ?>;

  if (preventEarlySubmit) {
      $("#FormSubmitButton").addClass("invisible");
  }

  function onYouTubeIframeAPIReady() {
    player = new YT.Player("player", {
      height  : "315",
      width   : "420",
      videoId : "<?= $videoId ?>",
      events  : {
        "onStateChange" : onPlayerStateChange
      },
      playerVars : {
        <?php foreach ($parameters as $parameter => $value): ?>
        "<?= $parameter ?>" : "<?= $value ?>", <?= "\r\n" ?>
        <?php endforeach; ?>
      }
    });
  }

  function onPlayerStateChange(e) {
    if (e.data === YT.PlayerState.ENDED) {
      if (submitOnDone) {
        $("#FormSubmitButton").click();
      }
      if (preventEarlySubmit) {
        $("#FormSubmitButton").removeClass("invisible");
      }
    }
  }
</script>
<script src="https://www.youtube.com/iframe_api"></script>

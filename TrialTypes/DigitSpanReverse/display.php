<?php
    include dirname(__DIR__)."/DigitSpanCommon/main.php";
    $text = (empty($text)) ? "Listen carefully." : $text;
?>

<script>
  /**
   * Determines if the presented sequence matches the user response.
   * @param  {string} sequence The presented sequence.
   * @param  {string} response The user's response sequence.
   * @return {number}          Returns 1 for a match, else false.
   */
  Record.score = function(sequence, response) {
    return (sequence === response.split("").reverse().join("")) ? 1 : 0;
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
      if (e.which == 13) {
        e.preventDefault();
        $("#advanceButton").click();
      }
    });

    task.run();
  });
</script>
/**
 * Track object.
 * Constructs a new track with the given source.
 * @param {string} source The path to the file to play.
 */
function Track(source)
{
  var self = this;

  /**
   * Creates the HTML5 audio element with the given source.
   * @param  {string}  source The path to the audio file for the element.
   * @return {element}        The created HTML5 audio element.
   */
  this.player = (function(source) {
    var audio = document.createElement("audio");
    audio.src = source;
    document.body.appendChild(audio);

    return audio;
  })(source);

  /**
   * Plays the Track.
   */
  this.play = function () {
    self.player.play();
  }
}

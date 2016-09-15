/* Big picture objects
 ***************************************************************************/
/**
 * Task object.
 * Constructs the object responsible for running the whole task.
 */
function Task(tracks)
{
  var self = this;
  this.record  = new Record();
  this.trial   = null;
  this.level   = null;
  this.attempt = null;
  this.beepTrack = new Track(beepFile);
  this.cueTracks = tracks;

  /**
   * Creates the trial for the next level and starts it.
   */
  this.playLevel = function () {
    this.trial = new Trial(self.level, self.cueTracks);
    this.trial.play();
  }

  /**
   * Records the response value from the element at the given ID.
   * @param  {string} id The id of input box with the response in it.
   */
  this.recordResponse = function (id) {
    var resp = document.getElementById(id);
    seq = this.trial.sequence.join('');
    this.record.record(seq, resp.value.replace(/[^0-9]/, ''));
    resp.value = '';
  }

  /**
   * Determines whether the task is complete or not.
   * @return {string} 'complete' if the last level has a score of 0 and both
   *                  trials were run for it, else 'incomplete'.
   */
  this.determineStatus = function () {
    if (this.level === null) {
      this.level = 2;
      this.attempt = 1;
    } else {
      ++this.attempt;
    }

    if (this.attempt > 2) {
      var levelScore = this.record.scores.slice(this.record.scores.length - 2);
      levelScore = levelScore[0] + levelScore[1];

      if (levelScore === 0) {
        return "complete";
      }

      ++this.level;
      this.attempt = 1;

      return "incomplete";
    }
  }

  /**
   * Advances the task to the next track or shows the input box for response.
   */
  this.advance = function () {
    if (self.trial.currentTrack < self.trial.sequence.length - 1) {
      setTimeout(self.trial.playNext, 1000-self.trial.tracks[self.trial.currentTrack].duration);
    } else {
      Record.setRtBase();
      self.beepTrack.play();
      showInput();
    }
  }

  // prepare the cueTracks with the advance function
  for (var i = 0, ct = this.cueTracks.length; i < ct; ++i){
    this.cueTracks[i].player.addEventListener("ended", self.advance);
  }

  this.run = function () {
    if (this.determineStatus() === "complete") {
      fsubmit(this.record.print());
      return;
    } else {
      this.playLevel();
    }
  }
}




/**
 * Record object.
 * Constructs the object used for recording data.
 */
function Record()
{
  this.sequences = [];
  this.responses = [];
  this.scores = [];
  this.rts = [];

  /**
   * Records a Trial's sequence, response, score (calculated0, and reaction
   * time (calculated).
   * @param  {string} sequence The sequence of digits presented.
   * @param  {string} response The sequence of digits given as a response.
   */
  this.record = function(sequence, response) {
    this.sequences.push(sequence);
    this.responses.push(response);
    this.scores.push(Record.score(sequence, response));
    this.rts.push(this.getRt());
  }

  /**
   * Calculates the reaction time, defined as milliseconds between the last
   * call to Record.setRtBase and the call of this function.
   * @return {number} The number of milliseconds since Record.setRtBase was last called.
   */
  this.getRt = function () {
    return Date.now() - Record.rtBase;
  }

  /**
   * Prints all sequences, responses, scores, and rts as a JSON object
   * organized by presentation order.
   * @return {object} JSON object of all recorded data organized by trial.
   */
  this.print = function () {
    var output = [];
    for (var i = 0, ct = this.sequences.length; i < ct; ++i) {
      output.push({
        'sequence': this.sequences[i],
        'response': this.responses[i],
        'score': this.scores[i],
        'rt': this.rts[i]
      });
    }

    return JSON.stringify(output);
  }
}

/**
 * Record property to store the start time for calculating reaction times.
 * @type {number}
 */
Record.rtBase = null;


/**
 * Sets the Record.rtBase property to the current time in milliseconds from Unix Epoch.
 */
Record.setRtBase = function () {
  Record.rtBase = Date.now();
}

/**
 * Determines if the presented sequence matches the user response.
 * @param  {string} sequence The presented sequence.
 * @param  {string} response The user's response sequence.
 * @return {number}          Returns 1 for a match, else false.
 */
Record.score = function(sequence, response) {
  return (sequence === response) ? 1 : 0;
}


/* Trial specific objects
 ***************************************************************************/
/**
 * Trial object.
 * Constructs a new trial for the given level using the given set of tracks.
 * @param {number} level  The level of the trial.
 * @param {array}  tracks The full 1-9 array of digit tracks (0-indexed).
 */
function Trial(level, tracks)
{
  var self = this;

  /**
   * The level of the Trial, i.e. the number of digits in it.
   * @type {number}
   */
  this.level = level;

  /**
   * The random sequence of digits in the trial.
   * @type {array}
   */
  this.sequence = [];

  /**
   * The playlist of recorded tracks for the sequence.
   * @type {array}
   */
  this.tracks = [];

  /**
   * The track counter.
   * @type {number}
   */
  this.currentTrack = 0;

  /**
   * Starts the current track.
   */
  this.play = function () {
    self.tracks[self.currentTrack].play();
  }

  /**
   * Increments the track counter and starts track.
   */
  this.playNext = function () {
    ++self.currentTrack;
    self.play();
  }

  // build the sequence and playlist
  var digit = 0;

  for (var i = 0; i < this.level; ++i) {
    do {
      digit = 1 + Math.floor(Math.random() * (9 - 1 + 1));
    } while (digit === this.sequence[0]);

    this.sequence.unshift(digit);
    this.tracks.unshift(tracks[digit - 1]);
  }
}

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

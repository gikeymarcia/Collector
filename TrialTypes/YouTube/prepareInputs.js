// get the answer for this trial
var answer = Trial.get_input('answer')[0];

// figure out what the first two characters are:
var stem = answer.substring(0, 2);

// set the {answer stem} to the first two characters
Trial.add_input('answer stem', stem);




var url = Trial.get_input('Cue')[0];
var id = url.substring(32,43);
Trial.add_input('videoID', id);
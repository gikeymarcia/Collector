// get the answer for this trial
var answer = Trial.get_input('answer')[0];

// figure out what the first two characters are:
var stem = answer.substring(0, 2);

// set the {answer stem} to the first two characters
Trial.add_input('answer stem', stem);

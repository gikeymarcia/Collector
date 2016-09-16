// read 'Text' column for this trial
var feedback_text = Trial.get_input("text");

// If 'Text' doesn't exist (or is empty) use default
if (feedback_text == null || feedback_text == "") {
    Trial.add_input('feedback text', 'The correct answer was:');
} else {
    // Otherwise, use the contents of 'Text'
    Trial.add_input('feedback text', feedback_text);
}

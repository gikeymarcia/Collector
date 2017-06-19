var text = Trial.get_input('text');

if (text == null || text == '') {
    Trial.add_input('feedback message', 'The correct answer was:');
} else {
    Trial.add_input('feedback message', text);
}
<!-- Warn if try to edit with GUI !-->
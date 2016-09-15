var text = Trial.get_input('text');

if (text == null || text == "") {
    Trial.add_input('default message', 'Listen Carefully...');
} else {
    Trial.add_input('default message', text);
}

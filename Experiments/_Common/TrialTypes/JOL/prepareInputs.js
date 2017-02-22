// read the text column
var text = Trial.get_input('Text');

// separate into a list after breaking up by |
var all = text.split('|');

// get the first element after splitting
var main = all.shift().trim();

// set as {main text}
Trial.add_input('main text', main);

// calculate how to show descriptors
var descr = '';
if (main.length > 0) {
    for (var each in main) {
        descr = '<p>' + each + '<p>';
    }
}
// add {descriptors}
Trial.add_input('descriptors', descr);

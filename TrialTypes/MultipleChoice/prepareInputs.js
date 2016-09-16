// Read text value and split it into a list by | character
var text = Trial.get_input('Text').split('|');

// take the first item of the list as the {main text}
var main = text.shift().trim();
Trial.add_input('main text', main);

// calculate options, add as {options}
var ops = '';
if (main.length > 0) {
    for (var option in text) {
        option = option.trim();
        ops = ops + '<label>'
                  +   '<div><input type="radio" name="Response" ' + 'value="' + option + '" /></div>'
                  +   '<div>' + option + '</div>'
                  +'</label>';
    }
}
Trial.add_input('options', ops);

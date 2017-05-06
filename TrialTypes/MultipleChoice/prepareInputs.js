/* 
	* Transferring information to the html file with the data from excel Procedure/Stimuli flies
	* Using Trial object add_input function to transfer parameter values
*/

var text = Trial.get_input('Text').split('|');

var main = text.shift().trim();
Trial.add_input('main text', main);

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

/* 
	* Transferring information to the html file with the data from excel Procedure/Stimuli flies
	* Using Trial object add_input function to transfer parameter values
*/

var text = Trial.get_input('text');

if (text == null || text == '') {
    Trial.add_input('feedback message', 'The correct answer was:');
} else {
    Trial.add_input('feedback message', text);
}

/* 
	* Transferring information to the html file with the data from excel Procedure/Stimuli flies
	* Using Trial object add_input function to transfer parameter values
	* Options parameter in Procedure/Stimuli file determines Likert scale values
*/

var labels = Trial.get_procedure('Labels');
var label = labels.split('|');
var label_html = "";

for (var i=0; i<label.length; ++i) {
    label_html += "<div class='likertLabel'>" + label[i] + "</div>";
}
Trial.add_input('label', label_html);


var settings = Trial.get_procedure('Options');
var setting = settings.split('::');

var likert_start = parseFloat(setting[0].substr(-1));
var likert_end = parseFloat(setting[1]);


if (likert_start == null) {
	likert_start = 1.0;
}
if (likert_end == null) {
	likert_end = 7.0;
}

var extra_settings = settings.split('#');
if (extra_settings[1] == null) {
	var step_size = 1.0;
}
else {
	var step_size = parseFloat(extra_settings[1]);
}


var options_html = "<div id='main'>" + "<div id='table'>" + "<div id='row'>";
for (i = likert_start; i <= likert_end; i+=step_size) {
	options_html += "<div class='cell'>" + "<label>" + i + "<input type=\"radio\" name=\"Response\"" + "</label></div>";
}
options_html += "</div>" + "</div>" + "</div>";
Trial.add_input('option', options_html);


var label_width = (Math.floor(1000 / Math.max(1, label.length)) / 10);
Trial.add_input('label_width', label_width.toString());
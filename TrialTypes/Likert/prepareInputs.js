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

var options_html = "<div class='likertOption'>";
for (i = likert_start; i <= likert_end; i+=step_size) {
	options_html += "<label>" + i + "<input type=\"radio\" name=\"Response\"" + "</label>";
}
options_html += "</div>";
Trial.add_input('option', options_html);


var total_options = (((likert_end+1)-likert_start)/step_size);
var label_width = (Math.floor(1000 / Math.max(1, label.length)) / 10);
var option_width = (Math.floor(1000 / total_options) / 10);
var options_width = 90 + total_options*1.4;

Trial.add_input('label_width', label_width.toString());
Trial.add_input('option_width', option_width.toString());
Trial.add_input('options_width', options_width.toString());


var option_pad = 92;
var reduce = 0;
var j = 0;
for (i = likert_start;i < likert_end/step_size ; i+=2) {
	reduce += Math.pow((i*j),j) +1;
	j+= 0.8;
}
option_pad -= reduce;
if (option_pad < 0) {
	option_pad = 50;
}

Trial.add_input('option_pad', option_pad.toString());


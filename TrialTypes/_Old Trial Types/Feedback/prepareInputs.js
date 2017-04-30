var main_trial_type = Trial.get_input('trial type');


var pic_position = main_trial_type.indexOf('pic');
var pic_position_main = main_trial_type.length - 3;

if (pic_position == pic_position_main) {
	var cue = "<div class='pic'>" + Trial.get_input('cue') + "</div>";
	var text = "<div class='textcenter'><h3>" +  "The correct answer was: " + Trial.get_input('text') + "</h3></div>";
	var answer = "<h2 class='textcenter'>" + Trial.get_input('answer') + "</h2>";
	var studies = cue + text + answer;
}
else {
	var cues = Trial.get_input('cue').split('|');
	var answers = Trial.get_input('answer').split('|');
	
	var studies = "<h2 class='textcenter'>" + Trial.get_input('text') + "</h2>";
	studies += "<div class='study'>";
	for (var i = 0, n = cues.length; i < n; i++) {
		studies += "<span class='study-left'>" + cues[i] + "</span>";
    	studies += "<span class='study-divider'>" + ":" + "</span>";
    	studies += "<span class='study-right'>" + answers[i] + "</span>";
	}
	studies += "</div>";
}

Trial.add_input('studies', studies);


// time in seconds to use for 'default' timing
// $defaultMaxTime = 8;
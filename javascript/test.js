/*
#### JQuery notes
	if selecting class="Name"
		$(".Name")
	if selecting id="ThisOne"
		$("#ThisOne")
	if selecting by tag (e.g., all <p> or all <li>)
		$("p") || $("li")

####
*/

var timer = 0;
var interval = 10;				// ## SET ##, The smaller the interval the more CPU power needed.  # = timing accuracy in ms
var keypress = 0;
var trialTime = $("#Time").html();


window.onload = function() {
	$(".PreCache").removeClass("PreCache");
	setInterval(addtime,interval);
}

// hide SubmitButton if the form name is 'ComputerTimed'
if($("form").attr('name') == 'ComputerTiming') {
	$("#FormSubmitButton").addClass('Hidden');
}


// on pageload focus onto item with class="Textbox"
$("document").ready( function(){
	// $("#FormSubmitButton").focus();			// turned off auto-focus onto formsubmitbuttons because it lead to annoying behavior
	$(".Textbox:first").focus();
	$("textarea").focus();
});


function addtime() {
	timer = timer + interval;
	// update RT field with timer value
	$(".RT").attr("value",timer);
	if(keypress < 1) {
		$(".RTkey").attr("value",timer);
	}
	// submit form if time is up
	if (timer >= (trialTime*1000)) {
		$("form").submit();
	}
}


// Disable enter key for textboxes with class "Textbox" inside of forms named "ComputerTiming"
$(".Textbox").bind("keypress",function(e){
	if( $('form').attr('name') == 'ComputerTiming') {
		if(e.keyCode == 13) return false;
	}
	if(e.keyCode == 13) return true;
});


// disable enter key for FreeRecall (textarea) with class="ComputerTiming"
$(".ComputerTiming").bind("keypress",function(e){
	if( $('form').attr('name') == 'ComputerTiming') {
		if(e.keyCode == 13) return false;
	}
	if(e.keyCode == 13) return true;
});

// updates last keypress value each time a key is pressed
$(".Textbox").keypress(function(){
	keypress++;
	$(".RTlast").attr("value",timer);
});

// updates last keypress value each time a key is pressed
$("textarea").keypress(function(){
	keypress++;
	$(".RTlast").attr("value",timer);
});

// updates the response value when a MC button is pressed; then submits the form
$(".TestMC").click(function(){
	var clicked = $(this).html();
	$(".Textbox").attr("value",clicked);
	$("form").submit();
});

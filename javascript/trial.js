/* Collector
	A program for running experiments on the web
	Copyright 2012-2013 Mikey Garcia & Nate Kornell
*/

/*
#### JQuery notes
	if selecting class="Name"
		$(".Name")
	if selecting id="ThisOne"
		$("#ThisOne")
	if selecting by tag (e.g., all <p> or all <li>)
		$("p") || $("li")
*/

	var timer		= 0;
	var interval	= 10;				// ## SET ##, The smaller the interval the more CPU power needed.  # = timing accuracy in ms
	var keypress	= 0;
	var trialTime	= $("#Time").html();

	// on DOM ready reset timer
	$("document").ready( function(){
		timer		= 0;
	});
	
	
	// on pageload reset timer, show pre-cached, start timer, focus on textboxes
	window.onload = function() {
		if(trialTime != 0) {
			$(".PreCache").addClass("DuringTrial");			// add class that does nothing (but lets us know what used to be hidden)
			$(".PreCache").removeClass("PreCache");			// remove class that hides the content
		}
		setInterval(addtime,interval);
		$(".Textbox:first").focus();
		$("textarea").focus();
	};
	
	
	// timer function
	function addtime() {
		timer = timer + interval;
		if (timer >= (trialTime*1000)) {			// submit form if time is up
			$(".DuringTrial").addClass("PreCache");	// hide content
			$(".RT").attr("value",timer);			// update RT field with timer value
			timer = 0;
			$("form").submit();
		}
	}
	
	
	// intercept FormSubmitButton click
	$("#FormSubmitButton").click(function(){
		$(".DuringTrial").addClass("PreCache");		// hide content
		$(".RT").attr("value",timer);				// put RT into hidden field
		$("form").submit();							// submit values to server
	});
	
	
	// Disable enter key inside class "Textbox" when form is named "ComputerTiming"
	$(".Textbox").bind("keypress",function(e){
		if( $("form").attr("name") == "ComputerTiming") {
			if(e.keyCode == 13) return false;
		}
		if(e.keyCode == 13) return true;
	});
	
	
	// updates last keypress value each time a key is pressed (for textboxes)
	$(".Textbox").keypress(function(){
		keypress++;
		if(keypress == 1) {
			$(".RTkey").attr("value",timer);
		}
		$(".RTlast").attr("value",timer);
	});
	
	
	// updates last keypress value each time a key is pressed (for textareas)
	$("textarea").keypress(function(){
		keypress++;
		if(keypress == 1) {
			$(".RTkey").attr("value",timer);
		}
		$(".RTlast").attr("value",timer);
	});
	
	
	// updates the response value when a MC button is pressed
	$(".TestMC").click(function(){
		var clicked = $(this).html();
		$(".Textbox").attr("value",clicked);
		
		if(keypress == 0) {								// setting first and/or last keypress times
			$(".RTkey").attr("value",timer);
			keypress++;
			$(".RTlast").attr("value",timer);
		}
		else {
			$(".RTlast").attr("value",timer);
		}
		
		if( $("form").attr("name") == "UserTiming") {
			$(".DuringTrial").addClass("PreCache");		// hide content
			$(".RT").attr("value",timer);
			$("form").submit();
		}
		$(".TestMC").css("background","#566673");		// remove highlighting from all buttons
		$(this).css("background","#00ac86");			// add highlighting to clicked button
	});
	
	// Prevent the backspace key from navigating back.
	// MAGIC!!! found on stackoverflow (http://stackoverflow.com/questions/1495219/how-can-i-prevent-the-backspace-key-from-navigating-back)
	$(document).unbind("keydown").bind("keydown", function (event) {
	    var doPrevent = false;
	    if (event.keyCode === 8) {
	        var d = event.srcElement || event.target;
	        if ((d.tagName.toUpperCase() === "INPUT" && d.type.toUpperCase() === "TEXT") 
	             || d.tagName.toUpperCase() === "TEXTAREA") {
	            doPrevent = d.readOnly || d.disabled;
	        }
	        else {
	            doPrevent = true;
	        }
	    }
	
	    if (doPrevent) {
	        event.preventDefault();
	    }
	});
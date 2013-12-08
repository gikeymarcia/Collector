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
	var minTime		= $("#minTime").html();
	var MCpickColor = "#00ac86";
	

	// do when structure (HTML) but not necessarily all content has loaded
	$("document").ready( function(){
		timer		= 0;									// reset the timer
		if (minTime > 0) {									// if a mimnum time is set
			$("#FormSubmitButton").css("display","none");		// hide submit button
			$(".Textbox").addClass("noEnter");					// disable enter from submitting the trial
		}
		if( $("form").hasClass("ComputerTiming")) {			// if trial is ComputerTiming
			$(".Textbox").addClass("noEnter");					// disable enter from submitting the trial
		}
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
		if (timer >= (minTime*1000)) {						// when minimum time is reached
			$("#FormSubmitButton").css("display","inline");		// show 'Done' / 'Submit' button
			$(".Textbox").removeClass("noEnter");				// allow enter to progress the trial
		}
		if (timer >= (trialTime*1000)) {					// if time is up
			$(".DuringTrial").addClass("PreCache");				// hide content
			$(".RT").attr("value",timer);						// update RT field with timer value
			timer = 0;											// reset timer
			$("form").submit();									// submit form
		}
	}
	
	
	// intercept FormSubmitButton click
	$("#FormSubmitButton").click(function(){		// when 'Done' / 'Submit' is pressed
		$(".DuringTrial").addClass("PreCache");			// hide content
		$(".RT").attr("value",timer);					// update RT field with timer value
		$("form").submit();								// submit form
	});
	
	
	// keypress related functionality (for textboxes)
	$("input").bind("keypress",function(e){
		
		if(e.keyCode == 13) {							// if enter is pressed
			if($("input").hasClass("noEnter")) {			// disable for all 'noEnter' inputs
					return false;
				}
		}
		else {
			// monitor and log first/last keypress
			keypress++;									// increment counter
			if(keypress == 1) {							// on first keypress
				$(".RTkey").attr("value",timer);			// set 'RTkey' time
			}
			$(".RTlast").attr("value",timer);			// update last keypress time
		}

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
			originalColor = $(".TestMC").css("background");
			$(".RTkey").attr("value",timer);
			keypress++;
			$(".RTlast").attr("value",timer);
		}
		else {
			$(".RTlast").attr("value",timer);
		}
		
		if( $("form").hasClass("UserTiming")) {			// if 'user' timing
			$(".DuringTrial").addClass("PreCache");			// hide content
			$(".RT").attr("value",timer);					// update RT field with timer value
			$("form").submit();								// submit form
		}
		$(".TestMC").css("background",originalColor);	// remove highlighting from all buttons
		$(this).css("background", MCpickColor);			// add highlighting to clicked button
	});
	
	
	// Prevent the backspace key from navigating back.
	// MAGIC!!! found on stackoverflow (http://stackoverflow.com/questions/1495219/how-can-i-prevent-the-backspace-key-from-navigating-back)
	$(document).unbind('keydown').bind('keydown', function (event) {
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
/**
 *  Code below is from jsCode.js
 */
/* Collector
	A program for running experiments on the web
	Copyright 2012-2013 Mikey Garcia & Nate Kornell
*/


var timer		= 0;
var interval	= 100;				// ## SET ##, The smaller the interval the more CPU power needed.  # = timing accuracy in ms
var keypress	= 0;
var trialTime	= $("#Time").html();


// do these things when everything has loaded
window.onload = function () {
	setInterval(addtime,interval);
};


function addtime(){
	timer = timer + interval;
	$("#RT").prop("value",timer);
}










/**
 * 	Code below is from trial.js
 */

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
	var interval	= 10;				// ## SET ##, The smaller the interval the more CPU power needed.  # = timing accuracy for ending trials in ms
	var keypress	= 0;
	var trialTime	= $("#Time").html();
	var minTime		= $("#minTime").html();
	var MCpickColor = "#00ac86";

	var startTime	= Date.now();						// take a snapshot of the current system time
	var currentTime	= Date.now();
	var last		= Date.now();
	var showTimer	= false;								// ## SET ##, change to `true` or `false` without tickmarks



	// do when structure (HTML) but not necessarily all content has loaded
	$("document").ready( function(){
		timer		= 0;									// reset the timer
		if (minTime > 0) {									// if a mimnum time is set
			$("#FormSubmitButton").addClass("invisible");	// hide submit button
			$("input:text").addClass("noEnter");			// disable enter from submitting the trial
		}

		if( $("form").hasClass("ComputerTiming")) {			// if trial is ComputerTiming
			$("#FormSubmitButton").addClass("hidden");		// remove submit button
			$("input:text").addClass("noEnter");			// disable enter from submitting the trial
		}
	});


	// on pageload reset timer, show pre-cached, start timer, focus on textboxes
	window.onload = function() {
		if(trialTime != 0) {
			$(".precache").addClass("DuringTrial");			// add class that does nothing (but lets us know what used to be hidden)
			$(".precache").removeClass("precache");			// remove class that hides the content
		}
		startTime	= Date.now();							// take a snapshot of the current system time
		window.tickTock = setInterval(getTime,interval);	// start the timer
		$('input[type=text]:visible:first').focus();
	};


	// unhide counter if you've set showTimer == true
	if(showTimer == true) {
		$("#showTimer").removeClass("Hidden");
		$("#start").html(startTime);
	}


	// timer function
	function getTime() {
		currentTime = Date.now();
		timer = currentTime - startTime;

		if (timer > (minTime*1000)) {							// when minimum time is reached
			$("#FormSubmitButton").removeClass("invisible");		// show 'Done' / 'Submit' button
			$("input:text").removeClass("noEnter");				// allow enter to progress the trial
		}

		if (timer >= (trialTime*1000)) {						// if time is up
			$(".DuringTrial").addClass("precache");				// hide content
			$("#RT").prop("value",timer);						// update RT field with timer value
			timer = 0;											// reset timer
			$("form").submit();									// submit form
			clearInterval(tickTock);							// stop timer from running again
		}

		// DEBUG function that updates shown timer ~ every 100ms
		if (showTimer == true) {
			if( (currentTime - last) > 100) {
				last = currentTime;
				$("#current").html(currentTime);
				$("#dif").html(timer);
			}
		}
	}


	// intercept FormSubmitButton click
	$("#FormSubmitButton").click(function(){			// when 'Done' / 'Submit' is pressed
		getTime();										// get ms accurate time
		clearInterval(tickTock);						// stop timer from running again
		$(".DuringTrial").addClass("precache");			// hide content
		$("#RT").prop("value",timer);					// update RT field with timer value
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
			getTime();									// get ms accurate time
			keypress++;									// increment counter
			if(keypress == 1) {							// on first keypress
				$("#RTkey").prop("value",timer);			// set 'RTkey' time
			}
			$("#RTlast").prop("value",timer);			// update last keypress time
		}

	});


	// updates last keypress value each time a key is pressed (for textareas)
	$("textarea").keypress(function(){
		getTime();									// get ms accurate time
		keypress++;
		if(keypress == 1) {
			$("#RTkey").prop("value",timer);
		}
		$("#RTlast").prop("value",timer);
	});


	// updates the response value when a MC button is pressed
	$(".TestMC").click(function(){
		getTime();										// get ms accurate time
		var clicked = $(this).html();
		$("#Response").prop("value",clicked);

		if(keypress == 0) {								// set first keypress times
			originalColor = $(".TestMC").css("background");
			$("#RTkey").prop("value",timer);
			keypress++;
		}
		$("#RTlast").prop("value",timer);				// set last keypress time


		if( $("form").hasClass("UserTiming")) {			// if 'user' timing
			getTime();								    // get ms accurate time
			clearInterval(tickTock);					// stop timer from running again
			$(".DuringTrial").addClass("precache");		// hide content
			$("#RT").prop("value",timer);				// update RT field with timer value
			$("form").submit();							// submit form
		}
		$(".TestMC").removeClass("button-active");		// remove highlighting from all buttons
		$(this).addClass("button-active");				// add highlighting to clicked button
	});





/**
 *	code below is from tetris.js
 */

	var timer = $("#Time").html();
	var interval = 1000;
	$(".countdown").html(timer);

	setInterval(countdown,interval);

	function countdown() {
		timer = timer-1;
		if(timer >= 0) {
			$(".countdown").html(timer);
		}
		if(timer == 5) {
			// hide clock and show get ready prompt on last 5 secs
			$(".stepout-clock").hide();
			$(".tetris-wrap")
				.addClass("action-bg")
				.css("margin-top", "30px")
				.html("<h1>Get ready to continue in ... </h1> <h1 class=countdown>5</h1>");
		}
		if(timer <= 0) {
			$('#loadingForm').submit();
		}
	}

	// reveal on clicking start
	$("#reveal").click(function() {
	    $("#reveal").hide();
	    $(".tetris").slideDown(400, function() {
	        var off = $(".tetris").offset();
	        $("html, body").animate({scrollTop: off.top}, 500);
	    });
	});
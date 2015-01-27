/**
 *	JS for Collector
 *  ver. 1.0.0
 *  by Adam Blake - adamblake@g.ucla.edu
 *
 * 	Collector is program for running experiments on the web
 * 	Copyright 2012-2013 Mikey Garcia & Nate Kornell
 *
 *
 * 	Notes on this file:
 *
 *  This Javascript uses a technique called DOM-based Routing. You can read more about how it works
 *  here: http://viget.com/inspire/extending-paul-irishs-comprehensive-dom-ready-execution
 *
 * 	Here are the basics:
 * 	1. All JS code is included in one file in an object-oriented framework
 *  2. The HTML <body> "data-controller" and "data-action" attributes map to keys in the object literal
 *  3. On $(window).load, we use functions to run the appropriate bits of code. In order they are:
 * 		 i.  Common -> init
 * 	    ii.	 [data-controller] -> init
 * 	   iii.  [data-controller] -> [data-action]
 *
 *  Note: step 3.ii. will only occur if a data-controller is specified in the HTML and 3.iii. will only
 * 		  occur if both a data-controller AND data-action have been specified
 *
 * 	Using this method, while a bit complex, allows us to include page-specific JS and shared JS in a
 *  single file. This file will be cached on the first visit to the site, so in the end we reduce the
 *  load times of subsequent pages, and we do it in a nicely organized file.
 *
 *  Happy coding! --Adam
 *
 */

var COLLECTOR = {

	/**
	 *	Sets starting time to a property we can access anywhere in the namespace
	 */
	startTime: Date.now(),

	/**
	 *	Timer function
	 *
	 *  This countdown timer is for any case where you would want to timeout, like in timed trials
 	 *  For other cases where you just want to get the elapsed time, use the "getTime" function
 	 *
 	 *  @param {Int} 		timeUp: the amount of time the timer runs for
 	 *  @param {Function}	callback: the function you want to run when the timer stops
 	 * 	@param {Object}		show (optional): if included, the timer will send it's current value to this element
 	 *
 	 *
 	 *  Example usage:
 	 * 		COLLECTOR.timer(2, function() {
	 *			$("form").submit();
	 *		}, $("#countdown"));
 	 */
	timer: function (timeUp, callback, show) {
		// waitPercent is the percentage of timeRemaining that will be used for each setTimeout
		// waitPercent should be greater than 0, but less than 1
		// cap is the lowest number of ms allowed per interval.
		// for HTML5 browsers, 4ms is the setTimeout minimum, so the cap should be at least 4.
		// for both of these values, increasing them lowers both accuracy and processing requirements
		var waitPercent = .5,
			cap         = 4,
			goal        = Date.now() + timeUp*1000,
			lastWait = cap*2,
			lastAim = cap*2/waitPercent;

		function instance() {
			var timeRemaining = goal - Date.now(),
				timeFormatted;

			// stop timer at the allotted time
  			if (timeRemaining <= cap) {
				while(true) {
					timeRemaining = goal - Date.now();
					if (timeRemaining <= 0) {
						return callback();
					}
				}
  			}

			if (show) {
				timeFormatted = Math.round( timeRemaining/100 )/10;
				if (Math.round(timeFormatted) == timeFormatted) { timeFormatted += '.0'; }
  				if (show.is('input')) {
  					show.val( timeFormatted );
  				} else {
  					show.html( timeFormatted );
                }
				timeRemaining = Math.min( 20, timeRemaining );
  			} else {
                timeRemaining = Math.min(timeRemaining, 10000);
            }

			if (timeRemaining <= lastWait) {
				timeRemaining = cap;
			} else if (timeRemaining <= lastAim) {
				timeRemaining = timeRemaining - Math.floor(cap*1.5);
			} else {
				timeRemaining *= waitPercent;
			}
			timeRemaining = Math.max (cap, Math.floor(timeRemaining));

			// run the timer again, using a percentage of the time remaining
			setTimeout(function() { instance(); }, timeRemaining );
		}

  		// start the timer
		instance();
	},

	getRT: function() {
		var currentTime = Date.now();
		return (currentTime - COLLECTOR.startTime);
	},

	common: {
		init: function() {
			var startTime = COLLECTOR.startTime;

			// these happen immediately on load
			$(':input:enabled:visible:first').focus();			// focus cursor on first input
			$("#loadingForm").submit();							// submit form to advance page

			// intercept FormSubmitButton click
			$("#FormSubmitButton").click(function(){			// when 'Done' / 'Submit' is pressed
				$("#RT").val( COLLECTOR.getRT() );				// record RT
                
                if (typeof COLLECTOR.submitFunction === 'function')
                {
                    COLLECTOR.submitFunction();
                }
                
				$(".DuringTrial").addClass("precache");			// hide content
				$("form").submit();								// submit form
			});

			// allows for the collapsing of readable() outputs
			$(".collapsibleTitle").click(function() {
				$(this).parent().children().not(".collapsibleTitle").toggle(350);
			});
			// starts them collapsed
			$(document).ready(function() {
                $(".collapsibleTitle").parent().children().not(".collapsibleTitle").toggle(350);
                window.scrollTo(0, 0);
            });
			
			// forceNumeric
			$(".forceNumeric").forceNumeric();
		}
	},

	instructions: {
		init: function() {
			var fails = 0;

			// reveal readcheck questions
			$("#revealRC").click(function() {
			    $("#revealRC").hide();
				$(".readcheck").slideDown(400, function() {
					var off = $(".readcheck").offset();
					$("html, body").animate({scrollTop: off.top}, 500);
				});
			});

			// submit the form when they click the item with id="correct"
			// emulates clicking the FormSubmitButton which runs the intercept code in common
			$("#correct").click(function(){
				$("#FormSubmitButton").click();
			});

			// when they click an item with class="wrong" add to fail count and alert them to re-read instructions
			$(".wrong").click(function(){
				fails++;
				$(".cframe-outer").animate({"top":"30px"});
				window.scrollTo(0,0);
				$(".alert").fadeIn(100).fadeOut(100).fadeIn(100);
				$("#Fails").prop("value",fails);
			});
		}
	},

	trial: {
		init: function() {
			var trialTime = parseFloat( $("#Time").html() ),
				minTime	= parseFloat( $("#minTime").html() ),
				fsubmit = $("#FormSubmitButton");
				keypress = false;

			// show trial content
			if( isNaN(trialTime) || trialTime > 0) {
				$(".precache").addClass("DuringTrial");			// add class that does nothing (but lets us know what used to be hidden)
				$(".precache").removeClass("precache");			// remove class that hides the content
				$("form").removeClass("invisible");
				COLLECTOR.startTime = Date.now();
				$(':input:enabled:visible:first').focusWithoutScrolling();  // focus cursor on first input
			} else {
				$("form").submit();
			}

			// start timers
			if (!(isNaN(trialTime))) {
				COLLECTOR.timer(trialTime, function() {
					$(document.body).hide();
					fsubmit.click();							// see common:init "intercept FormSubmitButton"
				}, false);										// run the timer (no minTime set)
				$(":input").addClass("noEnter");				// disable enter from submitting the trial
				if(isNaN(minTime)) {
					fsubmit.addClass("hidden");					// hide submit button
				}
			}

			if (!(isNaN(minTime))) {
				fsubmit.prop("disabled", true);					// disable submit button when minTime is set
				$(":input").addClass("noEnter");				// disable enter from submitting the trial
				COLLECTOR.timer(minTime, function() {			// run timer for minTime
					fsubmit.prop("disabled", false);			// enable
					$(":input").removeClass("noEnter");
				}, false );
			}

			// disable 'noEnter' inputs and gather RTs for keypresses
			$(":input").bind("keypress",function(e){
				if(e.keyCode == 13) {							// if enter is pressed
					if($(this).hasClass("noEnter")) {			// disable for all 'noEnter' inputs
						return false;
					}
				}
				else {
					// monitor and log first/last keypress
					if(keypress == false) {						// on first keypress
						$("#RTkey").val( COLLECTOR.getRT() );	// set first keypress times
						keypress = true;
					}
					$("#RTlast").val( COLLECTOR.getRT() );		// update 'RTlast' time
				}
			});

			// updates the response value when a MC button is pressed
			$(".TestMC").click(function(){
				var clicked = $(this).html();
					$("#Response").prop("value",clicked);		// record which button was clicked
					$("#RT").val( COLLECTOR.getRT() );			// set RT

				// if UserTiming, submit, but only highlight choice otherwise
				if ($("form").hasClass("UserTiming")) {
					fsubmit.click();							// see common:init "intercept FormSubmitButton"
				} else {
					if(keypress === false) {
						$("#RTkey").val( COLLECTOR.getRT() );	// set first keypress times
						keypress === true;
					}
					$("#RTlast").val( COLLECTOR.getRT() );		// update 'RTlast' time

					$(".TestMC").removeClass("collector-button-active");	// remove highlighting from all buttons
					$(this).addClass("collector-button-active");			// add highlighting to clicked button
				}
			});

			// prevent the backspace key from navigating back.
			// http://stackoverflow.com/questions/1495219/how-can-i-prevent-the-backspace-key-from-navigating-back
			$(document).unbind('keydown').bind('keydown', function (event) {
			    var doPrevent = false;
			    if (event.keyCode === 8) {
			        var d = event.srcElement || event.target;
			        if ((d.tagName.toUpperCase() === "INPUT" && d.type.toUpperCase() === "TEXT")
			             || d.tagName.toUpperCase() === "TEXTAREA") {
			            doPrevent = d.readOnly || d.disabled;
			        } else {
			            doPrevent = true;
			        }
			    }
			    if (doPrevent) {
			        event.preventDefault();
			    }
			});
		},

        passage: function() {
            // make sure trial is starting at the top of the page
            $('body').scrollTop(0);
        },

		tetris: function() {
			// get trial time from page and run timer
			COLLECTOR.timer(parseFloat($("#Time").html() )-5, function () {
				// hide game and show get ready prompt for 5 secs
				$(".stepout-clock").hide();
				$(".tetris-wrap")
					.removeClass("tetris-wrap")
					.html("<div class='cframe-content action-bg textcenter'><h1>Get ready to continue in ... </h1><h1 id=getready></h1></div>");
				COLLECTOR.timer( 5, function() {
					$('form').submit();
				}, $("#getready"));
			}, $(".countdown"));

			// reveal on clicking start
			$("#reveal").click(function() {
			    $("#reveal").hide();
			    $(".tetris").slideDown(400, function() {
			        var off = $(".tetris").offset();
			        $("html, body").animate({scrollTop: off.top}, 500);
			    });
			});
		}
	},

	finalQuestions: {
		init: function() {
			// slider for Likert questions
			$("#slider").slider({
				value:1,
				min: 1,
				max: 7,
				step: 1,
				slide: function(event, ui) {
					$("#amount").val(ui.value);
				}
			});
			$("#amount").val( $("#slider").slider("value") );
		}
	},

	multiSession: {
		init: function() {
			// Disable enter key for textboxes
			$(":input").bind("keypress",function(e){
				if(e.keyCode == 13) return false;
			});
		}
	}
};

UTIL = {
	exec: function( controller, action ) {
		var ns = COLLECTOR,
		    action = (action === undefined) ? "init" : action;

		if (controller !== "" && ns[controller] && typeof ns[controller][action] == "function") {
			ns[controller][action]();
		}
	},

	init: function() {
		var body = document.body,
            controller = body.getAttribute("data-controller"),
            action = body.getAttribute("data-action");

		UTIL.exec("common");
		UTIL.exec(controller);
		UTIL.exec(controller, action);
	}
};



$.fn.focusWithoutScrolling = function(){
	// credit to Felipe Martim at
	// http://stackoverflow.com/questions/4898203/jquery-focus-without-scroll
	var x = window.scrollX, y = window.scrollY;
	this.focus();
	window.scrollTo(x, y);
	return this; //chainability
};

if( !Date.now ) {
	Date.now = function now() {
		return new Date().getTime();
	};
}

jQuery.fn.forceNumeric = function () {
	// forceNumeric() plug-in implementation
	// I got forceNumeric from http://weblog.west-wind.com/posts/2011/Apr/22/Restricting-Input-in-HTML-Textboxes-to-Numeric-Values
	return this.each(function () {
		$(this).keydown(function (e) {
			var key = e.which || e.keyCode;
			
			if (!e.shiftKey && !e.altKey && !e.ctrlKey &&
			 // numbers   
				key >= 48 && key <= 57 ||
			 // Numeric keypad
				key >= 96 && key <= 105 ||
			 // comma, period and minus, . on keypad
			 //	key == 190 || key == 188 || key == 109 || key == 110 ||
				key == 190 || key == 110 ||
			 // Backspace and Tab and Enter
				key == 8 || key == 9 || key == 13 ||
			 // Home and End
				key == 35 || key == 36 ||
			 // left and right arrows
				key == 37 || key == 39 ||
			 // Del and Ins
				key == 46 || key == 45)
				return true;
			
			return false;
		});
	});
};



$(window).load(UTIL.init);
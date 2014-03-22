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
	 *	Timer function
	 *
	 *  This countdown timer is for any case where you would want to timeout, like in timed trials
 	 *  For other cases where you just want to get the elapsed time, use the "getTime" function
 	 *
 	 *  @param {Object} 	timeUp: the amount of time the timer runs for
 	 *  @param {Function}	callback: the function you want to run when the timer stops
 	 *
 	 *  Example usage:
 	 * 		COLLECTOR.timer(2, function() {
	 *			$("form").submit();
	 *		});
 	 */
	timer: function (timeUp, callback) {
	    // set timer speed, counter, and starting timestamp
  		var speed = 10,
  			counter = 0,
  			elapsed = 0;
  			start = new Date().getTime();

  		// self-correcting timer instance function (each instance is adjusted based on actual elapsed time)
  		function instance() {
  			// work out the real and ideal elapsed time
  			var real = (counter * speed),
  				ideal = (new Date().getTime() - start);

  			// increment the counter
  			counter++;

  			// increment elapsed
			elapsed += speed;

  			// stop timer at the allotted time
  			if ( elapsed >= timeUp*1000 ) {
  				return callback();
  			}

  			// calculate the difference
  			var diff = (ideal - real);

  			// delete the difference from the speed of the next instance and run again
  			(window.setTimeout(function() { instance(); }, (speed - diff) ))/100;
  		}

  		// start the timer
		instance();
	},

	common: {
		init: function() {
			COLLECTOR.timer(2, function() {
				$("#RT").val(10);
			});

			$("#loadingForm").submit();
			$("#waiting").addClass("hidden");
			$(".readcheck").removeClass("hidden");
			$(":text").focus();

			// allows for the collapsing of readable() outputs
			$(".collapsibleTitle").click(function() {
				$(this).parent().children().not(".collapsibleTitle").toggle(350);
			});



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
			$("#correct").click(function(){
				$("form").submit();
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
			// Disable enter key for textboxes with id="TextboxComputerTimed"
			$("#TextboxComputerTimed").bind("keypress",function(e){
				if(e.keyCode == 13) return false;
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
		},

		stepout: function() {

		}
	},

	finalQuestions: {
		init: function() {
			// slider for Likert questions
			$( "#slider" ).slider({
				value:1,
				min: 1,
				max: 7,
				step: 1,
				slide: function( event, ui ) {
					$( "#amount" ).val( ui.value );
				}
			});
			$( "#amount" ).val( $( "#slider" ).slider( "value" ) );
		}
	}
};

UTIL = {
	exec: function( controller, action ) {
		var ns = COLLECTOR,
		    action = ( action === undefined ) ? "init" : action;

		if ( controller !== "" && ns[controller] && typeof ns[controller][action] == "function" ) {
			ns[controller][action]();
		}
},

	init: function() {
		var body = document.body,
            controller = body.getAttribute( "data-controller" ),
            action = body.getAttribute( "data-action" );

		UTIL.exec( "common" );
		UTIL.exec( controller );
		UTIL.exec( controller, action );
	}
};

$( window ).load( UTIL.init );
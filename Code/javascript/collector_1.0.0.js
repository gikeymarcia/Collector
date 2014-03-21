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
 *  3. On $(document).ready, we use functions to run the appropriate bits of code. In order they are:
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

COLLECTOR = {
	common: {
		init: function() {
      		// application-wide code

		}
	},

	users: {
		init: function() {
			// controller-wide code
		},

		show: function() {
			// action-specific code
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

$( document ).ready( UTIL.init );
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
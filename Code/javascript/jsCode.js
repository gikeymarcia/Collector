/* Collector
	A program for running experiments on the web
	Copyright 2012-2013 Mikey Garcia & Nate Kornell
*/


var timer		= 0;
var interval	= 100;				// ## SET ##, The smaller the interval the more CPU power needed.  # = timing accuracy in ms
var keypress	= 0;
var trialTime	= $("#Time").html();
var Fails		= 0;


// do these things when everything has loaded
window.onload = function () {
	$("#loadingForm").submit();
	$("#waiting").addClass("hidden");
	$(".readcheck").removeClass("hidden");
	setInterval(addtime,interval);
};


// on DOM ready focus onto item with class="Textbox"
	$("document").ready( function(){
		$(".Textbox").focus();
	});


// Disable enter key for textboxes with id="TextboxComputerTimed"
	$("#TextboxComputerTimed").bind("keypress",function(e){
		if(e.keyCode == 13) return false;
	});


function addtime(){
	timer = timer + interval;
	$("#RT").prop("value",timer);
}

// reveal readcheck questions
$("#revealRC").click(function() {
    $(this).hide();
	$(".readcheck").slideDown("slow", function() {
    	var offset = $(".readcheck").offset();
    	$(document).animate({scrollTo:offset}, 2000);
    });
});

// submit the form when they click the item with id="correct"
$("#correct").click(function(){
	$("form").submit();
});


// when they click an item with class="wrong" add to fail count and alert them to re-read instructions
$(".wrong").click(function(){
	Fails++;
	$(".cframe-outer").animate({"top":"30px"});
	window.scrollTo(0,0);
	$(".alert").fadeIn(100).fadeOut(100).fadeIn(100);
	$("#Fails").prop("value",Fails);
});


// allows for the collapsing of Readable() outputs
$(".collapsibleTitle").click(function() {
	var change = $(this).parent().children().not(".collapsibleTitle");
	if($(this).hasClass("hiding")) {
		change.show(350);
		$(this).removeClass("hiding");
	}
	else {
		$(this).addClass("hiding");
		change.hide(350);
	}
});
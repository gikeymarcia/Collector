/* Generic 2.00a1
	A program for running experiments on the web
	Copyright 2012 Mikey Garcia & Nate Kornell
*/


var timer		= 0;
var interval	= 100;				// ## SET ##, The smaller the interval the more CPU power needed.  # = timing accuracy in ms
var keypress	= 0;
var trialTime	= $("#Time").html();
var Fails		= 0;


// do these things when everything has loaded
window.onload = function () {
	$("#loadingForm").submit();
	$("#waiting").addClass("Hidden");
	$(".readcheck").removeClass("Hidden");
	setInterval(addtime,interval);
	
}


// on DOM ready focus onto item with class="Textbox"
	$("document").ready( function(){
		$(".Textbox").focus();
	})


// Disable enter key for textboxes with id="TextboxComputerTimed"
	$("#TextboxComputerTimed").bind("keypress",function(e){
		if(e.keyCode == 13) return false;
	});


// make SubmitButton div submit the form when clicked
// only used on the login page to stop people who have js disabled
	$("#SubmitButton").click(function(){
		$("form").submit();
	});


function addtime(){
	timer = timer + interval;
	$(".RT").attr("value",timer);
}


// submit the form when they click the item with id="correct"
$("#correct").click(function(){
	$("form").submit();
});


// when they click an item with class="wrong" add to fail count and alert them to re-read instructions
$(".wrong").click(function(){
	Fails++;
	alert('Please carefully read the instructions again.');
	$(".Fails").attr("value",Fails);
});
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
var interval = 100;					// ## SET ##, The smaller the interval the more CPU power needed.  # = timing accuracy in ms
var Fails = 0;

// on pageload start timer
	$("document").ready( function(){
		setInterval(addtime,interval);
	});

// everytime this function adds to the timer it also updates the input with class="RT"
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
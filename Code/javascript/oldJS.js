/**
 *	for multisession.php
 */
	// Disable enter key for textboxes with id="TextboxComputerTimed"
	$("#TextboxComputerTimed").bind("keypress",function(e){
		if(e.keyCode == 13) return false;
	});
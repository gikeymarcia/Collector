var fails = 0;

// reveal readcheck questions
$("#revealRC").click( function() {
    $(".instructions").slideToggle(400);            // hide instructions
    $(".readcheck").slideToggle(400);               // show questions
    if (fails > 0) {                                // if you've missed the question before
        $(".alert").slideToggle();                      // hide the alert
        $("#content").removeClass("redOutline");        // remove the red outline
    }
});

// When a button is clicked it checks if the user is right/wrong
// either advance page or gives notice to read closely
$(".MCbutton").click( function() {
    if (this.id === "correct") {
        $("form").submit();
    }
    else {
        $(".instructions").slideToggle(400);        // show instructions text
        $(".readcheck").slideToggle(400);           // hide multiple choice questions
        fails++;                                    // add to fails counter
        $("#Fails").val(fails);                     // set value of fails
        $(".alert").slideDown();                    // show alert that the user is wrong
        $("#content").addClass("redOutline");       // add a red outline to the form
    }
});
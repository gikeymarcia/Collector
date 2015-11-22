<section class="instructions">
    <h2 class="textcenter">Task Instructions</h2>
    <p>In this study you will be studying some stuff then you will need to recall that stuff.
       After each bunch of stuff there will be some kind of memory task. </p>
    <p>Please pay close attention to the things we are showing you.
    <p> As many paragraphs as you would like can go here.  Instructions are done.  Time for you to move onto the experiment </p>

    <div class="textcenter" id="revealRC">
        <button class="collectorButton" type="button">Advance</button>
    </div>
</section>

<!-- ## SET ## This ensures that participants read your instructions.
     Participants must correctly answer something about the procedure -->
<div class="readcheck">
    Should you pay close attention?  (hint: Answer is in the instructions)
    <ul>
        <li class="MCbutton"             > I don't think so </li>
        <li class="MCbutton"             > Nope             </li>
        <li class="MCbutton" id="correct"> Yes              </li>
        <li class="MCbutton"             > I can't read.    </li>
    </ul>
</div>


<script type="text/javascript">
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
        if (this.id == "correct") {
            $("#RT").val( COLLECTOR.getRT() );
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
</script>
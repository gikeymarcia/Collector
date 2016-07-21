<div class="tetris-wrap textcenter">
    <h3>Play a quick game of Tetris while we load the next part</h3>
    <div class="tetris-controls">
        <div class="grid-item">
            <h2>Controls</h2>
        </div><!--
     --><div class="grid-item grid-1-3">
            <p>Move:</p>
        </div><!--
     --><div class="grid-item grid-2-3">
            <p><strong>Left</strong> and <strong>Right arrows</strong></p>
        </div><!--
     --><div class="grid-item grid-1-3">
            <p>Rotate Right:</p>
        </div><!--
     --><div class="grid-item grid-2-3">
            <p><strong>X</strong> or <strong>Up arrow</strong></p>
        </div><!--
     --><div class="grid-item grid-1-3">
            <p>Rotate Left:</p>
        </div><!--
     --><div class="grid-item grid-2-3">
            <p><strong>Z</strong></p>
        </div><!--
     --><div class="grid-item grid-1-3">
            <p>Drop:</p>
        </div><!--
     --><div class="grid-item grid-2-3">
            <p><strong>Spacebar</strong></p>
        </div>
    </div>

    <div class="collectorButton" id="reveal">Start</div>

    <div class="tetris">
        <embed src="http://www.cogfog.com/nblox.swf" menu="false" width="550" height="650"
               quality="high" type="application/x-shockwave-flash"
               pluginspage="http://www.macromedia.com/go/getflashplayer/" />
    </div>
</div>

<?php if (is_numeric($max_time)): ?>
<div class="stepout-clock">
    <span>Seconds remaining</span>
    <h3 class="countdown"></h3>
</div>

<?php else: ?>
<div class="textcenter">
    <button class="collectorButton collectorAdvance" id="FormSubmitButton">Next</button>
</div>
<?php endif; ?>

<script type="text/javascript">

// reveal on clicking start
$("#reveal").click( function() {
    $("#reveal").hide();
    $(".tetris").slideDown(400, function() {
        var off = $(".tetris").offset();
        $("html, body").animate({scrollTop: off.top}, 500);
    });
});

Collector.start() = function() {
    if($.isNumeric( Collector.inputs.max )) {
        var gameDuration = parseFloat( Collector.inputs.max );
        var gameTimer = new Collector.Timer(gameDuration, function(){
            $(".stepout-clock").hide();
            $(".tetris-wrap")
                .removeClass("tetris-wrap")
                .html("<div class='action-bg textcenter fullPad'>" +
                          "<h1 class='pad'>Get ready to continue in ... </h1>" +
                          "<h1 id=getready></h1>" +
                      "</div>"
            );
            Collector.max_timer.show( $("#getready") );
        });
        gameTimer.show( $(".countdown") );
    }
}
</script>
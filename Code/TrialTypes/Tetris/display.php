
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

<?php
    if (is_numeric($maxTime)) {
        ?>
        <div class="stepout-clock">
            <span>Seconds remaining</span>
            <h3 class="countdown"></h3>
        </div>

        <!-- used to set timer -->
        <div id="maxTime" class="hidden"><?php echo trim($maxTime); ?></div>
        <?php
    } else {
        ?>
        <div class="textcenter">
            <button class="collectorButton collectorAdvance" id="FormSubmitButton">Next</button>
        </div>
        <?php
    }
?>
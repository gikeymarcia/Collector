<div class="textcenter">
    <audio src="<?= "{$_PATH->experiment}/{$cue}" ?>" autoplay>
        <p>Your browser does not support the audio element.</p>
    </audio>
</div>

<!-- include form to collect RT and advance page -->
<div><?php echo $text; ?></div>
<div class="textcenter">
    <button class="collectorButton collectorAdvance" id="FormSubmitButton">Next</button>
</div>

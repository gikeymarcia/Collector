<?php
	$compTime = 8;					// time in seconds to use for 'computer' timing
    if ($text === '') { $text = 'How likely are you to correctly recall this item on a later test?|Type your response on a scale from 0-100.'; }
    $texts = explode('|', $text);
    $mainText = array_shift($texts);
?>

    <div class="textcenter">
        <h3><?php echo $mainText; ?></h3>
        <?php
            foreach ($texts as $t) {
                echo '<p>' . $t . '</p>';
            }
        ?>
    </div>

    <div class="textcenter">
        <input name="JOL" type="text" value="" autocomplete="off" class="forceNumeric"/>
        <input class="button"  id="FormSubmitButton" type="submit" value="Submit"   />
    </div>

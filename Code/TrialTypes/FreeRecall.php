<?php
	$compTime = 60;					// time in seconds to use for 'computer' timing

	$prompt =& $currentTrial['Procedure']['Procedure Notes'];
?>
    <div class="prompt"><?php echo $prompt; ?></div>
    <textarea rows="20" cols="55" name="Response" class="precache" wrap="physical" value=""></textarea>
    <div class="textleft">
        <input class="button button-trial-advance" id="FormSubmitButton" type="submit" value="Submit"   />
    </div>
    </form>
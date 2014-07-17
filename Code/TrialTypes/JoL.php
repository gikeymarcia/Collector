<?php
	$compTime = 8;					// time in seconds to use for 'computer' timing
	trialTiming();					// determines timing and user/computer timing mode
?>

    <div class=textcenter>
        <h3>How likely are you to correctly recall this item on a later test?</h3>
        <p>Type your response on a scale from 0-100.</p>
        <br />
    </div>

    <form class="<?php echo $formClass; ?> collector-form textcenter"  autocomplete=off  action='<?php echo $postTo; ?>'  method="post">
        <input  name=Response type=text value="" autocomplete=off class="forceNumeric" />
        <input class=hidden  id=RT     name=RT       type=text value="RT"       />
        <input class=hidden  id=RTkey  name=RTkey    type=text value="no press" />
        <input class=hidden  id=RTlast name=RTlast   type=text value="no press" />
        <input class=button  id=FormSubmitButton type=submit value="Submit"   />
    </form>

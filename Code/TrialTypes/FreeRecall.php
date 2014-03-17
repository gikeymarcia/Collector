<?php
	$compTime = 60;					// time in seconds to use for 'computer' timing
	trialTiming();					// determines timing and user/computer timing mode

	$prompt =& $currentTrial['Procedure']['Procedure Notes'];
?>
    <div class=prompt><?php echo $prompt; ?> </div>
    <form class="<?php echo $formClass; ?> collector-form"  autocomplete="off"  action="postTrial.php"  method="post">
        <textarea rows=20 cols=55 name=Response class=precache wrap=physical value=""></textarea>
        <input class=hidden  id=RT     name=RT       type=text value="RT"       />
        <input class=hidden  id=RTkey  name=RTkey    type=text value="no press" />
        <input class=hidden  id=RTlast name=RTlast   type=text value="no press" />
        <div class=textright>
            <input class="button button-trial-advance" type=submit value="Submit"   />
        </div>
    </form>
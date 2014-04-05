<?php
	$compTime = 8;					// time in seconds to use for 'computer' timing
	trialTiming();					// determines timing and user/computer timing mode
?>
	<div class='precache pic'>
		<?php echo show($cue); ?>
	</div>

    <form class="<?php echo $formClass; ?> precache collector-form textcenter" action="<?php echo $postTo; ?>" method=post autocomplete=off>
        <input class=testPic name=Response id=Response type=text value=''         />
        <input class=hidden  name=RTkey    id=RTkey    type=text value="no press" />
        <input class=hidden  name=RTlast   id=RTlast   type=text value="no press" />
        <input class=hidden  name=RT       id=RT       type=text value="RT"       />
        <input class=button  id=FormSubmitButton type=submit value="Submit"       />
    </form>
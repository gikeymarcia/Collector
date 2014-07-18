<?php
	$compTime = 8;					// time in seconds to use for 'computer' timing
	trialTiming();					// determines timing and user/computer timing mode

	#### Set up MC button grid ####
	// shuffle button positions (first time only) and save to session
	if(isset($_SESSION['MCbutton']) == false) {
		$mc = $MultiChoiceButtons;   // set this in Settings.php
		shuffle($mc);				 // comment out this line to prevent shuffling
		$_SESSION['MCbutton'] = $mc;
	} else {
	    $mc = $_SESSION['MCbutton'];
	}

    // load setting for items per row (in Settings.php)
    $perRow = $MCitemsPerRow;

    // generate mc choice grid
    // note: because the grid is built using "display:inline" whitespace control between grid-items
    //       or it breaks in Chrome. This is why html comments are added between those divs
    $MCGrid = '';
    for ($i=0; $i<count($mc); $i++) {
        $newitem = "<div class='grid-item grid-1-{$perRow}'>
                        <div class='button TestMC'>{$mc[$i]}</div>
                    </div>";
        if ($i !== (count($mc)-1)) {
            $newitem .= "<!--";
        }
        if ($i !== 0) {
            $newitem = "-->".$newitem;
        }
        $MCGrid .= $newitem;
    }
?>

	<!-- show the image -->
    <div class="precache pic">
		<?php echo show($cue); ?>
    </div>

	<!-- display the MC button choices -->
    <div class="precache grid">
        <?php echo $MCGrid; ?>
    </div>

    <form class="<?php echo $formClass; ?>" action="<?php echo $postTo; ?>" method="post">
    	<input class="hidden" name="Response" id="Response" type="text" value=""         />
    	<input class="hidden" name="RTkey"    id="RTkey"    type="text" value="no press" />
    	<input class="hidden" name="RTlast"   id="RTlast"   type="text" value="no press" />
    	<input class="hidden" name="RT"       id="RT"       type="text" value="RT"       />
    	<input class="hidden" id="FormSubmitButton" type="submit" value="Submit"         />
    </form>
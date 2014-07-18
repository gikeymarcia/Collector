<?php
	$compTime = 8;					// time in seconds to use for 'computer' timing
	trialTiming();					// determines timing and user/computer timing mode
	
	
	function findSetting( $settings, $setting ) {
		foreach( $settings as $set ) {
			$test = removeLabel( $set, $setting );
			if( $test !== FALSE ) {
				return $test;
			}
		}
		return FALSE;
	}
	
	
	function trimExplode( $delimiter, $string ) {
		$output = array();
		$explode = explode( $delimiter, $string );
		foreach( $explode as $explosion ) {
			$output[] = trim($explosion);
		}
		return $output;
	}
	
	
	function readRange( $string ) {
		$output = array();
		$ranges = trimExplode( ',', $string );
		foreach( $ranges as $range ) {
			$rangePoints = trimExplode( '::', $range );
			if( count($rangePoints) === 1 ) $rangePoints[1] = $rangePoints[0];
			$first = array_shift($rangePoints);
			$last  = array_pop  ($rangePoints);
			$output = array_merge($output, range($first,$last));
		}
		return $output;
	}
	
	
	if( !isset($settings) ) $settings = '';
	$settings = trimExplode( '|', $settings );
	
	$criterion = findSetting( $settings, 'criterion' );
	$itemList  = findSetting( $settings, 'stimuli'   );
	
	if( is_bool($criterion) ) $criterion = 1;
	if( is_bool($itemList)  ) $itemList = $item;
	
	$itemList = readRange( $itemList );
	
	$allStim = array();
	foreach( $itemList as $thisItem ) {
		if( isset( $_SESSION['Stimuli'][ $thisItem ] ) ) {
			$allStim[] = $_SESSION['Stimuli'][ $thisItem ];
		}
	}
?>
	<style>
		.testCueRight	{	white-space: nowrap;	}
		.testCueRight input	{	width: 300px;	padding: 0px 1px;	font-family: "Roboto", "Open Sans", Arial, Helvetica, sans-serif;	}
		
		.CriterionItem	{	display: none;	}
		
		.SubmitContainer	{	display: none;	}
	</style>
	<form  class="<?php echo $formClass; ?> collector-form"  autocomplete="off"  action="<?php echo $postTo; ?>"  method="post">
		<?php
			foreach( $allStim as $stim ) {
				$cue = $stim['Cue'];
				$ans = $stim['Answer'];
				?>
		<div class="study CriterionItem CriterionStudy">
			<span class="study-left">  <?= $cue; ?>  </span>
			<span class="study-divider">     :       </span>
			<span class="study-right"> <?= $ans; ?>  </span>
		</div>
				<?php
			}
		?>
		<?php
			foreach( $allStim as $stim ) {
				$cue = $stim['Cue'];
				$ans = $stim['Answer'];
				?>
		<div class="study CriterionItem CriterionTest">
			<span class="study-left">  <?= $cue; ?>  </span>
			<span class="study-divider">     :       </span>
			<div class="testCueRight">
				<input class="CriterionTestInput" type="text" value="" autocomplete="off" data-answer="<?= strtolower($ans) ?>"/>
			</div>
		</div>
				<?php
			}
		?>
		<div class="SubmitContainer">
			<h3><?= $procedureNotes ?></h3>
			<input class="hidden"  name="RT"       id="RT"     type="text" value="RT"       />
			<input class="hidden"  name="RTkey"    id="RTkey"  type="text" value="no press" />
			<input class="hidden"  name="RTlast"   id="RTlast" type="text" value="no press" />
			<input class="hidden"  name="LoopCount"            type="text" value="0"        />
			<input class="hidden"  name="Performance"          type="text" value=""         />
			<div class="textcenter">
				<input class="button button-trial-advance" id="FormSubmitButton" type="submit" value="Next"  />
			</div>
		</div>
	</form>
	
	<script>
		(function($){
			// credit to James Padolsey at
			// http://james.padolsey.com/javascript/shuffling-the-dom/
		 
			$.fn.shuffle = function() {
		 
				var allElems = this.get(),
					getRandom = function(max) {
						return Math.floor(Math.random() * max);
					},
					shuffled = $.map(allElems, function(){
						var random = getRandom(allElems.length),
							randEl = $(allElems[random]).clone(true)[0];
						allElems.splice(random, 1);
						return randEl;
				   });
		 
				this.each(function(i){
					$(this).replaceWith($(shuffled[i]));
				});
		 
				return $(shuffled);
		 
			};
		 
		})(jQuery);
		
		COLLECTOR.trial.criteriontest = function() {
			var studyTime = 2;
			var testTime  = 5;
			var delayTime = .25;
			var criterion = <?= $criterion ?>;
			var totalPossible = $(".CriterionTest").length;
			function nextStage() {
				var timeToWait;
				if( $(".CriterionItem:visible").hasClass("CriterionStudy") ) {
					timeToWait = studyTime;
				} else {
					timeToWait = testTime;
				}
				COLLECTOR.timer( timeToWait, function() {
					var currentItem = $(".CriterionItem:visible");
					currentItem.hide();
					if( currentItem.next().hasClass("SubmitContainer") ) {
						var totalCorrect = 0;
						var performance;
						$(".CriterionTestInput").each( function() {
							if( $(this).val().toLowerCase() === $(this).data("answer") ) {
								++totalCorrect;
							}
						} );
						performance = totalCorrect / totalPossible;
						if( $("input[name='Performance']").val() === "" ) {
							$("input[name='Performance']").val( performance );
						} else {
							$("input[name='Performance']").val( $("input[name='Performance']").val() + "," + performance );
						}
						$("input[name='LoopCount']").val( 1+parseInt( $("input[name='LoopCount']").val() ) );
						if( performance >= criterion ) {
							COLLECTOR.timer( delayTime, function() {
								$(".SubmitContainer").show();
							} );
						} else {
							COLLECTOR.timer( delayTime, function() {
								beginCriterionCycle();
							} );
						}
					} else {
						COLLECTOR.timer( delayTime, function() {
							currentItem.next().show().find(":input:first").focus();
							nextStage();
						} );
					}
				} );
			}
			function beginCriterionCycle() {
				$(".CriterionStudy").shuffle();
				$(".CriterionTest").shuffle();
				$(".CriterionTestInput").val( "" );
				$(".CriterionItem:first").show();
				nextStage();
			}
			beginCriterionCycle();
		}
	</script>
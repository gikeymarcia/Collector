<?php
    $compTime = 8;                    // time in seconds to use for 'computer' timing
    
    
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

<style> .CriterionItem, .SubmitContainer  { display: none; } </style>

<section class="vcenter">

<?php foreach($allStim as $stim):
    $cue = $stim['Cue'];
    $ans = $stim['Answer']; ?> 
  <div class="study CriterionItem CriterionStudy">
    <span class="study-left">   <?php echo $cue; ?>  </span>
    <span class="study-divider">         :           </span>
    <span class="study-right">  <?php echo $ans; ?>  </span>
  </div>
<?php endforeach; ?>

<?php foreach($allStim as $stim):
    $cue = $stim['Cue'];
    $ans = $stim['Answer']; ?>
  <div class="study CriterionItem CriterionTest">
    <span class="study-left">  <?php echo $cue; ?>  </span>
    <span class="study-divider">         :          </span>
    <div class="study-right">
      <input class="copybox CriterionTestInput" type="text" value="" 
             autocomplete="off" data-answer="<?php echo strtolower($ans); ?>">
    </div>
  </div>
<?php endforeach; ?>

  <div class="study SubmitContainer">
    <h4><?php echo $text; ?></h4>
    <input class="hidden" name="LoopCount" type="text" value="0">
    <input class="hidden" name="Performance" type="text" value="">
  </div>
  <div class="collector-form-element textcenter">
    <button class="collectorButton collectorAdvance" id="FormSubmitButton">Next</button>
  </div>
</section>
    
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

    COLLECTOR.experiment.criteriontest = function() {
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
    };
</script>
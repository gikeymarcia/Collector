<?php
    /************
     * Settings *
     ************/
    // Sets the names displayed on the multiple choice buttons
    $MultiChoiceButtons = array(
        'Cat1', 'Cat2', 'Cat3', 'Cat4', 'Cat6', 'Cat6', 
        'Cat7', 'Cat 8', 'Cat9', 'Cat10', 'Cat11', 'Cat 12'
    );
    // Sets how many items are displayed per row
    // use values 1-4; anything bigger causes problems which require css changes
    $MCitemsPerRow = 4;


    function trimExplode($delim, $string) {
        $explode = explode($delim, $string);
        foreach ($explode as &$str) {
            $str = trim($str);
        }
        unset($str);
        return $explode;
    }

    #### Set up MC button grid ####
    // shuffle button positions (first time only) and save to session
    if(isset($_SESSION['MCbutton']) == false) {
        $mc = $MultiChoiceButtons;   // set this above in Settings
        shuffle($mc);        // comment out this line to prevent shuffling
        $_SESSION['MCbutton'] = $mc;
    } else {
        $mc = $_SESSION['MCbutton'];
    }

    // load setting for items per row (above in Settings)
    $perRow = $perCol = $MCitemsPerRow;

    $cues    = trimExplode('|', $cue);
    $answers = trimExplode('|', $answer);

    $buttons     = array();
    $limitPerRow = true;
    $horizontal  = true;

    $share = false; 

    $settings = trimExplode('|', $settings);
    $stimCols = array();
    foreach ($currentTrial['Stimuli'] as $column => $notImportant) {
        $stimCols[strtolower($column)] = $column;
    }

    foreach ($settings as $setting) {
        $theseAreButtons = false;
        $shuffleThese    = false;

        if (removeLabel($setting, 'button') !== false) {
            $theseAreButtons = true;
        } elseif (removeLabel($setting, 'perRow') !== false) {

            $test = removeLabel($setting, 'perRow');
            if (is_numeric($test)) {
                $perRow = (int)$test;
                $limitPerRow = true;
            }

        } elseif (removeLabel($setting, 'perColumn') !== false) {

            $test = removeLabel($setting, 'perColumn');
            if (is_numeric($test)) {
                $perCol = (int)$test;
                $limitPerRow = false;
            }

        } elseif (removeLabel($setting, 'horizontal') !== false) {
            $horizontal = true;
        } elseif (removeLabel($setting, 'vertical') !== false) {
            $horizontal = false;
        } elseif (removeLabel($setting, 'shuffle') !== false) {

            $unlabeled = removeLabel($setting, 'shuffle');
            if (removeLabel($unlabeled, 'button') !== false) {
                $setting         = $unlabeled;
                $theseAreButtons = true;
            } else {
                shuffle($buttons);
            }

        } elseif (removeLabel($setting, 'share') !== false) {
            $share = removeLabel($setting, 'share');
        } else {
            $theseAreButtons = true;
        }

        if ($theseAreButtons) {
            $theseButtons = removeLabel($setting, 'button');
            $theseButtons = trimExplode(';', $theseButtons);
            $newButtons   = array();
            foreach ($theseButtons as $thisButton) {
                if ($thisButton === '') { continue; }
                if ($thisButton[0] === '$') {
                    $sep = strrpos($thisButton, '[');
                    if ($sep === false) {
                        $col = substr($thisButton, 1);
                        $index = $item;
                    } else {
                        $col = substr($thisButton, 1, $sep-1);
                        $index = substr($thisButton, $sep+1, strrpos($thisButton, ']')-$sep-1);
                    }
                    $col = strtolower(trim($col));
                    if (isset($stimCols[$col])) {
                        $index = rangeToArray($index);
                        foreach ($index as $i) {
                            $newButtons[] = $_SESSION[ 'Stimuli' ][ $i ][ $stimCols[$col] ];
                        }
                    } else {
                        $newButtons[] = $thisButton;                    // so we can see which button is being outputted as $bad button [2o3nri...
                        $trialFail = true;
                        echo '<h3>Buttons incorrectly defined. For dynamic buttons, please use a dollar sign, followed by the column name, followed by a space, followed by a number or range, like <strong>$cue[2::8]</strong></h3>';
                    }
                } else {
                    $newButtons[] = $thisButton;
                }
            }
            if ($shuffleThese) {
                shuffle($newButtons);
            }
            $buttons = array_merge($buttons, $newButtons);
        }
    }
    if ($buttons === array()) {
        $buttons = $_SESSION['MCbutton'];
    }
    $buttons = array_unique($buttons);

    if (!isset($currentTrial['Response']['Buttons'])) {
        if ($share !== false) {
            if (!isset($_SESSION['Share'][$share]['Buttons'])) {
                $_SESSION['Share'][$share]['Buttons'] = $buttons;
            } else {
                $buttons = $_SESSION['Share'][$share]['Buttons'];
            }
        }
        $currentTrial['Response']['Buttons'] = implode('|', $buttons);
    } else {
        $buttons = explode('|', $currentTrial['Response']['Buttons']);
    }

    $buttonGrid = array();
    $x = 0;
    $y = 0;

    $count = count($buttons);
    if ($limitPerRow AND $horizontal) {
        $numCols = min($perRow, $count);
    } elseif (!$limitPerRow AND !$horizontal) {
        $numRows = min($perCol, $count);
    } elseif (!$limitPerRow AND $horizontal) {
        $numCols = (int)ceil($count / min($perCol, $count));
    } else {                // ($limitPerRow AND !$horizontal)
        $numCols = (int)ceil($count / min($perCol, $count));
        $numRows = (int)ceil($count / $numCols);
        $rem     = $count % $numCols;
    }

    foreach ($buttons as $button) {
        $buttonGrid[$y][$x] = $button;
        if ($horizontal) { ++$x; } else { ++$y; }
        if ($horizontal AND $x === $numCols) {
            $x = 0;
            ++$y;
        } elseif (!$horizontal AND $y === $numRows) {
            $y = 0;
            ++$x;
            --$rem;
            if($rem === 1) {
                --$perCol;
            }
        }
    }

    $tdWidth    = 78 / count($buttonGrid[0]);
?>
<style type="text/css"> 
    .mcPicTable td {
        width: <?= $tdWidth ?>%;
    }
</style>

  <!-- show the image -->
  <div class="pic">
    <?php echo show($cues[0]); ?>
  </div>
  
  <!-- optional text -->
  <div><?php echo isset($text) ? $text : ""; ?></div>

  <!-- button grid -->
  <table class="mcPicTable">
  <?php foreach ($buttonGrid as $row) : ?>
    <tr>
    <?php foreach ($row as $field) : ?>
      <td>
        <div class="collectorButton TestMC"> 
          <?php echo $field; ?>
        </div>
      </td>
    <?php endforeach; ?>
    </tr>
  <?php endforeach; ?>
  </table>

  <input class="hidden" name="Response" id="Response" type="text" value="">
  <button class="hidden" id="FormSubmitButton">Submit</button>
  
  <script>
    // updates the response value when a MC button is pressed
    $(".TestMC").click(function() {
        var clicked = $(this).html();
        var form = $("form");
        $("#Response").prop("value",clicked);       // record which button was clicked
        $("#RT").val( COLLECTOR.getRT() );          // set RT
        form.addClass("submitAfterMinTime");

        // if UserTiming, submit, but only highlight choice otherwise
        if (form.hasClass("UserTiming") && !form.hasClass("WaitingForMinTime")) {
            form.submit();                         // see common:init "intercept FormSubmitButton"
        } else {
            if (keypress === false) {
                $("#RTfirst").val( COLLECTOR.getRT() );   // set first keypress times
                keypress === true;
            }
            $("#RTlast").val( COLLECTOR.getRT() );      // update 'RTlast' time

            $(".TestMC").removeClass("collectorButtonActive");  // remove highlighting from all buttons
            $(this).addClass("collectorButtonActive");          // add highlighting to clicked button
        }
    });
  </script>

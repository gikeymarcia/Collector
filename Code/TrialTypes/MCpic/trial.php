<?php
$compTime = 8;    // time in seconds to use for 'computer' timing
    
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
    $mc = $MultiChoiceButtons;   // set this in Settings.php
    shuffle($mc);		 // comment out this line to prevent shuffling
    $_SESSION['MCbutton'] = $mc;
} else {
    $mc = $_SESSION['MCbutton'];
}

// load setting for items per row (in Settings.php)
$perRow = $perCol = $MCitemsPerRow;

$cues    = trimExplode('|', $cue);
$answers = trimExplode('|', $answer);

$buttons     = array();
$limitPerRow = TRUE;
$horizontal  = TRUE;

$share = FALSE; 

$settings = trimExplode('|', $settings);
$stimCols = array();
foreach ($currentTrial['Stimuli'] as $column => $notImportant) {
    $stimCols[strtolower($column)] = $column;
}

foreach ($settings as $setting) {
    $theseAreButtons = FALSE;
    $shuffleThese    = FALSE;

    if (removeLabel($setting, 'button') !== FALSE) {
        $theseAreButtons = TRUE;
    } elseif (removeLabel($setting, 'perRow') !== FALSE) {

        $test = removeLabel($setting, 'perRow');
        if (is_numeric($test)) {
            $perRow = (int)$test;
            $limitPerRow = TRUE;
        }

    } elseif (removeLabel($setting, 'perColumn') !== FALSE) {

        $test = removeLabel($setting, 'perColumn');
        if (is_numeric($test)) {
            $perCol = (int)$test;
            $limitPerRow = FALSE;
        }

    } elseif (removeLabel($setting, 'horizontal') !== FALSE) {
        $horizontal = TRUE;
    } elseif (removeLabel($setting, 'vertical') !== FALSE) {
        $horizontal = FALSE;
    } elseif (removeLabel($setting, 'shuffle') !== FALSE) {

        $unlabeled = removeLabel($setting, 'shuffle');
        if (removeLabel($unlabeled, 'button') !== FALSE) {
            $setting         = $unlabeled;
            $theseAreButtons = TRUE;
        } else {
            shuffle($buttons);
        }

    } elseif (removeLabel($setting, 'share') !== FALSE) {
        $share = removeLabel($setting, 'share');
    } else {
        $theseAreButtons = TRUE;
    }

    if ($theseAreButtons) {
        $theseButtons = removeLabel($setting, 'button');
        $theseButtons = trimExplode(';', $theseButtons);
        $newButtons   = array();
        foreach ($theseButtons as $thisButton) {
            if ($thisButton === '') { continue; }
            if ($thisButton[0] === '$') {
                $sep = strrpos($thisButton, '[');
                if ($sep === FALSE) {
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
                    $trialFail = TRUE;
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
    if ($share !== FALSE) {
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
<style> .mcPicTable td { width: <?php echo $tdWidth; ?>%; ?> </style>


  <!-- show the image -->
  <div class="pic pad">
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
        <div class="collector-button TestMC"> 
          <?php echo $field; ?>
        </div>
      </td>
    <?php endforeach; ?>
    </tr>
  <?php endforeach; ?>
  </table>

  <input class="hidden" name="Response" id="Response" type="text" value="">
  <input class="hidden" id="FormSubmitButton" type="submit" value="Submit">

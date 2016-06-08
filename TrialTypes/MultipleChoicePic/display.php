<?php
$cues = explode('|', $cue);

$save = false;
if ($_TRIAL->settings->save) {
    $save = (string) $_TRIAL->settings->save;
}

if ($save AND isset($_SESSION['Saved Buttons'][$save])) {
    $buttons = $_SESSION['Saved Buttons'][$save];
} else {
    $buttons = array();
    
    $stimCols = array();
    foreach (array_keys($stimuli) as $col) {
        $colCode = str_replace(' ', '_', strtolower($col));
        $stimCols[$colCode] = $col;
    }
    
    $settings = explode('|', $settings);
    
    // in string like "buttons=one;two | shuffle buttons=three;four | buttons=dont know | share A"
    // find all settings starting with "buttons" and "shuffle buttons"
    foreach ($settings as $setting) {
        $setting = explode('=', $setting);
        if (!isset($setting[1])) continue;
        
        $key = strtolower(trim($setting[0]));
        if ($key === 'buttons' || $key === 'shuffle buttons') {
            $thesebuttonsRaw = explode(';', $setting[1]);
            $theseButtons = array();
            
            foreach ($thesebuttonsRaw as $buttonRaw) {
                $buttonRaw = trim($buttonRaw);
                if ($buttonRaw === '') continue; // skip empty string
                
                if ($buttonRaw[0] === '$') {
                    $err = "$trial_type error in Settings: buttons incorrectly defined with $buttonRaw.";
                    // dynamically defined button, like $answer or $answer[2::8]
                    // get stimuli column
                    if ($buttonRaw[strlen($buttonRaw)-1] === ']') {
                        if (strpos($buttonRaw, '[') === false) {
                            trigger_error("$err If ending with ']', must contain '['.", E_USER_WARNING);
                            continue;
                        }
                        
                        $customRange = true;
                        
                        $colCode = substr($buttonRaw, 1, strpos($buttonRaw, '[')-1);
                    } else {
                        $customRange = false;
                        $colCode = substr($buttonRaw, 1);
                    }
                    
                    // check if stimuli column exists
                    if (!isset($stimCols[$colCode])) {
                        trigger_error("$err '$colCode' not an existing stimuli column.", E_USER_WARNING);
                        continue;
                    } else {
                        $col = $stimCols[$colCode];
                    }
                    
                    // get values from that stimuli column
                    if ($customRange) {
                        // if defined with a custom range, like $answer[2::8], look in general stimuli
                        $range = rangeToArray(substr($buttonRaw, strpos($buttonRaw, '[') + 1, -1));
                        foreach ($range as $buttonItemRaw) {
                            $buttonItem = $buttonItemRaw - 2;
                            if (isset($_EXPT->stimuli[$buttonItem])) {
                                $theseButtons[] = $_EXPT->stimuli[$buttonItem][$col];
                            } else {
                                trigger_error("$err '$buttonItemRaw' not an existing stimuli row.", E_USER_WARNING);
                            }
                        }
                    } else {
                        // if defined without custom range, like $answer, use the $answer of this trial
                        $theseButtons = explode('|', $stimuli[$col]);
                    }
                } else {
                    // statically defined button
                    $theseButtons[] = $buttonRaw;
                }
            }
            
            if ($key === 'shuffle buttons') shuffle($theseButtons);
            
            $buttons = array_merge($buttons, $theseButtons);
        }
    }
    
    $buttons = array_unique($buttons);
    
    if ($save) $_SESSION['Saved Buttons'][$save] = $buttons;
}

$_EXPT->responses[$currentPos]['Buttons'] = implode('|', $buttons);

if ($_TRIAL->settings->vertical) {
    $buttonsHorizontal = false;
} else {
    $buttonsHorizontal = true;
}

if (is_numeric($_TRIAL->settings->columns)) {
    $columns = (int) $_TRIAL->settings->columns;
    $rows    = ceil(count($buttons) / $columns);
} elseif (is_numeric($_TRIAL->settings->rows)) {
    $rows    = (int) $_TRIAL->settings->rows;
    $columns = ceil(count($buttons) / $rows);
} else {
    $countButtons = count($buttons);
    $columns = ($countButtons % 3 === 0) ? 3 : 4;
    $columns = min($countButtons, $columns);
    $rows    = ceil(count($buttons) / $columns);
}

$buttonGrid = array();
$x = 0;
$y = 0;
foreach ($buttons as $i => $button) {
    $buttonGrid[$y][$x] = $button;
    if ($buttonsHorizontal) {
        ++$x;
        if ($x >= $columns) {
            $x = 0;
            ++$y;
        }
    } else {
        ++$y;
        if ($y >= $rows) {
            $y = 0;
            ++$x;
        }
    }
}

?>

<style type="text/css">
  #content { width: 100%; padding: 0; }
  
  .mcPicTable { max-width: 850px; margin: auto; }
  .mcPicTable td { padding: 10px 16px; vertical-align: top; text-align: center; }
</style>

<!-- show the image -->
<div class="pic"><?= show($cues[0]) ?></div>

<!-- optional text -->
<div><?= $_EXPT->get('text') ?></div>

<!-- button grid -->
<table class="mcPicTable">
  <?php foreach ($buttonGrid as $row): ?>
  <tr>
    <?php foreach ($row as $field): ?>
    <td><div class="collectorButton TestMC"><?= $field ?></div></td>
    <?php endforeach;?>
  </tr>
  <?php endforeach;?>
</table>

<input class="hidden" name="Response" id="Response" type="text" value="">
<button class="hidden" id="FormSubmitButton">Submit</button>
  
<script>
  // updates the response value when a MC button is pressed
  $(".TestMC").click(function() {
    var clicked = $(this).html();
    var form = $("form");
    
    // record which button was clicked
    $("#Response").prop("value",clicked);
    
    // set RT
    $("#RT").val( COLLECTOR.getRT() );
    form.addClass("submitAfterMinTime");

    // if UserTiming, submit, but only highlight choice otherwise
    if (form.hasClass("UserTiming") && !form.hasClass("WaitingForMinTime")) {
      form.submit();// see common:init "intercept FormSubmitButton"
    } else {
      // set first keypress times
      if (typeof keypress !== 'undefined') {
        $("#RTfirst").val( COLLECTOR.getRT() );
        keypress = true;
      }
      
      // update 'RTlast' time
      $("#RTlast").val( COLLECTOR.getRT() );

      // remove highlighting from all buttons
      $(".TestMC").removeClass("collectorButtonActive");
      
      // add highlighting to clicked button
      $(this).addClass("collectorButtonActive");
    }
  });
</script>

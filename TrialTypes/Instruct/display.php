<?php
/* WARNING!  Any changes you make to this Instruct trial type will not change 
 * the experiment because there is another version of 'Instruct' within the 
 * '/Experiment/TrialTypes/` folder.
 * 
 * This was done as an example of how you can copy trial types from 
 * '/Code/TrialTypes/' into '/Experiment/TrialTypes/' and override the default 
 * trial display. This is a feature meant to keep all of your modification in 
 * one place '/Experiment/'.
 * 
 * You can also make your own new trial types inside of the 
 * '/Experiment/TrialTypes/' folder and will have access to them in your 
 * experiment. The real benefit of developing experiments this way is that when
 * new versions of Collector come out with features you want you will be able to
 * download the new version and copy your /Experiment/ folder into the new 
 * version so you can take advantage of new features without having to 
 * completely port your experiment to the new version.
 */

// use the `Cue` if a valid one is called and there is no `Text` set in the procedure
if (($cue != '') && (trim($text) == '')) {
    $text = $cue;
}

if (!empty($font_size) AND is_numeric($font_size)) {
    $size = $font_size.'%';
    $style = "<style type='text/css' media='screen'>
        body {
            font-size:{$size};
        }
    </style>Test!";
    echo $style;
}
?>
<div class=""><?= $text; ?></div>

<div class="textright">
  <button class="collectorButton collectorAdvance" id="FormSubmitButton">Next</button>
</div>
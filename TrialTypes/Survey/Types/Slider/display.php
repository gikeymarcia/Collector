<?php
    if (!isset($customData[$type])) {
        $customData[$type] = 'initialized';
        echo "
<style>
.$typeClass {
    max-width: 600px;
    min-width: 400px;
    margin: auto;
}
.$typeClass input {
    opacity: 0;
    position: absolute;
}
.$typeClass .inputSlider {
    cursor: pointer;
    margin: 0 auto 40px;
}
.$typeClass .ui-slider-handle {
    cursor: pointer;
}
.$typeClass .scaleDescriptions {
    white-space: nowrap;
    margin: 0 auto 8px;
}
.$typeClass .scaleDescriptions > div {
    display: inline-block;
    width: 50%;
    box-sizing: border-box;
}
.$typeClass .scaleDescriptions > div:first-child {
    text-align: left;
    padding-right: 10%;
}
.$typeClass .scaleDescriptions > div:nth-child(2) {
    text-align: right;
    padding-left: 10%;
}
</style>";
?><script>
    $(document).ready(function() {
        $(".<?= $typeClass ?> > div").each(function() {
            var slider = $(this).find(".inputSlider");
            var thisMin = slider.data("min");
            var thisMax = slider.data("max");
            $(this).find(".inputSlider").slider({
                value:thisMin,
                min:  thisMin,
                max:  thisMax,
                step: 1,
                slide: function(event, ui) {
                    $(ui.handle).show().closest(".sliderArea").find("input").val(ui.value);
                }
            }).find(".ui-slider-handle").hide();
        });
    });
</script><?php
    }
    
    foreach ($surveyRows as $row) {
        $qName = htmlspecialchars($row['Question Name'], ENT_QUOTES);
        $required = isRespRequired($row) ? 'required' : '';
        
        $ans = surveyRangeToArray($row['Answers']);
        $thisMin = null;
        $thisMax = null;
        foreach ($ans as $a) {
            if (!is_numeric($a)) continue;
            if ($thisMin === null) {
                $thisMin = $a;
            } elseif ($thisMin > $a) {
                $thisMin = $a;
            }
            if ($thisMax === null) {
                $thisMax = $a;
            } elseif ($thisMax < $a) {
                $thisMax = $a;
            }
        }
        $leftDesc  = '';
        $rightDesc = '';
        if (isset($row['Settings'])) {
            $settings = explode('|', $row['Settings']);
            foreach ($settings as $setting) {
                $left  = removeLabel($setting, 'leftDescription');
                if ($left  !== false) $leftDesc  = $left;
                $right = removeLabel($setting, 'rightDescription');
                if ($right !== false) $rightDesc = $right;
            }
        }
        if ($thisMin === null) $thisMin = 0;
        if ($thisMax === null) $thisMax = 100;
        echo '<div class="sliderArea">';
        echo     "<p>{$row['Question']}</p>";
        echo     "<div class='scaleDescriptions'>"
           .         "<div>" . $leftDesc  . "</div>"
           .         "<div>" . $rightDesc . "</div>"
           .     "</div>";
        echo     "<input name='$qName' type='text' $required title='Please select a location on this slider.'>";
        echo     "<div class='inputSlider' data-min='$thisMin' data-max='$thisMax'></div>";
        echo '</div>';
    }

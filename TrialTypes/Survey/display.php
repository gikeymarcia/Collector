</form>
<?php
    if (is_numeric($item)) {
        $surveyFile = $_EXPT->get('cue');
    } else {
        $surveyFile = $item;
    }
    
    $trialTypeDir = dirname($trialFiles['display']);
    $surveyDir = $_PATH->get('Common') . '/Surveys';
    
    require $_PATH->get('Shuffle Functions');
    require "$trialTypeDir/SurveyFunctions.php";
    
    // if we haven't yet loaded up this survey, do so now
    // this way, we only shuffle once, and then go from there
    unset($_SESSION['CurrentSurvey']);
    if (!isset($_SESSION['CurrentSurvey'])) {
        $_SESSION['CurrentSurvey'] = readSurveyFile($surveyFile, $surveyDir, $trialTypeDir);
    }
    $survey = $_SESSION['CurrentSurvey'];
?>
<style>
    body {
        background-color: #6B7286;
    }
    body > form {
        padding: 0px;
        width: 100%;
    }
    .SurveyContainer {
        text-align: center;
        padding: 15px;
        background-color: #F5F7FD;
        display: inline-block;
        min-width: 600px;
        border-radius: 5px;
    }
    .SurveyPage {
        display: none;
        text-align: left;
        max-width: 100%;
        width: 100%;
    }
    .SurveyPage.CurrentPage {
         /* display: inline-block; */
    }
    .collectorButton {
        padding: .25em .6em;
        margin-top: 30px;
    }
    html, body {
        height: 100%;
        max-height: 100%;
        width: 100%;
        max-width: 100%;
    }
    html { display: table; }
    body {
        display: table-cell;
        vertical-align: middle;
        text-align: center;
    }
</style>

<div class="SurveyContainer">
<form class="SurveyPage">
<?php
    $allSurveyTypes = getSurveyTypes($trialTypeDir);
    $nextButton = '<div class="textcenter">'
                .     '<button class="collectorButton surveyNextButton" type="submit">Next</button>'
                . '</div>';
                
    $surveyIndex = 0;
    while (isset($survey[$surveyIndex])) {
        $type = $survey[$surveyIndex]['Type'];
        $type = cleanSurveyType($type);
        $surveyRows = array($survey[$surveyIndex]);
        ++$surveyIndex;
        
        while (isset($survey[$surveyIndex])
            && cleanSurveyType($survey[$surveyIndex]['Type']) === $type
        ) {
            $surveyRows[] = $survey[$surveyIndex];
            ++$surveyIndex;
        }
        
        if ($type === 'page_break') {
            echo     $nextButton
               . '</form>'
               . '<form class="SurveyPage">';
        } elseif ($type === 'type_break') {
            continue;
        } else {
            $typeClass = "survey_$type";
            echo "<div class='surveyBlock $typeClass'>";
            require $allSurveyTypes[$type]['display'];
            echo "</div>";
        }
    }
    
    echo $nextButton;
?>
</form>
</div>

<script>
    $(document).ready(function() {
        $("form").attr("autocomplete", "off");
        
        $("form").on("submit", function(e) {
            var thisForm = $(this);
            if (thisForm.hasClass("experimentForm")) {
                $(":input").detach().appendTo(".experimentForm");
            } else {
                e.preventDefault(); // dont submit the individual pages
            
                if (thisForm.hasClass("CurrentPage")) {
                    // work-around for safari not supporting required attribute
                    invalids = $(document.querySelectorAll(".CurrentPage :invalid"));
                    if (invalids.length > 0) {
                        // manually send errors
                        var firstInvalid = invalids.first();
                        firstInvalid.focus();
                        var warning = $("<span>");
                        warning.html("Please fill out this field");
                        warning.css("position", "absolute");
                        warning.css("background-color", "#EEE");
                        warning.css("border", "2px solid #F55");
                        warning.css("padding", "5px");
                        warning.css("border-radius", "5px");
                        warning.css("box-shadow", "0px 0px 9px 3px red");
                        var offset = firstInvalid.offset();
                        warning.css("top",  (offset.top + 30)  + "px");
                        warning.css("left", offset.left + "px");
                        $("body").append(warning);
                        warning.on("mouseenter", function() {
                            $(this).clearQueue().fadeOut(400, function() { $(this).remove(); });
                        });
                        warning.delay(2000).fadeOut(800, function() { $(this).remove(); });
                        return false;
                    }
                    var nextPage = thisForm.next();
                    thisForm.slideUp(200, function() {
                        thisForm.removeClass("CurrentPage");
                        if (nextPage.length === 1) {
                            nextPage.delay(80).slideDown(200, function() {
                                $(this).addClass("CurrentPage");
                            });
                        } else {
                            $(".SurveyContainer").hide();
                            $(".experimentForm").submit();
                        }
                    });
                }
            }
        });
    });
    $(window).on("load", function() {
        $(".SurveyPage:first").slideDown(200, function() {
            $(this).addClass("CurrentPage");
        });
    });
</script>

<form class="extraInputsForm">

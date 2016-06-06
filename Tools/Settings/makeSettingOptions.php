<?php
adminOnly();
$form = " form='toMod' ";

// functions to make all the setting inputs
function setting_string(Collector\Settings $settingClass, $name, $label, $tips = null)
{
    $current = $settingClass->$name;

    if(isset($tips[$name])) {
        $clean = htmlentities($tips[$name]);
        $title = 'data-title="' . $clean . '"';
    } else {
        $title = "";
    }
    echo
        "<label class='stringSetting'>
            <span $title class='tooltip'>$label</span>
            <input form='toMod' type='textbox' name='$name'
            value='$current'/>
        </label>";
}
function setting_bool(Collector\Settings $settingClass, $name, $label, $tips = null)
{
    $current = $settingClass->$name;
    $tChecked = ($current) ? ' checked="checked"' : '';
    $fChecked = ($current) ? '' : ' checked="checked"';

    if(isset($tips[$name])) {
        $clean = htmlentities($tips[$name]);
        $title = 'data-title="' . $clean . '"';
    } else {
        $title = "";
    }

    echo
    "<div class='boolSetting'>
        <span $title class='tooltip'>$label</span>
        <label>On<input type='radio' name='{$name}' value='true'  form='toMod' $tChecked></label>
        <label>Off<input type='radio' name='{$name}' value='false' form='toMod' $fChecked></label>
    </div>";
}

$tooltips = array(
    "force_experiment"    => "",
    "experimenter_email"  => "The email that will be shown to participants when a participant finishes the experiment.",
    "check_all_files"     => "When logging in should we check all that all experiment conditions are valid?",
    "check_current_files" => "Should we check the current condition is correct when logging in?",
    "debug_name"          => "Start login name with this code to login as debug. For example, if debug name is 'Mikey' then logging in as 'MikeyGarcia' would be in debug mode.",
    "debug_time"          => "When logged in with debug mode this is the timing that will be set for every trial.",
    "trial_diagnostics"   => "Shows information beneath each trial that explains the state of the program.",
    "stop_at_login"       => "",
    "stop_for_errors"     => "",

    "experiment_name"         => "Experiment name will be recorded with every line of data collected. Very helpful to set an experiment name so your data will forever be labeled with this experiment name.",
    "debug_mode"              => "Turn to on and all logins will be in debug mode.",
    "lenient_criteria"        => "% match required between a response and 'answer' to score as correct by 'lenientAcc' standards",
    "welcome"                 => "Message shown at the top of the page where participants log in.",
    "exp_description"         => "Small description of that task given to participants on the page where they log in to the experiment.",
    "ask_for_login"           => "Which unique identifier to ask participants for.  It will also be shown in grey text within the textbox before participants begin typing their identifier.",
    "show_condition_selector" => "Turn On/Off the dropbown menu for choosing your condition.",
    "use_condition_names"     => "Either show condition names (ON) or show condition position (OFF)",
    "show_condition_info"     => "When a participant hovers over a condition should we pop up the name of the procedure/stimuli files?",
    "hide_flagged_conditions" => "Show conditions that are turned off. To turn off a condition begin it's 'Description' with an * character.",
    "verification"            => "If a verification code is set then it will be shown to participants when they get to 'Done.php'. If you do not want to give a verification code then leave this cell blank",
    "check_elig"              => "",
    "blacklist"               => "",
    "whitelist"               => "",
);
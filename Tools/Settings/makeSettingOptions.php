<?php
adminOnly();
$form = " form='toMod' ";

// functions to make all the setting inputs
function setting_string(Settings $settingClass, $name, $label, $tips = null)
{
    $current = $settingClass->$name;

    if(isset($tips[$name])) {
        $clean = htmlentities($tips[$name]);
        $title = 'title="' . $clean . '"';
    } else {
        $title = "";
    }
    echo
        "<label class='stringSetting'>
            <span class='settingDisplay' $title>$label</span>
            <input form='toMod' type='textbox' name='$name'
            value='$current'/>
        </label>";
}
function setting_bool(Settings $settingClass, $name, $label, $tips = null)
{
    $current = $settingClass->$name;
    $tChecked = ($current) ? ' checked="checked"' : '';
    $fChecked = ($current) ? '' : ' checked="checked"';

    if(isset($tips[$name])) {
        $clean = htmlentities($tips[$name]);
        $title = "title='{$clean}'";
        $title = 'title="' . $clean . '"';
    } else {
        $title = "";
    }

    echo 
    "<div class='boolSetting'>
        <span class='settingDisplay' $title>$label</span>
        <label>On <input type='radio' name='{$name}' value='true'  form='toMod' $tChecked></label>
        <label>Off<input type='radio' name='{$name}' value='false' form='toMod' $fChecked></label>
    </div>";
}

$tooltips = array(
    "force_experiment"    => "",
    "experimenter_email"  => "The email that will be shown to participants when a participant finishes the experiment.",
    "check_all_files"     => "When logging in should we check all that all experiment conditions are valid?",
    "check_current_files" => "Should we check the current condition is correct when logging in?",
    "debug_name"          => "Start login name with this code to login as debug. For example, if debug name is 'Mikey' then logging in as 'MikeyGarcia' would be in debug mode.",
    "debug_time"          => "",
    "trial_diagnostics"   => "",
    "stop_at_login"       => "",
    "stop_for_errors"     => "",

    "experiment_name"         => "",
    "debug_mode"              => "Turn to on and all logins will be in debug mode.",
    "lenient_criteria"        => "% match required between a response and 'answer' to score as correct by 'lenientAcc' standards",
    "welcome"                 => "",
    "exp_description"         => "",
    "ask_for_login"           => "",
    "show_condition_selector" => "",
    "use_condition_names"     => "",
    "show_condition_info"     => "",
    "hide_flagged_conditions" => "",
    "verification"            => "",
    "check_elig"              => "",
    "blacklist"               => "",
    "whitelist"               => "",
);
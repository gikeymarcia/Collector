<?php
    adminOnly();
    $check_these_settings = array(
        "force_experiment",
        "experimenter_email",
        "check_all_files",
        "check_current_files",
        "debug_name",
        "debug_time",
        "trial_diagnostics",
        "stop_at_login",
        "stop_for_errors",

        "experiment_name",
        "debug_mode",
        "lenient_criteria",
        "welcome",
        "exp_description",
        "ask_for_login",
        "show_condition_selector",
        "use_condition_names",
        "show_condition_info",
        "hide_flagged_conditions",
        "verification",
        "check_elig",
        "blacklist",
        "whitelist"
    );

    $found = false;
    foreach ($check_these_settings as $var) {
        if (isset($_POST[$var])) {
            $found = true;
            $value = $_POST[$var];
            if ($value != $yourSettings->$var) {
                $yourSettings->set($var, $value);
            }
        }
    }

    if ($found === true) {
        $yourSettings->writeSettings();
        header('Location: ./');
    }
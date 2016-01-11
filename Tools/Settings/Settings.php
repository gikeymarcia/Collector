<?php
// access control
adminOnly();

// make a temporary pathfinder to get us to the right settings
$tempPath = new Pathfinder($_DATA['path']);
$exps = array_flip(getCollectorExperiments());

// change experiment if one is selected
if (isset($_GET['Exp'])
    && isset($exps[$_GET['Exp']])
) {
    $_DATA['exp'] = $_GET['Exp'];
    $tempPath->setDefault('Current Experiment', $_GET['Exp']);
}
$_DATA['exp'] = (isset($_DATA['exp'])) ? $_DATA['exp'] : "";
if (!empty($_DATA['exp'])) {
    $tempPath->setDefault('Current Experiment', $_DATA['exp']);
}

// make a new instance of the settings class
$yourSettings = new Settings(
    $tempPath->get("Common Settings"),
    $tempPath->get("Experiment Settings"),
    $tempPath->get("Password")
);

require 'saveSettings.php';
require 'makeSettingOptions.php';

?>
<link rel="stylesheet" type="text/css" href="Settings/styles.css">

<!-- This is where you choose what to edit -->
<div class="toolWidth expSelect">
    <h3>Which experiment settings would you like to edit?</h3>
    <select class="collectorInput" name="Exp" form="chooseExp">
        <option id="selectLabel">Choose your settings</option>
        <?php
            foreach ($exps as $name => $i) {
                $selected = ($_DATA["exp"] == $name) ? " selected" : "";
                echo "<option value='$name' $selected>$name</option>";
            }
        ?>
    </select>
</div>

<?php
    if (!empty($_DATA['exp'])) {
        $name = $_DATA['exp'];
        echo '<div class="exp settings toolWidth">';
            echo "<h3 class='type'>Experiment Settings ($name)</h3>";
            setting_string($yourSettings, "experiment_name", "Experiment Name", $tooltips);
            setting_bool  ($yourSettings, "debug_mode", "Debug Mode", $tooltips);
            setting_string($yourSettings, "lenient_criteria", "Lenient Criteria", $tooltips);

            echo "<div class='subgroup'><h4>Welcome Screen Settings</h4>";
            setting_string($yourSettings, "welcome", "Welcome Message", $tooltips);
            setting_string($yourSettings, "exp_description", "Welcome Description", $tooltips);
            setting_string($yourSettings, "ask_for_login", "Asked for Login", $tooltips);
            setting_bool($yourSettings, "show_condition_selector", "Conditon Selector", $tooltips);setting_bool($yourSettings, "use_condition_names", "Show Condition Names", $tooltips);
            setting_bool($yourSettings, "show_condition_info", "Show Condition Info", $tooltips);
            setting_bool($yourSettings, "hide_flagged_conditions", "Hide Flagged Conditions", $tooltips);
            echo "</div>";

            echo "<div class='subgroup'><h4>Done Screen</h4>";
            setting_string($yourSettings, "verification", "Verification Code", $tooltips);
            echo "</div>";

        echo '</div>';
    }
?>

<!-- This is where you edit common experiment settings -->
<div class="common settings toolWidth">
    <h3 class='type'>Common Settings (Shared Across Experiments)</h3>
    <?php
        // echo ""
        setting_string($yourSettings, "experimenter_email" , "Experimenter Email" , $tooltips);
        setting_bool  ($yourSettings, "check_all_files"    , "Check All Files"    , $tooltips);
        setting_bool  ($yourSettings, "check_current_files", "Check Current Files", $tooltips);
        setting_string($yourSettings, "debug_name", "Debug Name" , $tooltips);
        setting_string($yourSettings, "debug_time", "Debug Time" , $tooltips);
        setting_bool  ($yourSettings, "trial_diagnostics", "Show Trial Diagnostics", $tooltips);
    ?>
</div>

<button id="saveSettings" form="toMod">Save Changes</button>

<form id="toMod" method="post" action=""></form>
<form id="chooseExp" method="get" action=""></form>

<script type="text/javascript">
    // hide the default option when showing the dropdown box
    $(".expSelect select").focusin(function(event) {
        $("#selectLabel").css("display","none");
    });

    // submit as soon as an experiment is selected
    $(".expSelect select").change(function(){
        $("#chooseExp").submit();
    });

    // click header to toggle experiment/common settings
    $(".type").click(function() {
        $(this).siblings().toggle(350);
    });
    // click subheading to toggle a group of settings
    $(".subgroup h4").click(function(event) {
        $(this).siblings().toggle(350);
    });
</script>
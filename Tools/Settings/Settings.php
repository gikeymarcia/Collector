<?php
// access control
require_once 'loginFunctions.php';      // This is so we can run the function below
adminOnly();

$exps = getCollectorExperiments();
$yourSettings = new Settings(
    $_PATH->get("Common Settings"),
    $_PATH->get("Experiment Settings"),
    $_PATH->get("Password")
);

require 'RecordPosts.php';

// functions to make all the setting inputs
function setting_string(Settings $settingClass, $name, $label)
{
    $current = $settingClass->$name;
    echo
        "<label>
            <span>$label</span>
            <input form='toMod' type='textbox' name='$name'
            value='$current'/>
        </label>";
}

?>

<div class="toolWidth expSelect">
    <h3>Which settings would you like to edit?</h3>
    <select class="collectorInput">
        <option select id="selectLabel">Choose your settings</option>
        <?php
            foreach ($exps as $i => $name) {
                echo "<option value='$name'>$name</option>";
            }
        ?>
    </select>
</div>
<div class="common toolWidth">
    <h3>Common Settings</h3>
    <?php
        setting_string($yourSettings, "experimenter_email", "Experimenter Email")
    ?>
</div>

<form id="toMod" method="post" action="">
</form>

<style type="text/css">
    h4 {
        display: inline-block;
    }
    .expSelect h4 {
        padding: .3em 2em .3em 0em;
    }
    .expSelect * {
        float: left;
    }
    .common {
        text-align: left;
        margin-top: 1em;
        line-height: 1.5em;
        background-color: #F1F1F1;
        border-radius: 5px;
        padding: 1em;
    }

    .common input {
        width: 500px;
        padding: 0px 0px 0px .25em;
        vertical-align: bottom;
        /*line-height: 1.5em;*/
    }
    .common span {
        /*line-height: 1.5em;*/
        display: inline-block;
        padding-top: 5px;
    }
</style>
<script type="text/javascript">
    // hide the default option when showing the dropdown box
    $(".expSelect").focusin(function(event) {
        $("#selectLabel").css("display","none");
    });
</script>
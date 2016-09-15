<?php
    require __DIR__ . '/initiateCollector.php';

    $user_data = $_FILES->read('User Data');
    $responses = $_FILES->read('Output');

    if ($responses === null) {
        $responses = array();
    }

    $user_data['responses'] = $responses;
    $added_scripts = array($_FILES->get_path("Experiment JS"));
    require $_FILES->get_path('Header');


    $added_scripts = array($_FILES->get_path("Trial JS"));
    ob_start();
    require $_FILES->get_path('Header');
    require $_FILES->get_path('Trial Content');
    require $_FILES->get_path('Footer');
    $trial_page = ob_get_clean();

    $trial_type_data = get_all_trial_type_data($_FILES);
?>

<style>
    #ExperimentContainer, #ExperimentContainer > iframe {
        width: 100%;
        height: 100%;
        margin: 0;
        padding: 0;
        display: block;
    }
</style>

<div id="ExperimentContainer"></div>

<script>
var User_Data = {
    Username:   "<?= $_SESSION['Username'] ?>",
    ID:         "<?= $_SESSION['ID'] ?>",
    Debug_Mode: <?= $_SESSION['Debug Mode'] ? "true" : "false" ?>,
    Experiment_Data: <?= json_encode($user_data) ?>
}
var trial_page  = <?= json_encode($trial_page) ?>;
var trial_types = <?= json_encode($trial_type_data) ?>;

var Collector_Experiment = new Experiment(
    User_Data.Experiment_Data,
    $("#ExperimentContainer"),
    trial_page,
    trial_types,
    $_FILES->get_path("Media Dir")
);

</script>

<?php
    require $_FILES->get_path('Footer');

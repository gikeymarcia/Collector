<?php
    require __DIR__ . '/initiateCollector.php';

    $added_scripts = array($FILE_SYS->get_path("Experiment JS"));     // header.php is loading these
    output_page_header($FILE_SYS, 'Collector Experiment', $added_scripts);

    // load data required for the experiment
    $user_data       = load_user_data($FILE_SYS);
    $trial_page      = get_trial_page($FILE_SYS);
    $trial_type_data = get_all_trial_type_data($FILE_SYS);

    
    //setting up varialbes new Experiment.js needs
    $media_path = $FILE_SYS->get_path("Media Dir");
    $root_path  = $FILE_SYS->get_path("Root");
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
    Exp_Name:   "<?= $FILE_SYS->get_default('Current Experiment') ?>",
    Debug_Mode: <?= $_SESSION['Debug Mode'] ? "true" : "false" ?>,
    Experiment_Data: <?= json_encode($user_data) ?>
}

var trial_page   = <?= json_encode($trial_page) ?>;
var trial_types  = <?= json_encode($trial_type_data) ?>;
var server_paths = {
    media_path: '<?= $media_path ?>',
    root_path:  '<?= $root_path ?>',
};



var Collector_Experiment = new Experiment(
    User_Data.Experiment_Data,
    $("#ExperimentContainer"),
    trial_page,
    trial_types,
    server_paths
);

Collector_Experiment.run_trial();

</script>

<?php
    output_page_footer($FILE_SYS);

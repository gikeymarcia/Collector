<?php
    require __DIR__ . '/initiateCollector.php';

    $user_data = $_FILES->read('User Data');
    $responses = $_FILES->read('Output');

    if ($responses === null) {
        $responses = array();
    }

    $user_data['Responses'] = $responses;
    $added_scripts = array($_FILES->get_path("Experiment JS"));
    require $_FILES->get_path('Header');
?>

<script>

var User_Data = {
    Username:   "<?= $_SESSION['Username'] ?>",
    ID:         "<?= $_SESSION['ID'] ?>",
    Debug_Mode: <?= $_SESSION['Debug Mode'] ? "true" : "false" ?>,
    Experiment_Data: <?= json_encode($user_data) ?>
}

var Collector_Experiment = new Experiment(User_Data.Experiment_Data);
</script>

You've made it!

<?php
    require $_FILES->get_path('Footer');

<?php
    require __DIR__ . '/initiateCollector.php';
    
    $user_data = $_FILES->read('User Data');
    $responses = $_FILES->read('Output');
    
    if ($responses === null) {
        $responses = array();
    }
    
    $user_data['Responses'] = $responses;
    
    require $_FILES->get_path('Header');
?>

<script>
var User_Data = {
    Username:   "<?= $_SESSION['Username'] ?>",
    ID:         "<?= $_SESSION['ID'] ?>",
    Debug_Mode: <?= $_SESSION['Debug Mode'] ? "true" : "false" ?>,
    Experiment: <?= json_encode($user_data) ?>
}
</script>

You've made it!

<?php
    require $_FILES->get_path('Footer');

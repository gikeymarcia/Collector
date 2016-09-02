<?php
/*  Collector
    A program for running experiments on the web
 */

require __DIR__ . '/initiateCollector.php';

$input_username  = filter_input(INPUT_GET, 'username');
$input_exp       = filter_input(INPUT_GET, 'experiment');
$input_condition = filter_input(INPUT_GET, 'condition');

// wipe out session and replace it with new login info
$_SESSION = Login::run($input_username);

// restore FileSystem alias
$_FILES = $_SESSION['_FILES'];

$_FILES->set_default('Current Experiment', $input_exp);

// if data doesn't exist for this user, create it
if ($_FILES->read('User Data') === null) {
    $user_data = create_experiment($_FILES, $input_condition);
    $_FILES->overwrite('User Data', $user_data);
}

header('Location: ' . $_FILES->get_path('Experiment Page'));
exit;

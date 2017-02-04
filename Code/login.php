<?php
/*  Collector
    A program for running experiments on the web
 */

require __DIR__ . '/initiateCollector.php';

$input_username  = filter_input(INPUT_GET, 'Username');
$input_exp       = filter_input(INPUT_GET, 'Experiment');
$input_condition = filter_input(INPUT_GET, 'Condition');

// wipe out session and replace it with new login info
$_SESSION = Login::run($input_username, $_SETTINGS);

// restore FileSystem alias
$FILE_SYS = $_SESSION['_FILES'];

$FILE_SYS->set_default('Current Experiment', $input_exp);

// if data doesn't exist for this user, create it
if ($FILE_SYS->read('User Data') === null) {
    $user_data = create_experiment($FILE_SYS, $input_condition);
    save_user_data($user_data, $FILE_SYS);
}

header('Location: ' . $FILE_SYS->get_path('Experiment Page'));
exit;

<?php
$classes = array(
    'Autoloader',
    'Pathfinder',
    'Helpers',
    'MiniDb',
    'Experiment',
    'ExperimentFactory',
    'Response',
    'TrialSettings',
    'Trial',
    'MainTrial',
    'PostTrial',
    'Validator',
    'ValidatorFactory',
);
foreach ($classes as $class) {
    require_once dirname(__DIR__) . '/classes/' . $class . '.php';
}

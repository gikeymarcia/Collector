<?php
$classes = array(
    'Autoloader',
    'Pathfinder',
    'Helpers',
    'MiniDb',
    'Experiment',
    'ExperimentFactory',
    'Response',
    'Trial',
    'MainTrial',
    'PostTrial',
    'Validator',
    'ValidatorFactory',
);
foreach ($classes as $class) {
    require_once dirname(__DIR__) . '/classes/' . $class . '.php';
}

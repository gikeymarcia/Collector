<?php
return function($row) {
    $options = surveyRangeToArray($row['Answers']);
    $qName = $row['Question Name'];
    $responses = array();
    foreach ($options as $opt) {
        $name = $qName . '_' . $opt;
        if (isset($_POST[$name])) $responses[] = $_POST[$name];
    }
    return $responses;
};

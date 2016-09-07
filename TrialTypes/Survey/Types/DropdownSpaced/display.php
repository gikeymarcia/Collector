<?php
    if (!isset($customData[$type])) {
        $customData[$type] = 'initialized';
        echo "
<style>
.$typeClass > div {
    display: table;
    margin: 20px 0;
    width: 100%;
    outline: 1px solid #CCC;
}
.$typeClass > div > label {
    display: table-row;
}
.$typeClass > div > label:nth-child(odd) {
    background-color: #EEE;
}
.$typeClass > div > label:nth-child(even) {
    background-color: #FFF;
}

.$typeClass > div > label > div {
    display: table-cell;
    text-align: left;
    vertical-align: bottom;
    padding: 2px 4px;
}
.$typeClass > div > label > div:nth-child(1) {
    padding-bottom: 6px;
}
.$typeClass > div > label > div:nth-child(2) {
    text-align: right;
    width: 200px;
}
.$typeClass > div > label > div:nth-child(2) select {
    min-width: 190px;
}
</style>";
    }

    foreach ($surveyRows as $row) {
    echo '<div>';
    $qName = htmlspecialchars($row['Question Name'], ENT_QUOTES);
    $ans = '<option>' . implode('</option><option>', surveyRangeToArray($row['Answers'])) . '</option>';
    $required = isRespRequired($row) ? 'required' : '';
    echo "<label>"
       .     "<div>{$row['Question']}</div>"
       .     "<div>"
       .         "<select name='$qName' $required>"
       .             "<option selected hidden></option>"
       .             $ans
       .         "</select>"
       .     "</div>"
       . "</label>";
    echo '</div>';
    }

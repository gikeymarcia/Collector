<?php
    if (!isset($customData[$type])) {
        $customData[$type] = 'initialized';
        echo "
<style>
.$typeClass > div {
    margin: 20px auto 30px;
    border: 1px solid #BBB;
    padding: 5px;
    background-color: #FFF;
}
.$typeClass > div > div {
    margin: 5px 0 15px;
}
.$typeClass textarea {
    width: 100%;
    box-sizing: border-box;
}
</style>";
    }

    foreach ($surveyRows as $row) {
        $qName = htmlspecialchars($row['Question Name'], ENT_QUOTES);
        $required = isRespRequired($row) ? 'required' : '';
        echo '<div>';
        echo     "<div>{$row['Question']}</div>";
        echo     "<textarea name='$qName' rows='6' $required></textarea>";
        echo '</div>';
    }

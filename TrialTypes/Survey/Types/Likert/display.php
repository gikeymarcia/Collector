<?php
    if (!isset($customData[$type])) {
        $customData[$type] = 'initialized';
        echo "
<style>
.$typeClass .LikertTable {
    display: table;
    margin: 30px 0;
    width: 100%;
    border: 1px solid #AAA;
}
.$typeClass .LikertTable > div {
    display: table-row;
}
.$typeClass .LikertTable > div:nth-child(odd) {
    background-color: #EEE;
}
.$typeClass .LikertTable > div:nth-child(even) {
    background-color: #FFF;
}

.$typeClass .LikertTable > div > * {
    display: table-cell;
    text-align: center;
    vertical-align: middle;
    padding: 2px 4px;
    position: relative;
}
.$typeClass .LikertTable > div > div {
    padding-right: 30px;
    text-align: left;
}

.$typeClass .LikertTable input {
    position: absolute;
    left: 50%;
    top: 50%;
    opacity: 0;
}

.$typeClass .LikertTable span {
    border-style: solid;
    border-width: 2px;
    border-radius: 25px;
    display: inline-block;
    padding: 4px 8px;
    width: 100%;
    box-sizing: border-box;
}
.$typeClass .LikertTable > div:nth-child(odd) span {
    border-color: #EEE;
}
.$typeClass .LikertTable > div:nth-child(even) span {
    border-color: #FFF;
}

.$typeClass .LikertTable > div > label:hover span,
.$typeClass .LikertTable > div   input:focus + span {
    border-color: #0F0;
}
.$typeClass .LikertTable > div input:checked + span {
    border-color: #0A0;
}
.$typeClass .LikertTable > div > label:hover input:checked + span,
.$typeClass .LikertTable > div > label > input:focus + span {
    border-color: #0D0;
}
</style>";
    }
    
    $i = 0;
    
    while (isset($surveyRows[$i])) {
        $currentAnswersCount = count(rangeToArray($surveyRows[$i]['Answers']));
        $currentLikertRows = array();
        
        while (isset($surveyRows[$i]) 
            && count(rangeToArray($surveyRows[$i]['Answers'])) === $currentAnswersCount
        ) {
            $currentLikertRows[] = $surveyRows[$i];
            ++$i;
        }
        
        echo '<div class="LikertTable">';
        foreach ($currentLikertRows as $likertRow) {
            $currentAnswers = $likertRow['Answers'];
            $currentAnswers = rangeToArray($currentAnswers);
            $name = $likertRow['Question Name'];
            echo '<div class="LikertRow">'
               .     '<div>' . $likertRow['Question'] . '</div>';
            foreach ($currentAnswers as $currAns) {
                echo "<label>"
                   .     "<input name='$name' type='radio' value='$currAns' required>"
                   .     "<span>$currAns</span>"
                   . "</label>";
            }
            echo '</div>';
        }
        echo '</div>';
    }

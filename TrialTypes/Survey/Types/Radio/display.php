<?php
    if (!isset($customData[$type])) {
        $customData[$type] = 'initialized';
        echo "
<style>
.$typeClass {
    max-width: 900px;
    margin: auto;
}
.$typeClass > div {
    margin: 30px auto 40px;
    border: 1px solid #BBB;
    padding: 10px 0;
    background-color: #FFF;
}
.$typeClass > div > div:first-child {
    padding: 0 10px 10px;
}
.$typeClass .respArea {
    display: table;
    width: 100%;
}
.$typeClass .respArea label {
    display: table-row;
    height: 30px;
}
.$typeClass .respArea label span {
    display: table-cell;
    text-align: left;
    vertical-align: middle;
    padding: 5px;
}
.$typeClass .respArea label span:first-child {
    padding-left: 20px;
    width: 10px;
}

.$typeClass .respArea label:hover {
    background-color: #AFD;
}
.$typeClass .respArea input {
    margin: 0;
}
</style>";
    }
    
    foreach ($surveyRows as $row) {
        echo '<div>';
        $options = Collector\Helpers::rangeToArray($row['Answers']);
        $qName = $row['Question Name'];
        echo     "<div>{$row['Question']}</div>";
        
        echo     '<div class="respArea">';
        foreach ($options as $opt) {
            echo     "<label>"
               .         "<span><input type='radio' name='$qName' required></span>"
               .         "<span>$opt</span>"
               .     "</label>";
        }
        echo     '</div>';
        echo '</div>';
    }

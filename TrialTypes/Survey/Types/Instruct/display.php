<?php
    if (!isset($customData[$type])) {
        $customData[$type] = 'initialized';
        echo "
<style>
.$typeClass {
    max-width: 900px;
    margin: 20px auto;
}
</style>";
    }
    
    foreach ($surveyRows as $row) {
        echo "<p>{$row['Question']}</p>";
    }

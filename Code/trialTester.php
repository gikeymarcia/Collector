<?php
    require 'initiateCollector.php';
    
    include 'Header.php';
    
    $_SESSION = array();
    
    $trialTypes = require 'scanTrialTypes.php';
    $trialTypeOptions = '<option>' . implode('</option><option>', array_keys($trialTypes)) . '</option>';
    
?>
<div id="allContain">
<style>
    iframe          {   border: 0px solid #000; width: 100%; border-top-width: 1px; min-height: 100%;  }
    .trialOption    {   text-align: center; margin: 15px;   display: inline-block;   }
    #allContain     {   height: 100%;   }
    
    .expFile        {   width: 49%; display: inline-block;   padding: 0px 100px 0 0; vertical-align: top;  }
    
    .tableContainer {   display: inline-block;   max-width: 100%;    overflow: auto;  }
    .blockContainer {   display: inline-block;  text-align: left;   max-width: 100%;    white-space: nowrap;  }
    .newColumnBtn   {   vertical-align: top;    }
    .expSettings    {   text-align: center; }
    .expSettings td, .expSettings th    {   min-width: 100px;   border: 1px solid #ccc; vertical-align: middle;   }
    .expSettings th {   font-weight: bold;  }
</style>
<script>
    $(window).load(function(){
        $("input[name='reloadFrame']").on("click", function(){
            if ($("select[name='trialType']").val() === null) { return false; }
            var src = "trialLoader.php?";
            src += encodeURIComponent("Procedure_Trial Type") + "=" + encodeURIComponent($("select[name='trialType']").val());
            src += "&" + convertTableToGet( $("#Stimuli") ) + "&" + convertTableToGet( $("#Procedure") );
            $("iframe").attr("src", src);
        });
        $(".expSettings").find("td, th").prop("contenteditable", true);
        $(".newColumnBtn").on("click", function(){
            var targetTable = $(this).closest(".blockContainer").find(".expSettings");
            $(targetTable).find("thead tr").append('<th contenteditable="true"></th>');
            $(targetTable).find("tbody tr").append('<td contenteditable="true"></td>');
        });
        $(".newRowBtn").on("click", function(){
            var targetTable = $(this).closest(".blockContainer").find(".expSettings");
            var columns = $(targetTable).find("th").length;
            var newContent = "<tr>";
            for( var i=0; i<columns; ++i ) {
                newContent += '<td contenteditable="true"></td>';
            }
            newContent += "</tr>";
            $(targetTable).find("tbody").append(newContent);
        });
    });
    function convertTableToGet(table) {
        var i, header, contents, j, rows;
        var columns = $(table).find("th").length;
        var tableID = $(table).attr("id");
        var bad1 = encodeURIComponent(tableID + "_Trial Type");
        var allContents = "";
        for (i=1; i<=columns; ++i) {
            header = tableID + "_" + $(table).find("thead tr th:nth-child("+i+")").html();
            header = encodeURIComponent(header);
            if (header === bad1) { continue; }
            rows = $(table).find("tbody tr").length;
            contents = "";
            for (j=1; j<=rows; ++j) {
                if (j>1) { contents += '|'; }
                contents += $(table).find("tbody tr:nth-child("+j+") td:nth-child("+i+")").html();
            }
            contents = encodeURIComponent(contents);
            if (allContents !== "") { allContents += "&"; }
            allContents += header + "=" + contents;
        }
        return allContents;
    }
</script>
<div class="textcenter">
    <h2>Welcome to the trial type tester!</h2>
    <div>Please choose the settings you want. <input type="button" name="reloadFrame" value="Go!" /></div>
    <div>Trial Type: 
        <select name="trialType">
            <option hidden disabled selected></option>
            <?= $trialTypeOptions ?>
        </select>
    </div>
    <div class="expFile">
        <h3>Stimuli File</h3>
        <div class="blockContainer">
            <div class="tableContainer">
                <table class="expSettings" id="Stimuli">
                    <thead>
                        <tr><th>Cue</th><th>Answer</th></tr>
                    </thead>
                    <tbody>
                        <tr><td></td><td></td></tr>
                    </tbody>
                </table>
            </div>
            <button type="button" class="newColumnBtn">New Column</button>
            <br>
            <button type="button" class="newRowBtn">New Row</button>
        </div>
    </div>
    <div class="expFile">
        <h3>Procedure File</h3>
        <div class="blockContainer">
            <div class="tableContainer">
                <table class="expSettings" id="Procedure">
                    <thead>
                        <tr><th>Text</th><th>Settings</th></tr>
                    </thead>
                    <tbody>
                        <tr><td></td><td></td></tr>
                    </tbody>
                </table>
            </div>
            <button type="button" class="newColumnBtn">New Column</button>
            <br>
            <button type="button" class="newRowBtn" disabled="disabled">New Row</button>
        </div>
    </div>
    <iframe src="trialLoader.php"></iframe>
</div>
<?php 
    
    include 'Footer.php';

?>
</div>

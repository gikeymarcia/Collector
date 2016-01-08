</div></div></div>
<script>
    $(".content-center-outer:first").remove();
</script>
<?php
    // above, I was getting rid of some extra divs created by SnippetTester, that I dont need here
    
    
    function convertRawCsvToAssocArray(array $array) {
        $headers = array_shift($array);
        $output = array();
        foreach ($array as $line) {
            $row = array();
            foreach ($headers as $i => $header) {
                $header = (string) $header;
                // skip empty headers
                if ($header === '') continue;
                $row[$header] = $line[$i];
            }
            $isEmptyRow = true;
            foreach ($row as $cell) {
                if ($cell !== '' AND $cell !== null) {
                    $isEmptyRow = false;
                    break;
                }
            }
            // skip empty rows
            if ($isEmptyRow) continue;
            
            $output[] = $row;
        }
        return $output;
    }
    
    
    
    if (isset($_POST['stimTableInput'])) {
        $stimuli   = json_decode($_POST['stimTableInput']);
        $stimuli   = convertRawCsvToAssocArray($stimuli);
    } else {
        $stimuli = array (
            array (
                'Cue' => 'A', 'Answer' => 'Apple', 'groups' => 'all' ,   'Shuffle' => 'off'
            ),
            array (
                'Cue' => 'B', 'Answer' => 'Banana', 'groups' => 'all',   'Shuffle' => 'off'
            ),
            array (
                'Cue' => 'C', 'Answer' => 'Coconut', 'groups' => 'all',  'Shuffle' => 'off'
            ),
        );
        
        
    }
    
    $trialTypes = array('Instruct', 'Study', 'Copy', 'Test', 'FreeRecall', 'JOL');
    array_unshift($trialTypes, 'Nothing');
    $trialTypesJSarray = '["' . implode('", "', $trialTypes) . '"]';
    
    $stimData = array(array_keys(reset($stimuli)));
    foreach ($stimuli as $row) {
        $stimData[] = array_values($row);
    }
    $stimData = json_encode($stimData);
    
        
    
?>


<link rel="stylesheet" href="handsontables/handsontables.full.css">
<script src="handsontables/handsontables.full.js"></script>
<style>
    body { color: black; background-color: white; }
    #header { font-size: 180%; text-align: center; margin: 10px 0 40px; }
    form {
        text-align: center;
        margin: 30px;
    }
    .tableArea {
        display: inline-block;
        width: 50%;
        box-sizing: border-box;
        padding: 10px 30px;
        vertical-align: top;
    }
	textarea { border: none; }
</style>
<h1><textarea name="eventName" style="color:blue; text-align:center" rows="1">CueTrial1</textarea></h1><!--note that this isn't collector blue! !-->
<form method="post">
 <div>   
<?php
    // doing this in PHP to prevent whitespace
    echo '<div id="stimArea" class="tableArea">'
       .         '<div id="stimTable" class="expTable"></div>'
       .     '</div>'
       . '</div>';
?>
</div>
<input type="hidden" name="stimTableInput">
    <input type="hidden" name="procTableInput">
    <button class="collectorButton" id="submitButton" type="button">Submit</button>
    <button class="collectorButton" id="resetButton"  type="button">Reset</button>
</form>
<script>
    var stimTable;
    var trialTypes = <?= $trialTypesJSarray ?>;
    function isTrialTypeHeader(colHeader) {
        var isTrialTypeCol = false;
        
        if (colHeader === 'Trial Type') isTrialTypeCol = true;
        
        if (   colHeader.substr(0, 5) === 'Post '
            && colHeader.substr(-11)  === ' Trial Type'
        ) {
            postN = colHeader.substr(5, colHeader.length - 16);
            postN = parseInt(postN);
            if (!isNaN(postN) && postN != 0) {
                isTrialTypeCol = true;
            }
        }
        
        return isTrialTypeCol;
    }
    function isNumericHeader(colHeader) {
        var isNum = false;
        if (colHeader.substr(-4) === 'Item')     isNum = true;
        if (colHeader.substr(-8) === 'Max Time') isNum = true;
        if (colHeader.substr(-8) === 'Min Time') isNum = true;
        return isNum;
    }
    function isShuffleHeader(colHeader) {
        var isShuffle = false;
        if (colHeader.indexOf('Shuffle') !== -1) isShuffle = true;
        return isShuffle;
    }
    function firstRowRenderer(instance, td, row, col, prop, value, cellProperties) {
        Handsontable.renderers.TextRenderer.apply(this, arguments);
        td.style.fontWeight = 'bold';
        if (value == '') {
            $(td).addClass("htInvalid");
        }
    }
    function numericRenderer(instance, td, row, col, prop, value, cellProperties) {
        Handsontable.renderers.TextRenderer.apply(this, arguments);
        if (isNaN(value) || value === '') {
            td.style.background = '#D8F9FF';
        }
    }
    function shuffleRenderer(instance, td, row, col, prop, value, cellProperties) {
        Handsontable.renderers.TextRenderer.apply(this, arguments);
        if (value === '') {
            td.style.background = '#DDD';
        } else if (
            typeof value === 'string' 
         && (   value.indexOf('#') !== -1
             || value.toLowerCase() === 'off'
            )
        ) {
            td.style.background = '#DDD';
        }
    }
    function trialTypesRenderer(instance, td, row, col, prop, value, cellProperties) {
        Handsontable.renderers.AutocompleteRenderer.apply(this, arguments);
        if (value === 'Nothing' || value === '') {
            if (instance.getDataAtCell(0,col) === 'Trial Type') {
                $(td).addClass("htInvalid");
            } else {
                td.style.background = '#DDD';
            }
        }
    }
    function updateDimensions(hot, addWidth, addHeight) {
        var addW = addWidth  || 0;
        var addH = addHeight || 0;
        
        var container   = hot.container;
        var thisSizeBox = $(container).find(".wtHider");
        
        var thisWidth  = thisSizeBox.width()+22+addW;
        var thisHeight = thisSizeBox.height()+22+addH;
        
        var thisArea = $(container).closest(".tableArea");
        
        thisWidth  = Math.min(thisWidth,  thisArea.width());
        thisHeight = Math.min(thisHeight, 600);
        
        hot.updateSettings({
            width:  thisWidth,
            height: thisHeight
        });
    }
    function updateDimensionsDelayed(hot, addWidth, addHeight) {
        updateDimensions(hot, addWidth, addHeight);
        setTimeout(function() {
            updateDimensions(hot);
        }, 0);
    }
    function createHoT(container, data) {
        var table = new Handsontable(container, {
            data: data,
            width: 1,
            height: 1,
			
            afterChange: function(changes, source) {
                updateDimensions(this);
            },
            afterInit: function() {
                updateDimensions(this);
            },
            afterCreateCol: function() {
                updateDimensionsDelayed(this, 55, 0);
            },
            afterCreateRow: function() {
                updateDimensionsDelayed(this, 0, 28);
            },
            afterRemoveCol: function() {
                updateDimensionsDelayed(this);
            },
            afterRemoveRow: function() {
                updateDimensionsDelayed(this);
            },
            rowHeaders: true,
            colHeaders: true,
            contextMenu: true,
            cells: function(row, col, prop) {
                var cellProperties = {};
                
                if (row === 0) {
                    // header row
                    cellProperties.renderer = firstRowRenderer;
                } else {
                    var thisHeader = this.instance.getDataAtCell(0,col);
                    if (typeof thisHeader === 'string' && thisHeader != '') {
                        if (isTrialTypeHeader(thisHeader)) {
                            cellProperties.type = 'dropdown';
                            cellProperties.source = trialTypes;
                            cellProperties.renderer = trialTypesRenderer;
                        } else {
                            cellProperties.type = 'text';
                            if (isNumericHeader(thisHeader)) {
                                cellProperties.renderer = numericRenderer;
                            } else if (isShuffleHeader(thisHeader)) {
                                cellProperties.renderer = shuffleRenderer;
                            } else {
                                cellProperties.renderer = Handsontable.renderers.TextRenderer;
                            }
                        }
                    } else {
                        cellProperties.renderer = Handsontable.renderers.TextRenderer;
                    }
                }
                
                return cellProperties;
            },
            minSpareCols: 0,
            minSpareRows: 1,
            manualColumnFreeze: true,
            fixedRowsTop: 1,
//			contextMenu: true,
			colHeaders: ['Cues','Answer','Group(s)','Shuffle']
			
        });
        
        return table;
    }
    $(document).ready(function() {
        var stimContainer = document.getElementById("stimTable");
        var stimData = <?= $stimData ?>;
		stimTable = createHoT(stimContainer, stimData);        
    });
    
    // limit resize events to once every 100 ms
    var resizeTimer;
    
    $(window).resize(function() {
        window.clearTimeout(resizeTimer);
        resizeTimer = window.setTimeout(function() {
            updateDimensions(stimTable);
        }, 100);
    });
    
    
    
    // Code to submit or reset stuff
    
    $("#submitButton").on("click", function() {
        $("input[name='stimTableInput']").val(JSON.stringify(stimTable.getData()));
        $("form").submit();
    });
    $("#resetButton").on("click", function() {
        $("input[name='stimTableInput']").remove();
        $("form").submit();
    });
</script>

<?php
    // access control
    require_once 'loginFunctions.php';
    adminOnly();                                        // only allow tool use when logged in

    // needed for this tool
    require_once $_PATH->get('Shuffle Functions');      // load shuffle functions

    // Find all available experiments
    $experiments = array_flip(getCollectorExperiments());


    // save selected experimet if availavle and valid
    if (isset($_GET['exp'])
        AND isset($experiments[$_GET['exp']])
    ) {
        $_DATA['exp'] = $_GET['exp'];
    } elseif (empty($_DATA['exp'])) {
        $_DATA['exp'] = "";
    }

    // if an experimet has been selected
    if (!empty($_DATA['exp'])) {
        $exp = $_DATA['exp'];

        // scan for procedure and stim files
        $ShuffleFolders = array(
            'Stimuli'   => array(),
            'Procedure' => array()
        );
        foreach ($ShuffleFolders as $type => $empty) {
            $searching = scandir("{$_root}/Experiments/$exp/$type/");
            foreach ($searching as $item => $path) {
                if(!instring('.csv', $path, true)) {
                    unset($searching[$item]);                       // remove files that aren't .csv
                }
                $ShuffleFolders[$type] = $searching;
            }
        }

        // save selected file if in URL
        if (isset($_GET['shuffleFile'])) {
            $_DATA['get']  = $_GET['shuffleFile'];
            $_DATA['name'] = substr($_DATA['loc'], strrpos($_DATA['loc'], '/')+1 );
            $_DATA['loc']  = "{$_root}/Experiments/$exp/{$_GET['shuffleFile']}";
            if (!file_exists($_DATA['loc'])) {
                $_DATA['loc'] = "";
            }
        }
    }

    // set defaults if values have not been set (otherwise leave alone)
    $_DATA['loc']  = empty($_DATA['loc'])  ? "" : $_DATA['loc'];
    $_DATA['get']  = empty($_DATA['get'])  ? "" : $_DATA['get'];
    $_DATA['name'] = empty($_DATA['name']) ? "" : $_DATA['name'];
    
?>
    <!-- Custom CSS specific to shuffle tester -->
    <style type="text/css">
        #shuffleSelectBar {
            width: 100%;
            margin: 0px auto;
            overflow:auto;
        }
        #shuffleSelectBar * {
            float: left;
        }
        #shuffleSelectBar h3{
            margin-right: 10px;
            clear: left;
        }
        #shuffleSelectBar select, #shuffleSelectBar button {
            margin: 3px;
        }
        #shuffleSelectBar button {
            padding: 3px;
        }
        #shuffleDisplay {
            clear:left;
        }
        #zoom {
            float: right;
        }
        #reset {
            margin-left:15px;
        }
        dl {
            margin-left:5%;
            margin-top: 25px;
        }
        dt{
            font-weight: 700;
        }
        #oldZoom {
            display:none;
        }
        #cellContents {
            width:99%;
            font-size: 1.2em;
            max-width: 99%;
            margin: 3px auto 0px auto;
            height:1.2em;
            padding: 3px;
        }
            .locked {                     /*Added to <textarea> when contents are locked*/
                border-color: #F0CA31;
                border-style:dotted;
                border-width:3%;
                background-color: #FDF0BE;
            }
        .before #RF {
            float:right;
        }
        td:first-child {
            font-weight:700;
        }
        dl {
            clear: left;
            padding-top:20px;
        }
        #expSelect > *{
            float:left;
        }
        #expSelect > h4 {
            margin-right: 1.3em;
        }
        #expSelect > button {
            margin: 0em .3em;
            font-size: .7em;
        }

    </style>

    <div class="toolWidth" id="expSelect">
        <h4>Which experiment?</h4>
        <?php
        foreach ($experiments as $expName => $value) {
            echo "<button form='shuffleFile' class='collectorButton' name='exp' value='$expName'>$expName</button>";
        }
        ?>
    </div>
    
    <!-- Show bar with dropdown file selector + zoom in/out reset buttons -->
    <?php
        if ($_DATA['exp'] != "") {
?>
    <div class="toolWidth">
        <div id="shuffleSelectBar">
            <h3>Which file would you like to shuffle?</h3>
            <div>
                <!-- Create the select dropdown and populate it with all procedure and stimuli files -->
                <select form="shuffleFile" class="toolSelect collectorInput" name="shuffleFile">
                <?php
                    foreach ($ShuffleFolders as $type => $files) {
                        echo "<optgroup label='$type Files'>";                  // group options by type: Stimuli OR Procedure
                        foreach ($files as $file) {
                            $value = "$type/$file";
                            $selected = ($_DATA['get'] == $value) ? " selected" : "";
                           echo "<option label='$file' value='$value' class='goShuffle' $selected>$file</option>";
                        }
                    }
                ?>
                </select>
                <button form="shuffleFile">Shuffle!</button>
            </div>
            <!-- Show zoom and reset buttons.  Functionality is handled by Jquery at the bottom of the page -->
            <div id="zoom">
                <button id="in"   ><b>Zoom +</b></button>
                <button id="out"  ><b>Zoom -</b></button>
                <button id="reset">Reset</button>
            </div>
        </div>
    </div>
    
    <!-- Show a text area that will be filled with the contents of the cell being hovered over. Functionality is in the JQuery script below -->
    <div class="toolWidth">
        <textarea id="cellContents" readonly></textarea>
    </div>

<?php
        }
    ?>

    
<?php
    // check if a custom zoom value is in the get
    if (isset($_GET['zoom'])) {
        $val = $_GET['zoom'];
    } else { $val = 'default'; }
?>
    <input type="text" form="shuffleFile" name="zoom" value="<?= $val ?>" class="zoomInput" hidden/>   <!-- Jquery puts modified zoom value here and it is submitted when shuffle button is pressed-->
    <span id="zoomVal" class="hidden"><?= $val ?></span>                                               <!-- keeps the zoom value most recently submitted -->
    <form id="shuffleFile" action="" method="get"></form>                   <!-- the form that submits the selctions made on this page -->

<?php    
    if (    ($_DATA['loc'] !== "")                              // if a shuffle file has been choosen
        AND ($_DATA['exp'] !== "")
        AND inString('.csv', $_DATA['loc']) == true           // and it is a csv file
    ){                     
        $before = GetFromFile($_DATA['loc']);           // grab file to shuffle
        $timer  = microtime(true);                      // start a timer
        $after  = multiLevelShuffle($before);           // run basic shuffles
        $after  = shuffle2dArray($after);               // run advanced shuffles
        $timer  = (microtime(true) - $timer);           // calculate difference since start
        $timer  = round($timer * 1000000, 0);           // multiply by 1,000,000 and round
        
        $tableTimer = microtime(true);
        // show the before shuffling version
        echo '<div class="before"><div id="RF"><h2>Before</h2>';
                  display2dArray($before);
        echo '</div></div>';
        // show the after shuffling version
        echo '<div class="after"><h2>After</h2>';
                  display2dArray($after);
        echo '</div>';
        $tableTimer = round((microtime(true) - $tableTimer) * 1000000, 0);  // calculate timer to rounded to nearest microsecond
    }
?>  
    <!-- Debug to make sure I'm getting the right stuff back -->
    <dl class="brand">
        <dt>Filename</dt>
        <dd><code><?= $_DATA['name'] ?></code></dd>
        <dt>File location</dt>
        <dd><code><?= $_DATA['loc']  ?></code></dd>
<?php
        if (isset($timer)) {                         // only show duration when a file was actually shuffled
            echo  '<dt>Time to shuffle</dt>'
                . '<dd>' . number_format($timer) . ' microseconds</dd>'
                . '<dt>Time to build display tables</dt>'
                . '<dd>' . number_format($tableTimer) . ' microseconds</dd>';
        }
?>
    </dl>
    
<script type="text/javascript">
    var iniitalSize = parseFloat( $(".display2dArray").css("font-size") );      // save default table font size
    var size = iniitalSize;
    var zoom = parseFloat($("#zoomVal").html());                                // save custom zoom value
    var clicked = 0;                                                            // reset click binary (0 = not locked, 1 = locked contents)
    $(".display2dArray").css("font-size", zoom);                                // change table zoom to custom zoom
    if(!isNaN(zoom)) {                                                          // if a custom zoom is set use it as starting point for zoom in/out calls
        size = zoom;
    }
    
    $(window).ready(function () {
        $('#in').click(function (){                         // when zoom in button is clicked
           size = size * 1.1;                                   // scale up the size
           $(".display2dArray").css("font-size", size);         // change font to new size value
           $(".zoomInput").val(size);                           // put new zoom value into hidden input
        });
        $('#out').click(function (){                        // when zoom out button is clicked
           size = size * 0.9;                                   // scale down the size
           $(".display2dArray").css("font-size", size);         // change font to the new size value
           $(".zoomInput").val(size);                           // put new zoom value into hidden input
        });
        $('#reset').click(function (){                      // when reset button is pressed
           size = iniitalSize;                                  // change size to inititial size
           $(".display2dArray").css("font-size", size);         // change table font-size back to original
           $(".zoomInput").val(size);                           // put the original size back into the hidden zoom input field
           clicked = 0;                                         // set clicked to false / unclicked / unlocked
           $("textarea").removeClass("locked");                 // remove textarea locked styling
        });
        $("td").click(function() {                          // if a table cell is clicked
            if (clicked == 0) {                                 // if not locked
                clicked = 1;                                        // lock
                $("textarea").addClass("locked");                   // add lock style to textarea
                $("#reset").html("Reset/Unlock");                   // change reset button to Reset/Unlock button
            } else {                                            // if locked
                clicked = 0;                                        // unlock
                $("textarea").removeClass("locked");                // remove locked styling from textarea
                $("#reset").html("Reset");                          // change reset button back to "Reset"
            }
        });
        $("td").hover(function() {                          // if you hover over a table cell
            if (clicked == 0) {                                 // if contents are not locked
                var contents = $(this).children().html();           // save contents of cell being hovered over
                $("#cellContents").html(contents).val();            // insert contents into textarea in a way that preserves HTML markup
            }
        });
        
    });
    $(".goShuffle").click(function(){
        $("#shuffleFile").submit();
    })
</script>
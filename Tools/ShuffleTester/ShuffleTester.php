<?php
    // access control
    require_once 'loginFunctions.php';
    adminOnly();                                        // only allow tool use when logged in
    
    // needed for this tool
    $root = '../';                                      // gets us to the root
    require_once $root . 'Code/advancedShuffles.php';   // nadvanced (Tyson) shuffles
    
    
    // making a place to store all variables (storing inside admin is best because it will be wiped on logout)
    $_SESSION['admin']['shuffleTester'] = array();
    $store =& $_SESSION['admin']['shuffleTester'];
    
    // save selected file if in URL
    if (isset($_GET['shuffleFile'])) {
        $store['loc'] = $root . 'Experiment/' . $_GET['shuffleFile'];
        $store['get'] = $_GET['shuffleFile'];
        $store['name'] = substr($store['loc'], strrpos($store['loc'], '/')+1 );
    }
    // set defaults if values have not been set
    if (!isset($store['loc'])) {
        $store['loc'] = '';
        $store['get'] =  '';
        $store['name'] = '';
    }
    
    // scan for stimuli files
    $stimuliFolder   = scandir($root . 'Experiment/Stimuli/');
    foreach ($stimuliFolder as $item => $path) {
        if (!inString('.csv', $path, TRUE)) {
            unset($stimuliFolder[$item]);             // only keep .csv stimuli files
        }
    }
    
    // scan for procedure files
    $procedureFolder = scandir($root . 'Experiment/Procedure/');
    foreach ($procedureFolder as $item => $path) {
        if (!inString('.csv', $path, TRUE)) {
            unset($procedureFolder[$item]);             // only keep .csv procedure files
        }
    }
?>
    <!-- Custom CSS specific to shuffle tester -->
    <style type="text/css">
        #shuffleSelectBar {
            width: 100%;
            margin: 0px auto;
        }
        #shuffleSelectBar * {
            float: left;
        }
        #shuffleSelectBar h3{
            display:inline-block;
            margin-right: 15px;
            clear: left;
        }
        #shuffleSelectBar select, #shuffleSelectBar button {
            margin-top:10px;
        }
        #shuffleDisplay {
            clear:left;
        }
        #zoom {
            float: right;
            margin-right: 20px;
        }
        #reset {
            margin-left:10px;
        }
        dl {
            margin-left:5%;
            margin-top: 25px;
        }
        dt{
            font-weight: bold;
        }
        #oldZoom {
            display:none;
        }
    </style>
    
    <div class="toolWidth">
        <div id="shuffleSelectBar">
            <h3>Which file would you like to shuffle?</h3>
            <select form="shuffleFile" class="collector-select" name="shuffleFile">
                <optgroup label="Stimuli Files">
                    <!-- <option label="$file" value="Stimuli/$file"$selected>$file</option> -->
                    <?php
                        foreach ($stimuliFolder as $file) {
                            if ($store['name'] == $file) {          // add selected property to the file we've shuffled
                                $selected = ' selected';
                            } else { $selected = ''; }
                            echo '<option label="' . $file. '" value="Stimuli/' . $file. '"' . $selected . '>' . $file. '</option>';    //show the option
                        }
                    ?>
                </optgroup>
                <optgroup label="Procedure Files">
                    <!-- <option label="$file" value="Procedure/$file"$selected>$file</option> -->
                    <?php
                        foreach ($procedureFolder as $file) {
                            if ($store['name'] == $file) {          // add selected property to the file we've shuffled
                                $selected = ' selected';
                            } else { $selected = ''; }
                            echo '<option label="' . $file . '" value="Procedure/' . $file . '"' . $selected . '>' . $file . '</option>';   //show the option
                        }
                    ?>
                </optgroup>
            </select>
            <button form="shuffleFile">Shuffle!</button>
            <div id="zoom">
                <button id="in"   >Zoom In</button>
                <button id="out"  >Zoom Out</button>
                <button id="reset">Reset </button>
            </div>
        </div>
        <form id="shuffleFile" action="" method="get"></form>
    </div>
    
<?php
    if (isset($_GET['zoom'])) {
        $val = $_GET['zoom'];
    } else { $val = 'default'; }
    
    echo '<input type="text" form="shuffleFile" name="zoom" value="' . $val . '" class="zoomInput" hidden/>';   // save modified zoom value
    echo '<span id="zoomVal" class="hidden">' . $val . '</span>';                                               // hold any submitted zoom value
    
    if ($store['loc'] !== '') {
        
        $before = GetFromFile($store['loc']);
        $start  = microtime(TRUE);
        $after  = multiLevelShuffle($before);
        $after  = shuffle2dArray($after);
        $end    = microtime(TRUE);
        $duration = ($end - $start)*1000000;
        echo '<div class="before"><h3>Before</h3>';
                  display2dArray($before);
        echo '</div>';
        echo '<div class="after"><h3>After</h3>';
                  display2dArray($after);
        echo '</div>';
    }
?>  
    <!-- Debug to make sure I'm getting the right stuff back -->
    <dl class="brand">
        <dt>Filename</dt>
        <dd><code><?= $store['name'] ?></code></dd>
        <dt>File location</dt>
        <dd><code><?= $store['loc']  ?></code></dd>
        <dt>Time to shuffle</dt>
        <dd><?=  $duration //round($duration, 5) ?> microseconds</dd>
    </dl>
    
<script type="text/javascript">
    var iniitalSize = parseInt( $(".display2dArray").css("font-size") );
    var size = iniitalSize;
    var zoom = parseInt($("#zoomVal").html());
    $(".display2dArray").css("font-size", zoom);

    $(window).ready(function () {
        $('#in').click(function (){
           size = size + 2;
           $(".display2dArray").css("font-size", size);
           $(".zoomInput").val(size);
        });
        $('#out').click(function (){
           size = size - 2;
           $(".display2dArray").css("font-size", size);
           $(".zoomInput").val(size);
        });
        $('#reset').click(function (){
           size = iniitalSize;
           $(".display2dArray").css("font-size", size);
           $(".zoomInput").val(size);
        });
    });
</script>
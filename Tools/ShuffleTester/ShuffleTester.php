<?php
// access control: disable access to non-admin
adminOnly();

// load shuffle functions needed for this tool
require_once $_PATH->get('Shuffle Functions');

// find all available experiments
$experiments = array_flip(getCollectorExperiments());

// save selected experimet if available and valid
$exp = filter_input(INPUT_GET, 'exp', FILTER_SANITIZE_STRING);
if (($exp !== null) && isset($experiments[$exp])) {
    $_DATA['exp'] = $exp;
} elseif (empty($_DATA['exp'])) {
    $_DATA['exp'] = '';
}

// if an experimet has been selected
if (!empty($_DATA['exp'])) {
    $exp = $_DATA['exp'];

    // scan for procedure and stim files
    $ShuffleFolders = array('Stimuli' => array(), 'Procedure' => array());
    foreach ($ShuffleFolders as $type => $empty) {
        $searching = scandir("{$_root}/Experiments/$exp/$type/");
        foreach ($searching as $item => $path) {
            // remove files that aren't .csv
            if (!instring('.csv', $path, true)) {
                unset($searching[$item]);
            }
            $ShuffleFolders[$type] = $searching;
        }
    }

    // save selected file if in URL
    $shuffleFile = filter_input(INPUT_GET, 'shuffleFile', FILTER_SANITIZE_STRING);
    if ($shuffleFile !== null) {
        $_DATA['get'] = $shuffleFile;
        $_DATA['name'] = substr($_DATA['loc'], strrpos($_DATA['loc'], '/') + 1);
        $_DATA['loc'] = "{$_root}/Experiments/{$exp}/{$shuffleFile}";
        if (!file_exists($_DATA['loc'])) {
            $_DATA['loc'] = '';
        }
    }
}

// set defaults if values have not been set (otherwise leave alone)
$_DATA['loc'] = empty($_DATA['loc'])  ? '' : $_DATA['loc'];
$_DATA['get'] = empty($_DATA['get'])  ? '' : $_DATA['get'];
$_DATA['name'] = empty($_DATA['name']) ? '' : $_DATA['name'];

?>
<!-- Custom CSS specific to shuffle tester -->
<link rel="stylesheet" href="ShuffleTester/styles.css">

<!-- display available experiments -->
<div class="toolWidth" id="expSelect">
  <h4>Which experiment?</h4>
  <?php foreach ($experiments as $expName => $value): ?>
  <button form='shuffleFile' class='collectorButton' name='exp' value='<?= $expName?>'>
      <?= $expName ?>
  </button>
  <?php endforeach; ?>
</div>


<!-- Show bar with dropdown file selector + zoom in/out reset buttons -->
<?php if ($_DATA['exp'] !== ''): ?>
<div class="toolWidth">
  <div id="shuffleSelectBar">
    <h3>Which file would you like to shuffle?</h3>

    <!-- Create the menu and populate it with procedure and stimuli files -->
    <div>
      <select form="shuffleFile" class="toolSelect collectorInput" name="shuffleFile">
      <?php foreach ($ShuffleFolders as $type => $files): ?>
        <optgroup label='<?= $type ?> Files'>
          <?php foreach ($files as $file):
              $value = "$type/$file";
              $selected = ($_DATA['get'] == $value) ? ' selected' : '';
          ?>
          <option label='<?= $file ?>' value='<?= $value?>' class='goShuffle' <?= $selected ?>>
            <?= $file ?>
          </option>
          <?php endforeach; ?>
        <?php endforeach; ?>
      </select>

      <button form="shuffleFile">Shuffle!</button>
    </div>

    <!-- Show zoom and reset buttons. Functionality is handled by Jquery at the bottom of the page -->
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
<?php endif; // from `if ($_DATA['exp'] !== ''):` ?>


<?php
// check if a custom zoom value is in the get
$zoom = filter_input(INPUT_GET, 'zoom', FILTER_SANITIZE_STRING);
$val = ($zoom !== null) ? $zoom : 'default';
?>
<!-- jQuery puts modified zoom value here and it is submitted when shuffle button is pressed-->
<input type="text" form="shuffleFile" name="zoom" value="<?= $val ?>" class="zoomInput" hidden/>

<!-- this holds the zoom value that was most recently submitted -->
<span id="zoomVal" class="hidden"><?= $val ?></span>

<!-- the form that submits the selections made on this page -->
<form id="shuffleFile" action="" method="get"></form>


<?php
// if a shuffle file has been choosen and it is a csv file
if (($_DATA['loc'] !== '') && ($_DATA['exp'] !== '')
    && inString('.csv', $_DATA['loc']) === true
) {
    // grab file to shuffle
    $before = getFromFile($_DATA['loc']);

    // start a timer
    $shufflestart = microtime(true);

    // run basic shuffles
    $basic = multiLevelShuffle($before);

    // run advanced shuffles
    $after = shuffle2dArray($basic);

    // calculate shuffle time and round to nearest microsecond
    $timer = round((microtime(true) - $shufflestart) * 1000000, 0);

    // start time for table creation
    $tableStart = microtime(true);

    // display HTML
    ?>

<div class="before">
  <div id="RF">
    <h2>Before</h2>
    <?= display2dArray($before) ?>
  </div>
</div>
<!-- version after shuffling -->
<div class="after">
  <h2>After</h2>
  <?= display2dArray($after) ?>
</div>

    <?php
    // calculate table creation time round to nearest microsecond
    $tableTimer = round((microtime(true) - $tableStart) * 1000000, 0);
}
?>

<!-- Debug to make sure I'm getting the right stuff back -->
<dl class="brand">
  <dt>Filename</dt>
  <dd><code><?= $_DATA['name'] ?></code></dd>

  <dt>File location</dt>
  <dd><code><?= $_DATA['loc']  ?></code></dd>

  <?php if (isset($timer)): // only show duration when a file was actually shuffled ?>
  <dt>Time to shuffle</dt>
  <dd><?= number_format($timer) ?> microseconds</dd>

  <dt>Time to build display tables</dt>
  <dd><?= number_format($tableTimer) ?> microseconds</dd>
  <?php endif; ?>
</dl>

<script type="text/javascript">
  // default table font size
  var iniitalSize = parseFloat( $(".display2dArray").css("font-size") );
  var size = iniitalSize;

  // custom zoom value
  var zoom = parseFloat($("#zoomVal").html());

  // reset click binary (0 = not locked, 1 = locked contents)
  var clicked = 0;

  // change table zoom to custom zoom
  $(".display2dArray").css("font-size", zoom);

  // if a custom zoom is set use it as starting point for zoom in/out calls
  if ($.isNumeric(zoom)) {
    size = zoom;
  }

  $(window).ready(function () {
    // when zoom in button is clicked
    $('#in').click(function (){
      // scale up the size
      size = size * 1.1;
      // change font to new size value
      $(".display2dArray").css("font-size", size);
      // put new zoom value into hidden input
      $(".zoomInput").val(size);
    });

    // when zoom out button is clicked
    $('#out').click(function (){
      // scale down the size
       size = size * 0.9;
       // change font to the new size value
       $(".display2dArray").css("font-size", size);
       // put new zoom value into hidden input
       $(".zoomInput").val(size);
    });

    // when reset button is pressed
    $('#reset').click(function (){
      // change size to inititial size
       size = iniitalSize;
       // change table font-size back to original
       $(".display2dArray").css("font-size", size);
       // put the original size back into the hidden zoom input field
       $(".zoomInput").val(size);
       // set clicked to false / unclicked / unlocked
       clicked = 0;
       // remove textarea locked styling
       $("textarea").removeClass("locked");
    });

    // if a table cell is clicked
    $("td").click(function() {
      // if not locked
      if (clicked === 0) {
        // lock
        clicked = 1;
        // add lock style to textarea
        $("textarea").addClass("locked");
        // change reset button to Reset/Unlock button
        $("#reset").html("Reset/Unlock");
      } else {
        // unlock
        clicked = 0;
        // remove locked styling from textarea
        $("textarea").removeClass("locked");
        // change reset button back to "Reset"
        $("#reset").html("Reset");
      }
    });

    // if you hover over a table cell
    $("td").hover(function() {
      // if contents are not locked
      if (clicked === 0) {
        // save contents of cell being hovered over
        var contents = $(this).children().html();
        // insert contents into textarea in a way that preserves HTML markup
        $("#cellContents").html(contents).val();
      }
    });

  });

  $(".goShuffle").click(function(){
    $("#shuffleFile").submit();
  });
</script>
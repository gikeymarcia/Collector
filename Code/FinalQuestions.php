<?php
/*  Collector
    A program for running experiments on the web
    Copyright 2012-2015 Mikey Garcia & Nate Kornell
*/
    require 'initiateCollector.php';
    
    // if this is the first time on FinalQuestions.php then load questions from file
    if (isset($_SESSION['FinalQs']) == false) {
        $fQ = GetFromFile($_PATH->get('Final Questions'));
        // loop that deletes trailing empty positions from $fQ array
        for ($i=(count($fQ)-1); $i>0; $i--) {
            if ($fQ[$i] == null) {
                unset($fQ[$i]);
            } else {
                break;
            }
        }
        $_SESSION['FinalQs'] = $fQ;
        $_SESSION['FQpos']   = 2;
    }


    // setting up aliases (makes all later code easier to read)
    $allFQs  =&  $_SESSION['FinalQs'];
    $pos     =&  $_SESSION['FQpos'];
    $FQ      =&  $allFQs[$pos];                          // all info about current final question
    $Q       =   $FQ['Question'];                        // the question on this trial
    $type    =   trim(strtolower($FQ['Type']));          // type of question to display for this trial (i.e, likert, text, radio, checkbox)
    $options =   array();


    // loading values into $options
    for ($i=1; isset($FQ[$i]); $i++) {
        if ($FQ[$i] != '') {
            $rawString  = $FQ[$i];
            $split      = explode('|', $rawString);
            $temp       = array('value' => $split[0],
                                'text'  => $split[1]);
            $options[]  = $temp;
        }
    }

    // if the question starts with '*' then skip it; good for skipping questions when debugging without deleting finalQuestions
    if ($Q[0] == '*') {
        echo '<meta http-equiv="refresh" content="0; url=' . $_PATH->get('Final Questions Record'). '">';
        exit;
    }
    
    $title = 'Final Questions';
    $_dataController = 'finalQuestions';
    
    require $_PATH->get('Header');
?>
<style>
    #content { width: 750px;}
</style>

<form id="content" name="FinalQuestion" autocomplete="off" action="<?= $_PATH->get('Final Questions Record') ?>" method="post" class="finalquestions">
    <h1 class="textcenter">Final Questions</h1>
    <p><?php echo $Q ?>
    <?php


    // radio button code
    if ($type == 'radio'): ?>
        <section class="radioButtons">
        <?php foreach ($options as $choice): ?>
                <label><input
                        type="radio"
                        name="formData"
                        value="<?php echo $choice['value']; ?>"
                        required><?php echo $choice['text']; ?>
                </label>
        <?php endforeach; ?>
        </section>
        <div class="pad textcenter">
          <button class="collectorButton" id="FormSubmitButton">Submit</button>
        </div>
    <?php endif;


    // checkbox code
    if ($type == 'checkbox'): ?>
        <?php foreach ($options as $choice): ?>
            <section class="radioButtons">
                <label><input
                        type="checkbox"
                        name="formData[]"
                        value="<?php echo $choice['value']; ?>"
                        ><?php echo $choice['text']; ?>
                </label>
            </section>
        <?php endforeach; ?>
        <div class="pad textcenter">
          <button class="collectorButton" id="FormSubmitButton">Submit</button>
        </div>
    <?php endif;


    // likert code
    if ($type == 'likert'): ?>
        <div id="slider"></div>
        <div class="amount">
          <input name="formData" type="text" id="amount">
          <button class="collectorButton" id="FormSubmitButton">Submit</button>
        </div>
    <?php endif;


    // textbox code
    if ($type == 'text'): ?>
        <div class="textcenter">
          <input type="text" name="formData" class="wide collectorInput" autocomplete="off" />
          <button class="collectorButton" id="FormSubmitButton">Submit</button>
        </div>
    <?php endif;

    // textarea code
    if ($type == 'textarea'): ?>
        <textarea rows="10" cols="50" name="formData" wrap="physical" value="" class="collectorInput"></textarea>
        <div class="pad textright">
            <button class="collectorButton collectorAdvance" id="FormSubmitButton">Submit</button>
        </div>
    <?php endif; ?>

    <input name="RT" id="RT" class="hidden" type="text" value="">
</form>
    
<?php
    require $_PATH->get('Footer');

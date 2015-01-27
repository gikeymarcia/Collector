<?php
/*  Collector
    A program for running experiments on the web
    Copyright 2012-2014 Mikey Garcia & Nate Kornell
*/
    require 'initiateCollector.php';
    
    // if this is the first time on FinalQuestions.php then load questions from file
    if (isset($_SESSION['FinalQs']) == FALSE) {
        $fQ = GetFromFile($up.$expFiles.$finalQuestionsFileName);
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
        echo '<meta http-equiv="refresh" content="0; url=FQdata.php">';
        exit;
    }
    
    $title = 'Final Questions';
    $_dataController = 'finalQuestions';
    
    require $_codeF . 'Header.php';
?>
    <section class="finalquestions vcenter">
        <h1 class="textcenter">Final Questions</h1>
        <p><?php echo $Q ?>

        <form class="collector-form" name="FinalQuestion" autocomplete="off" action="FQdata.php" method="post">

            <?php
            // radio button code
            if ($type == 'radio'): ?>
                <div class="collector-form-element">
                  <div>
                  <?php foreach ($options as $choice): ?>
                    <input id="<?php echo $choice['value']; ?>" 
                           name="formData" 
                           type="radio" 
                           value="<?php echo $choice['value']; ?>" 
                           required>
                    <label for="<?php echo $choice['value']; ?>">
                        <?php echo $choice['text']; ?>
                    </label>
                  <?php endforeach; ?>
                  </div>
                </div>
                
                <div class="collector-form-element textcenter">
                  <input class="collector-button" id="FormSubmitButton" type="submit" value="Submit">
                </div>
            <?php endif;

            // checkbox code
            if($type == 'checkbox'): ?>
                <?php foreach ($options as $choice): ?>
                  <div class="collector-form-element">
                    <input type="checkbox"
                           name="formData[]"
                           id="<?php echo $choice['value']; ?>"
                           value="<?php echo $choice['value']; ?>">
                    <label for="<?php echo $choice['value']; ?>">
                        <?php echo $choice['text']; ?>
                    </label>
                  </div>
                <?php endforeach; ?>
                  <div class="collector-form-element textcenter">
                    <input class="collector-button" id="FormSubmitButton" type="submit" value="Submit">
                  </div>
            <?php endif;

            // likert code
            if($type == 'likert'): ?>
                <div id="slider"></div>
                <div class="collector-form-element amount">
                  <input name="formData" type="text" id="amount">
                  <input class="collector-button" id="FormSubmitButton" type="submit" value="Submit">
                </div>
            <?php endif;

            // textbox code
            if ($type == 'text'): ?>
                <div class="collector-form-element textcenter">
                  <input type="text" name="formData" autocomplete="off" />
                  <input class="collector-button" id="FormSubmitButton" type="submit" value="Submit">
                </div>
            <?php endif;

            // textarea code
            if ($type == 'textarea'): ?>
                <textarea rows="10" cols="50" name="formData" wrap="physical" value=""></textarea>
                <div class="textright">
                  <input class="collector-button collector-button-advance" id="FormSubmitButton" type="submit" value="Submit">
                </div>
            <?php endif; ?>

            <input name="RT" id="RT" class="hidden" type="text" value="">

        </form>
    </section>
    
<?php
    require $_codeF . 'Footer.php';
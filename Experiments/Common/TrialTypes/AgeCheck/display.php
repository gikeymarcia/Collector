<?php
    /* * * *
     * How to use this trial type:
     *
     * This trial was designed to demonstrate how to filter by demographics.
     * In this example, we are filtering out participants who are younger than 18.
     * To make this work, we would first need to ask a survey, using the survey trial type,
     * on the previous row in the Procedure file. This survey will need to ask them how old
     * they are, and the "Question Name" for this question should simply be "Age". Then, in
     * this trial, we are going to check the data associated with "Age", and see if it meets
     * our criterion of being 18 or greater.
     *
     * To modify this, you can simply change the data you are looking for, using the general
     * structure of $responses[ QUESTION_NAME ]. So, if you wanted to filter by gender instead,
     * you could create a survey which has one of the questions with the Question Name of "Gender",
     * and then in this trial, check if $responses["Gender"] is equal to "Male" or "Female".
     * * */
    
    $currentPos;                   // the current trial in the experiment (i.e. row in the Procedure file). This variable is automatically defined for every trial, because of separate code found in Experiment.php, in the Code/ folder
    $prevPos    = $currentPos - 1; // the position of the last trial, where we would ask a survey
    $responses  = $_SESSION['Responses'][$prevPos]; // the responses on the last trial
    
    if (isset($responses['Age']) AND $responses['Age'] < 18) {
        // 1. do we have the response for their age?
        // 2. are they too young to participate?
        // if so, they they are ineligible
        // so, we are going to display the contents of the Text column,
        // which should be set to some polite message, in the Procedure file
        echo $text;
        
        // if the Text column was blank, we are going to fill in some message, so that
        // the participant wont just see a blank screen
        if ($text === '') {
            echo "I'm sorry, but you are not eligible to participate in this experiment. ";
            echo "If you are participating for an mTurk HiT, please return the HiT now.";
        }
        
    }
    else {
        // if we reach this part of the code, then this participant will be treated as eligible
        // so, they will get a button that allows them to proceed to the next trial
        echo "Great, thank you for your responses. To advance to the next portion of this task, ";
        echo "please click on the button below";
        
        echo "<div class='textcenter'>";
        echo     "<button class='collectorButton collectorAdvance' id='FormSubmitButton'>Next</button>";
        echo "</div>";
        
    }

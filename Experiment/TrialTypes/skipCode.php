<?php
    /**
     * This is a file that is only meant to be run from within another trial
     * type.  SkipCode should not be used directly as its own trial type. 
     * But, putting code into its own file like this can help make maintaining
     * separate but related trial types easier.
     */
    if (!isset($_SESSION['SkippedItems'][$item]))
    {
        $_SESSION['SkippedItems'][$item] = FALSE;
    }
    
    if ($_SESSION['SkippedItems'][$item])
    {
        $extraData = array(
            'Response*Skipped' => 'SKIPPED'
        );
        recordTrial($extraData);
        
        // search for trial types that have "skip" in their name, 
        // check if their item is on the "skip" list ($_SESSION['SkippedItems']), 
        // and record a skipped line if appropriate. 
        // Otherwise, restart the trial on that row.
        while (stripos($_SESSION['Trials'][$currentPos]['Procedure']['Trial Type'], 'skip') !== FALSE)
        {
            $nextItem = $_SESSION['Trials'][$currentPos]['Procedure']['Item'];
            
            if ($_SESSION['SkippedItems'][$nextItem])
            {
                recordTrial($extraData);    // this will record a line, and advance the position
            }
            else
            {
                break;  // don't skip this one
            }
            
        }
        
        header('Location: trial.php');
        exit;
        
    }

<?php
/**
 * TrialValidator class
 */

/**
 * @todo summary for TrialValidator class
 */
class TrialValidator
{
    /**
     * @todo docblock for TrialValidator::oStim
     * @var Stimuli
     */
    protected $oStim;
    
    /**
     * @todo docblock for TrialValidator::oProc
     * @var Procedure
     */
    protected $oProc;
    
    /**
     * @todo docblock for TrialValidator::oErr
     * @var ErrorController
     */
    protected $oErr;
    
    /**
     * @todo docblock for TrialValidator::trialTypes
     * @var array
     */
    protected $trialTypes;
    
    /**
     * @todo docblock for TrialValidator::validators
     * @var array
     */
    protected $validators = array();
    
    /**
     * @todo docblock for TrialValidator::foundErrors
     * @var array
     */
    protected $foundErrors = array();
    
    /**
     * @todo docblock for TrialValidator::__construct
     * @param Stimuli $oStim
     * @param Procedure $oProc
     * @param ErrorController $oErr
     */
    function __construct(Stimuli $oStim, Procedure $oProc, ErrorController $oErr) {
        $this->oStim = $oStim;
        $this->oProc = $oProc;
        $this->oErr  = $oErr;
        
        $this->trialTypes = getAllTrialTypeFiles();
        
        $this->validateAllTrials();
        
        $this->sendErrorsToErrorController();
    }
    
    /**
     * @todo docblock for TrialValidator::validateAllTrials()
     */
    protected function validateAllTrials() {
        $postTrials = $this->determinePostTrialLevels();
        $postTrials = range(0, $postTrials);
        $procedure  = $this->oProc->unshuffled();
        
        foreach ($procedure as $pos => $row) {
            foreach ($postTrials as $postN) {
                $trialValues = $this->getTrialValues($row, $postN);
                
                if ($trialValues === false) {
                    continue; // post-trial set to one of the "off" values
                } else {
                    $this->validateTrial($trialValues, $pos, $postN);
                }
            }
        }
    }
    
    /**
     * @todo docblock for TrialValidator::validateTrial
     * @param array $trialValues
     * @param array $procRow
     * @param int $postTrial
     */
    protected function validateTrial($trialValues, $procRow, $postTrial) {
        $trialType = $trialValues['Trial Type'];
        $trialType = strtolower($trialType);
        
        // if haven't checked this trialType for a validator, do so
        if (!isset($this->validators[$trialType])) {
            if (isset($this->trialTypes[$trialType]['validator'])) {
                $this->validators[$trialType] = require $this->trialTypes[$trialType]['validator'];
            } else {
                $this->validators[$trialType] = false;
            }
        }
        
        // if this trial type has a validator, run it
        if ($this->validators[$trialType] !== false) {
            $foundErrors = $this->validators[$trialType]($trialValues);
            // lets be strict about this. The validator must return an array of strings
            if (!is_array($foundErrors)) {
                $errMsg = "Validator for '<b>$trialType</b>' must return an array of strings. "
                        . "What it actually returned was of type '<b>" . getType($foundErrors) . "</b>'. "
                        . "Check the <b>Validator.php</b> file for this trial type, "
                        . "located at '<b>" . $this->trialTypes[$trialType]['validator'] . "</b>'";
                // can we make this a strict error that needs fixing immediately, but also allow the rest of the error checks?
                $this->foundErrors[] = $errMsg;
                return;
            }
            foreach ($foundErrors as $err) {
                if (!is_string($err)) {
                    $errMsg = "Validator for '<b>$trialType</b>' must return an array of strings. "
                            . "However, at least one value inside the returned array was of type '<b>" . getType($err) . "</b>'. "
                            . "Check the <b>Validator.php</b> file for this trial type, "
                            . "located at '<b>" . $this->trialTypes[$trialType]['validator'] . "</b>'";
                    // can we make this a strict error that needs fixing immediately, but also allow the rest of the error checks?
                    $this->foundErrors[] = $errMsg;
                    return;
                }
            }
            
            // yay, they returned the correct type. If errors are found, add to error class
            if ($foundErrors !== array()) {
                $rowOrigin = $this->oProc->getRowOrigin($procRow);
                $errMsg = "Validator for trial type '<b>$trialType</b>' has found something wrong "
                        . "in the procedure file '<b>{$rowOrigin['filename']}</b>', in row <b>{$rowOrigin['row']}</b>, "
                        . "for post trial level <b>$postTrial</b>.<ol>";
                
                foreach ($foundErrors as $err) {
                    $errMsg .= "<li>$err</li>";
                }
                
                $errMsg .= "</ol>";
                
                $this->foundErrors[] = $errMsg;
            }
        }
    }
    
    /**
     * Indicates whether the trial is valid or not.
     * Checks if any errors have been logged in TrialValidator::foundErrors.
     * @return bool True if no errors were found, or false.
     */
    public function isValid() {
        if ($this->foundErrors === array()) {
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * @todo docblock for TrialValidator::sendErrorsToErrorController()
     */
    protected function sendErrorsToErrorController() {
        foreach ($this->foundErrors as $errMsg) {
            $this->oErr->add($errMsg);
        }
    }
    
    /**
     * @todo docblock for TrialValidator::determinePostTrialLevels()
     * @return int
     */
    protected function determinePostTrialLevels() {
        $level = 0;
        
        $procColumns = $this->oProc->getKeys();
        $procColumns = array_flip($procColumns);
        
        while (isset($procColumns["Post $level Trial Type"])) {
            ++$level;
        }
        
        return $level;
    }
    
    /**
     * @todo docblock for TrialValidator::getTrialValues()
     * @param array $procRow
     * @param int $postN
     * @return bool|array
     */
    protected function getTrialValues($procRow, $postN) {
        // returns false if you are getting a post trial that is "off","no",""
        // else, returns the array of columns, stim and proc, that will be available to that trial
        $stimuli = $this->oStim->shuffled();
        $procCols = array();
        $procCols['Item'] = $procRow['Item'];   // this can be overwritten, if there is a "Post 1 Item"
        
        // get columns for this post trial
        // if post level is 0, skip all columns starting with "Post "
        // else, make sure that the column starts with "Post X "
        // if we are getting post columns, trim out the post part 
        // e.g., "Post 1 Trial Type" becomes "Trial Type"
        if ($postN === 0) {
            foreach ($procRow as $col => $val) {
                if (substr($col, 0, 5) === 'Post ') {
                    continue;
                } else {
                    $procCols[$col] = $val;
                }
            }
        } else {
            $colPre = "Post $postN ";
            $colPreLen = strlen($colPre);
            
            foreach ($procRow as $col => $val) {
                if (substr($col, 0, $colPreLen) !== $colPre) {
                    continue;
                } else {
                    $colClean = substr($col, $colPreLen);
                    $procCols[$colClean] = $val;
                }
            }
        }
        
        // if this post trial is not actually a trial, return false
        $trialType = strtolower($procCols['Trial Type']);
        if ($trialType === 'off' || $trialType === 'no' || $trialType === '') {
            return false;
        }
        
        // time to search for stimuli
        // use item to determine rows in the stim file to use
        $items = rangeToArray($procCols['Item']);
        $theseStim = array();
        
        // check that stim actually exists, because they might use "" or "0"
        // for trials without stimuli, like instruct
        foreach ($items as $i) {
            if (!isset($stimuli[$i]) || $stimuli[$i] === 0) {
                continue;
            } else {
                $theseStim[] = $stimuli[$i];
            }
        }
        
        // if we didn't find any existing stimuli, fill in some defaults
        if ($theseStim === array()) {
            $stimHeaders = array_keys($stimuli[0]);
            
            foreach ($stimHeaders as $stimCol) {
                $theseStim[0][$stimCol] = '.';
            }
        }
        
        // rearrange stimuli from $stim[row][col] to $stim[col]
        $stimMerged = array();
        foreach ($theseStim as $row) {
            foreach ($row as $col => $val) {
                $stimMerged[$col][] = $val;
            }
        }
        
        foreach ($stimMerged as $col => $vals) {
            $stimMerged[$col] = implode('|', $vals);
        }
        
        $trialValues = $stimMerged;
        // merge in proc values manually, since array_merge will re-index numeric columns
        foreach ($procCols as $col => $val) {
            $trialValues[$col] = $val;
        }
        
        return $trialValues;
    }
}

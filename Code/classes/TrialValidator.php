<?php
/**
 * TrialValidator class.
 */

/**
 * Handles validation for trials, including global and custom validation.
 */
class TrialValidator
{
    /**
     * The stimuli object to use.
     *
     * @var Stimuli
     */
    protected $stimuli;

    /**
     * The procedure object to use.
     *
     * @var Procedure
     */
    protected $procedure;

    /**
     * The associated ErrorController object.
     *
     * @var ErrorController
     */
    protected $errObj;

    /**
     * The trial types available to Collector.
     *
     * @var array
     */
    protected $trialTypes;

    /**
     * The validators available to Collector.
     *
     * @var array
     */
    protected $validators = array();

    /**
     * The errors found during validation.
     *
     * @var array
     */
    protected $foundErrors = array();

    /**
     * Constructor.
     *
     * @param Stimuli         $stimuli   The Stimuli object to use.
     * @param Procedure       $procedure The Procedure object to use.
     * @param ErrorController $errObj    The ErrorController to use.
     */
    public function __construct(Stimuli $stimuli, Procedure $procedure,
        ErrorController $errObj
    ) {
        $this->stimuli = $stimuli;
        $this->procedure = $procedure;
        $this->errObj = $errObj;

        $this->trialTypes = getAllTrialTypeFiles();

        $this->validateAllTrials();

        $this->sendErrorsToErrorController();
    }

    /**
     * Validates all trials in the procedure.
     */
    protected function validateAllTrials()
    {
        $postTrials = range(0, $this->determinePostTrialLevels());
        $procedure = $this->procedure->getUnshuffled();

        foreach ($procedure as $pos => $row) {
                $trialValues = $this->getTrialValues($row, $postN);

                if ($trialValues !== false) {
                    $this->validateTrial($trialValues, $pos, $postN);
                }
            }
    }

    /**
     * Validates a trial.
     *
     * @param array $trialValues The array of information about the trial.
     * @param array $procRow     The array of information from the procedure row.
     * @param int   $post        The post trial level to use.
     */
    protected function validateTrial(array $trialValues, $procRow, $post)
    {
        $trialType = strtolower($trialValues['Trial Type']);

        // if haven't checked this trialType for a validator, do so
        if (!isset($this->validators[$trialType])) {
            if (isset($this->trialTypes[$trialType]['validator'])) {
                $this->validators[$trialType] = require $this->trialTypes[$trialType]['validator'];
            }
        }

        // if this trial type has a validator, run it
        if (isset($this->validators[$trialType])) {
            $foundErrors = $this->validators[$trialType]($trialValues);

            // the validator must return an array of strings
            if (!is_array($foundErrors)) {
                $errMsg = "Validator for '<b>$trialType</b>' must return an array of strings. "
                        ."What it actually returned was of type '<b>".getType($foundErrors)."</b>'. "
                        .'Check the <b>Validator.php</b> file for this trial type, '
                        ."located at '<b>".$this->trialTypes[$trialType]['validator']."</b>'";
                // @todo can we make this a strict error that needs fixing immediately, but also allow the rest of the error checks?
                $this->foundErrors[] = $errMsg;

                return;
            }
            foreach ($foundErrors as $err) {
                if (!is_string($err)) {
                    $errMsg = "Validator for '<b>$trialType</b>' must return an array of strings. "
                            ."However, at least one value inside the returned array was of type '<b>".getType($err)."</b>'. "
                            .'Check the <b>Validator.php</b> file for this trial type, '
                            ."located at '<b>".$this->trialTypes[$trialType]['validator']."</b>'";
                    // @todo can we make this a strict error that needs fixing immediately, but also allow the rest of the error checks?
                    $this->foundErrors[] = $errMsg;

                    return;
                }
            }

            // yay, they returned the correct type. If errors are found, add to error class
            if ($foundErrors !== array()) {
                $rowOrigin = $this->procedure->getRowOrigin($procRow);
                $errMsg = "Validator for trial type '<b>$trialType</b>' has found something wrong "
                        ."in the procedure file '<b>{$rowOrigin['filename']}</b>', in row <b>{$rowOrigin['row']}</b>, "
                        ."for post trial level <b>$post</b>.<ol>";

                foreach ($foundErrors as $err) {
                    $errMsg .= "<li>$err</li>";
                }

                $errMsg .= '</ol>';

                $this->foundErrors[] = $errMsg;
            }
        }
    }

    /**
     * Indicates whether the trial is valid or not.
     * Checks if any errors have been logged in TrialValidator::foundErrors.
     *
     * @return bool True if no errors were found, or false.
     */
    public function isValid()
    {
        return ($this->foundErrors === array()) ? true : false;
    }

    /**
     * Passes errors to the ErrorController object.
     */
    protected function sendErrorsToErrorController()
    {
        foreach ($this->foundErrors as $errMsg) {
            $this->errObj->add($errMsg);
        }
    }

    /**
     * Determines the number of post trial levels.
     *
     * @return int The number of post trial levels.
     */
    protected function determinePostTrialLevels()
    {
        $level = 0;

        $keys = $this->procedure->getKeys();
        $keysFlipped = array_flip($keys);

        while (isset($keysFlipped["Post $level Trial Type"])) {
            ++$level;
        }

        return $level;
    }

    /**
     * Gets the the array of columns from the stimuli and procedure that will be
     * available to that trial.
     * Returns false if you are getting a post trial that is "off","no", or "".
     *
     * @param array $procRow The array of information from the procedure row.
     * @param int   $post    The post trial number.
     *
     * @return array|bool The array of columns, or false if it is an "off" post.
     */
    protected function getTrialValues($procRow, $post)
    {
        $stimuli = $this->stimuli->getShuffled();
        $procCols = array();
        $procCols['Item'] = $procRow['Item'];   // this can be overwritten, if there is a "Post 1 Item"

        // get columns for this post trial
        // if post level is 0, skip all columns starting with "Post "
        // else, make sure that the column starts with "Post X "
        // if we are getting post columns, trim out the post part 
        // e.g., "Post 1 Trial Type" becomes "Trial Type"
        if ($post === 0) {
            foreach ($procRow as $col => $val) {
                if (substr($col, 0, 5) === 'Post ') {
                    continue;
                } else {
                    $procCols[$col] = $val;
                }
            }
        } else {
            $colPre = "Post $post ";
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
            $i -= 2;
            if (!isset($stimuli[$i])) {
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

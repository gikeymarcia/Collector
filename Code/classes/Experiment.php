<?php
/**
 * Experiment class
 */

/**
 * Stores and manipulates all information about the current Experiment.
 * 
 * @todo add detailed description of how this class should be used
 * @todo add Experiment::validate($pos = 'all') method
 */
class Experiment
{
    /**
     * The position of the current trial.
     * @var int
     */
    public $position;
    
    /**
     * The current Post Trial position.
     * @var int
     */
    public $postNumber;
    
    /**
     * All the (shuffled) stimuli array for the Experiment.
     * @var array
     */
    public $stimuli;
    
    /**
     * The (shuffled) procedure array for the Experiment.
     * @var array
     */
    public $procedure;
    
    /**
     * Indexed array of recorded responses (corresponds to position)
     * @var array
     */
    public $responses;
    
    /**
     * The condition information for the Experiment.
     * @var array
     */
    public $condition;
    
    /**
     * Constructor.
     * @param array $stimuli The stimuli to use in the Experiment.
     * @param array $procedure The procedure to follow for the Experiment.
     * @param array $conditionInfo The condition information for the Experiment.
     * @param array $responses The responses associated with each trial.
     *            (Generally this will be left as an empty array.)
     */
    public function __construct(array $stimuli, array $procedure, 
        array $conditionInfo, array $responses = array()
    ) {
        $this->condition = $conditionInfo;
        $this->stimuli = $stimuli;
        $this->procedure = $procedure;
        $this->responses = $responses;
        $this->position = 0;
        $this->postNumber = 0;
    }
    
    /**
     * Gets procedure and stimuli data for given procedure row and post trial.
     * @param int $pos The position of the trial (defaults to current position).
     * @param int $post The post trial number (defaults to current post).
     * @return array Associative array of the "Stimuli" and "Procedure".
     * @uses Experiment::getTrialProcedure()
     * @uses Experiment::getTrialStimuli()
     */
    public function getTrial($pos = null, $post = null)
    {
        $procedure = $this->getTrialProcedure($pos, $post);
        
        return ['Stimuli' => $this->getTrialStimuli($procedure['Item']),
                'Procedure' => $procedure];
    }
    
    /**
     * Gets procedure columns for single trial.
     * Optionally specify specific position and post trial number. Post trial 
     * columns are transformed into their unposted form (e.g. 'Text' instead of
     * 'Post 1 Text').
     * @param int $pos The position of the trial (defaults to current position).
     * @param int $post The post trial number (defaults to current post).
     * @return array The array of procedure items for the trial.
     */
    public function getTrialProcedure($pos = null, $post = null)
    {
        if ($pos  === null) { $pos  = $this->position; }       
        if ($post === null) { $post = $this->postNumber; }

        $procRow  = $this->procedure[$pos];
        $procCols = ($post === 0)
                  ? $this->extractProcColsForTrial($procRow)
                  : $this->extractProcColsForPostTrial($procRow, $post);

        // if a post trial item is not set, use the original trial's item
        if (!isset($procCols['Item'])) {
            $procCols['Item'] = $procRow['Item'];
        }

        return $procCols;
    }
    
    /**
     * Gets all items from a Procedure Row array, excluding Post Trial items.
     * @param array $array The array to process.
     * @return array The stripped array.
     */
    private function extractProcColsForTrial(array $array)
    {
        $output = [];
        foreach ($array as $col => $val) {
            if (substr($col, 0, 5) !== 'Post ') {
                $output[$col] = $val;
            }
        }
        
        return $output;
    }
    
    /**
     * Gets all items from a procedure row with the given post trial number.
     * @param array $array The array to process.
     * @param int $post The post trial to extract.
     * @return array The stripped array.
     */
    private function extractProcColsForPostTrial(array $array, $post)
    {
        $colPre = "Post $post ";
        $colPreLen = strlen($colPre);

        $output = [];
        foreach ($array as $col => $val) {
            if (substr($col, 0, $colPreLen) === $colPre) {
                $colClean = trim(substr($col, $colPreLen));
                $output[$colClean] = $val;
            }
        }
        
        return $output;
    }
    
    /**
     * Get stimuli for given item(s).
     * @param int|string $item Typically, contents of "Item" column in the 
     *            procedure file.
     * @return array The stimuli, with column values imploded with | if multiple
     *             items are specified.
     * @uses Experiment::getTrialProcedure()
     * @uses Experiment::getTrial()
     */
    public function getTrialStimuli($item)
    {
        $stimRows = $stimCols = array();
        
        // populate rows from the stimuli array
        foreach (rangeToArray($item) as $item) {
            if (isset($this->stimuli[$item - 2])) {
                $stimRows[] = $this->stimuli[$item - 2];
            }
        }
        
        // if no item then populate with placeholder values in each column (".")
        if ($stimRows === array()) {
            foreach ($this->stimuli[0] as $col => $val) {
                $stimRows[0][$col] = '.';
            }
        }
        
        // combine rows
        foreach ($stimRows as $row) {
            foreach ($row as $col => $val) {
                $stimCols[$col][] = $val;
            }
        }
        foreach ($stimCols as $col => $val) {
            $stimCols[$col] = implode('|', $val);
        }
        
        return $stimCols;
    }
    
    /**
     * Saves array of data into (optionally) specified trial's response array. 
     * "Post" prefixes are appended appropriately.
     * @param array $data The 1-D array of responses to save, typically $_POST with custom scoring
     * @param int $pos The position of the trial (defaults to current position).
     * @param int $post The post trial number (defaults to current post).
     * 
     * @todo Should also be a method of a Trial class
     */
    public function recordResponses(array $data, $pos = null, $post = null)
    {
        if ($pos  === null) { $pos  = $this->position; }
        if ($post === null) { $post = $this->postNumber; }

        if (!isset($this->responses[$pos])) {
            $this->responses[$pos] = array();
        }

        if ($post == 0) {
            foreach ($data as $col => $val) {
                $this->responses[$pos][$col] = $val;
            }
        } else {
            foreach ($data as $col => $val) {
                $this->responses[$pos]["Post1_{$col}"] = $val;
            }
        }
    }
    
    /**
     * Finds next post trial after current (or given) trial, or returns false if row
     * has no more valid trials.
     * @param int $pos The position of the trial (defaults to current position).
     * @param int $post The post trial number (defaults to current post).
     * @return int|bool The position of the next valid post trial level, or false
     * @see getValidPostTrials()
     */
    public function getNextPostLevel($pos = null, $post = null)
    {
        if ($pos  === null) { $pos  = $this->position; }
        if ($post === null) { $post = $this->postNumber; }

        $validPosts  = $this->getValidPostTrials($pos);

        foreach ($validPosts as $validPostLevel) {
            if ($validPostLevel > $post) {
                return $validPostLevel;
            }
        }

        return false;
    }

    /**
     * Gets all levels of post trials with valid trial types for the desired row 
     * in the procedure.
     * @param int $pos [Optional] The position to extract Post Trials from
     * @return array Integers specifying which post trials are valid, including 
     *             0 for non-post trials.
     */
    public function getValidPostTrials($pos = null)
    {
        if ($pos === null) { $pos = $this->position; }
        $procRow = $this->procedure[$pos];

        $notTrials  = array('off', 'no', '', 'n/a');
        $validPosts = array();
        $type = $procRow['Trial Type'];
        if (!in_array($type, $notTrials)) {
            $validPosts[] = 0;
        }

        $i = 1;
        while (isset($procRow["Post $i Trial Type"])) {
            $nextType = strtolower($procRow["Post $i Trial Type"]);
            if (!in_array($nextType, $notTrials)) {
                $validPosts[] = $i;
            }
            ++$i;
        }

        return $validPosts;
    }
    
    /**
     * Gets the next trial, whether its the next post trial or the first trial
     * of the next row. 
     * Returns false if no trials are remaining.
     * @param int $pos The position of the trial (defaults to current position).
     * @param int $post The post trial number (defaults to current post).
     * @return array|bool Array as returned by getTrial(), or false
     * @uses Experiment::getTrial()
     * @uses Experiment::getNextTrialIndex()
     */
    public function getNextTrial($pos = null, $post = null)
    {
        $nextIndex = $this->getNextTrialIndex($pos, $post);

        return ($nextIndex === false)
            ? false
            : $this->getTrial($nextIndex[0], $nextIndex[1]);
    }    
    
    /**
     * Gets the proc row index and post trial number of the next trial
     * @param int $pos The position of the trial (defaults to current position).
     * @param int $post The post trial number (defaults to current post).
     * @return array|bool, Array with position and post-trial indices, or false
     */
    public function getNextTrialIndex($pos = null, $post = null)
    {
        if ($pos  === null) { $pos  = $this->position; }
        if ($post === null) { $post = $this->postNumber; }

        $nextPost = $this->getNextPostLevel($pos, $post);

        if ($nextPost === false) {
            $nextPos  = $pos + 1;
            $nextPost = 0;
        } else {
            $nextPos  = $pos;   // same proc row, different post trial
        }

        return (isset($this->procedure[$nextPos]))
            ? array($nextPos, $nextPost)
            : false;
    }
    
    /**
     * Adds images from next trial to a hidden div
     * @param int $pos [Optional] The position of the trial prior to the trial
     *            that is being precached (default: current trial).
     * @param int $post [Optional] The post position of the trial prior to the
     *            trial that is being precached (default: current post).
     * @return string|bool The precache or false if next trial does not exist
     * @uses Experiment::getNextTrial()
     */
    public function getPrecache($pos = null, $post = null)
    {
        $nextTrial = $this->getNextTrial($pos, $post);
        if ($nextTrial !== false) {
            $precache = '<div class="precachenext">';
            foreach (array_values($nextTrial['Stimuli']) as $val) {
                if (show($val) !== $val) {
                    $precache .= show($val);
                }
            }
            $precache .= '</div>';
        }

        return ($nextTrial !== false) ? $precache : false;
    }    
    
    /**
     * Shows all available info for trial, helpful if trial fails
     * @param int $pos The position of the trial (defaults to current position).
     * @param int $post The post trial number (defaults to current post).
     * @return void * @see Experiment::getTrial()
     */
    public function showTrialDiagnostics($pos = null, $post = null)
    {
        if ($pos  === null) { $pos  = $this->position; }
        if ($post === null) { $post = $this->postNumber; }
        $currentTrial = $this->getTrial($pos, $post);

        // clean the arrays used so that they output strings, not code
        $clean_cond = arrayCleaner($this->condition);
        $stimfiles = $procfiles = [];
        foreach($clean_cond as $key => $val) {
            if (strpos($key, 'Stimuli') === 0) {
                $stimfiles[] = $val;
            }
            if (strpos($key, 'Procedure') === 0) {
                $procfiles[] = $val;
            }
        }
        
        $clean_proc = arrayCleaner($currentTrial['Procedure']);
        $clean_stim = arrayCleaner($currentTrial['Stimuli']);
        echo '<div class=diagnostics>'
            .  '<h2>Diagnostic information</h2>'
            .  '<ul>'
            .    '<li> Condition Stimuli File: '   . implode(', ', $stimfiles)
            .    '<li> Condition Procedure File: ' . implode(', ', $procfiles)
            .    '<li> Condition description: '    . $clean_cond['Description']
            .  '</ul>'
            .  '<ul>'
            .    '<li> Trial Number: '   . $pos
            .    '<li> Trial Type: '     . $clean_proc['Trial Type']
            .    '<li> Trial max time: ' . $clean_proc['Max Time']
            .  '</ul>'
            .  '<ul>'
            .    '<li> Cue: '    . show($clean_stim['Cue'])
            .    '<li> Answer: ' . show($clean_stim['Answer'])
            .  '</ul>';
        readable(['Stimuli'=>$clean_stim, 'Procedure' => $clean_proc], "Information loaded about the current trial");
        readable($this->stimuli,      "Information loaded about the stimuli");
        readable($this->procedure,    "Information loaded about the procedure");
        readable($this->responses,    "Information loaded about the responses");
        echo '</div>';
    }
    
    /**
     * Converts 2-D associative array into a 1-D array for extract(). 
     * Array keys are converted to strings suitable for variables. For 
     * overlapping column names (e.g. "Cue" in both the Procedure and the 
     * Stimuli files, or "Cue" in the Stimulli file and "Post 1 Cue" in the 
     * Procedure file) only the first value will be kept and a warning will be 
     * triggered.
     * @param array $pos Position of the trial to be extract()ed (default: current)
     * @return array Converted array: columns to lowercase, spaces to underscores
     * 
     * @todo should be a method of a Trial class
     * @todo update error message to include that the variable is available in the stimuli and procedure properties
     */
    public function prepareAliases($pos = null)
    {
        $trialValues = array();
        foreach (array_values($this->getTrial($pos)) as $row) {
            foreach ($row as $col => $val) {
                $aliasCol = str_replace(' ', '_', strtolower($col));

                // trigger warning warning if already set
                if (isset($trialValues[$aliasCol])) {
                    // temp hack until we work out how to handle shuffle cols
                    if (stripos($aliasCol, 'shuffle') !== false) { continue; }

                    $err = "Overlap with aliases: $aliasCol already defined, $col not used";
                    trigger_error($err, E_USER_WARNING);
                } else {
                    $trialValues[$aliasCol] = $val;
                }
            }
        }

        return $trialValues;
    }
    
    /**
     * Determines if the experiment is done or not.
     * @return bool True if complete, false if at least one more trial exists.
     */
    public function isDone()
    {
        return isset($this->procedure[$this->position]) ? false : true;
    }
    
    /**
     * Retrieves and organizes data from a specific trial.
     * @param int $pos The position of the trial (defaults to current position).
     * @return array Associative array of the data.
     */
    public function getTrialRecord($pos = null)
    {
        if ($pos === null) { $pos = $this->position; }
        $data = array();
        $procRow = $this->procedure[$pos];

        $data['Cond'] = $this->condition;
        $data['Proc'] = $procRow;
        $data['Stim'] = $this->getTrialStimuli($procRow['Item']);
        foreach ($this->getValidPostTrials() as $postNum) {
            if ($postNum === 0) { continue; }  // already grabbed those items
            if (isset($procRow["Post $postNum Item"])) {
                $data["StimPost$postNum"] = $this->getTrialStimuli($procRow["Post $postNum Item"]);
            }
        }
        $data['Resp'] = $this->responses[$pos];

        return $data;
    }
}

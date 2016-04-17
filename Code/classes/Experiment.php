<?php
/**
 * Experiment class.
 */

namespace Collector;

/**
 * Stores and manipulates all information about the current Experiment.
 *
 * @todo add detailed description of how this class should be used
 * @todo add validate($pos = 'all') method
 */
class Experiment
{
    /**
     * The position of the current trial.
     *
     * @var int
     */
    public $position;

    /**
     * The current Post Trial position.
     *
     * @var int
     */
    public $postNumber;

    /**
     * All the (shuffled) stimuli array for the Experiment.
     *
     * @var array
     */
    public $stimuli;

    /**
     * The (shuffled) procedure array for the Experiment.
     *
     * @var array
     */
    public $procedure;

    /**
     * Indexed array of recorded responses (corresponds to position).
     *
     * @var array
     */
    public $responses;

    /**
     * The condition information for the Experiment.
     *
     * @var array
     */
    public $condition;

    /**
     * Constructor.
     *
     * @param array $stimuli       The stimuli to use in the Experiment.
     * @param array $procedure     The procedure to follow for the Experiment.
     * @param array $conditionInfo The condition information for the Experiment.
     * @param array $responses     The responses associated with each trial.
     *                             (Generally this will be left as an empty array.)
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
     * Advances the position counter by one, if there are more trials.
     *
     * @return bool Returns true on success, or false if no more trials.
     */
    public function advance()
    {
        if (!$this->isDone()) {
            ++$this->position;
            $this->postNumber = 0;

            return true;
        }

        return false;
    }

    /**
     * Inserts the given trial array at the given position (defaults to the
     * next position).
     * Note: the given array is expected to be a valid trial array, but is not
     * currently validated.
     *
     * @param array $trialArray The trial to insert.
     * @param int   $offset     The number of trials ahead to add the trial.
     *                          Negative offsets are not allowed. If the offset
     *                          indicates a trial that does not exist the new
     *                          trial is inserted at the end of the experiment.
     *
     * @throws \InvalidArgumentException Negative offsets are not allowed.
     *
     * @todo When Trial class is implemented the typehint should point to it.
     */
    public function insert(array $trialArray, $offset = 0)
    {
        if ($offset < 0) {
            throw new \InvalidArgumentException('Negative offsets not allowed');
        }

        $pos = $this->position + $offset;

        if (!isset($this->procedure[$pos])) {
            array_push($this->procedure, $trialArray);
        }

        array_splice($this->procedure, $pos, 0, $trialArray);
    }

    /**
     * Gets the trial number of the last time the current stimuli were used.
     * If the $strict parameter is made true, the stimuli will only be matched
     * if the trial type also matches.
     *
     * @param bool $strict Set true for matching of stimuli and trial types.
     *
     * @return int|bool The position of the trial or false if first use.
     *
     * @see Experiment::getTrial()
     */
    public function getLastUseOfStimulus($strict = false)
    {
        $current = $this->getTrial();
        for ($i = $this->position - 1; $i > -1; --$i) {
            $lastTrial = $this->getTrial($i);

            if (($strict === false || $current['Procedure']['Trial Type'] === $lastTrial['Procedure']['Trial Type'])
                && $current['Stimuli'] === $lastTrial['Stimuli']
            ) {
                return $i;
            }
        }

        return false;
    }

    /**
     * Gets the full response data from the last time the stimulus was used.
     *
     * @param bool $strict Set true for matching of stimuli and trial types.
     *
     * @return array|bool The array of data or false if first use.
     */
    public function getLastResponseOfStimulus($strict = false)
    {
        $lastUse = $this->getLastUseOfStimulus($strict);

        return $lastUse ? $this->responses[$lastUse] : false;
    }

    /**
     * Gets procedure and stimuli data for given procedure row and post trial.
     *
     * @param int $pos  The position of the trial (defaults to current position).
     * @param int $post The post trial number (defaults to current post).
     *
     * @return array Associative array of the "Stimuli" and "Procedure".
     *
     * @uses Experiment::getTrialProcedure()
     * @uses Experiment::getTrialStimuli()
     */
    public function getTrial($pos = null, $post = null)
    {
        $procedure = $this->getTrialProcedure($pos, $post);

        return ['Stimuli' => $this->getStimulus($procedure['Item']),
                'Procedure' => $procedure, ];
    }

    /**
     * Returns the trial information for the given trial offset and post number.
     *
     * @param int $offset The offset from the current position to the desired trial.
     * @param int $post   The post number to use for the retrieved trial.
     *
     * @return array The trial at the indicated offset.
     */
    public function getTrialRelative($offset = 0, $post = null)
    {
        return $this->getTrial($this->position + $offset, $post);
    }

    /**
     * Gets procedure columns for single trial.
     * Optionally specify specific position and post trial number. Post trial
     * columns are transformed into their unposted form (e.g. 'Text' instead of
     * 'Post 1 Text').
     *
     * @param int $pos  The position of the trial (defaults to current position).
     * @param int $post The post trial number (defaults to current post).
     *
     * @return array The array of procedure items for the trial.
     */
    public function getTrialProcedure($pos = null, $post = null)
    {
        if ($pos  === null) {
            $pos = $this->position;
        }
        if ($post === null) {
            $post = $this->postNumber;
        }

        $procRow = $this->procedure[$pos];
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
     *
     * @param array $array The array to process.
     *
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
     *
     * @param array $array The array to process.
     * @param int   $post  The post trial to extract.
     *
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
     *
     * @param int|string $item Typically, contents of "Item" column in the
     *                         procedure file.
     *
     * @return array The stimuli, with column values imploded with | if multiple
     *               items are specified.
     *
     * @uses Experiment::getTrialProcedure()
     * @uses Experiment::getTrial()
     */
    public function getStimulus($item)
    {
        $stimRows = $stimCols = array();

        // populate rows from the stimuli array
        foreach (Helpers::rangeToArray($item) as $item) {
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
     *
     * @param array $data The 1-D array of responses to save, typically $_POST with custom scoring
     * @param int   $pos  The position of the trial (defaults to current position).
     * @param int   $post The post trial number (defaults to current post).
     *
     * @todo recordResponses should probably be a method of a Trial class
     */
    public function recordResponses(array $data, $pos = null, $post = null)
    {
        if ($pos  === null) {
            $pos = $this->position;
        }
        if ($post === null) {
            $post = $this->postNumber;
        }

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
     *
     * @param int $pos  The position of the trial (defaults to current position).
     * @param int $post The post trial number (defaults to current post).
     *
     * @return int|bool The position of the next valid post trial level, or false
     *
     * @see getValidPostTrials()
     */
    public function getNextPostLevel($pos = null, $post = null)
    {
        if ($pos  === null) {
            $pos = $this->position;
        }
        if ($post === null) {
            $post = $this->postNumber;
        }

        $validPosts = $this->getValidPostTrials($pos);

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
     *
     * @param int $pos [Optional] The position to extract Post Trials from
     *
     * @return array Integers specifying which post trials are valid, including
     *               0 for non-post trials.
     */
    public function getValidPostTrials($pos = null)
    {
        if ($pos === null) {
            $pos = $this->position;
        }
        $procRow = $this->procedure[$pos];

        $notTrials = array('off', 'no', '', 'n/a');
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
     *
     * @param int $pos  The position of the trial (defaults to current position).
     * @param int $post The post trial number (defaults to current post).
     *
     * @return array|bool Array as returned by getTrial(), or false
     *
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
     * Gets the proc row index and post trial number of the next trial.
     *
     * @param int $pos  The position of the trial (defaults to current position).
     * @param int $post The post trial number (defaults to current post).
     *
     * @return array|bool, Array with position and post-trial indices, or false
     */
    public function getNextTrialIndex($pos = null, $post = null)
    {
        if ($pos  === null) {
            $pos = $this->position;
        }
        if ($post === null) {
            $post = $this->postNumber;
        }

        $nextPost = $this->getNextPostLevel($pos, $post);

        if ($nextPost === false) {
            $nextPos = $pos + 1;
            $nextPost = 0;
        } else {
            $nextPos = $pos;   // same proc row, different post trial
        }

        return (isset($this->procedure[$nextPos]))
            ? array($nextPos, $nextPost)
            : false;
    }

    /**
     * Adds images from next trial to a hidden div.
     *
     * @param int $pos  [Optional] The position of the trial prior to the trial
     *                  that is being precached (default: current trial).
     * @param int $post [Optional] The post position of the trial prior to the
     *                  trial that is being precached (default: current post).
     *
     * @return string|bool The precache or false if next trial does not exist
     *
     * @uses Experiment::getNextTrial()
     */
    public function getPrecache($pos = null, $post = null)
    {
        $nextTrial = $this->getNextTrial($pos, $post);
        if ($nextTrial !== false) {
            $precache = '<div class="precachenext">';
            foreach (array_values($nextTrial['Stimuli']) as $val) {
                if (Helpers::show($val) !== $val) {
                    $precache .= Helpers::show($val);
                }
            }
            $precache .= '</div>';
        }

        return ($nextTrial !== false) ? $precache : false;
    }

    /**
     * Shows all available info for trial, helpful if trial fails.
     *
     * @param int $pos  The position of the trial (defaults to current position).
     * @param int $post The post trial number (defaults to current post).
     */
    public function showTrialDiagnostics($pos = null, $post = null)
    {
        if ($pos  === null) {
            $pos = $this->position;
        }
        if ($post === null) {
            $post = $this->postNumber;
        }
        $currentTrial = $this->getTrial($pos, $post);

        // clean the arrays used so that they output strings, not code
        $clean_cond = Helpers::arrayCleaner($this->condition);
        $stimfiles = $procfiles = [];
        foreach ($clean_cond as $key => $val) {
            if (strpos($key, 'Stimuli') === 0) {
                $stimfiles[] = $val;
            }
            if (strpos($key, 'Procedure') === 0) {
                $procfiles[] = $val;
            }
        }

        $clean_proc = Helpers::arrayCleaner($currentTrial['Procedure']);
        $clean_stim = Helpers::arrayCleaner($currentTrial['Stimuli']);
        echo '<div class=diagnostics>'
            .'<h2>Diagnostic information</h2>'
            .'<ul>'
            .'<li> Condition Stimuli File: '.implode(', ', $stimfiles)
            .'<li> Condition Procedure File: '.implode(', ', $procfiles)
            .'<li> Condition description: '.$clean_cond['Description']
            .'</ul>'
            .'<ul>'
            .'<li> Trial Number: '.$pos
            .'<li> Trial Type: '.$clean_proc['Trial Type']
            .'<li> Trial max time: '.$clean_proc['Max Time']
            .'</ul>'
            .'<ul>'
            .'<li> Cue: '.Helpers::show($clean_stim['Cue'])
            .'<li> Answer: '.Helpers::show($clean_stim['Answer'])
            .'</ul>';
        $trial = ['Stimuli' => $clean_stim, 'Procedure' => $clean_proc];
        Helpers::readable($trial, 'Information loaded about the current trial');
        Helpers::readable($this->stimuli,      'Information loaded about the stimuli');
        Helpers::readable($this->procedure,    'Information loaded about the procedure');
        Helpers::readable($this->responses,    'Information loaded about the responses');
        echo '</div>';
    }

    /**
     * Converts 2-D associative array into a 1-D array for extract().
     * Array keys are converted to strings suitable for variables. For
     * overlapping column names (e.g. "Cue" in both the Procedure and the
     * Stimuli files, or "Cue" in the Stimulli file and "Post 1 Cue" in the
     * Procedure file) only the first value will be kept and a warning will be
     * triggered.
     *
     * @param array $pos Position of the trial to be extract()ed (default: current)
     *
     * @return array Converted array: columns to lowercase, spaces to underscores
     *
     * @todo prepareAliases should probably be a method of a Trial class
     */
    public function prepareAliases($pos = null)
    {
        $trialValues = array();

        foreach ($this->getTrial($pos) as $name => $row) {
            foreach ($row as $col => $val) {
                $aliasCol = str_replace(' ', '_', strtolower($col));

                // temp hack until we work out how to handle shuffle cols
                if (stripos($aliasCol, 'shuffle') !== false) {
                    continue;
                }

                // trigger warning warning if already set and skip it
                if (isset($trialValues[$aliasCol])) {
                    $err = "Overlap with aliases: $aliasCol is already defined, "
                            ."$col from $name not used";
                    trigger_error($err, E_USER_WARNING);
                    continue;
                }

                $trialValues[$aliasCol] = $val;
            }
        }

        return $trialValues;
    }

    /**
     * Determines if the experiment is done or not.
     *
     * @return bool True if complete, false if at least one more trial exists.
     */
    public function isDone()
    {
        return isset($this->procedure[$this->position]) ? false : true;
    }

    /**
     * Retrieves and organizes data from a specific trial.
     *
     * @param int $pos The position of the trial (defaults to current position).
     *
     * @return array Associative array of the data.
     */
    public function getTrialRecord($pos = null)
    {
        if ($pos === null) {
            $pos = $this->position;
        }
        $data = array();
        $procRow = $this->procedure[$pos];

        $data['Cond'] = $this->condition;
        $data['Proc'] = $procRow;
        $data['Stim'] = $this->getStimulus($procRow['Item']);
        foreach ($this->getValidPostTrials() as $postNum) {
            if ($postNum === 0) {
                continue;
            }  // already grabbed those items
            if (isset($procRow["Post $postNum Item"])) {
                $data["StimPost$postNum"] = $this->getStimulus($procRow["Post $postNum Item"]);
            }
        }
        $data['Resp'] = $this->responses[$pos];

        return $data;
    }
}

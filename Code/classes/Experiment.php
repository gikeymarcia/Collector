<?php
/**
 * Experiment class.
 */

namespace Collector;

/**
 * The Experiment class is the primary access point for running experiments. It
 * has "magic" functions add, get, update, and record which will automatically
 * determine which MainTrial or PostTrial to work with.
 */
class Experiment extends MiniDb implements \Countable
{
    /**
     * The current position of the Experiment (current offset of trials array).
     * @var int
     */
    public $position;
    
    /**
     * The Condition information for this Experiment.
     * @var array
     */
    protected $condition;

    /**
     * The shuffled stimuli information for this Experiment.
     * @var array
     */
    protected $stimuli;

    /**
     * The Trials that were built for this Experiment.
     * @var array
     */
    protected $trials;
    
    /**
     * The Validators that should be used for each trial type. Stored in a
     * trialType => validator array.
     * @var array
     */
    protected $validators;
    
    /**
     * The directory or directories where the Validators can be found.
     * @var array|string
     */
    protected $pathfinder;

    /* Implements
     **************************************************************************/
    /**
     * Returns the number of MainTrials in this Experiment. This is also the
     * implementation of the count function and will be called when count is
     * called on the Experiment like count($experiment).
     *
     * @return int The number of MainTrials in the Experiment.
     */
    public function count()
    {
        return count($this->trials);
    }

    /* Overrides
     **************************************************************************/
    /**
     * Constructor.
     * 
     * @param array        $condition     The condition information.
     * @param array        $stimuli       The stimuli to use.
     * @param array|string $validatorDirs The paths to the trial types folders.
     * @param Pathfinder   $pathfinder    The Experiment's Pathfinder.
     */
    public function __construct(array $condition = array(),
        array $stimuli = array(), Pathfinder $pathfinder = null
    ) {
        $this->trials = array();
        $this->stimuli = $stimuli;
        $this->condition = $condition;
        $this->position = 0;
        $this->pathfinder = $pathfinder;
        $this->validators = array();
        
        parent::__construct();
    }
    
    /**
     * Runs code to prime the trials in the experiment.
     */
    public function warm()
    {
        $this->apply(function($trial) {
            $trial->settings->addSettings($this->getFromStimuli('settings'));
            
            // other warm up code here...
        });
        
        return $this;
    }

    /**
     * Adds a key to the current Trial if the key does not already exist.
     *
     * @param string $name  The key to add.
     * @param mixed  $value The value to assign to the key.
     *
     * @return bool|null Returns true if the key is added, false if it already
     *                   exists, and null on failure.
     *
     * @uses Experiment::getCurrent() Uses getCurrent() to get the current Trial.
     */
    public function add($name, $value = null)
    {
        $trial = $this->getCurrent();

        return isset($trial) ? $trial->add($name, $value) : null;
    }

    /**
     * Gets the value at the given key in the current Trial.
     *
     * @param string $name   The key to retrieve the value for.
     * @param bool   $strict Set to true to restrict the search only to the
     *                       current MainTrial.
     *
     * @return mixed Returns the stored value if the key exists, else null.
     *
     * @uses Experiment::getCurrent() Uses getCurrent() to get the current Trial.
     */
    public function get($name, $strict = true)
    {
        $trial = $this->getCurrent();

        return isset($trial) ? $trial->get($name, $strict) : null;
    }

    /**
     * Updates the value at the given key in the current Trial, adding it if it
     * does not yet exist.
     *
     * @param string $name  The key to add or update.
     * @param mixed  $value The value to assign to the key.
     *
     * @return int|null Returns 1 if a key was updated, 0 if a key was added, or
     *                  null on failure.
     */
    public function update($name, $value)
    {
        $trial = $this->getCurrent();

        return isset($trial) ? $trial->update($name, $value) : null;
    }

    /**
     * Exports all Experiment data.
     *
     * @param string $format The format of the exported data: array or JSON.
     *
     * @return array Returns the formatted array.
     */
    public function export($format = 'array')
    {
        $trials = array();
        foreach ($this->trials as $trial) {
            $trials[] = $trial->export();
        }

        $data = array(
            'Condition' => $this->condition,
            'Stimuli' => $this->stimuli,
            'Trials' => $trials,
        );

        return $this->formatArray($data, $format);
    }

    /* Class specific
     **************************************************************************/
    /**
     * Advances the Experiment to the next Trial, first attempting to advance
     * to the next PostTrial via the MainTrial, then by advancing the Experiment
     * position if the MainTrial and all PostTrials are complete.
     * 
     * @return int Returns 1 if advancing caused us to move to a new MainTrial,
     *             else 0.
     */
    public function advance()
    {
        $trial = $this->getTrial();
        $trial->advance();
        if ($trial->isComplete()) {
            $this->position = $this->isComplete() ? $this->position : $this->position + 1;
            
            return 1;
        }
        
        return 0;
    }

    /**
     * Marks the current MainTrial (including PostTrials) as complete and
     * advances the Experiment position.
     * 
     * @param int $num The number of MainTrials to skip (including the current).
     */
    public function skip($num = 1)
    {
        for ($num; $num > 0; --$num) {
            $this->getTrial()->markComplete();
            if ($this->position >= count($this) - 1) {
                return;
            }
            ++$this->position;
        }
    }
    
    /**
     * Determines whether there are more Trials to run in the Experiment.
     * 
     * @return bool Returns TRUE if the Experiment is complete, else FALSE.
     */
    public function isComplete()
    {
        return ($this->position === count($this->trials) - 1)
            ? $this->getTrial()->isComplete()
            : ($this->position >= count($this->trials));
    }

    /**
     * Records a response to the current trial.
     *
     * @param array $data      The associative array of data to record.
     * @param bool  $overwrite Indicates whether existing data should be updated
     *                         or if values should only be added.
     *
     * @return bool Returns true if the data was recorded, else false.
     */
    public function record(array $data, $overwrite = true)
    {
        $trial = $this->getCurrent();

        return isset($trial) ? $trial->record($data, $overwrite) : false;
    }

    /**
     * Retrieves the given key from the current Trial's Response object. If no
     * key is given, the full Response is returned.
     *
     * @param string $name The key to retrieve the response value from.
     *
     * @return mixed Returns the value of the specified key, if it exists, or
     *               the full Response if no name is specified.
     */
    public function getResponse($name = null)
    {
        $trial = $this->getCurrent();

        return isset($trial) ? $trial->getResponse($name) : null;
    }
    
    /**
     * Gets the current Trial (i.e. if on a PostTrial, get the PostTrial,
     * otherwise return a reference to the MainTrial).
     *
     * @return MainTrial|PostTrial The reference to the current Trial.
     */
    public function getCurrent()
    {
        return $this->getTrial()->getCurrent();
    }

    /**
     * Gets the Trial that occurs directly after the current Trial.
     * 
     * @param int $offset The absolute offset of the Trial to retrieve, e.g. if 
     *                    at PostTrial 1 of a MainTrial with 2 PostTrials, 
     *                    getNext(1) would return PostTrial 2 and getNext(2)
     *                    would return the next MainTrial.
     * 
     * @return MainTrial|PostTrial|null Returns the MainTrial or PostTrial at 
     *                                  the given absolute offset if it exists,
     *                                  else null.
     */
    public function getNext($offset = 1)
    {
        // get current Main and adjust offset back to it
        $current = $this->getCurrent();
        if (get_class($current) !== 'Collector\MainTrial') {
            $offset += $current->position;
            $current = $this->getTrial();
        }
        
        while ($offset > -1) {
            if ($offset < count($current)) {
                // offset is post within current main
                return $current->getPostTrialAbsolute($offset);
            }
            
            // offset is outside of current main
            $offset -= count($current);
            $current = $this->getTrial(1);
            if ($current === null) {
                return null;
            }
        }
    }
    
    /**
     * Gets the Trial at the position directly before the current Trial, i.e.
     * the last PostTrial or MainTrial in the line-up.
     * 
     * Note this function will not honor skips. That is, if you skipped a Trial,
     * this function will not recognize that and will return the last Trial that
     * would have occurred had you not skipped.
     * 
     * @param int $offset The absolute offset of a previous Trial to receive
     *                    (offset should be a positive number), e.g. if at
     *                    PostTrial 1 of a MainTrial with 2 PostTrials, and the
     *                    previous MainTrial had 2 PostTrials, getPrev(1) would
     *                    return the current MainTrial and getPrev(2) would 
     *                    return PostTrial 2 of the previous MainTrial.
     * 
     * @return MainTrial|PostTrial|null Returns the MainTrial or PostTrial at
     *                                  the given absolute offset if it exists,
     *                                  else null.
     */
    public function getPrev($offset = 1)
    {
        $current = $this->getCurrent();
        if (get_class($current) !== 'Collector\MainTrial'
            && ($current->position - $offset) > -1
        ) {
            // offset is reachable in current MainTrial
            return $current->getMainTrial()->getPostTrial(-1 * $offset);
        }

        // offset is in a prior MainTrial, move offset to beginning of this Main
        $offset -= $current->position;
        
        // go back until offset becomes negative or 0 (just past requested)
        while ($offset > 0) {
            $current = $this->getTrial(-1);
            if ($current === null) {
                return null;
            }
            $offset -= count($current);
        }
        
        // requested is now the inverse of the offset
        return $current->getPostTrialAbsolute(-1 * $offset);
    }

    /**
     * Gets the MainTrial at the given relative position.
     *
     * @param int $offset The relative offset of the MainTrial to retrieve.
     *
     * @return MainTrial Returns the MainTrial at the given relative offset.
     */
    public function getTrial($offset = 0)
    {
        return $this->getTrialAbsolute($this->position + $offset);
    }

    /**
     * Gets the trial at the given 0-indexed position.
     *
     * @param int $pos The 0-indexed position of the trial to retrieve.
     *
     * @return MainTrial|null Returns the MainTrial at the given 0-indexed
     *                        position if it exists, else null.
     */
    public function getTrialAbsolute($pos)
    {
        return isset($this->trials[$pos]) ? $this->trials[$pos] : null;
    }
    
    /**
     * Gets the trials at the relative offsets from the current position.
     * 
     * An array of the offsets should be passed, or 'all' or a string that can
     * be converted using Experiment::stringToRange.
     * 
     * @param array|string $offsets The array of offsets for the Trials to
     *                              retrieve. If 'all' is given, all of the
     *                              Trials are returned. If any other string is
     *                              given, the method converts it to a range
     *                              array using Experiment::stringToRange.
     * 
     * @return array The array of Trials with the relative offsets as keys.
     * 
     * @uses stringToRange Uses stringToRange to convert strings to offset range
     *                     arrays.
     */
    public function getTrials($offsets)
    {
        if (!is_array($offsets)) {
            if (trim(strtolower($offsets)) === 'all') {
                return $this->getTrialsAbsolute($offsets);
            }
            
            $offsets = Experiment::stringToRange($offsets);
        }
        
        foreach ($offsets as &$offset) {
            $offset += $this->position; 
        }
        
        return $this->getTrialsAbsolute($offsets);
    }
    
    /**
     * Gets the trials at the absolute positions in the current Experiment.
     * 
     * An array of the offsets should be passed, or 'all' or a string that can
     * be converted using Experiment::stringToRange.
     * 
     * @param array|string $positions The array of positions for the Trials to
     *                                retrieve. If 'all' is given, all of the
     *                                Trials are returned. If any other string
     *                                is given, the method converts it to a
     *                                range array using stringToRange.
     * 
     * @return array The array of Trials with the positions as keys.
     * 
     * @uses stringToRange Uses stringToRange to convert strings to offset range
     *                     arrays.
     */
    public function getTrialsAbsolute($positions)
    {
         if (!is_array($positions)) {
            if (trim(strtolower($positions)) === 'all') {
                return $this->trials;
            }
            
            $positions = Experiment::stringToRange($positions);
        }
        
        $trials = array();
        foreach ($positions as $pos) {
            $trials[$pos] = $this->getTrialAbsolute($pos);
        }
        
        return $trials;
    }
    
    /**
     * Deletes the MainTrial at the given position in the Experiment.
     * 
     * This function will fail when trying to delete previous trials.
     * 
     * @param int $position The absolute position of the MainTrial to delete.
     * 
     * @return boolean|int Returns FALSE if the position to delete is less than
     *                     the current position, 0 if the position to delete
     *                     does not exist, TRUE if the position was deleted, or
     *                     FALSE if the delete failed.
     */
    public function deleteTrialAbsolute($position)
    {
        if ($position < $this->position) {
            return false;
        }
        
        if (!isset($this->trials[$position])) {
            $result = 0;
        }
        
        unset($this->trials[$position]);
        if (isset($this->trials[$position])) {
            $result = false;
        }
        
        $this->updatePositions();
        $this->trials = array_values($this->trials);
        ksort($this->trials);
        
        return isset($result) ? $result : true;        
    }
    
    /**
     * Deletes the MainTrial at the given offset from the current position in 
     * the Experiment.
     * 
     * This function will fail when trying to delete previous trials.
     * 
     * @param int $offset The relative offset of the MainTrial to delete.
     * 
     * @return boolean|int Returns FALSE if the position to delete is less than
     *                     the current position, 0 if the position to delete
     *                     does not exist, TRUE if the position was deleted, or
     *                     FALSE if the delete failed.
     */
    public function deleteTrial($offset = 0)
    {
        return $this->deleteTrialAbsolute($this->position + $offset);
    }
    
    /**
     * Deletes the MainTrials at the given positions in the Experiment.
     * 
     * This function will fail to delete previous trials.
     * 
     * @param array|string $positions The absolute positions of the MainTrials 
     *                                to delete as indicated by an array of the 
     *                                positions or a valid stringToArray string.
     */
    public function deleteTrialsAbsolute($positions)
    {
        if (!is_array($positions)) {
            $positions = Experiment::stringToRange($positions);
        }
        
        // must start deleting from smallest value and update as we go
        sort($positions);
        $offset = 0;
        foreach ($positions as $pos) {
            $result = $this->deleteTrialAbsolute($pos - $offset);
            if ($result !== false) {
                ++$offset;
            }
        }
    }

    /**
     * Deletes the MainTrials at the given offsets from the current position in
     * the Experiment.
     * 
     * This function will fail to delete previous trials.
     * 
     * @param array|string $offsets The absolute positions of the MainTrials to
     *                              delete as indicated by an array of the 
     *                              offsets or a valid stringToArray string.
     */    
    public function deleteTrials($offsets)
    {
        if (!is_array($offsets)) {
            $offsets = Experiment::stringToRange($offsets);
        }
        
        foreach ($offsets as &$offset) {
            $offset += $this->position; 
        }
        
        $this->deleteTrialsAbsolute($offsets);
    }

    /**
     * Gets the stimuli or subset of the stimuli for this Experiment.
     *
     * Output can be restricted to a specific offset or set of offsets using
     * range syntax (e.g. '1, 3, 4::5'). If subset is 'all' or blank, all
     * stimuli are returned.
     *
     * @param int|string $subset The offset or range syntax indicating offsets.
     *
     * @return array The array of requested stimuli.
     */
    public function getStimuli($subset = 'all')
    {
        if ($subset !== 'all' && !empty($subset)) {
            $items = array();
            foreach (Experiment::stringToRange($subset) as $pos) {
                $items[] = $this->getStimulus($pos);
            }
            
            return $items;
        }

        return $this->stimuli;
    }

    /**
     * Gets the stimulus information from the offset in Experiment::stimuli.
     *
     * @param int $pos The offset of Experiment::stimuli to retrieve.
     *
     * @return array|null The stimulus information if it exists, else null.
     */
    public function getStimulus($pos)
    {
        if ($pos !== null && isset($this->stimuli[$pos - 2])) {
            return $this->stimuli[$pos - 2];
        }
        
        return null;
    }
    
    /**
     * Retrieves the value at the given key in the stimuli array for the current
     * Trial.
     * 
     * @param string $name The name of the variable to retrieve.
     * 
     * @return mixed The value for the named variable in the current Trial's
     *               stimuli array.
     */
    public function getFromStimuli($name)
    {
        $trial = $this->getCurrent();

        return isset($trial) ? $trial->getFromStimuli($name) : null;
    }
    
    /**
     * Gets the condition information for this Experiment.
     * 
     * @return array Returns the array of condition information for this object.
     */
    public function getCondition()
    {
        return $this->condition;
    }

    /**
     * Gets the Experiment's Pathfinder object.
     * 
     * @return Pathfinder Returns the Experiment's Pathfinder object.
     */
    public function getPathfinder()
    {
        return $this->pathfinder;
    }
    
    /**
     * Sets the Experiment's Pathfinder object.
     * @param Pathfinder $pathfinder The new Pathfinder object to use.
     */
    public function setPathfinder(Pathfinder $pathfinder)
    {
        $this->pathfinder = $pathfinder;
    }
    
    /**
     * Inserts a MainTrial at the specified position. If no position is given,
     * the trial is inserted at the end. The position numbers for all trials are
     * updated after this function is called. Note: the position is 0-indexed
     * and the CSV rows on the procedure sheet start at 2.
     *
     * Sample usage:
     * ```php
     * // get the position you want to insert at
     * $pos = $expt->next('trialtype', 'study')->position;
     *
     * // create a new trial from previous
     * $dup = $expt->duplicate($expt->getTrial(-1));
     *
     * // insert duplicate at position and then at end
     * $expt->insertTrial($dup, $pos);
     * $expt->insertTrial($dup);
     * ```
     *
     * @param MainTrial|array $trial The MainTrial or the data to construct the
     *                               MainTrial with.
     * @param int             $pos   The 0-indexed position at which to insert. 
     *                               If null is given (default) the trial will 
     *                               be added at the end of the Experiment. If a 
     *                               negative offset is given, the insertion is 
     *                               made at that position relative to the end 
     *                               of the Experiment.
     *
     * @return MainTrial Returns the inserted trial.
     */
    public function addTrialAbsolute($trial = array(), $pos = null)
    {
        if (is_array($trial)) {
            $trial = new MainTrial($trial);
        } else if (is_object($trial) && get_class($trial) === "Collector\MainTrial") {
            $trial = $trial->copy();
        } else {
            throw new \InvalidArgumentException('Add trial functions require '
                . 'that the to-be-added trial information is an array or '
                . 'already constructed MainTrial ');
        }
        
        $trial->setExperiment($this);

        array_splice($this->trials, is_null($pos)
            ? count($this->trials)
            : $pos, 0, array($trial)
        );
        $this->updatePositions();

        return $trial;
    }

    /**
     * Inserts an array of MainTrials at the specified position. If no position
     * is given, the trial is inserted at the end. The position numbers for all
     * trials are updated after this function is called. Note: the position is
     * 0-indexed and the CSV rows on the procedure sheet start at 2.
     *
     * @param array $array The array of data arrays to create the MainTrials from.
     * @param int  $pos    The 0-indexed position at which to insert.
     */
    public function addTrialsAbsolute(array $array, $pos = null)
    {
        foreach ($array as $i => $data) {
            $this->addTrialAbsolute($data, is_null($pos) ? $pos : ($i + $pos));
        }
    }
    
    /**
     * Adds a Trial or trial information array at the given relative offset.
     * 
     * @param MainTrial|array $trial  The MainTrial or array to create a 
     *                                MainTrial from.
     * @param int             $offset The offset to insert the trial at.
     * 
     * @return MainTrial Returns the MainTrial that was added.
     */
    public function addTrial($trial = array(), $offset = 1)
    {
        return $this->addTrialAbsolute($trial, $this->position + $offset);
    }
    
    /**
     * Adds multiple MainTrials from an array at the given relative offset. The
     * array can consist of any combination of MainTrials or trial information
     * arrays that MainTrials can be created from.
     * 
     * @param array $trials The trials to insert.
     * @param int   $offset The offset at which to insert the trials.
     */
    public function addTrials(array $trials, $offset = 1)
    {
        $this->addTrialsAbsolute($trials, $this->position + $offset);
    }
    
    /**
     * Updates the position properties of all the trials. This function is
     * called each time a trial is added or removed.
     */
    protected function updatePositions()
    {
        $i = 0;
        foreach ($this->trials as $trial) {
            $trial->position = $i;
            ++$i;
        }
    }
    
    /**
     * Execute an anonymous function on every trial in the experiment. The
     * function must accept a Trial as the first argument.
     * 
     * @param \Closure $function The function to apply on each trial.
     * @param array    $args     The arguments to use in the function call.
     */
    public function apply(\Closure $function, array $args = array())
    {
        foreach ($this->trials as $maintrial) {
            $maintrial->apply($function, $args);
       } 
    }
    
    /**
     * Validates a Trial using the Validator registered for its trial type, if
     * the Validator exists.
     * 
     * @return array Returns an indexed array of the errors found when running
     *               validation and information about the Trials with errors.
     */
    public function validate()
    {
        $errors = array();
        foreach ($this->trials as $trial) {
            $result = $trial->validate();
            foreach ($result as $error) {
                $errors[] = $error;
            }
        }
        
        return $errors;
    }
    
    /**
     * Adds a validator to be used by the Experiment. The trial type it should
     * be used for must be specified.
     * 
     * @param string    $trialtype The trial type to use the Validator for.
     * @param Validator $validator The Validator to add.
     * @param bool      $merge     Indicates whether the new Validator should
     *                             replace any existing (FALSE) or merge with
     *                             them (TRUE).
     */
    public function addValidator($trialtype, Validator $validator, $merge = false)
    {
        if (!array_key_exists($trialtype, $this->validators)) {
            $this->validators[$trialtype] = $validator;
        } else if ($merge) {
            $this->validators[$trialtype]->merge($validator);
        }
    }
    
    /**
     * Retrieves the Validator for the given trial type, if one exists.
     * 
     * @param string $trialtype The trial type to retrieve the Validator for.
     * 
     * @return Validator|null The Validator for the given trial type, else null.
     */
    public function getValidator($trialtype)
    {
        if (!isset($this->validators[$trialtype])) {
            $this->loadValidator($trialtype);
        }
        
        return $this->validators[$trialtype];
    }
    
    /**
     * Loads a Validator if it is not present in the validators array.
     * 
     * @param string $trialtype The trial type to load the Validator for.
     */
    protected function loadValidator($trialtype)
    {
        $this->validators[$trialtype] = isset($this->pathfinder)
            ? ValidatorFactory::createSpecific(
                $trialtype,
                array($this->pathfinder->get('Custom Trial Types'),
                      $this->pathfinder->get('Trial Types')
                ),
                false)
            : null;
    }
    
    /**
     * Sets an array of Validators to the validators property. By default the
     * old Validators are replaced, but the merge argument can be used to 
     * combine the groups.
     * 
     * @param array $validators The Validators to set.
     * @param bool $merge       Indicates whether the new Validator should
     *                          replace any existing (FALSE) or merge with
     *                          them (TRUE).
     */
    public function setValidators(array $validators, $merge = false)
    {
        if (!$merge) {
            $this->validators = $validators;
        } else {
            $this->validators = ValidatorFactory::mergeGroup(
                $this->validators, $validators
            );
        }
    }

    /**
     * Gets a clone of the full MainTrial at the given offset(relative to current).
     *
     * @param int $pos   The position of the MainTrial to copy, relative to the
     *                   current position.
     * 
     * @return MainTrial The cloned MainTrial.
     */
    public function copy($pos = 0)
    {
        return $this->getTrial($pos)->copy();
    }
    
    /**
     * Converts a string in selective range syntax to an array of the digits.
     * Syntax: separate terms with commas (',') or semicolons (';'), and
     * indicate ranges with double colons ('::'). Example:
     * ```php
     * Experiment::stringToRange('4; 6, 8::9,11::13');
     * // returns array(4, 6, 8, 9, 11, 12, 13)
     * ```
     *
     * @param string $string The string in range syntax to convert.
     *
     * @return array The array of the digits indicated by the string.
     */
    public static function stringToRange($string)
    {
        $csv = str_replace(';', ',', str_replace(' ', '', $string));
        $arr = explode(',', $csv);
        $out = array();
        foreach ($arr as $val) {
            $pos = strpos($val, '::');
            if ($pos !== false) {
                $val = range(substr($val, 0, $pos), substr($val, $pos + 2));
            }
            $out = array_merge($out, is_array($val) ? $val : array($val));
        }
        
        foreach ($out as $i => $string) {
            if (!is_numeric($string)) {
                unset($out[$i]);
            }
        }

        return $out;
    }
    
    /**
     * Determines if a string can be converted to a range using
     * Experiment::stringToRange().
     * 
     * @param string $string The string to check.
     * 
     * @return bool True if the string can be converted to a range, else false.
     */
    public static function isValidStringToRange($string)
    {
        return is_numeric(str_replace(array(' ', ',', ';', '::'), '', $string));
    }
}

<?php
/**
 * MainTrial class.
 */

namespace Collector;

/**
 * MainTrials are the bread and butter of Experiments. MainTrials always belong
 * to an Experiment can contain multiple PostTrials. Note that though MainTrials
 * have access to the basic MiniDb functions (add, get, update, export) they
 * have been overridden and may behave differently than expected.
 */
class MainTrial extends Trial implements \Countable
{
    /**
     * The offset of this Trial in the Experiment trial array.
     * @var int
     */
    public $position;

    /**
     * The array of PostTrials for this MainTrial.
     * @var array
     */
    protected $postTrials;

    /**
     * The current position in the post trial queue.
     * @var type
     */
    protected $postPosition;

    /**
     * Constructor.
     *
     * @param array      $data The trial information from the Procedure file.
     * @param Experiment $expt The Experiment that this Trial belongs to.
     */
    public function __construct(array $data = array(), Experiment $expt = null)
    {
        // initialize properties
        $this->position = null;
        $this->postPosition = 0;
        $this->postTrials = array();

        // construct the trial
        parent::__construct($data, $expt);
    }

    /* Implements
     **************************************************************************/
    /**
     * Marks the Trial (and all PostTrials) as complete and seals the Response.
     */
    public function markComplete()
    {
        $this->postPosition = null;
        $this->complete = true;
        $this->response->seal();
        foreach ($this->postTrials as $post) {
            $post->markComplete();
        }
    }

    /**
     * Validates the MainTrial and related PostTrials using the Validator 
     * registered for their trial types, if the Validators exist.
     * 
     * @return array Returns an indexed array of the errors found when running
     *               validation and the information about the Trial with errors.
     */
    public function validate()
    {
        $errors = array();
        $validator = $this->expt->getValidator($this->data['trial type']);
        if (isset($validator)) {
            $result = $validator->validate($this);
            foreach ($result as $error) {
                $errors[] = $error;
            }
        }
        
        foreach ($this->postTrials as $post) {
            $result = $post->validate();
            foreach ($result as $error) {
                $errors[] = $error;
            }
        }
        
        return $errors;
    }
    
    /**
     * Updates the named key in the relatedFiles MiniDb for the current Trial 
     * with the path to the given related file (like 'script.php').
     * 
     * @param string $name The name of the related file being added.
     * @param string $path The full path of the related file.
     * 
     * @return bool Returns true if the key is added, else false.
     */
    public function setRelatedFile($name, $path)
    {
        return $this->postPosition === 0 
            ? $this->relatedFiles->update($name, $path)
            : $this->getPostTrial()->setRelatedFile($name, $path);
    }
    
    /**
     * Gets the named path from the relatedFiles MiniDb for the current Trial.
     * 
     * @param string $name The name of the related file to get the path for.
     * 
     * @return mixed Returns the stored value if the key exists, else null.
     */
    public function getRelatedFile($name)
    {
        return $this->postPosition === 0
            ? $this->relatedFiles->get($name)
            : $this->getPostTrial()->getRelatedFile($name);
    }
    
    /**
     * Returns the number of Trials in the PostTrials property, including this
     * trial (i.e. a MainTrial with 2 PostTrials has a count of 3). 
     * 
     * This is also the implementation of the count function and will be called
     * when count is called on the MainTrial like count($trial).
     *
     * @return int The number of Trials in this MainTrial including itself.
     */
    public function count()
    {
        return count($this->postTrials) + 1;
    }
    
    /* Overrides
     **************************************************************************/
    /**
     * Adds a key to the current trial (determined by the postPosition) if the
     * key does not already exist. If strict is passed as true, the add function
     * is strictly called on this MainTrial.
     *
     * @param string $name   The key to add.
     * @param mixed  $value  The value to assign to the key.
     * @param bool   $strict Set to true to restrict the addition only to the
     *                       current MainTrial.
     *
     * @return bool Returns true if the key is added, else false.
     */
    public function add($name, $value = null, $strict = false)
    {
        if ($strict || ($this->postPosition === 0)) {
            return parent::add($name, $value);
        }

        $post = $this->getPostTrial();

        return isset($post) ? $post->add($name, $value)
                            : parent::add($name, $value);
    }

    /**
     * Gets the value of the key for the current trial (determined by the
     * postPosition). If no value exists at the given key, the MainTrial and
     * then the Response are checked for the key. If strict is passed as true,
     * the get function is strictly called on this MainTrial.
     *
     * @param string $name   The key to retrieve the value for.
     * @param bool   $strict Set to true to restrict the search only to the
     *                       current MainTrial.
     *
     * @return mixed Returns the stored value if the key exists, else null.
     */
    public function get($name, $strict = true)
    {
        $val = parent::get($name);
        if ($strict) {
            return $val;
        }

        if (($this->postPosition > 0) && (null !== $this->getPostTrial())) {
            $postval = $this->getPostTrial()->get($name, true);
            $val = isset($postval) ? $postval : $val;
        }

        return isset($val) ? $val : $this->response->get($name);
    }

    /**
     * Updates the value at the given key in the current trial (determined by
     * the postPosition), adding it if it does not yet exist.
     *
     * @param string $name  The key to add or update.
     * @param mixed  $value The value to assign to the key.
     */
    public function update($name, $value)
    {
        return ($this->postPosition > 0) && (null !== $this->getPostTrial())
            ? $this->getPostTrial()->update($name, $value)
            : parent::update($name, $value);
    }

    /**
     * Exports the full trial information (including PostTrials).
     *
     * @param string $format The format of the exported data: PHP array or JSON.
     *
     * @return mixed The formatted trial information.
     */
    public function export($format = 'array')
    {
        $data['main'] = $this->data;
        $data['main']['response'] = $this->response->export();
        foreach ($this->postTrials as $i => $post) {
            $num = $i;
            $data["post {$num}"] = $post->export();
        }

        return $this->formatArray($data, $format);
    }
    
    /**
     * Checks to see if this MainTrial has been marked complete, if not it
     * checks to see if the MainTrial is complete before marking it complete.
     * 
     * @return bool Returns true if the MainTrial is complete, else false.
     */
    public function isComplete()
    {
        if ($this->complete === true) {
            return true;
        }
        
        foreach ($this->postTrials as $trial) {
            if (!$trial->isComplete()) {
                return false;
            }
        }
        
        $this->markComplete();
        
        return true;
    }

    /* Class specific
     **************************************************************************/
    /**
     * Advances the Trial to the next PostTrial if applicable or marks it
     * complete if no more PostTrials exist.
     */
    public function advance()
    {
        if (!empty($this->postTrials) && $this->postPosition !== null
            && $this->postPosition < count($this->postTrials)
        ) {
            if ($this->postPosition !== 0) {
                $this->postTrials[$this->postPosition]->markComplete();
            }
            ++$this->postPosition;
        } else {
            // no post trials to run, all done
            $this->markComplete();
        }
    }

    /**
     * Creates a post trial from the trial data and adds it to this MainTrial.
     *
     * @param array $data The trial data to construct the PostTrial from.
     */
    public function addPostTrial(array $data = array())
    {
        $post = new PostTrial($this, $data);
        $post->position = count($this->postTrials) + 1;
        $this->postTrials[$post->position] = $post;

        return $this;
    }

    /**
     * Gets the PostTrial at the given 1-indexed position in this Trial (the
     * 0 position returns this MainTrial).
     *
     * @param int $pos The 1-indexed position to retrieve the PostTrial from.
     *
     * @return PostTrial|null Returns the PostTrial at the given location if the
     *                        offset exists, this MainTrial if 0, else null.
     */
    public function getPostTrialAbsolute($pos)
    {
        if ($pos === 0) {
            return $this;
        }

        return isset($this->postTrials[$pos]) ? $this->postTrials[$pos] : null;
    }

    /**
     * Gets the PostTrial at the given relative position in this Trial.
     *
     * @param int $pos The relative position to retrieve the PostTrial from.
     *
     * @return PostTrial Returns the PostTrial at the relative location.
     */
    public function getPostTrial($pos = 0)
    {
        return !$this->complete
            ? $this->getPostTrialAbsolute($this->postPosition + $pos)
            : null;
    }

    /**
     * Gets the current Trial within this Trial (i.e. if on a PostTrial, get the
     * PostTrial, otherwise return a reference to this MainTrial).
     *
     * @return MainTrial|PostTrial The reference to the current Trial.
     */
    public function getCurrent()
    {
        return $this->getPostTrial();
    }
    
    /**
     * Deletes the PostTrial at the given position in this MainTrial.
     * 
     * This function will fail when trying to delete previous trials.
     * 
     * @param int $position The absolute position of the PostTrial to delete.
     * 
     * @return boolean|int Returns FALSE if the position to delete is less than
     *                     the current position, 0 if the position to delete
     *                     does not exist, TRUE if the position was deleted, or
     *                     FALSE if the delete failed.
     */
    public function deletePostTrialAbsolute($position)
    {
        if ($position < $this->postPosition || $position === 0) {
            return false;
        }
        
        if (!isset($this->postTrials[$position])) {
            $result = 0;
        }
        
        unset($this->postTrials[$position]);
        if (isset($this->postTrials[$position])) {
            $result = false;
        }
        
        // reindex the post trials array starting at 1
        if (!empty($this->postTrials)) {
            $this->updatePositions();
            $this->postTrials = array_combine(
                range(1, count($this->postTrials)),
                array_values($this->postTrials)
            );
            ksort($this->postTrials);
        }
        
        return isset($result) ? $result : true;        
    }
    
    /**
     * Deletes the PostTrial at the given offset from the current position in 
     * this MainTrial.
     * 
     * This function will fail when trying to delete previous trials.
     * 
     * @param int $offset The relative offset of the PostTrial to delete.
     * 
     * @return boolean|int Returns FALSE if the position to delete is less than
     *                     the current position, 0 if the position to delete
     *                     does not exist, TRUE if the position was deleted, or
     *                     FALSE if the delete failed.
     */
    public function deletePostTrial($offset = 0)
    {
        return $this->deletePostTrialAbsolute($this->postPosition + $offset);
    }
    
    /**
     * Deletes the PostTrials at the given positions in this MainTrial.
     * 
     * This function will fail to delete previous trials.
     * 
     * @param array|string $positions The absolute positions of the MainTrials 
     *                                to delete as indicated by an array of the 
     *                                positions or a valid stringToArray string.
     */
    public function deletePostTrialsAbsolute($positions)
    {
        if (!is_array($positions)) {
            $positions = Experiment::stringToRange($positions);
        }
        
        // must start deleting from smallest value and update as we go
        sort($positions);
        $offset = 0;
        foreach ($positions as $pos) {
            $result = $this->deletePostTrialAbsolute($pos - $offset);
            if ($result !== false) {
                ++$offset;
            }
        }
    }

    /**
     * Deletes the PostTrials at the given offsets from the current position in
     * this MainTrial.
     * 
     * This function will fail to delete previous trials.
     * 
     * @param array|string $offsets The absolute positions of the PostTrials to
     *                              delete as indicated by an array of the 
     *                              offsets or a valid stringToArray string.
     */    
    public function deletePostTrials($offsets)
    {
        if (!is_array($offsets)) {
            $offsets = Experiment::stringToRange($offsets);
        }
        
        foreach ($offsets as &$offset) {
            $offset += $this->postPosition; 
        }
        
        $this->deletePostTrialsAbsolute($offsets);
    }

    /**
     * Applies a function to this trial and all of the related PostTrials. The
     * callable function must accept a Trial as it's first parameter: each trial
     * will be injected via this parameter.
     * 
     * @param Closure $function The function to run with each Trial. The
     *                          function must accept a Trial as the first
     *                          parameter.
     * @param array   $args     Any arguments to pass to the function after the
     *                          Trial is injected.
     */
    public function apply(\Closure $function, array $args = array())
    {
        $params = array_values($args);
        
        // apply to MainTrial
        array_unshift($params, $this);
        call_user_func_array($function, $params);
        
        // apply to PostTrials
        foreach ($this->postTrials as $trial) {
            $params[0] = $trial;
            call_user_func_array($function, $params);
        }
    }

    /**
     * The Experiment::duplicate method clones trials completely. After cloning
     * this magic method resets the responses array.
     *
     * @return MainTrial Returns this object with positions and response reset.
     */
    public function __clone()
    {
        $this->complete = false;
        $this->position = null;
        $this->postPosition = 0;
        $this->response = new Response();
        
        foreach ($this->postTrials as $pos => $trial) {
            $this->postTrials[$pos] = clone $trial;
            $this->postTrials[$pos]->position = $pos;
        }
    }
    
    /**
     * Updates the positions of the post trials.
     */
    protected function updatePositions()
    {
        $i = 1;
        foreach ($this->postTrials as $post) {
            $post->position = $i++;
        }
    }
}

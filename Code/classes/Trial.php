<?php
/**
 * Trial class.
 */

namespace Collector;

/**
 * This abstract class provides a foundation for creating different subclasses
 * of trials. Currently, MainTrial and PostTrial are the only extensions of this
 * class.
 */
abstract class Trial extends MiniDb
{
    /**
     * The Experiment that this Trial belongs to.
     * @var Experiment
     */
    protected $expt;

    /**
     * The information about the Trial from the Procedure file, with the
     * information about the stimulus injected from the Stimuli file into 'item'.
     * @var array
     */
    protected $data;

    /**
     * The state of the Trial's completeness.
     * @var bool
     */
    protected $complete;

    /**
     * The Response object for this Trial.
     * @var Response
     */
    protected $response;
    
    /**
     * Files related to this Trial like the scoring, display, styles.
     * @var MiniDb
     */
    protected $relatedFiles;

    /**
     * Constructor.
     *
     * @param array      $data The trial information from the Procedure file.
     * @param Experiment $expt The Experiment that this Trial belongs to.
     */
    public function __construct(array $data = array(), Experiment $expt = null)
    {
        // initialize
        $this->expt = $expt;
        $this->complete = false;
        $this->response = new Response();
        $this->relatedFiles = new MiniDb();

        // add all keys from data with non-empty values
        $cleanData = array();
        foreach ($data as $key => $val) {
            if (!empty($val)) {
                $cleanData[$key] = $val;
            }
        }
        parent::__construct($cleanData);
    }

    /* Abstracts
     **************************************************************************/
    /**
     * Should mark the trial as complete and run any related functions to seal
     * the Response so that it cannot be edited further.
     */
    abstract public function markComplete();
    
    /**
     * Should find the appropriate Validator if it exists and return any errors
     * found during validation along with relevant information about the Trial.
     * 
     * @return array Should return an array of errors found during validation.
     */
    abstract public function validate();
    
    /**
     * Should update the named key in the relatedFiles MiniDb for the current
     * Trial with the path to the given related file (like 'script.php').
     * 
     * @param string $name The name of the related file being added.
     * @param string $path The full path of the related file.
     * 
     * @return bool Should return true if the key is added, else false.
     */
    abstract public function addRelatedFile($name, $path);
    
    /**
     * Should gets the named path from the relatedFiles MiniDb.
     * 
     * @param string $name The name of the related file to get the path for.
     * 
     * @return mixed Should return the stored value if the key exists, else null.
     */
    abstract public function getRelatedFile($name);

    /* Overrides
     **************************************************************************/
    /**
     * Overrides MiniDB::update to force stimulus injection when updating 'item'.
     *
     * @param string $name  The key to add or update.
     * @param mixed  $value The value to assign to the key.
     * 
     * @return bool Returns true if a key was updated, else false if a key was 
     *              added.
     */
//    public function update($name, $value)
//    {
//        $result = parent::update($name, $value);
//        if (strtolower($name) === 'item') {
//            $this->injectStimulus();
//        }
//
//        return $result;
//    }
    
    /**
     * Overrides MiniDB::add to force stimulus injection when adding 'item'.
     * 
     * @param string $name  The key to add.
     * @param mixed  $value The value to assign to the key.
     *
     * @return bool Returns true if the key is added, else false.
     */
//    public function add($name, $value = null)
//    {
//        $result = parent::add($name, $value);
//        if (strtolower($name) === 'item' && isset($value)) {
//            $this->injectStimulus();
//        }
//
//        return $result;
//    }
    
    /**
     * Overrides MiniDB::get to also check the Trial's stimuli for a key.
     * 
     * @param string $name The key for the value to retrieve.
     * 
     * @return mixed The value if it exists, else null.
     */
    public function get($name)
    {
        $val = parent::get($name);
        if (!isset($val) || strtolower($name) === 'item') {
            $val = $this->getFromStimuli($name);
        }
        
        return $val;
    }

    /* Class specific
     **************************************************************************/
    /**
     * Retrieves the value for the given key in the Trial's 'item' array.
     * 
     * @param string $name The key to retrieve from the 'item' array.
     * 
     * @return mixed Returns the value at the key in the 'item' array if it
     *               exists, else null.
     */
    public function getFromStimuli($name)
    {
        $item = $this->expt->getStimulus(parent::get('item'));
        if (is_array($item) && !empty($item)) {
            $item = array_change_key_case($item, CASE_LOWER);
            
            return isset($item[strtolower($name)])
                ? $item[strtolower($name)]
                : null;
        }
        
        return null;
    }
    
    /**
     * Returns an array of the Trial's information to help debug it.
     * 
     * @param bool $noObjects Set TRUE to convert this Trial object using export
     *                        before outputting the information.
     * 
     * @returns array Returns an array of the Trial's information.
     */
    public function getDebugInfo($noObjects = false)
    {
        return array(
            'position' => $this->position,
            'postPosition' => $this->postPosition,
            'complete' => $this->complete,
            'data' => $this->data,
            'object' => $noObjects ? $this->export() : $this,
        );
    }
    
    /**
     * Retrieves the given key from the Trial's Response object.
     *
     * @param string $name The key to retrieve the response value from.
     *
     * @return mixed Returns the value of the specified key.
     */
    public function getResponse($name = null)
    {
        return isset($name) ? $this->response->get($name) : $this->response;
    }

    /**
     * Records information to the Response array.
     *
     * @param array $data      The associative array of data to record.
     * @param bool  $overwrite Indicates whether existing data should be updated
     *                         or if values should only be added.
     *
     * @return bool Returns true if the data was recorded, else false.
     */
    public function record(array $data, $overwrite = true)
    {
        if ($this->response->isSealed()) {
            return false;
        }

        foreach ($data as $key => $val) {
            $overwrite ? $this->response->update($key, $val)
                       : $this->response->add($key, $val);
        }

        return true;
    }

    /**
     * Gets the Experiment object that this Trial belongs to.
     *
     * @return Experiment The Experiment that this Trial belongs to.
     */
    public function getExperiment()
    {
        return $this->expt;
    }

    /**
     * Sets the Experiment variable. Used for manually changing the Experiment
     * that a Trial belongs to.
     *
     * @param Experiment $expt The Experiment that this Trial should belong to.
     */
    public function setExperiment(Experiment $expt)
    {
        $this->expt = $expt;
//        $this->injectStimulus();
    }

    /**
     * Checks to see if the Trial is marked as complete.
     *
     * @return bool Returns true if the Trial is complete, else false.
     */
    public function isComplete()
    {
        return $this->complete;
    }

    /**
     * Injects the array of stimulus data into the item key if applicable. If
     * the item key refers to multiple stimuli (e.g. 3::5) an array of these
     * arrays is injected. Stimuli for survey trial types are not updated (as
     * they should point to the survey file, not a line in the Stimuli file).
     */
//    public function injectStimulus()
//    {
//        $item = isset($this->data['item']) ? $this->data['item'] : null;
//        var_dump($item);
//        if (!empty($item) && (is_string($item) || is_numeric($item))
//            && $this->data['trial type'] !== 'survey'
//        ) {
//            $stimarr = $this->expt->getStimuli($item);
//            $this->data['item'] = count($stimarr) === 1 ? $stimarr[0] : $stimarr;
//        }
//    }
}

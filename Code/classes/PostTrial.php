<?php
/**
 * PostTrial class.
 */

namespace Collector;

/**
 * PostTrials are Trials that are always associated with MainTrials and come
 * after the presentation of the MainTrial. When MainTrial::advance is called,
 * the PostTrials are called in turn. The standard MiniDb functions can be used
 * (add, get, update, export) but note that PostTrial::get has some specific
 * overrides in it.
 */
class PostTrial extends Trial
{
    /**
     * The offset of this PostTrial in its MainTrial's array.
     * @var int
     */
    public $position;

    /**
     * The MainTrial that this PostTrial belongs to.
     * @var MainTrial
     */
    protected $main;

    /**
     * Constructor.
     *
     * @param MainTrial $main The MainTrial that this PostTrial belongs to.
     * @param array     $data The trial information from the Procedure file.
     */
    public function __construct(MainTrial $main, array $data = array())
    {
        $this->main = $main;
        parent::__construct($data, $main->getExperiment());
    }

    /* Implements
     **************************************************************************/
    /**
     * Marks this PostTrial as complete.
     */
    public function markComplete()
    {
        $this->response->seal();
        $this->complete = true;
    }
    
    /**
     * Validates the Trial using the Validator registered for its trial type, if
     * the Validator exists.
     * 
     * @return array Returns an indexed array of the errors found when running
     *               validation and the information about the Trial with errors.
     */
    public function validate()
    {
        $validator = $this->expt->getValidator($this->data['trial type']);
        
        return isset($validator) ? $validator->validate($this) : array();
    }
    
    /**
     * Updates the named key in the relatedFiles MiniDb for this Trial with the
     * path to the given related file (like 'script.php').
     * 
     * @param string $name The name of the related file being added.
     * @param string $path The full path of the related file.
     * 
     * @return bool Returns true if the key is added, else false.
     */
    public function setRelatedFile($name, $path)
    {
        return $this->relatedFiles->update($name, $path);
    }
    
    /**
     * Gets the named path from the relatedFiles MiniDb.
     * 
     * @param string $name The name of the related file to get the path for.
     * 
     * @return mixed Returns the stored value if the key exists, else null.
     */
    public function getRelatedFile($name)
    {
        return $this->relatedFiles->get($name);
    }

    /* Overrides
     **************************************************************************/
    /**
     * Gets the value of the key for the current trial (determined by the
     * postPosition). If no value exists at the given key, the MainTrial and
     * then the Response are checked for the key.
     *
     * @param string $name   The key to retrieve the value for.
     * @param bool   $strict Set to true to restrict the search only to the
     *                       current PostTrial.
     *
     * @return mixed Returns the stored value if the key exists, else null.
     */
    public function get($name, $strict = true)
    {
        $val = parent::get($name);
        
        if (!isset($val)) {
            $val = $this->main->getFromStimuli($name);
        }
        
        if ($strict) {
            return $val;
        }

        if (!isset($val)) {
            $val = $this->main->get($name, true);
        }

        return isset($val) ? $val : $this->getResponse($name);
    }

    /**
     * Exports this PostTrial's information.
     *
     * @param string $format The format of the exported data: PHP array or JSON.
     *
     * @return mixed The formatted PostTrial information.
     */
    public function export($format = 'array')
    {
        $data = $this->data;
        $data['response'] = $this->response->export();

        return $this->formatArray($data, $format);
    }

    /* Class specific
     **************************************************************************/
    /**
     * Gets the MainTrial that this PostTrial belongs to.
     *
     * @return MainTrial The MainTrial that this PostTrial belongs to.
     */
    public function getMainTrial()
    {
        return $this->main;
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
        $this->response = new Response();
    }
}

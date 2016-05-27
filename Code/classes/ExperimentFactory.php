<?php
/**
 * ExperimentFactory class.
 */

namespace Collector;

/**
 * Class for use in creating Experiments easily. Normally the Experiment should
 * be created and then Trials should be added to the Experiment. The create
 * function in this class accepts a raw procedure array, as returned from 
 * a Procedure object, and converts each line to a Trial and adds it to the
 * Experiment.
 */
class ExperimentFactory
{
    /**
     * Creates a new Experiment instance with the given condition, procedure,
     * stimuli, and validator directory arrays. Procedure and stimuli arrays 
     * should be pre-stitched and pre-shuffled.
     * 
     * This static factory function differs from normal instantiation of an
     * Experiment because it accepts a procedure array which is processed for
     * post trial information and then looped through to add new Trials to the
     * Experiment.
     * 
     * @param array      $condition  The array of condition information.
     * @param array      $procedure  The array of procedure information.
     * @param array      $stimuli    The array of stimuli information.
     * @param Pathfinder $pathfinder The Experiment's Pathfinder, which should
     *                               be able to find various related files like
     *                               trial types, validators, displays, etc.
     * 
     * @return Experiment Returns a fully-instantiated Experiment class.
     * 
     * @uses separatePostTrials Uses separatePostTrials to sort information in
     *                          the procedure array according to which post
     *                          trial it belongs to.
     */
    public static function create(
        array $condition = array(),
        array $procedure = array(),
        array $stimuli = array(),
        Pathfinder $pathfinder = null
    ) {
        $validatorDirs = isset($pathfinder)
                       ? array($pathfinder->get('Custom Trial Types'), 
                               $pathfinder->get('Trial Types'))
                       : null;
        
        $expt = new Experiment($condition, $stimuli, $validatorDirs);
        foreach ($procedure as $row) {
            // organize and clean up the row data
            $data = self::separatePostTrials($row);
            self::removeOffPostTrials($data);
            
            // create the trial
            $trial = $expt->addTrialAbsolute($data['main']);
            foreach ($data['post'] as $post) {
                $trial->addPostTrial($post);
            }
        }
        
        $expt->warm();
        if (isset($pathfinder)) {
            self::addRelatedFiles($expt, $pathfinder);
        }
            
        return $expt;
    }

    /**
     * Given the raw procedure data array, this function separates out the main
     * keys and the keys prepended by "Post [#]" into a 'main' and 'post' array.
     * Matching 'Post [#]' numbers are put into the same arrays with the prefix
     * stripped from the keys.
     *
     * @param array $procData The procedure array to process.
     *
     * @return array Returns an associative array of the separated data.
     */
    private static function separatePostTrials(array $procData)
    {
        $data = array('main' => array(), 'post' => array());

        foreach($procData as $key => $val) {
            $key = strtolower($key);
            if (strncmp($key, "post ", 5) === 0) {
                $postNum = trim(substr($key, 0, 7));
                $postKey = trim(substr($key, 7));
                $data['post'][$postNum][$postKey] = $val;
                continue;
            }

            $data['main'][$key] = $val;
        }

        ksort($data['post']);

        return $data;
    }
    
    /**
     * Removes any post trial arrays from the procedure data that have trial
     * types "off" or "no" or empty values.
     * 
     * @param array $procData The procedure data to filter.
     */
    private static function removeOffPostTrials(array &$procData)
    {
        foreach ($procData['post'] as $num => $post) {
            $lowerKeys = array_change_key_case($post, CASE_LOWER);
            if (array_key_exists('trial type', $lowerKeys)
                && (in_array($lowerKeys['trial type'], array('off', 'no'))
                    || empty($lowerKeys['trial type']))
            ) {
                unset($procData['post'][$num]);
            }
        }
    }
    
    /**
     * Adds related files to the Trials in the Experiment (e.g. 'display.php').
     * 
     * @param Experiment $expt       The Experiment to add related files to.
     * @param Pathfinder $pathfinder The Pathfinder that will find the files.
     */
    private static function addRelatedFiles(Experiment &$expt, Pathfinder $pathfinder)
    {
        $allRelated = Helpers::getAllTrialTypeFiles($pathfinder);
        $expt->apply(function($trial) use ($allRelated) {
            $type = $trial->get('trial type');
            $class = (new \ReflectionClass($trial))->getShortName();
            $position = ($class === 'PostTrial')
                      ? $trial->getMainTrial()->position ." (post: {$trial->position})"
                      : $trial->position;
                
            $relatedFiles = $allRelated[strtolower($type)];
            if (empty($relatedFiles)) {
                throw new \Exception('Could not retrieve related files for '
                    . "Trial of type {$class} with trial type '{$type}' at "
                    . "position {$position} in the Experiment (0-indexed). This"
                    . " usually happens when the trial type is not defined in "
                    . "Procedure, when the trial type does not exist, or when "
                    . "an invalid Pathfinder is specified.");
            }
            
            foreach ($relatedFiles as $name => $path) {
                $trial->setRelatedFile($name, $path);
            }
        });
    }
}

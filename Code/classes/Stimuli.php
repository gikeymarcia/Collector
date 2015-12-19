<?php
/**
 * Stimuli class.
 */

/**
 * Controls the error checking for stimuli files.
 *
 * @see ControlFile
 * 
 * @todo the current functions should be abstract functions in ControlFile
 */
class Stimuli extends ControlFile
{
    /**
     * Runs all of the error checking required for procedure files.
     */
    public function errorCheck()
    {
        // check that the file has all of the necessary columns
        $this->requiredColumns(array('Cue', 'Answer'));

        // implement any other checks here
    }
}

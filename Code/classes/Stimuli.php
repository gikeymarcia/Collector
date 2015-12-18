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
     * 
     * @todo rename?
     */
    public function errorCheck()
    {
        $this->checkColumns();
        // implement each check as its own method
    }

    /**
     * Uses ControlFile::requiredColumns() to check that the file has all of the
     * necessary columns.
     * 
     * @todo rename?
     */
    protected function checkColumns()
    {
        $required = array('Cue', 'Answer');
        $file = 'Stimuli';
        $this->requiredColumns($file, $required);
    }
}

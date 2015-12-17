<?php
/**
 * Stimuli class.
 */

/**
 * Controls the error checking for stimuli files.
*  @see ControlFile
 */
class Stimuli extends ControlFile
{
    /**
     * @todo docblock for Stimuli::errorCheck()
     */
    public function errorCheck()
    {
        $this->checkColumns();
        // implement each check as its own method
    }
    
    /**
     * @todo docblock for Stimuli::checkColumns()
     */
    protected function checkColumns()
    {
        $required = array('Cue', 'Answer');
        $file = 'Stimuli';
        $this->requiredColumns($file, $required);
    }
}
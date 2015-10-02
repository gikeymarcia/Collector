<?php
/**
 * Controls the error checking for stimuli files
*  Parent class: controlFileSetup handles the reading and stitching together of files
 */
class stimuli extends controlFileSetup
{
    public function errorCheck()
    {
        $this->columns();
        // I will do each check as it's own method
    }
    protected function columns()
    {
        $required = array('Cue', 'Answer');
        $file = 'Stimuli';
        $this->requiredColumns($file, $required);
    }
    public function getStimuliArray()
    {
        return $this->stitched;
    }
}
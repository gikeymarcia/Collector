<?php
/**
*  Controls the error checking for procedure file
*  Parent class: controlFileSetup handles the reading and stitching together of files
*/
class procedure extends controlFileSetup
{
    /**
     * Will run all of the error checking required for procedure files
     */
    public function errorCheck()
    {
        $this->columns();
    }
    /**
     * Uses parent method requiredColumns() to check that the file has all necessary columns
     */
    protected function columns()
    {
        $required = array('Item', 'Trial Type', 'Max Time', 'Text');
        $file = 'Procedure';
        $this->requiredColumns($file, $required);
    }
    /**
     * How to get the stimuli file that will be used for this participant
     * @return array     Stitched and unshuffled version of the stimuli file
     */
    public function getProcedureArray()
    {
        return $this->stitched;
    }
}

?>
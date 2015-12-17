<?php
/**
 * Procedure class.
 */

/**
*  Controls the error checking for procedure file.
*  @see ControlFile
*/
class Procedure extends ControlFile
{
    /**
     * @todo rename?
     * 
     * Runs all of the error checking required for procedure files.
     */
    public function errorCheck()
    {
        $this->columns();
    }
    /**
     * @todo rename?
     * 
     * Uses ControlFile::requiredColumns() to check that the file has all of the
     * necessary columns.
     */
    protected function columns()
    {
        $required = array('Item', 'Trial Type', 'Max Time');
        $file = 'Procedure';
        $this->requiredColumns($file, $required);
    }
    
    /**
     * Gets the procedure file that will be used for this participant.
     * @return array Stitched and unshuffled version of the procedure file.
     */
    public function getProcedureArray()
    {
        return $this->stitched;
    }
}

?>
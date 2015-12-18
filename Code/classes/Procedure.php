<?php
/**
 * Procedure class.
 */

/**
 *  Controls the error checking for procedure file.
 *
 *  @see ControlFile
 * 
 * @todo the current functions should be abstract functions in ControlFile
 */
class Procedure extends ControlFile
{
    /**
     * Runs all of the error checking required for procedure files.
     * 
     * @todo rename?
     */
    public function errorCheck()
    {
        $this->columns();
    }
    /**
     * Uses ControlFile::requiredColumns() to check that the file has all of the
     * necessary columns.
     * 
     * @todo rename?
     */
    protected function columns()
    {
        $required = array('Item', 'Trial Type', 'Max Time');
        $file = 'Procedure';
        $this->requiredColumns($file, $required);
    }

    /**
     * Gets the procedure file that will be used for this participant.
     *
     * @return array Stitched and unshuffled version of the procedure file.
     */
    public function getProcedureArray()
    {
        return $this->stitched;
    }
}

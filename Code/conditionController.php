<?php
/**
 * Controls the selecting, assigning, and returning of condition information
 */
class ConditionController
{
    private $selected;

    /**
     * Saves the condition selection made from index.php
     */
    public function selectedCondition()
    {
        $this->selected = filter_input(INPUT_GET, 'Condition', FILTER_SANITIZE_STRING);
    }

}
?>
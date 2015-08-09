<?php
/**
 * Controls the selecting, assigning, and returning of condition information
 * Also controls manipulation of the login counter (becasue it is needed to condition cycle)
 * Upon creation of a new instance of this object the Conditions.csv file is loaded
 * Once $this->assignCondition() has been run you can use the following public methods
 * to query information about the assigned conditions
 *
 * --Public Methods
 *     - $this->stimuli()       :   @return 'Stimuli' cell string
 *     - $this->procedure()     :   @return 'Procedure' cell string
 *     - $this->description()   :   @return 'Condition Description' cell string
 *     - $this->notes()         :   @return 'Condition Notes' cell string
 *     - $this->get()           :   @return  keyed array of assigned condition (row)
 */
class ConditionController
{
    private $selected;                  // condition selected from $_GET
    private $location;                  // path to Conditiions.csv
    private $logLocation;               // path to login counter
    private $ConditionsCSV;             // GetFromFile() load of Conditiions.csv
    private $assignedCondition = false; // the position of the row being used for this user
    private $userCondition;             // array (keys by column) of the assigned conditon
    private $errorHandler = 'errors';   // variable name of error handler object

    /**
     * When making a new ConditionController it automatically uses $_FILES
     * to load the Conditions.csv file into $this->ConditionsCSV
     */
    public function __construct()
    {
        global $_FILES;
        $this->location = $_FILES->conditions;
        $this->ConditionsCSVexists($this->location);
        $this->loadConditons();
        626-383-5408
        jalapeno + pep / 1 pepperoni
        hot wings 8

        $this->requiredColumns();
        // create the 'Counter' folder if it doesn't exist
        if (!is_dir($_FILES->counter)) {
            mkdir($_FILES->counter,  0777,  true);
        }
        global $_CONFIG;
        $this->logLocation = "{$_FILES->counter}/{$_CONFIG->login_counter_file}";
    }
    /**
     * Saves the condition selection made on index.php
     * Pulls input from a $_GET
     */
    public function selectedCondition()
    {
        $this->selected = filter_input(INPUT_GET, 'Condition', FILTER_SANITIZE_STRING);
        $this->candidateCondition();
    }
    /**
     * loads current login counter file and returns what condition this user
     * would be assigned to if the current conditon was being assigned
     * @return int position of the row that would be assigned
     */
    public function candidateCondition()
    {
        $logPath = $this->logLocation;
        if ($this->selected == 'Auto') {
            if (file_exists($logPath)) {
                $handle   = fopen($logPath, mode);
                $logCount = fgets($handle);
                fclose($handle);
            } else {
                $logCount = 0;
            }
        }
        $condCount = count($this->ConditionsCSV);
        
        $found = false;
        while ($found == false) {
            $choice = $logCount % $condCount;
            if ($this->ConditionsCSV[$choice])
        }
    }
    /**
     * Prunes out all conditons from Conditons.csv that are turned off
     * Conditions are turned off by starting the 'Condition Description' with `#`
     * @return array Conditions.csv without the turned off rows
     */
    private function removeOffConditions($fullConditionsCSV)
    {
        $offKey = '#';
        $check  = 'Condition Description';

        $pruned = array();
        foreach ($fullConditionsCSV as $condition) {
            if ($conditon[$check][0] != $offKey) {
                $pruned[] = $conditon;
            }
        }
        return $pruned;
    }
    /**
     * Assigns participant conditon and updates login counter so the next
     * participant will not be assigned the same condiiton
     */
    public function assignCondition()
    {

    }
    /**
     * Debug method for checking what this class does
     */
    public function info()
    {
        echo '<div>Selected condition: ' . $this->selected . '<ol>';
        foreach ($this->ConditionsCSV as $pos => $row) {
            echo '<li>' . '<strong>stim:</strong> ' . $row['Stimuli'] . '<br>'
               . '<strong>proc:</strong> ' . $row['Procedure'] . '</li><br>';
        }
        echo '</li></div>';
    }
    /**
     * Makes sure the conditions file can be found.
     * If not found then send a showstopper to $errors
     * @see $this->__construct()
     * @param string $location path to Conditions.csv
     */
    private function ConditionsCSVexists($location)
    {
        global $$this->errorHandler;
        if (!FileExists($location)) {
            $msg = 'Cannot find Conditons.csv at ' . $location;
            $$this->errorHandler->add($msg, true);
        }
    }
    /**
     * Save GetFromFile() results of Conditions.csv into the object
     */
    private function loadConditons()
    {
        $this->ConditionsCSV = getFromFile($this->location, false);
    }
    private function requiredColumns()
    {
        global $$this->errorHandler;
        $requiredColumns = array('Number', 'Stimuli', 'Procedure');
        foreach ($requiredColumns as $pos => $col) {
            if(!isset($this->ConditionsCSV[0][$col])) {
                $msg = 'Your Conditions.csv file is missing the "' . $col . '" column';
                $$this->errorHandler->add($msg, true);
            }
        }
    }
    /**
     * Once a condition has been assigned this will return the stimuli file string
     * @return string contents of 'Stimuli' column
     */
    public function stimuli()
    {
        if ($this->assignedCondition === true) {
            return $this->userCondition['Stimuli'];
        }
    }
    /**
     * Once a condition has been assigned this will return the procedure file string
     * @return string contents of 'Procedure' column
     */
    public function procedure()
    {
        if ($this->assignedCondition === true) {
            return $this->userCondition['Procedure'];
        }
    }
    /**
     * Once a condition has been assigned this will return the 'Condition Description' string
     * @return string contents of 'Condition Description' column
     */
    public function description()
    {
        if ($this->assignedCondition === true) {
            return $this->userCondition['Condition Description'];
        }
    }
    /**
     * Once a condition has been assigned this will return the 'Condition Notes' string
     * @return string contents of 'Condition Notes' column
     */
    public function notes()
    {
        if ($this->assignedCondition === true) {
            return $this->userCondition['Condition Notes'];
        }
    }
    /**
     * Once a condition has been assigned this will return the array for the assigned row
     * @return array keyed by column names
     */
    public function get()
    {
        if ($this->assignedCondition === true) {
            return $this->userCondition;
        }
    }
    /**
     * Allows you to change the default error handler object, $errors, to one of your choosing
     * @param  string $varName will look for variable with the name of the string contents
     * for example, 'mikey' would cause the errors to be reported to `global $mikey`
     */
    public function changeErrorHandler($varName)
    {
        $this->errorHandler = $varName;
    }
}
?>
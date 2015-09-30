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
class Condition
{
    private $selection;                 // condition selected from $_GET
    private $location;                  // how to get to Conditions.csv
    private $logLocation;               // path to login counter
    private $ConditionsCSV;             // GetFromFile() load of Conditiions.csv
    private $assignedCondition = false; // tells whether a conditions has been assigned or not
    private $userCondition;             // array (keys by column) of the assigned conditon
    private $errorHandler = 'errors';   // variable name of error handler object


    /**
     * This class needs the following information to function
     * @param string $conditionsLoc relative path to Conditions.csv
     * @param string $counterDir    relative path to the directory where the counter is held
     * @param string $logLocation   relative path to the login counter file 
     */
    public function setNeededData($conditionsLoc, $logLocation)
    {
        $this->location = $conditionsLoc;
        $this->logLocation = $logLocation;
        $this->loadConditons();
        $this->makeCounterDir($logLocation);

    }
    private function makeCounterDir($logLocation)
    {
        $dir = dirname($logLocation);
        if (!is_dir($dir)) {
            mkdir($dir,  0777,  true);
        }
    }
    /**
     * Saves the condition selection made on index.php
     * Pulls input from a $_GET
     */
    public function selectedCondition($selection)
    {
        $selection = filter_var($selection, FILTER_SANITIZE_STRING);
        if (is_numeric($selection) OR ($selection == 'Auto')) {
            $this->selection = $selection;
        } else {
            global $$this->errorHandler;
            $msg = 'Your condition selection: "' . $selection . '" is not valid';
            $$this->errorHandler->add($msg, true);
        }
    }
    // /**
    //  * loads current login counter file and returns what condition this user
    //  * would be assigned to if the current conditon was being assigned
    //  * @return int position of the row that would be assigned
    //  */
    // public function candidateCondition()
    // {
    //     $logPath = $this->logLocation;
    //     if ($this->selection == 'Auto') {
    //         if (file_exists($logPath)) {
    //             $handle   = fopen($logPath, mode);
    //             $logCount = fgets($handle);
    //             fclose($handle);
    //         } else {
    //             $logCount = 0;
    //         }
    //     }
    //     $condCount = count($this->ConditionsCSV);
        
    //     $found = false;
    //     // while ($found == false) {
    //     //     $choice = $logCount % $condCount;
    //     //     // if ($this->ConditionsCSV[$choice])
    //     // }
    // }
    /**
     * Assigns participant conditon and updates login counter so the next
     * participant will not be assigned the same condiiton
     */
    public function assignCondition()
    {
        $validConds = $this->removeOffConditions();
        if ($this->selection == 'Auto') {
            $log = $this->getLogVal();
            $index = $log % count($validConds);
            $this->userCondition = $validConds[$index];
            $this->incrementLog($log);
            $this->assignedCondition = true;
        } else {
            $index = $this->selection;
            if (isset($validConds[$index])) {
                $this->userCondition = $validConds[$index];
                $this->assignedCondition = true;
            }
        }        
    }
    private function getLogVal()
    {
        $logPath = $this->logLocation;
        if (file_exists($logPath)) {
            $handle   = fopen($logPath, "r");
            $logCount = fgets($handle);
            fclose($handle);
            return $logCount;
        } else {
            return 0;
        }
    }
    private function incrementLog($oldVal)
    {
        $newVal = $oldVal + 1;
        $handle = fopen($this->logLocation, "w");
        fputs($handle, $newVal);
        fclose($handle);
    }
    private function removeOffConditions()
    {
        $on = array();
        foreach ($this->ConditionsCSV as $row) {
            if ($row['Condition Description'][0] === '#') {
                continue;
            } else {
                $on[] = $row;
            }
        }
        return $on;
    }
    /**
     * Send it the array from a row of a Conditions.csv read
     * Must be formatted as a getFromFile() array
     * e.g., = array("Number"=> 1, "Stimuli"=>'something.csv',...)
     */
    public function overrideCondition($array)
    {
        if(is_array($array)) {
            $this->userCondition = $array;
        }
    }
    /**
     * Debug method for checking what this class does
     */
    public function info()
    {
        echo '<div>Selected condition: ' . $this->selection . '<ol>';
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
    private function conditionsExists()
    {
        global $$this->errorHandler;
        if (!FileExists($this->location)) {
            $msg = 'Cannot find Conditons.csv at: ' . $this->location;
            $$this->errorHandler->add($msg, true);
        }
    }
    /**
     * Save GetFromFile() results of Conditions.csv into the object
     */
    private function loadConditons()
    {
        $this->conditionsExists();
        $this->ConditionsCSV = getFromFile($this->location, false);
        $this->requiredColumns();
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
    public function changeErrorHandler($customErrorHandler)
    {
        $this->errorHandler = $customErrorHandler;
    }
}
?>
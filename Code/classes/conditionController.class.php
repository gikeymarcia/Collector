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
 *     - $this->description()   :   @return 'Description' cell string
 *     - $this->notes()         :   @return 'Notes' cell string
 *     - $this->get()           :   @return  keyed array of assigned condition (row)
 */
class conditionController
{
    protected $selection;                 // condition selected from $_GET
    protected $location;                  // how to get to Conditions.csv
    protected $logLocation;               // path to login counter
    protected $showFlagged;
    protected $ConditionsCSV;             // GetFromFile() load of Conditiions.csv
    protected $assignedCondition = false; // tells whether a conditions has been assigned or not
    protected $userCondition;             // array (keys by column) of the assigned conditon
    protected $errObj;                    // name of error handler object


    /**
     * This class needs the following information to function
     * @param string            $conditionsLoc relative path to Conditions.csv
     * @param string            $counterDir    relative path to the directory where the counter is held
     * @param string            $logLocation   relative path to the login counter file
     * @param errorController   $errorHandler  object that will capture and log
     */
    public function __construct($conditionsLoc, $logLocation, $showFlagged = false, errorController $errorHandler)
    {
        $this->errObj   = $errorHandler;
        $this->location = $conditionsLoc;
        $this->logLocation = $logLocation;
        $this->showFlagged = $showFlagged;
        $this->makeCounterDir($logLocation);
        $this->loadConditons();
    }
    /**
     * Save GetFromFile() results of Conditions.csv into the object
     */
    protected function loadConditons()
    {
        $this->conditionsExists();
        $this->ConditionsCSV = getFromFile($this->location, false);
        $this->requiredColumns();
    }
    protected function makeCounterDir($logLocation)
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
            $msg = "Your condition selection: $selection is not valid";
            $this->errObj->add($msg, true);
        }
    }
    /**
     * Assigns participant conditon and updates login counter so the next
     * participant will not be assigned the same condiiton
     */
    public function assignCondition()
    {
        $validConds = $this->removeOffConditions();
        if ($this->selection == 'Auto') {
            $this->assignedCondition = $this->getLogVal();
            $this->userCondition = $validConds[$this->assignedCondition];
        } else {
            $index = $this->selection;
            if (isset($validConds[$index])) {
                $this->userCondition = $validConds[$index];
                $this->assignedCondition = $index;
            }
        }        
    }
    protected function getLogVal()
    {
        $file = $this->logLocation;                 // where to find the log
        if (file_exists($file)) {
            $handle = fopen($file, "r");
            $available = fgetcsv($handle);          // read values as exploded csv
            fclose($handle);
            if (is_numeric($available[0])) {        // if the first position is a #
                $this->updateLogFile($available);   // update log file
                return $available[0];               // return # we read
            } else {
                $this->populateLogFile();           // fill log file with conditions
                return $this->getLogVal();          // read the log file
            }
        } else {
            $this->populateLogFile();               // fill log file with conditions
            return $this->getLogVal();              // read the log file
        }
    }
    protected function populateLogFile()
    {
        $file  = $this->logLocation;
        $conds = count($this->removeOffConditions());
        $possible = array();
        for ($i=0; $i < $conds; $i++) {
            $possible[] = $i;
        }
        shuffle($possible);
        $handle = fopen($file, 'w');
        fputcsv($handle, $possible);
        fclose($handle);
    }
    protected function updateLogFile($condsFound)
    {
        $used = array_shift($condsFound);       // pull off the value we used
        $log  = $this->logLocation;
        $handle = fopen($log, 'w');
        fputcsv($handle, $condsFound);             // write what is left as csv
        fclose($handle);
    }
    protected function removeOffConditions()
    {
        if ($this->showFlagged === true) {
            return $this->ConditionsCSV;
        }
        $on = array();
        foreach ($this->ConditionsCSV as $row) {
            if ($row['Description'][0] === '#') {
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
            $this->assignedCondition = 'overridden-' . microtime(true);
        }
    }
    /**
     * Debug method for checking what this class does
     */
    public function info()
    {
        // echo '<div>Selected condition: ' . $this->selection . '<ol>';
        echo "<div>Selected contion: $this->selection <ol>";
        foreach ($this->ConditionsCSV as $pos => $row) {
            echo "<li>
                      <strong>stim:</strong>{$row['Stimuli 1']}<br>
                      <strong>proc:</strong>{$row['Procedure 1']}
                  </li>";
        }
        echo '</ol></div>';
    }
    /**
     * Makes sure the conditions file can be found.
     * If not found then send a showstopper to $errors
     * @see $this->__construct()
     * @param string $location path to Conditions.csv
     */
    protected function conditionsExists()
    {
        
        if (!FileExists($this->location)) {
            $msg = "Cannot find Conditions.csv at $this->location";
            $this->errObj->add($msg, true);
        }
    }
    
    protected function requiredColumns()
    {
        $requiredColumns = array('Stimuli 1', 'Procedure 1', 'Description');
        foreach ($requiredColumns as $pos => $col) {
            if(!isset($this->ConditionsCSV[0][$col])) {
                $msg = "Your Conditions.csv file is missing the $col column";
                $this->errObj->add($msg, true);
            }
        }
    }
    /**
     * Once a condition has been assigned this will return the stimuli file string
     * @return string contents of 'Stimuli' column
     */
    public function stimuli($num = 1, $safeSearch = false)
    {
        if ($this->assignedCondition !== false) {
            if (empty($this->userCondition["Stimuli $num"])) {
                if ($safeSearch == true) {  return false;  }
                else {
                    $msg = "You have tried to get the value for 'Stimuli $num' but that column does not exist in your Conditions.csv file at
                            <code>$this->location</code>.";
                    $this->errObj->add($msg, true);
                }
            } else {
                return $this->userCondition["Stimuli $num"];
            }
        } else {
            $msg = "You are trying to get the value of 'Stimuli $num' before you have assigned a condition.
                    <br>Run \$cond->assignCondition() before attempting to use this method.";
            $this->errObj->add($msg);
        }
    }
    /**
     * Check conditions file for all cells that point to stimuli files
     * @return string implode by comma of all stim
     */
    public function allStim()
    {
        $valid = array();
        $toCheck = $this->colsContaining("Stimuli");
        for ($i=1; $i < count($toCheck); $i++) { 
            if (         isset($this->ConditionsCSV["Stimuli $i"])
                AND strtolower($this->ConditionsCSV["Stimuli $i"]) != "off"
                AND            $this->ConditionsCSV["Stimuli $i"]  != ""
                AND            $this->ConditionsCSV["Stimuli $i"]  != "."
            ){
                $valid[] = $this->ConditionsCSV["Stimuli $i"];
            }
        }
        return implode(",", $valid);
    }
    protected function colsContaining($needle)
    {
        $matches = array();
        foreach ($this->ConditionsCSV[0] as $key => $cell) {
            if (strpos($key,$needle) !== false) {
                $matches[] = $key;
            }
        }
        return $matches;
    }
    /**
     * Once a condition has been assigned this will return the first procedre file string
     * @param  string optionally pass an interger to tell me which procedure to pull
     * @return string contents of 'Procedure' column
     */
    public function procedure($num = 1, $safeSearch = false)
    {
        if ($this->assignedCondition !== false) {
            if (empty($this->userCondition["Procedure $num"])) {
                if ($safeSearch == true) {  return false;  }
                else {
                    $msg = "You have tried to get the value for 'Procedure $num' but that column does not exist in your Conditions.csv file at
                            <code>$this->location</code>.";
                    $this->errObj->add($msg, true);
                }
            } else {
                return $this->userCondition["Procedure $num"];
            }
        } else {
            $msg = "You are trying to get the value of 'Procedure $num' before you have assigned a condition.
                    <br>Run \$cond->assignCondition() before attempting to use this method.";
            $this->errObj->add($msg);
        }
    }
    public function allProc()
    {
        $valid = array();
        $toCheck = $this->colsContaining("Procedure");
        for ($i=1; $i < count($toCheck); $i++) { 
            if (         isset($this->ConditionsCSV["Procedure $i"])
                AND strtolower($this->ConditionsCSV["Procedure $i"]) != "off"
                AND            $this->ConditionsCSV["Procedure $i"]  != ""
                AND            $this->ConditionsCSV["Procedure $i"]  != "."
            ){
                $valid[] = $this->ConditionsCSV["Procedure $i"];
            }
        }
        return implode(",", $valid);
    }

    /**
     * Once a condition has been assigned this will return the 'Description' string
     * @return string contents of 'Description' column
     */
    public function description()
    {
        if ($this->assignedCondition !== false) {
            return $this->userCondition['Description'];
        }
    }
    /**
     * Once a condition has been assigned this will return the 'Notes' string
     * @return string contents of 'Notes' column
     */
    public function notes()
    {
        if ($this->assignedCondition !== false) {
            return $this->userCondition['Notes'];
        }
    }
    /**
     * Once a condition has been assigned this will return the array for the assigned row
     * @return array keyed by column names
     */
    public function get()
    {
        if ($this->assignedCondition !== false) {
            return $this->userCondition;
        }
    }
    /**
     * Gets the index, which is sort of the row number, of the assigned condition
     * @return bool|int|string false if not set, int if assigned typically, string if overridden
     */
    public function getAssignedIndex() {
        return $this->assignedCondition;
    }
    /**
     * Allows you to change the default error handler object, $errors, to one of your choosing
     * @param  string $varName will look for variable with the name of the string contents
     * for example, 'mikey' would cause the errors to be reported to `global $mikey`
     */
    public function changeErrorHandler(errorController $newErrHandler)
    {
        $this->errObj = $newErrHandler;
    }
}
?>
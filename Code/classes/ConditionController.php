<?php
/**
 * ConditionController class.
 */

/**
 * Controls the condition information.
 * 
 * Controls the selecting, assigning, and returning of condition information, as
 * well as manipulation of the login counter (becasue it is needed to condition 
 * cycle). Upon creation of a new instance of this object the Conditions.csv 
 * file is loaded. Once $this->assignCondition() has been run you can use the 
 * following public methods to query information about the assigned conditions.
 *     - $this->stimuli()     : retrieves 'Stimuli' cell string
 *     - $this->procedure()   : retrieves 'Procedure' cell string
 *     - $this->description() : retrieves 'Description' cell string
 *     - $this->notes()       : retrieves 'Notes' cell string
 *     - $this->get()         : retrieves keyed array of assigned condition (row)
 */
class ConditionController
{
    /**
     * Condition selected from $_GET.
     * @var int|string
     */
    protected $selection;  
    
    /**
     * Path to "Conditions.csv".
     * @var string
     */
    protected $location;
    
    /**
     * Path to login counter.
     * @var string
     */
    protected $logLocation;
    
    /**
     * Indicates whether conditions flagged as "off" should be shown.
     * @var bool
     */
    protected $showFlagged;
    
    /**
     * Full array of Conditions.csv as loaded by GetFromFile().
     * @var array
     * @see GetFromFile()
     */
    protected $ConditionsCSV;
    
    /**
     * Indicates whether a condition has been assigned.
     * @var bool
     */
    protected $assignedCondition = false;
    
    /**
     * Information about the assigned conditon.
     * @var array
     */
    protected $userCondition;
    
    /**
     * The associated ErrorController object.
     * @var ErrorController
     */
    protected $errObj;


    /**
     * Constructor
     * @param string $conditionsLoc Relative path to "Conditions.csv".
     * @param string $logLocation Relative path to the login counter file.
     * @param bool $showFlagged Inital value for the showFlagged property.
     * @param ErrorController $errorHandler Object that logs errors.
     */
    public function __construct($conditionsLoc, $logLocation, $showFlagged = false, ErrorController $errorHandler)
    {
        $this->errObj   = $errorHandler;
        $this->location = $conditionsLoc;
        $this->logLocation = $logLocation;
        $this->showFlagged = $showFlagged;
        $this->makeCounterDir($logLocation);
        $this->loadConditons();
    }
    
    /**
     * Loads the "Conditions.csv" information.
     * Stores the getFromFile() results of Conditions.csv to 
     * ConditionController::ConditionsCSV
     * @see getFromFile()
     */
    protected function loadConditons()
    {
        $this->conditionsExists();
        $this->ConditionsCSV = getFromFile($this->location, false);
        $this->requiredColumns();
    }
    
    /**
     * Ensures that there is a valid directory for the login counter.
     * @param string $logLocation The desired location of the login counter.
     */
    protected function makeCounterDir($logLocation)
    {
        $dir = dirname($logLocation);
        if (!is_dir($dir)) {
            mkdir($dir,  0777,  true);
        }
    }
    
    /**
     * @todo rename ConditionController::selectedCondition() to setSelection
     * 
     * Sets the selected condition.
     * Sets the condition to the value specified. If the selected condition is 
     * not numeric or 'Auto' an error will fire.
     * @param int|string $selection The number of the condition or 'Auto'.
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
     * Assigns the user's condition.
     * Sets the participant's condition to the condition specified by
     * ConditionController::selection and updates login counter so the next
     * participant will not be assigned the same condition.
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
    
    /**
     * @todo documentation for ConditionController::getLogVal()
     * @return int
     */
    protected function getLogVal()
    {
        $file = $this->logLocation;
        if (file_exists($file)) {
            $handle = fopen($file, "r");
            
            // read values as exploded csv
            $available = fgetcsv($handle);
            fclose($handle);
            
            if (is_numeric($available[0])) {
                // the first position is a #: update log file and return it
                $this->updateLogFile($available);
                return $available[0];
            } else {
                // fill log file with conditions and try reading again
                $this->populateLogFile();
                return $this->getLogVal();
            }
        } else {
            // log file does not yet exist: create and fill the log file
            $this->populateLogFile();
            return $this->getLogVal();
        }
    }
    
    /**
     * @todo documentation for ConditionController::populateLogFile()
     */
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
    
    /**
     * @todo documentation for ConditionController::updateLogFile()
     * @param array $condsFound
     */
    protected function updateLogFile(array $condsFound)
    {
        // pull off the value we used
        $used = array_shift($condsFound);
        
        // write what is left as csv
        $log  = $this->logLocation;
        $handle = fopen($log, 'w');
        fputcsv($handle, $condsFound);
        fclose($handle);
    }
    
    /**
     * Removes conditions that are turned off.
     * @return array The conditions flagged as being on.
     */
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
     * Overrides the current condition information with the desired information,
     * then logs the time it was overridden. Must be formatted as a 
     * Conditions.csv read in as a getFromFile() array,
     * e.g., array('Number' => 1, 'Stimuli' => 'something.csv', ...)
     * @param array $array The condition information to use.
     * @uses ConditionController::userCondition Replaces this array.
     * @uses ConditionController::assignedCondition Updates this to the time the
     *          condition was overridden
     */
    public function overrideCondition($array)
    {
        if (is_array($array)) {
            $this->userCondition = $array;
            $this->assignedCondition = 'overridden-' . microtime(true);
        }
    }
    
    /**
     * @todo documentation for ConditionController::info()
     * @todo change ConditionController::info() to return a string
     * 
     * Debug method for checking what this class does
     */
    public function info()
    {
        echo "<div>Selected condition: $this->selection <ol>";
        foreach ($this->ConditionsCSV as $pos => $row) {
            echo "<li>
                      <strong>stim:</strong>{$row['Stimuli 1']}<br>
                      <strong>proc:</strong>{$row['Procedure 1']}
                  </li>";
        }
        echo '</ol></div>';
    }
    
    /**
     * @todo rename ConditionController::conditionsExists() to conditionsFileExists()
     * 
     * Indicates that "Conditions.csv" exists.
     * Makes sure the conditions file can be found. If not found then trigger a
     * showstopper with the ErrorController.
     * @return bool True if the file exists.
     * @see ConditionController::__construct()
     * @see ConditionController::location
     */
    protected function conditionsExists()
    {
        if (!FileExists($this->location)) {
            $msg = "Cannot find Conditions.csv at $this->location";
            $this->errObj->add($msg, true);
        }
    }
    
    /**
     * Checks that necessary columns are present in the condition information.
     */
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
     * Gets the stimuli file string for the assigned condition, or false if it
     * has not been assigned, or an error if it does not exist.
     * @param string $num Optionally pass the integer for a specific procedure.
     * @param bool $safeSearch Forces false instead of an error.
     * @return string Contents of the 'Stimuli' column.
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
     * Gets the strings from all cells that point to stimuli files.
     * @param int $index Optionally indicate which row to retrieve from.
     * @return string Comma-separated list of all stimuli files.
     * @uses ConditionController::assignedCondition Used if no index is given.
     */
    public function allStim($index = null)
    {
        if ($index === null) $index = $this->assignedCondition;
        $valid = array();
        $toCheck = $this->colsContaining("Stimuli");
        $row = $this->ConditionsCSV[$index];
        for ($i=1; $i <= count($toCheck); $i++) { 
            if (         isset($row["Stimuli $i"])
                AND strtolower($row["Stimuli $i"]) != "off"
                AND            $row["Stimuli $i"]  != ""
                AND            $row["Stimuli $i"]  != "."
            ){
                $valid[] = $row["Stimuli $i"];
            }
        }
        return implode(",", $valid);
    }
    
    /**
     * Searches for a string within the column names (keys).
     * @param string $needle The string to search for in the keys.
     * @return array All of the keys which contain the search string.
     * @uses ConditionController::ConditionsCSV Searches within the first line 
     *          of this CSV.
     */
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
     * @todo refactor with ConditionController::stimuli() code
     * 
     * Gets the stimuli file string for the assigned condition, or false if it
     * has not been assigned, or an error if it does not exist.
     * @param string $num Optionally pass the integer for a specific procedure.
     * @param bool $safeSearch Forces false instead of an error.
     * @return string Contents of 'Procedure' column.
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
    
    /**
     * @todo refactor with ConditionController::allStim()
     * 
     * Gets the strings from all cells in a row that point to procedure files.
     * @param int $index Optionally indicate which row to retrieve from.
     * @return string Comma-separated list of all procedure files.
     * @uses ConditionController::assignedCondition Used if no index is given.
     */
    public function allProc($index = null)
    {
        if ($index === null) $index = $this->assignedCondition;
        $valid = array();
        $toCheck = $this->colsContaining("Procedure");
        $row = $this->ConditionsCSV[$index];
        for ($i=1; $i <= count($toCheck); $i++) { 
            if (         isset($row["Procedure $i"])
                AND strtolower($row["Procedure $i"]) != "off"
                AND            $row["Procedure $i"]  != ""
                AND            $row["Procedure $i"]  != "."
            ){
                $valid[] = $row["Procedure $i"];
            }
        }
        return implode(",", $valid);
    }

    /**
     * Gets the 'Description' string of the assigned condtion, or false if the
     * condition has not been assigned.
     * @return string Contents of 'Description' column.
     * @uses ConditionController::assignedCondition Checks this status before
     *          attempting to retrieve the description.
     * @uses ConditionController::userCondition Retrieves the description from 
     *          this array.
     */
    public function description()
    {
        if ($this->assignedCondition !== false) {
            return $this->userCondition['Description'];
        }
    }
    /**
     * Gets the 'Notes' string of the assigned condtion, or false if the
     * condition has not been assigned.
     * @return string Contents of 'Notes' column.
     * @uses ConditionController::assignedCondition Checks this status before
     *          attempting to retrieve the notes.
     * @uses ConditionController::userCondition Retrieves the notes from this 
     *          array.
     */
    public function notes()
    {
        if ($this->assignedCondition !== false) {
            return $this->userCondition['Notes'];
        }
    }
    /**
     * Gets the array of information for the assigned row, or false if the 
     * condition has not been assigned.
     * @return array Associative array of column => value for the condition.
     * @uses ConditionController::assignedCondition Checks this status before
     *          attempting to retrieve the row.
     * @uses ConditionController::userCondition Retrieves this array.
     */
    public function get()
    {
        if ($this->assignedCondition !== false) {
            return $this->userCondition;
        }
    }
    /**
     * Gets the index, which is sort of the row number, of the assigned condition
     * @return int|string|bool The integer of the assigned condition, the string
     *             if overridden, or false if not set.
     * @uses ConditionController::assignedCondition Returns this value.
     */
    public function getAssignedIndex() {
        return $this->assignedCondition;
    }
    
    /**
     * Changes the default error handler to a new instance of ErrorController.
     * @param ErrorController $altErrObj Error handler object.
     * @uses User::errObj Sets this value to the new error handler.
     */
    public function changeErrorHandler(ErrorController $altErrObj)
    {
        $this->errObj = $altErrObj;
    }
    
    /**
     * Gets a modified version of the Conditions.csv file where Procedures and 
     * Stimuli will be joined together by commas and flagged conditions are
     * excluded. Used in the Welcome.php script.
     */
    public function getAllConditions()
    {
        $conditions = array();
        $rows = $this->removeOffConditions();
        foreach ($rows as $i => $condRow) {
            $temp = array();
            foreach ($condRow as $key => $value) {
                if (    strpos($key, 'Stimuli')   === false
                    AND strpos($key, 'Procedure') === false
                ) {
                    $temp[$key] = $value;
                }
                $temp['Procedure'] = $this->allProc($i);
                $temp['Stimuli']   = $this->allStim($i);
            }
            $conditions[] = $temp;
        }
        return $conditions;
    }
    
    /**
     * Checks the entire conditions file for bad rows. Used in the Welcome.php 
     * script.
     * @param string $procDir The directory for the procedure files.
     * @param string $stimDir The directory for the stimuli files.
     * @uses ConditionController::allProc()
     * @uses ConditionController::allStim()
     */
    public function checkConditionsFile($procDir, $stimDir)
    {
        foreach ($this->ConditionsCSV as $i => $condRow) {
            $procs = $this->allProc($i);
            $stims = $this->allStim($i);
            $files = array (
                'Procedure' => $this->allProc($i),
                'Stimuli'   => $this->allStim($i)
            );
            $paths = array (
                'Procedure' => $procDir,
                'Stimuli'   => $stimDir
            );
            foreach ($files as $fileTypes => $filesCommaSeparated) {
                if ($filesCommaSeparated === '') {
                    $errMsg = "In the Conditions file, on row " . ($i+2) . ", "
                            . "there are no valid $fileTypes entries. At least "
                            . "one of the $fileTypes columns for this row must "
                            . "contain an actual filename.";
                    $this->errObj->add($errMsg);
                } else {
                    $files = explode(',', $filesCommaSeparated);
                    foreach ($files as $file) {
                        if (strpos($file, '..') !== false) {
                            $errMsg = "In the Conditions file, on row " . ($i+2) . ", "
                                    . "at least one of the $fileTypes filenames contains "
                                    . "\"..\", which is not allowed. "
                                    . "Please remove this part from the entry.";
                            $this->errObj->add($errMsg);
                        } else {
                            if (!fileExists("$paths[$fileTypes]/$file")) {
                                $errMsg = "In the Conditions file, on row " . ($i+2) . ", "
                                        . "the filename contains a $fileTypes file \"$file\" "
                                        . "which does not exist in the $fileTypes folder. "
                                        . "Please make sure that there isn't a typo in either "
                                        . "the filename or the entry in the Conditions file.";
                                $this->errObj->add($errMsg);
                            }
                        }
                    }
                }
            }
        }
    }
}
?>
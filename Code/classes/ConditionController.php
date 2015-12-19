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
     *
     * @var int|string
     */
    protected $selection;

    /**
     * Path to "Conditions.csv".
     *
     * @var string
     */
    protected $location;

    /**
     * Path to login counter.
     *
     * @var string
     */
    protected $logLocation;

    /**
     * Indicates whether conditions flagged as "off" should be shown.
     *
     * @var bool
     */
    protected $showFlagged;

    /**
     * Full array of Conditions.csv as loaded by GetFromFile().
     *
     * @var array
     *
     * @see GetFromFile()
     */
    protected $ConditionsCSV;

    /**
     * Indicates whether a condition has been assigned.
     *
     * @var bool
     */
    protected $assignedCondition = false;

    /**
     * Information about the assigned conditon.
     *
     * @var array
     */
    protected $userCondition;

    /**
     * The associated ErrorController object.
     *
     * @var ErrorController
     */
    protected $errObj;

    /**
     * Constructor.
     * 
     * Accepts the required locations to the Conditions and Log files as well as
     * a boolean indicating whether flagged conditions should be shown. Then, 
     * the Conditions.csv information is loaded and the required columns are
     * checked in the resultant array.
     *
     * @param string          $conditionsLoc Relative path to "Conditions.csv".
     * @param string          $logLocation   Relative path to the login counter file.
     * @param ErrorController $errorHandler  Object that logs errors.
     * @param bool            $showFlagged   Inital value for the showFlagged property.
     * 
     * @see getFromFile()
     */
    public function __construct($conditionsLoc, $logLocation,
        ErrorController $errorHandler, $showFlagged = false
    ) {
        $this->errObj = $errorHandler;
        $this->location = $conditionsLoc;
        $this->logLocation = $logLocation;
        $this->showFlagged = $showFlagged;
        $this->makeCounterDir($logLocation);

        // load the Conditions.csv information and check columns
        $this->conditionsFileExists();
        $this->ConditionsCSV = getFromFile($this->location, false);
        $this->requiredColumns();
    }

    /**
     * Ensures that there is a valid directory for the login counter.
     *
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
     * Sets the selected condition.
     * Sets the condition to the value specified. If the selected condition is 
     * not numeric or 'Auto' an error will fire.
     *
     * @param int|string $userChoice The number of the condition or 'Auto'.
     */
    public function setSelected($userChoice)
    {
        $selection = filter_var($userChoice, FILTER_SANITIZE_STRING);
        if (!is_numeric($selection) && ($selection !== 'Auto')) {
            $msg = "Your condition selection: $selection is not valid";
            $this->errObj->add($msg, true);
        }
        $this->selection = $selection;
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
        } elseif (isset($validConds[$this->selection])) {
            $this->userCondition = $validConds[$this->selection];
            $this->assignedCondition = $this->selection;
        }
    }

    /**
     * Gets the first position of the log, which should be the condition number.
     * If the value is not numeric or the file does not exist, the method
     * ConditionController::populateLogFile() is called.
     *
     * @return int The number of the current condition.
     */
    protected function getLogVal()
    {
        $file = $this->logLocation;
        if (file_exists($file)) {
            $handle = fopen($file, 'r');

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
     * Fills the log with information about the conditions.
     * Excludes conditions flagged as "off".
     */
    protected function populateLogFile()
    {
        $file = $this->logLocation;
        $conds = count($this->removeOffConditions());
        $possible = array();
        for ($i = 0; $i < $conds; ++$i) {
            $possible[] = $i;
        }
        shuffle($possible);
        $handle = fopen($file, 'w');
        fputcsv($handle, $possible);
        fclose($handle);
    }

    /**
     * Removes the first position of the log file (a number) and rewrites the
     * log file without it.
     *
     * @param array $condsFound The conditions found in the log.
     */
    protected function updateLogFile(array $condsFound)
    {
        // pull off the value we used
        array_shift($condsFound);

        // write what is left as csv
        $log = $this->logLocation;
        $handle = fopen($log, 'w');
        fputcsv($handle, $condsFound);
        fclose($handle);
    }

    /**
     * Removes conditions that are turned off.
     *
     * @return array The conditions flagged as being on.
     */
    protected function removeOffConditions()
    {
        $conditions = $this->ConditionsCSV;
        if ($this->showFlagged !== true) {
            $on = array();
            foreach ($conditions as $row) {
                if ($row['Description'][0] !== '#') {
                    $on[] = $row;
                }
            }
            $conditions = $on;
        }

        return $conditions;
    }

    /**
     * Overrides the current condition information with the desired information,
     * then logs the time it was overridden. Must be formatted as a 
     * Conditions.csv read in as a getFromFile() array,
     * e.g., array('Number' => 1, 'Stimuli' => 'something.csv', ...).
     *
     * @param array $array The condition information to use.
     *
     * @uses ConditionController::userCondition Replaces this array.
     * @uses ConditionController::assignedCondition Updates this to the time the
     *          condition was overridden
     */
    public function overrideCondition($array)
    {
        if (is_array($array)) {
            $this->userCondition = $array;
            $this->assignedCondition = 'overridden-'.microtime(true);
        }
    }

    /**
     * Debug method that echoes out the data from this class.
     * 
     * @param bool $asString Converts the info to an HTML formatted string.
     */
    public function getInfo($asString = true)
    {
        $info = array('selection' => $this->selection, 'options' => array());
        for ($i = 0; $i < count($this->ConditionsCSV); ++$i) {
            $info['options'][$i]['Stimuli'] = $this->allStim($i);
            $info['options'][$i]['Procedure'] = $this->allProc($i);
        }

        if ($asString === true) {
            $selection = $this->selection + 1;
            $str = "Selected condition: {$selection} <ol>";
            foreach ($info['options'] as $option) {
                $str .= "<li><strong>stim:</strong>{$option['Stimuli']}";
                $str .= "<br><strong>proc:</strong>{$option['Procedure']}";
            }
            $info = $str.'</ol>';
        }

        return $info;
    }

    /**
     * Indicates that "Conditions.csv" exists.
     * Makes sure the conditions file can be found. If not found then trigger a
     * showstopper with the ErrorController.
     *
     * @return bool True if the file exists.
     *
     * @see ConditionController::__construct()
     * @see ConditionController::location
     */
    protected function conditionsFileExists()
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
        foreach ($requiredColumns as $col) {
            if (!isset($this->ConditionsCSV[0][$col])) {
                $msg = "Your Conditions.csv file is missing the $col column";
                $this->errObj->add($msg, true);
            }
        }
    }

    /**
     * Gets the Stimuli file string for the assigned condition, or false if it
     * has not been assigned, or an error if it does not exist.
     *
     * @param int  $num        [Optional] The number of a specific procedure.
     * @param bool $safeSearch Forces false instead of an error.
     * 
     * @return bool Contents of the 'Stimuli' column or an error/false.
     * 
     * @uses ConditionController::getFileString to do all the work.
     */
    public function stimuli($num = 1, $safeSearch = false)
    {
        return $this->getFileString('Stimuli', $num, $safeSearch);
    }

    /**
     * Gets the Procedure file string for the assigned condition, or false if it
     * has not been assigned, or an error if it does not exist.
     *
     * @param int  $num        [Optional] The number of a specific procedure.
     * @param bool $safeSearch Forces false instead of an error.
     * 
     * @return bool Contents of the 'Procedure' column or an error/false.
     * 
     * @uses ConditionController::getFileString to do all the work.
     */
    public function procedure($num = 1, $safeSearch = false)
    {
        return $this->getFileString('Procedure', $num, $safeSearch);
    }

    /**
     * Gets the control file string for the assigned condition, or false if it
     * has not been assigned, or an error if it does not exist.
     * 
     * @param string $type       The type of file to retrieve (e.g. 'Stimuli').
     * @param int    $num        [Optional] The number of a specific procedure.
     * @param bool   $safeSearch Forces false instead of an error.
     * 
     * @return bool Contents of the $type's column or an error/false.
     */
    protected function getFileString($type, $num, $safeSearch)
    {
        if ($this->assignedCondition === false) {
            $msg = "You are trying to get the value of '$type $num' before "
            .'you have assigned a condition.<br>Run $cond->assignCondition() '
            .'before attempting to use this method.';
            $this->errObj->add($msg);
        }

        if (empty($this->userCondition["Stimuli $num"])) {
            if ($safeSearch == true) {
                return false;
            } else {
                $msg = "You have tried to get the value for '$type $num' "
                    .'but that column does not exist in your Conditions.csv'
                    ."file at <code>$this->location</code>.";
                $this->errObj->add($msg, true);
            }
        }

        return $this->userCondition["$type $num"];
    }

    /**
     * Gets the strings from all cells that point to Stimuli files.
     *
     * @param int $index [Optional] Indicates which row to retrieve from.
     *
     * @return string Comma-separated list of all stimuli files.
     *
     * @uses ConditionController::getAllFilesOfType
     */
    public function allStim($index = null)
    {
        return $this->getAllFilesOfType('Stimuli', $index);
    }

    /**
     * Gets the strings from all cells that point to Procedure files.
     *
     * @param int $index [Optional] Indicates which row to retrieve from.
     *
     * @return string Comma-separated list of all Procedure files.
     *
     * @uses ConditionController::getAllFilesOfType
     */
    public function allProc($index = null)
    {
        return $this->getAllFilesOfType('Procedure', $index);
    }

    /**
     * Gets the strings from all cells that point to the given control files.
     *
     * @param string $type  The type of file to retrieve (e.g. 'Stimuli').
     * @param int    $index [Optional] Indicates which row to retrieve from.
     *
     * @return string Comma-separated list of all stimuli files.
     *
     * @uses ConditionController::assignedCondition Used if no index is given.
     */
    protected function getAllFilesOfType($type, $index = null)
    {
        if ($index === null) {
            $index = $this->assignedCondition;
        }
        $row = $this->ConditionsCSV[$index];

        $valid = array();
        $toCheck = $this->colsContaining($type);
        for ($i = 1; $i < count($toCheck) + 1; ++$i) {
            $noLoad = array('off' => 1, '' => 1, '.' => 1);
            if (isset($row["$type $i"])
                && !isset($noLoad[strtolower($row["$type $i"])])
            ) {
                $valid[] = $row["$type $i"];
            }
        }

        return implode(',', $valid);
    }

    /**
     * Searches for a string within the column names (keys).
     *
     * @param string $needle The string to search for in the keys.
     *
     * @return array All of the keys which contain the search string.
     *
     * @uses ConditionController::ConditionsCSV Searches within the first line 
     *          of this CSV.
     */
    protected function colsContaining($needle)
    {
        $matches = array();
        foreach (array_keys($this->ConditionsCSV[0]) as $key) {
            if (strpos($key, $needle) !== false) {
                $matches[] = $key;
            }
        }

        return $matches;
    }

    /**
     * Gets the 'Description' string of the assigned condtion, or false if the
     * condition has not been assigned.
     *
     * @return string Contents of 'Description' column.
     *
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
     *
     * @return string Contents of 'Notes' column.
     *
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
     *
     * @return array Associative array of column => value for the condition.
     *
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
     * Gets the index, which is sort of the row number, of the assigned condition.
     *
     * @return int|string|bool The integer of the assigned condition, the string
     *                         if overridden, or false if not set.
     *
     * @uses ConditionController::assignedCondition Returns this value.
     */
    public function getAssignedIndex()
    {
        return $this->assignedCondition;
    }

    /**
     * Changes the default error handler to a new instance of ErrorController.
     *
     * @param ErrorController $altErrObj Error handler object.
     *
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
                if (strpos($key, 'Stimuli')   === false
                    && strpos($key, 'Procedure') === false
                ) {
                    $temp[$key] = $value;
                }
                $temp['Procedure'] = $this->allProc($i);
                $temp['Stimuli'] = $this->allStim($i);
            }
            $conditions[] = $temp;
        }

        return $conditions;
    }

    /**
     * Checks the entire conditions file for bad rows. Used in the Welcome.php 
     * script.
     *
     * @param string $procDir The directory for the procedure files.
     * @param string $stimDir The directory for the stimuli files.
     *
     * @uses ConditionController::allProc()
     * @uses ConditionController::allStim()
     */
    public function checkConditionsFile($procDir, $stimDir)
    {
        foreach (array_keys($this->ConditionsCSV) as $i) {
            $files = array(
                'Procedure' => $this->allProc($i),
                'Stimuli' => $this->allStim($i),
            );

            $paths = array(
                'Procedure' => $procDir,
                'Stimuli' => $stimDir,
            );

            $row = $i + 2;
            foreach ($files as $fileTypes => $filesCommaSeparated) {
                if ($filesCommaSeparated === '') {
                    $this->errObj->add("In the Conditions file, on row $row, "
                        ."there are no valid $fileTypes entries. At least one "
                        ."of the $fileTypes columns for this row must contain "
                        .'an actual filename.');
                    continue;
                }

                $files = explode(',', $filesCommaSeparated);
                foreach ($files as $file) {
                    if (strpos($file, '..') !== false) {
                        $this->errObj->add("In the Conditions file, on row $row, "
                            ."at least one of the $fileTypes filenames contains "
                            ."'..', which is not allowed. Please remove this "
                            .'part from the entry.');
                    } elseif (!fileExists("$paths[$fileTypes]/$file")) {
                        $this->errObj->add("In the Conditions file, on row $row, "
                            ."the filename contains a $fileTypes file \"$file\" "
                            ."which does not exist in the $fileTypes folder. "
                            ."Please make sure that there isn't a typo in "
                            .'either the filename or the entry in the '
                            .'Conditions file.');
                    }
                }
            }
        }
    }
}

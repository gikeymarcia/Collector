<?php
/**
 * ControlFile class
 */

/**
 * Handles the reading and stitching together of control files.
 * Both the Stimuli & Procedure classes will extend this object
 * on __construct you give it the base control file directory + files to be used
 *
 * The second parameter on __construct can either be a single file
 *     e.g., 'Demo.csv'
 *     OR it can be a series of files
 *     e.g., 'Instructions.csv, Demo.csv, FinalQuestions.csv'
 */
abstract class ControlFile
{
    /**
     * The location of the control file.
     * @var string
     */
    protected $dir;
    
    /**
     * The files to use when creating the object.
     * @var array
     */
    protected $files;
    
    /**
     * The combined control files.
     * @var array
     * 
     * @todo rename to data
     */
    protected $stitched = array();
    
    /**
     * The shuffled control file.
     * @var array|bool
     */
    protected $shuffled = false;
    
    /**
     * The filenames and actual row numbers of rows in the stitched procedure.
     * @var type 
     */
    protected $rowOrigins = array();
    
    /**
     * ErrorController object for handling errors.
     * @var ErrorController
     */
    protected $errorObj;

    /**
     * Reads in control files and combines them into one array with 
     * consistent keys stored in ControlFile::stitched.
     * @param string $dir       The directory of the files to use.
     * @param string $filenames Comma-separated list of .csv flenames to use.
     * @param ErrorController $errObj An object that handles errors.
     */
    public function __construct($dir, $filenames, ErrorController $errObj)
    {
        $this->errorObj = $errObj;
        $this->dir   = $dir;
        $this->files = explode(',', $filenames);
        $this->readFiles();
        $this->errorCheck();
        // $this->checkShuffleCols();
    }
    /**
     * Takes an array of .csv filenames and combines them all together and then
     * stores them to ControlFile::stitched.
     * @see ControlFile::stitched
     */
    protected function readFiles()
    {
        foreach ($this->files as $file) {
            $file = trim($file);
            $fullPath = $this->dir . '/' . $file;
            $this->exists($fullPath);
            $data = getFromFile($fullPath, false);
            $this->stitch($data);
            foreach ($data as $i => $row) {
                // I'm using the ? char as a delimiter
                // this way, if I find out later that row 328 in the proc file has some error,
                // I can check and see which file it came from, and which row in that file,
                // even though several files may have been stitched together
                $this->rowOrigins[] = $file . '?' . $i;
            }
        }
    }
    
    /**
     * Receives a 2d array and adds that array to ControlFile::stitched.
     * Makes sure keys are consistent throughout ControlFile::stitched even if
     * passed array keys do not match ControlFile::stitched keys.
     * @param array $in Array being added to ControlFile::stitched
     * @todo update docblock for ControlFile::stitch()
     */
    protected function stitch(array $in)
    {
        if (count($this->stitched) == 0) {               // add first file without checks
            $this->stitched = $in;
        } else {
            if ($this->keyMatch($in)) {                  // if newfile matches old pattern
                foreach ($in as $row => $value) {
                    $this->stitched[] = $value;             // add each row to $this->stitched
                }
            } else {                                    // otherwise
                $in = $this->conform($in);                  // make new data fit old pattern
                foreach ($in as $pos => $array) {
                    $this->stitched[] = $array;             // add each row to $this->stitched
                }
            }
        }
    }
    
    /**
     * Receives a 2-D array and checks if the keys in its first position's array
     * matche the keys in the first position of ControlFile::stitched.
     * @param  array $new 2-D array to check.
     * @return bool True if keys match, else false.
     * 
     * @todo ControlFile::keyMatch will always return false.
     */
    protected function keyMatch(array $new)
    {
        $existingKeys = array_keys($this->stitched[0]);
        $newKeys      = array_keys($new[0]);
        
        if (count($existingKeys) == count($newKeys)) {
            for ($i=0; $i < count($existingKeys); $i++) { 
                if($newKeys[$i] !== $existingKeys[$i]) {
                    return false;
                }
            }
        } else {
            return false;
        }
        
        return true;
    }
    
    /**
     * Makes a given array ($newData) match the keys used in ControlFile::stitched.
     * If $newData has keys that don't exist in ControlFile::stitched then they 
     * are added to the stitched array.
     * @param  array $newData The array being conformed.
     * @return array The array conformed to the ControlFile::stitched array.
     */
    protected function conform($newData)
    {
        $oldKeys = array_keys($this->stitched);
        $newKeys = array_keys($newData);
        foreach ($newData[0] as $key => $value) {
            if(!isset($this->stitched[0][$key])) {
                $this->addKey($key);
            }
        }
        $updatedKeys = array_keys($this->stitched[0]);
        $aligned = array();
        foreach ($newData as $pos => $array) {
            foreach ($updatedKeys as $key) {
                if(isset($array[$key])) {
                    $aligned[$pos][$key] = $array[$key];
                } else {
                    $aligned[$pos][$key] = '';
                }
            }
        }
        return $aligned;
    }
    
    /**
     * Adds a key with the an empty string for each position in ControlFile::stitched.
     * @param string $key Name of key to add.
     */
    protected function addKey($key)
    {
        foreach ($this->stitched as $pos => $array) {
            $this->stitched[$pos][$key] = '';
        }
    }
    
    /**
     * Checks if a file exists. The experiment will be halted entirely if the
     * file does not exist.
     * @param  string $path path to get to the file being checked
     * @return boolean True if the path points at a file.
     * 
     * @todo update code
     */ 
    protected function exists($path)
    {
        if(fileExists($path)) {
            return true;
        } else {
            $msg = "Could not find the following file specified by your Conditons file: <br><b>$path</b>";
            $this->errorObj->add($msg, true);
        }
    }
    
    /**
     * Ensures that a ControlFile file has has all the required columns.
     * @param  string $controlFileType The type of ControlFile being checked: 
     *             'stimuli', 'procedure'.
     * @param  array $cols The required columns.
     * 
     * @todo refactor out the $controlFileType argument
     */
    protected function requiredColumns($controlFileType, array $cols)
    {
        foreach ($cols as $column) {
            if(!isset($this->stitched[0][$column])) {
                $msg = "Your $controlFileType file does not contain the following required column: $column";
                $this->errorObj->add($msg);
            }
        }
    }
    
    /**
     * Shuffles ControlFile::stitched and returns the shuffled array.
     * @return array The shuffled data.
     */
    public function shuffle()
    {
        $data = multiLevelShuffle($this->stitched);
        $data = shuffle2dArray($data);
        $this->shuffled = $data;
        return $data;
    }
    
    /**
     * Returns the specific shuffled version that was created last time 
     * ControlFile::shuffle() was run.
     * @return array The previously shuffled data.
     * 
     * @todo change ControlFile::shuffled() to a public property?
     */
    public function shuffled()
    {
        return $this->shuffled;
    }
    
    /**
     * Returns the ControlFile data without shuffling it.
     * @return array The stitched control file(s), unshuffled.
     * 
     * @todo change ControlFile::stitched to a public property?
     */
    public function unshuffled()
    {
        return $this->stitched;
    }
    
    /**
     * Sets the value for ControlFile::stitched.
     * @param array $data The data to set (should be formatted as a 2-D array 
     *            like getFromFile() would produce).
     * 
     * @todo make ControlFile::stitched a public property?
     */
    public function manual($data)
    {
        $this->stitched = $data;
    }
    
    /**
     * Gets the list of the keys used in the stitched ControlFile data.
     * @param  bool $noShuffles Set true to remove shuffle related keys.
     * @return array All the keys (e.g., array(0=> 'item', 1=>'trial type')).
     * 
     * @todo update code (!=, AND, return)
     */
    public function getKeys($noShuffles = false)
    {
        if ($noShuffles == false) {
            return array_keys($this->stitched[0]);
        } else {
            $allKeys = array_keys($this->stitched[0]);
            $subset  = array();
            foreach ($allKeys as $key) {
                // if it isn't one of the shuffle columns then include it
                if (    substr($key, 0, 8)  != "Shuffle "
                    AND substr($key, 0, 10) != "AdvShuffle"
                ) {
                    $subset[] = $key;
                }
            }
            return $subset;
        }
    }
    
    /**
     * Checks if a set of given keys overlaps with the keys of the current 
     * ControlFile object. If an overlap is found, an error is added to the
     * ErrorController.
     * @param  array $otherKeys Indexed array of keys.
     * 
     * @todo rename function to make it clear errors are the result (or refactor)
     */
    public function overlap($otherKeys)
    {
        $doubles = array();
        $objKeys = $this->getKeys(true);
        $objKeys = array_flip($objKeys);
        foreach ($otherKeys as $key) {
            if (isset($objKeys[$key])) {
                $doubles[] = $key;
            }
        }
        if (count($doubles) > 0) {
            $msg = "<ol type='a'>Your stimuli and procedure files cannot contain column(s) with the same name(s)<br>
            The following columns are in both your stimuli and procedure file:<br>";
            foreach ($doubles as $columnName) {
                $msg .= "<li><b>$columnName</b></li>";
            }
            $msg .= "</ol>";
            $this->errorObj->add($msg);
        }
    }
    
    /**
     * Finds the filename and actual row number of a row in the stitched data.
     * @param int $i Index of item for which to retrieve row origin.
     * @return array|bool Associative array with keys 'filename' and 'row', or 
     *             false if the method fails.
     */
    public function getRowOrigin($i) {
        if (!isset($this->rowOrigins[$i])) {
            // issue error
            $errMsg = "Cannot get row origins for row $i in ".get_class($this).": Row does not exist";
            trigger_error($errMsg, E_USER_WARNING);
            return false;
        } else {
            $rowOrig = $this->rowOrigins[$i];
            $rowOrig = explode('?', $rowOrig);
            $rowOrig = array('filename' => $rowOrig[0], 'row' => $rowOrig[1]+2);
            return $rowOrig;
        }
    }

}
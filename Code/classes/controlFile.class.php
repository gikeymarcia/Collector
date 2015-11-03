<?php
/**
 * Handles the reading and stitching together of control files
 * Both the Stimuli & Procedure classes will extend this object
 * on __construct you give it the base control file directory + files to be used
 *
 * The second parameter on __construct can either be a single file
 *     e.g., 'Demo.csv'
 *     OR it can be a series of files
 *     e.g., 'Instructions.csv, Demo.csv, FinalQuestions.csv'
 */
class controlFile
{
    protected $dir;
    protected $files;
    protected $stitched = array();
    protected $shuffled = false;
    protected $rowOrigins = array();

    /**
     * Reads in control files and combines them into
     * one array with consistent keys called $this->stitched
     * @param string $dir       location where the files exist
     * @param string $filenames list of .csv flenames (comma separated)
     */
    public function __construct($dir, $filenames)
    {
        $this->dir   = $dir;
        $this->files = explode(',', $filenames);
        $this->readFiles();
        $this->errorCheck();
        // $this->checkShuffleCols();

    }
    /**
     * Takes an array of .csv filenames
     * and combines them all together into $this->stitched
     * @return n/a updates $this->stitched
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
     * Receives a 2d array and adds that array to $this->stitched
     * Makes sure keys are consistent throughout $this-stitched
     * even if $in doesn't match $this->stitched
     * @param  array $in array being added to $this->stitched
     * @return n/a     updates value of $this->stitched
     */
    protected function stitch($in)
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
     * Receives a 2d array and checks if the keys in it's first position
     * matches the keys in the first position of $this-stitched
     * @param  array $new 2d-array
     * @return boolean      true/false if keys match
     */
    protected function keyMatch($new)
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
     * Makes a given array ($newData) match the keys used in 
     * $this->stitched.  If $newData has keys that don't exist in 
     * $this->stitched then they are added to $this->stitched
     * @param  array $newData data being conformed to match $this->stitched
     * @return array          $newData transformed to match $this->stitched
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
     * Adds a key to with the value of an empty string for
     * each position in stitched
     * @param string $key name of key to add
     */
    protected function addKey($key)
    {
        foreach ($this->stitched as $pos => $array) {
            $this->stitched[$pos][$key] = '';
        }
    }
    /**
     * checks if a file exists
     * @param  string $path path to get to the file being checked
     * @return boolean       true if the path points at a file
     *                       and showstopper error if the file doesn't exist
     */ 
    protected function exists($path)
    {
        if(fileExists($path)) {
            return true;
        } else {
            global $errors;
            $msg = 'Could not find the following file specified by your Conditons file: <br><b>' .
            $path . '</b>';
            $errors->add($msg, true);
        }
    }
    /**
     * Makes sure that the array has all required columns
     * @param  string $filename type of file being checked (usually stimuli or procedure)
     * @param  [type] $cols     [description]
     * @return [type]           [description]
     */
    protected function requiredColumns($filename, $cols)
    {
        foreach ($cols as $column) {
            if(!isset($this->stitched[0][$column])) {
                global $errors;
                $msg = "Your {$filename} file does not contain the following required column: {$column}";
                $errors->add($msg);
            }
        }
    }
    /**
     * Shuffles $this->stitched and returns the result
     * @return [type] [description]
     */
    public function shuffle()
    {
        $data = multiLevelShuffle($this->stitched);
        $data = shuffle2dArray($data);
        $this->shuffled = $data;
        return $data;
    }
    /**
     * Returns the specific shuffled version that was 
     * created last time $this->shuffle() was run
     * @return array result of the last time $this->shuffle() was used
     */
    public function shuffled()
    {
        return $this->shuffled;
    }
    /**
     * Return $this->stitched without shuffling
     * @return array stitched outcome of reading the control file(s)
     */
    public function unshuffled()
    {
        return $this->stitched;
    }
    /**
     * Overrides whatever was created for $this->stitched
     * with the array you pass it
     * @param  array $newStitched expecting something in 2d getFromFile() format
     */
    public function manual($newStitched)
    {
        $this->stitched = $newStitched;
    }
    /**
     * Use to get a list of the keys used in $this->stitched
     * @return $array (e.g., array(0=> 'item', 1=>'trial type'))
     */
    public function getKeys()
    {
        return array_keys($this->stitched[0]);
    }
    /**
     * Checks if a set of given keys overlaps
     * with the keys of the current object (stimuli or procedure)
     * $errors->add() called if overlap is found
     * @param  array $otherKeys list of keys [expecting format of $this->getKeys()]
     * @return n/a              does not return anything but can add() to errors if there is overlap
     */
    public function overlap($otherKeys)
    {
        $doubles = array();
        $objKeys = $this->getKeys();
        $objKeys = array_flip($objKeys);
        foreach ($otherKeys as $key) {
            if (isset($objKeys[$key])) {
                $doubles[] = $key;
            }
        }
        if (count($doubles) > 0) {
            $msg = "<ol>Your stimuli and procedure files cannot contain column(s) with the same name(s)<br>
            The following columns are in both your stimuli and procedure file:<br>";
            foreach ($doubles as $columnName) {
                $msg .= "<li>$columnName</li>";
            }
            $msg .= "</ol>";
            global $errors;
            $errors->add($msg);
        }
    }
    /**
     * Work in progress -Goal is that it correct for the Derek mistake
     * @return [type] [description]
     */
    protected function checkShuffleCols()
    {
        $shuffleBase = 'Shuffle';
        $keys = $this->getKeys();
        // remove all columns names that don't have 'Shuffle' in them
        foreach ($keys as $pos => $name) {
            if (strpos($name, $shuffleBase) === false) {
                unset($keys[$pos]);
            }
        }
        $keys = array_flip($keys);
        $validShuffles = 0;
        for ($i=1; $i < count($keys); $i++) { 
            if($i == 1 
                AND (isset($keys[$shuffleBase]))
            ) {
                $validShuffles++;
            }
        }
    }
    
    /**
     * Finds the filename and actual row number of a row in the stitched procedure
     * @param int $i index of procedure to get origins
     * @return array assoc array with indices 'filename' and 'row'
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
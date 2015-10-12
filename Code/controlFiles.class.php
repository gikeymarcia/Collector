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
class controlFileSetup
{
    protected $dir;
    protected $files;
    protected $stitched = array();
    protected $shuffled = false;

    /**
     * Reads in control files and combines them into
     * one array with consistent keys called $this->stitched
     * @param string $dir       location where the files exist
     * @param string $filenames list of .csv flenames (comma separated)
     */
    public function __construct($dir, $filenames)
    {
        $this->dir   = $dir;
        $this->files = explode(',', $this->filenames);
        $this->readFiles();
        $this->errorCheck();

    }
    /**
     * Takes an array of .csv filenames
     * and combines them all together into $this->stitched
     * @return n/a updates $this->stitched
     */
    protected function readFiles()
    {
        foreach ($this->files as $file) {
            $fullPath = $this->dir . '/' . trim($file);
            $this->exists($fullPath);
            $data = getFromFile($fullPath, false);
            $this->stitch($data);
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
    protected function addKey($key)
    {
        foreach ($this->stitched as $pos => $array) {
            $this->stitched[$pos][$key] = '';
        }
    }
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
    public function shuffle()
    {
        $data = multiLevelShuffle($this->stitched);
        $data = shuffle2dArray($data);
        $this->shuffled = $data;
        return $data;
    }
    public function shuffled()
    {
        return $this->shuffled;
    }
    public function unshuffled()
    {
        return $this->stitched;
    }
    public function manual($array)
    {
        $this->stitched = $array;
    }
}
<?php
/**
 * ControlFile class.
 */

/**
 * Handles the reading and stitching together of control files.
 * Both the Stimuli & Procedure classes will extend this object
 * on __construct you give it the base control file directory + files to be used.
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
     *
     * @var string
     */
    protected $dir;

    /**
     * The files to use when creating the object.
     *
     * @var array
     */
    protected $files;

    /**
     * The combined control files.
     *
     * @var array
     */
    protected $data = array();

    /**
     * The shuffled control file.
     *
     * @var array|bool
     */
    protected $shuffled = false;

    /**
     * The filenames and actual row numbers of rows in the stitched procedure.
     *
     * @var type
     */
    protected $rowOrigins = array();

    /**
     * ErrorController object for handling errors.
     *
     * @var ErrorController
     */
    protected $errorObj;

    /**
     * Reads in control files and combines them into one array with 
     * consistent keys stored in ControlFile::stitched.
     *
     * @param string          $dir       The directory of the files to use.
     * @param string          $filenames Comma-separated list of .csv flenames to use.
     * @param ErrorController $errObj    An object that handles errors.
     */
    public function __construct($dir, $filenames, ErrorController $errObj)
    {
        $this->errorObj = $errObj;
        $this->dir = $dir;
        $this->files = explode(',', $filenames);
        $this->readFiles();
        $this->errorCheck();
        // $this->checkShuffleCols();
    }
    /**
     * Takes an array of .csv filenames and combines them all together and then
     * stores them to ControlFile::stitched.
     * 
     * A record of the stitched rows' original files and row number is logged in
     * ControlFile::rowOrigins. For every newly stitched row, an index with
     * value "{$filename}?{$rownumber}" is appended to ControlFile::rowOrigins.
     *
     * @see ControlFile::stitched
     */
    protected function readFiles()
    {
        foreach ($this->files as $file) {
            $fullPath = $this->dir.'/'.trim($file);
            $this->exists($fullPath);
            $data = getFromFile($fullPath, false);
            $this->stitch($data);
            foreach (array_keys($data) as $i) {
                $this->rowOrigins[] = $file.'?'.$i;
            }
        }
    }

    /**
     * Receives a 2-D array and adds that array to ControlFile::stitched.
     * Makes sure keys are consistent throughout ControlFile::stitched even if
     * passed array keys do not match ControlFile::stitched keys.
     *
     * @param array $in Array being added to ControlFile::stitched
     */
    protected function stitch(array $in)
    {
        // if not first file and key conflicts exist, data needs to be conformed
        if (count($this->data) > 0 && !$this->keyMatch($in)) {
            // add any new keys to the current stitched data
            foreach (array_keys($in[0]) as $key) {
                if (!isset($this->data[0][$key])) {
                    $this->addKey($key);
                }
            }

            // conform the new data to match the current data
            $in = $this->conform($in);
        }

        foreach ($in as $value) {
            $this->data[] = $value;
        }
    }

    /**
     * Receives a 2-D array and checks if the keys in its first position's array
     * match the keys in the first position of ControlFile::stitched.
     *
     * @param array $new 2-D array to check.
     *
     * @return bool True if keys match, else false.
     */
    protected function keyMatch(array $new)
    {
        $existingKeys = array_keys($this->data[0]);
        $newKeys = array_keys($new[0]);

        if (count($existingKeys) !== count($newKeys)) {
            return false;
        }
        $length = count($existingKeys);
        for ($i = 0; $i < $length; ++$i) {
            if ($newKeys[$i] !== $existingKeys[$i]) {
                return false;
            }
        }

        return true;
    }

    /**
     * Makes a given array ($newData) match the keys used in ControlFile::stitched.
     * If $newData has keys that don't exist in ControlFile::stitched then they 
     * are added to the stitched array.
     *
     * @param array $newData The array being conformed.
     *
     * @return array The array conformed to the ControlFile::stitched array.
     */
    protected function conform($newData)
    {
        $keys = array_keys($this->data[0]);
        $conformed = array();
        foreach ($newData as $pos => $row) {
            foreach ($keys as $key) {
                $conformed[$pos][$key] = isset($row[$key]) ? $row[$key] : '';
            }
        }

        return $conformed;
    }

    /**
     * Adds a key with the an empty string for each position in ControlFile::stitched.
     *
     * @param string $key Name of key to add.
     */
    protected function addKey($key)
    {
        foreach (array_keys($this->data) as $pos) {
            $this->data[$pos][$key] = '';
        }
    }

    /**
     * Checks if a file exists. The experiment will be halted entirely if the
     * file does not exist.
     *
     * @param string $path path to get to the file being checked
     *
     * @return bool True if the path points at a file.
     */
    protected function exists($path)
    {
        if (!fileExists($path)) {
            // stop the show with an error
            $this->errorObj->add('Could not find the following file specified '
                ."by your Conditions file: <br><b>{$path}</b>", true);
        }

        return true;
    }

    /**
     * Ensures that a ControlFile file has has all the required columns.
     *
     * @param array $cols The required columns.
     * 
     * @todo will allow a stitched array from a file that does not have the required columns as long as it is paired with a file that does have them
     */
    protected function requiredColumns(array $cols)
    {
        foreach ($cols as $column) {
            if (!isset($this->data[0][$column])) {
                $msg = 'Your '.get_class($this).' file(s) does not contain the '
                    ."following required column: {$column}";
                $this->errorObj->add($msg);
            }
        }
    }

    /**
     * Shuffles ControlFile::stitched and returns the shuffled array.
     *
     * @return array The shuffled data.
     */
    public function shuffle()
    {
        $shuffled = shuffle2dArray(multiLevelShuffle($this->data));
        $this->shuffled = $shuffled;

        return $shuffled;
    }

    /**
     * Returns the specific shuffled version that was created last time 
     * ControlFile::shuffle() was run.
     *
     * @return array The previously shuffled data.
     */
    public function getShuffled()
    {
        return $this->shuffled;
    }

    /**
     * Returns the ControlFile data without shuffling it.
     *
     * @return array The stitched control file(s), unshuffled.
     */
    public function getUnshuffled()
    {
        return $this->data;
    }

    /**
     * Sets the value for ControlFile::stitched.
     *
     * @param array $data The data to set (should be formatted as a 2-D array 
     *                    like getFromFile() would produce).
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * Alias for ControlFile::manual().
     * 
     * @param array $data The data to set.
     * 
     * @see ControlFile::manual()
     */
    public function setUnshuffled($data)
    {
        $this->setData($data);
    }

    /**
     * Gets the list of the keys used in the stitched ControlFile data.
     *
     * @param bool $noShuffles Set true to remove shuffle related keys.
     *
     * @return array All the keys (e.g., array(0=> 'item', 1=>'trial type')).
     */
    public function getKeys($noShuffles = false)
    {
        $keys = array_keys($this->data[0]);

        // filter out the shuffle columns if requested
        if ($noShuffles === true) {
            $subset = array();
            foreach ($keys as $key) {
                // if it isn't one of the shuffle columns then include it
                if (substr($key, 0, 8) !== 'Shuffle '
                    && substr($key, 0, 10) !== 'AdvShuffle'
                ) {
                    $subset[] = $key;
                }
            }

            $keys = $subset;
        }

        return $keys;
    }

    /**
     * Checks if a set of given keys overlaps with the keys of the current 
     * ControlFile object. If an overlap is found, an error is added to the
     * ErrorController.
     *
     * @param array $otherKeys Indexed array of keys.
     */
    public function checkOverlap($otherKeys)
    {
        $doubles = array();
        $objKeys = array_flip($this->getKeys(true));
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
            $msg .= '</ol>';
            $this->errorObj->add($msg);
        }
    }

    /**
     * Finds the filename and actual row number of a row in the stitched data.
     *
     * @param int $i Index of item for which to retrieve row origin.
     *
     * @return array|bool Associative array with keys 'filename' and 'row', or 
     *                    false if the method fails.
     */
    public function getRowOrigin($i)
    {
        if (!isset($this->rowOrigins[$i])) {
            // original row can't be found: issue error
            $errMsg = "Cannot get row origins for row $i in ".get_class($this).': Row does not exist';
            trigger_error($errMsg, E_USER_WARNING);

            return false;
        } else {
            // return the file and row
            $rowOrig = explode('?', $this->rowOrigins[$i]);

            return array('filename' => $rowOrig[0], 'row' => $rowOrig[1] + 2);
        }
    }
}

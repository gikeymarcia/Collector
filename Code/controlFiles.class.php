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

    public function __construct($dir, $filenames)
    {
        $this->dir   = $dir;
        $this->files = $this->split($filenames);
        $this->readFiles();

    }
    public function split($strings)
    {
        if (strpos($strings, ',')) {
            $contents = explode(',', $strings);
        } else {
            $contents = array(0 => $strings);
        }
        foreach ($contents as $key => $value) {
            $value = trim($value);
        }
        return $contents;
    }
    protected function readFiles()
    {
        foreach ($this->files as $file) {
            $fullPath = $this->dir . '/' . $file;
            $this->exists($fullPath);
            $data = getFromFile($fullPath, false);
// var_dump($data, 'this is bullshit');
            $this->stitch($data);
        }
    }
    protected function stitch($in)
    {
        if (count($this->stitched) == 0) {               // add first file without checks
// var_dump($in,'first file in');
            $this->stitched = $in;
        } else {
// var_dump($in, 'coming in hot');
            if ($this->keyMatch($in)) {                  // if newfile matches old pattern
                foreach ($in as $row => $value) {
                    $this->stitched[] = $value;             // add each row to $this->stitched
                }
            } else {                                    // otherwise
// var_dump($this, 'middle of stitch');
                $in = $this->conform($in);                  // make new data fit old pattern
// var_dump($in, 'did it conform?');
                foreach ($in as $pos => $array) {
                    $this->stitched[] = $array;             // add each row to $this->stitched
                }
            }
        }
    }
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
    protected function conform($newData)
    {
// var_dump($newData);
        $oldKeys = array_keys($this->stitched);
        $newKeys = array_keys($newData);
// var_dump($newData, 'this is what Im adding');
// var_dump($this->stitched, 'this is what we had');
        foreach ($newData[0] as $key => $value) {
            if(!isset($this->stitched[0][$key])) {
// var_dump($this->stitched, 'keys should be filling in');
                $this->addKey($key);
            }
        }
        $updatedKeys = array_keys($this->stitched[0]);
// var_dump($updatedKeys);
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
// var_dump($this->stitched[0], 'what');
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
}
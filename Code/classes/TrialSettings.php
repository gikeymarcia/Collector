<?php
/**
 * TrialSettings class.
 */

namespace Collector;

/**
 * @todo add description for TrialSettings
 * 
 * All settings must be of the format [key] = [value]. Settings must be
 * separated by the "|" character (e.g. "setting1=a|setting2=b"). A setting
 * can be assigned an array of values through the following syntax:
 * arraySetting[]=1;2;3;4, which will yield arraySetting = array(1, 2, 3, 4).
 */
class TrialSettings
{
    /**
     * The raw settings string that this object was constructed with along with
     * any other strings added via addSettings (these get separated by "|").
     * 
     * @var string
     */
    public $rawSettings;
    
    /**
     * The settings for the trial.
     * 
     * @var array
     */
    public $settings;
    
    /**
     * Constructor.
     * 
     * @param string $settings A string that can be parsed by parseSettings.
     */
    public function __construct($settings) {
        $this->rawSettings = $settings;
        $this->parseSettings();
    }
    
    /**
     * Retrieves the property if it exists.
     * 
     * @param string $property The name of the property to get.
     * 
     * @return mixed Returns the property if it exists, else false.
     */
    public function __get($property) {
        $property = strtolower($property);
        
        return isset($this->settings[$property]) 
            ? $this->settings[$property]
            : false;
    }
    
    /**
     * Parses the raw settings string and sets the values to 'settings'.
     */
    protected function parseSettings() {
        $settings = array();
        
        foreach (explode('|', $this->rawSettings) as $rawSetting) {
            $splitSetting = explode('=', $rawSetting);
            $key = strtolower(trim($splitSetting[0]));
            $val = isset($splitSetting[1]) ? trim($splitSetting[1]) : null;
            
            // determine if setting should be parsed as an array
            if (substr($key, -2) === '[]') {
                $key = substr($key, 0, -2);
                $getValuesAsArray = true;
            } else {
                $getValuesAsArray = false;
            }
            
            // do not overwrite, and skip invalid keys
            if (isset($settings[$key]) || empty($key)) {
                continue;
            }
            
            // set the values
            $settings[$key] = $getValuesAsArray
                ? (isset($val) ? $this->parseArraySetting($val) : array())
                : (isset($val) ? $val : true);
        }
        
        $this->settings = $settings;
    }
    
    /**
     * Converts a string of values separated by semicolons to an array.
     * 
     * @param string $value The string to convert.
     * 
     * @return array Returns the array of values with whitespace trimmed.
     */
    protected function parseArraySetting($value)
    {
        $valArray = array();
        foreach (explode(';', $value) as $subVal) {
            $subVal = trim($subVal);
            if (!empty($subVal)) {
                $valArray[] = $subVal;
            }
        }
        
        return $valArray;
    }
    
    /**
     * Adds more settings to this object's rawSettings property, separating the
     * new from the old with a "|".
     * 
     * @param string $newSettings Parseable ettings string to add to this object.
     */
    public function addSettings($newSettings)
    {
        $this->rawSettings .= '|' . $newSettings;
        $this->parseSettings();
    }
    
    /**
     * Returns the rawSettings property with the object is cast to string.
     * 
     * @return string The rawSettings property containing the original settings
     *                string and any added via addSettings().
     */
    public function __toString()
    {
        return $this->rawSettings;
    }
    
    /**
     * Gets all of the settings that have been added to this object.
     * 
     * @return array Returns an associative array of all the settings.
     */
    public function getAllSettings()
    {
        return $this->settings;
    }
}

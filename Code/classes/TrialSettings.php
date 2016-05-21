<?php
/**
 * TrialSettings class.
 */

namespace Collector;

/**
 * @todo add description for TrialSettings
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
     * Parses the raw settings string.
     */
    protected function parseSettings() {
        $settings = array();
        $rawSettings = $this->rawSettings;
        
        $splitRawSettings = explode('|', $rawSettings);
        foreach ($splitRawSettings as $rawSetting) {
            $splitSetting = explode('=', $rawSetting);
            $key = strtolower(trim($splitSetting[0]));
            if (substr($key, -2) === '[]') {
                $key = substr($key, 0, -2);
                $getValuesAsArray = true;
            } else {
                $getValuesAsArray = false;
            }
            
            if ($key === '') {
                // can't have empty key, makes no sense
                continue;
            }
            
            if (isset($settings[$key])) {
                // dont overwrite
                continue;
            }
                
            if (!isset($splitSetting[1])) {
                if ($getValuesAsArray) {
                    $settings[$key] = array();
                } else {
                    $settings[$key] = true;
                }
            } else {
                if ($getValuesAsArray) {
                    $valArray = array();
                    $valSplit = explode(';', $splitSetting[1]);
                    foreach ($valSplit as $subVal) {
                        $subVal = trim($subVal);
                        if ($subVal !== '') $valArray[] = $subVal;
                    }
                    $settings[$key] = $valArray;
                } else {
                    $settings[$key] = trim($splitSetting[1]);
                }
            }
        }
        
        $this->settings = $settings;
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

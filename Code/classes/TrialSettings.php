<?php
Class TrialSettings {
    public $rawSettings;
    public $settings;
    
    public function __construct($settings) {
        $this->rawSettings = $settings;
        $this->parseSettings();
    }
    
    public function __get($property) {
        $property = strtolower($property);
        if (!isset($this->settings[$property])) return false;
        return $this->settings[$property];
    }
    
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
            if ($key === '') continue; // cant have empty key, makes no sense
            if (isset($settings[$key])) continue; // dont overwrite
            
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
    
    public function addSettings($newSettings) {
        $this->rawSettings .= '|' . $newSettings;
        $this->parseSettings();
    }
    
    public function __toString() {
        return $this->rawSettings;
    }
    
    public function getAllSettings() {
        return $this->settings;
    }
}

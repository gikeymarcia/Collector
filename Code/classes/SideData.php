<?php
/**
 * SideData class.
 */

namespace Collector;

Class SideData
{
    protected $data = array();
    
    public function add($data, $category) {
        $badChars = array("\r", "\n", "\t");
        
        $keys = str_replace($badChars, ' ', array_keys($data));
        $vals = str_replace($badChars, ' ', $data);
        
        $data = array_combine($keys, $vals);
        
        $category = str_replace('_', '', $category);
        
        $i = 0;
        while (isset($this->data[$category][$i])) {
            ++$i;
        }
        
        $this->data[$category][$i] = $data;
    }
    
    public function get($key, $category) {
        $category = str_replace('_', '', $category);
        
        if (!isset($this->data[$category])) return null;
        
        $categoryData = end($this->data[$category]);
        
        return isset($categoryData[$key]) ? $categoryData[$key] : null;
    }
    
    public function getCategory($category) {
        $category = str_replace('_', '', $category);
        
        if (isset($this->data[$category])) {
            return $this->data[$category];
        } else {
            return null;
        }
    }
    
    public function getCategoryIndex($category, $index) {
        $category = str_replace('_', '', $category);
        
        if (isset($this->data[$category][$index])) {
            return $this->data[$category][$index];
        } else {
            return null;
        }
    }
    
    public function getCategoryFirst($category) {
        $category = str_replace('_', '', $category);
        
        if (isset($this->data[$category])) {
            return reset($this->data[$category]);
        } else {
            return null;
        }
    }
    
    public function getCategoryLast($category) {
        $category = str_replace('_', '', $category);
        
        if (isset($this->data[$category])) {
            return end($this->data[$category]);
        } else {
            return null;
        }
    }
    
    public function getAll($getAsCsvArray = false) {
        if ($getAsCsvArray) {
            $output = array();
            
            foreach ($this->data as $category => $dataArrays) {
                foreach ($dataArrays as $catIndex => $dataArray) {
                    foreach ($dataArray as $key => $value) {
                        $output[$category.'_'.$catIndex.'_'.$key] = $value;
                    }
                }
            }
            
            return $output;
            
        } else {
            return $this->data;
        }
    }
}

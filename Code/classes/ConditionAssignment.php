<?php

class ConditionAssignment
{
    public static function get(FileSystem $_files, $condition = null) {
        if ($_files->get_default('Current Experiment') === null) {
            throw new Exception('Cannot assign condition with FileSystem provided,' .
                                ' because it does not know the current condition.');
        }
        
        if (is_numeric($condition)) {
            return self::get_specific_condition($_files, $condition);
        } else {
            return self::get_random_assignment($_files);
        }
    }
    
    private static function get_specific_condition(FileSystem $_files, $condition) {
        $conditions = $_files->read('Conditions');
        
        if (!isset($conditions[$condition])) {
            throw new Exception('We attempted to load condition "' . 
                                htmlspecialchars($condition, ENT_QUOTES) .
                                '", but couldn\'t find it in the conditions file.');
        } else {
            return $conditions[$condition];
        }
    }
    
    private static function get_random_assignment(FileSystem $_files) {
        $conditions = $_files->read('Conditions');
        
        $random_assignments = $_files->read('Random Assignments');
        $random_assignments = explode(',', $random_assignments);
        
        while ($assignment = array_pop($random_assignments)) {
            if (isset($conditions[$assignment])) {
                $condition = $conditions[$assignment];
                break;
            }
        }
        
        if (!isset($condition)) {
            $random_assignments = self::make_new_random_assignments($conditions);
            $condition = $conditions[array_pop($random_assignments)];
        }
        
        $_files->overwrite('Random Assignments', implode(',', $random_assignments));
        
        return $condition;
    }
    
    private static function make_new_random_assignments($conditions) {
        $assignments = range(0, count($conditions)-1);
        shuffle($assignments);
        return $assignments;
    }
}

<?php

class CollectorData
{
    public static function get_data_as_html_table($usernames, $columns) {
        $output_function = function($output_row) {
            static $headers = true;
            
            if ($headers) {
                echo '<tr>';
                
                foreach ($output_row as $cell) {
                    echo '<th>', str_replace(array('&', '<'), array('&amp;', '&lt;'), $cell), '</th>';
                }
                
                echo '</tr></thead> <tbody>';
                $headers = false;
            } else {
                echo '<tr>';
                
                foreach ($output_row as $cell) {
                    echo '<td>', str_replace(array('&', '<'), array('&amp;', '&lt;'), $cell), '</td>';
                }
                
                echo '</tr>';
            }
        };
        
        echo '<table><thead>';
        
        self::get_data($usernames, $columns, $output_function);
        
        echo '</tbody></table>';
    }
    
    public static function get_data_as_javascript_array($usernames, $columns) {
        echo "[\n";
        
        $output_function = function($output_row) {
            echo json_encode(array_values($output_row)), ",\n";
        };
        
        self::get_data($usernames, $columns, $output_function);
        
        echo ",\n]\n";
    }
    
    public static function get_data_as_csv($usernames, $columns) {
        $filename = 'Collector_GetData_' . implode('_', array_keys($usernames)) . '_' . date('y.m.d') . '.csv';
        header("Cache-Control: public");
        header("Content-Description: File Transfer");
        header("Content-Disposition: attachment; filename=".$filename);
        header("Content-Type: text/csv"); 
        header("Content-Transfer-Encoding: binary");
        
        $output_function = function($output_row) {
            static $stream = null;
            
            if ($stream === null) $stream = fopen('php://output', 'w');
            
            fputcsv($stream, $output_row);
        };
        
        self::get_data($usernames, $columns, $output_function);
    }
    
    public static function get_data($usernames, $columns, $output_function) {
        $_files = new FileSystem();
        
        self::output_columns($columns, $output_function);
        
        foreach ($usernames as $exp_name => $user_list) {
            foreach ($user_list as $username) {
                self::output_user($_files, $username, $exp_name, $output_function, $columns);
            }
        }
    }
    
    public static function output_user(FileSystem $_files, $username, $exp_name, $output_function, $columns) {
        $system_map_values = array(
            'Current Experiment' => $exp_name,
            'Data Sub Dir'       => '',
            'Username'           => $username
        );
        
        $responses = $_files->read('User Responses', $system_map_values);
        $globals   = $_files->read('User Globals',   $system_map_values);
        $globals   = self::format_globals($globals);
        
        foreach ($responses as $row) {
            $row = array_merge($row, $globals);
            $sorted_row = array();
            
            foreach ($columns as $col) {
                if (isset($row[$col])) {
                    $sorted_row[$col] = $row[$col];
                } else {
                    $sorted_row[$col] = '';
                }
            }
            
            $output_function($sorted_row);
        }
    }
    
    public static function format_globals($globals) {
        $formatted = array();
        $flat_globals = self::flatten_array($globals);
        
        foreach ($flat_globals as $key => $val) {
            $formatted["Glob_$key"] = $val;
        }
        
        return $formatted;
    }
    
    public static function flatten_array($array) {
        $flat = array();
        
        foreach ($array as $key => $val) {
            if (!is_array($val)) {
                $flat[$key] = $val;
            } else {
                foreach (self::flatten_array($val) as $sub_key => $sub_val) {
                    $flat[$key . '_' . $sub_key] = $sub_val;
                }
            }
        }
        
        return $flat;
    }
    
    public static function output_columns($columns, $output_function) {
        foreach ($columns as $i => $col) {
            $columns[$i] = strtr($col, ' ', '_');
        }
        
        $output_function($columns);
    }
    
    public static function find_available_data() {
        $_files = new FileSystem();
        
        $usernames = self::get_usernames_in_each_exp($_files);
        $columns   = self::get_columns_of_users_in_each_exp($_files, $usernames);
        
        return array(
            'Usernames' => $usernames,
            'Columns'   => $columns
        );
    }
    
    public static function get_usernames_in_each_exp(FileSystem $_files) {
        $data_dirs = self::get_data_dirs($_files);
        $usernames = array();
        
        foreach ($data_dirs as $dir) {
            $exp_name = substr($dir, 0, -5);
            $usernames[$exp_name] = self::get_usernames_in_exp($_files, $exp_name);
        }
        
        return $usernames;
    }
    
    public static function get_data_dirs(FileSystem $_files) {
        $data_root = $_files->get_path('Data');
        
        if (!is_dir($data_root)) mkdir($data_root, 0777, true);
        
        $data_dirs = scandir($data_root);
        
        foreach ($data_dirs as $i => $dir) {
            if ($dir === '.'
                || $dir === '..'
                || !is_dir("$data_root/$dir")
                || substr($dir, -5) !== '-Data'
            ) {
                unset($data_dirs[$i]);
            }
        }
        
        return array_values($data_dirs);
    }
    
    public static function get_usernames_in_exp(FileSystem $_files, $exp_name) {
        $system_map_values = array(
            'Current Experiment' => $exp_name,
            'Data Sub Dir'       => ''
        );
        
        $data_dir_path = $_files->get_path('Current Data Dir', $system_map_values);
        
        $users = scandir($data_dir_path);
        
        foreach ($users as $i => $username) {
            if (!is_file("$data_dir_path/$username/responses.csv")) {
                unset($users[$i]);
            }
        }
        
        return array_values($users);
    }
    
    public static function get_columns_of_users_in_each_exp(FileSystem $_files, $usernames_in_exps) {
        $columns = array();
        
        $system_map_values = array();
        
        foreach ($usernames_in_exps as $exp_name => $user_list) {
            foreach ($user_list as $username) {
                $user_columns = self::get_columns_of_user($_files, $exp_name, $username);
                
                foreach ($user_columns as $column) {
                    $columns[$column] = true;
                }
            }
        }
        
        return array_keys($columns);
    }
    
    public static function get_columns_of_user(FileSystem $_files, $exp_name, $username) {
        $system_map_values = array(
            'Current Experiment' => $exp_name,
            'Data Sub Dir'       => '',
            'Username'           => $username
        );
        
        $resp_columns = $_files->get_columns('User Responses', $system_map_values);
        $globals      = $_files->read(       'User Globals',   $system_map_values);
        $globals      = self::format_globals($globals);
        $glob_columns = array_keys($globals);
        
        foreach ($glob_columns as $col) {
            $resp_columns[] = "Glob_$col";
        }
        
        return $resp_columns;
    }
}

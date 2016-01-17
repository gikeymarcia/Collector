<?php

	##### these functions need to be reconciled with rest of collector ####
	
		// this function scans a directory and returns a list of csvs found inside
    function getCsvsInDir($dir) {
        $scan = scandir($dir);
        $output = array();
        foreach ($scan as $entry) {
            // get the lowercase last 4 characters of the file name,
            // and see if the file ends in ".csv"
            if (strtolower(substr($entry, -4)) === '.csv') {
                // if it matches, take everything up to the extension
                // e.g., from "myStimuli.csv", get "myStimuli"
                $output[] = $entry;
            }
        }
        return $output;
    }
	
	function csv_to_array($filename='', $delimiter=','){
		if(!file_exists($filename) || !is_readable($filename))
			return FALSE;
		
		$header = NULL;
		$data = array();
		if (($handle = fopen($filename, 'r')) !== FALSE)
		{
			while (($row = fgetcsv($handle, 1000, $delimiter)) !== FALSE)
			{
				if(!$header)
					$header = $row;
				else
					$data[] = array_combine($header, $row);
			}
			fclose($handle);
		}
		return $data;
	}
	
	// Tpoja's solution for renaming a folder on http://stackoverflow.com/questions/30077379/php-rename-access-is-denied-code-5
	
	function rename_win($oldfile,$newfile) {
		if (!rename($oldfile,$newfile)) {
			if (copy ($oldfile,$newfile)) {
				unlink($oldfile);
				return TRUE;
			}
			return FALSE;
		}
		return TRUE;
	}
	
	### the below code was found at: http://stackoverflow.com/questions/2050859/copy-entire-contents-of-a-directory-to-another-using-php
	function recurse_copy($src,$dest) {
	$dir = opendir($src); 
		@mkdir($dest); 
		while(false !== ( $file = readdir($dir)) ) { 
			if (( $file != '.' ) && ( $file != '..' )) { 
				if ( is_dir($src . '/' . $file) ) { 
					recurse_copy($src . '/' . $file,$dest . '/' . $file);
				} 
				else { 
					copy($src . '/' . $file,$dest . '/' . $file); 
				} 
			} 
		} 
		closedir($dir);
	}

?>
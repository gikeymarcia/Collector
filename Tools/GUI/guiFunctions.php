<?php

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
		mkdir($dest); 
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
	
	function writeHoT($filename, $contents) {
    // get rid of empty columns
    $emptyCols = array();
    foreach ($contents[0] as $i => $header) {
        if ((string) $header === '') {
            $emptyCols[] = $i;
        }
    }
    
    foreach ($contents as &$row) {
        foreach ($emptyCols as $emptyCol) {
            unset($row[$emptyCol]);
        }
        $row = array_values($row);
    }
    unset($row);
    
    // get rid of extra rows
    foreach ($contents as $i => $row) {
        $isEmpty = true;
        foreach ($row as $cell) {
            if ((string) $cell !== '') {
                $isEmpty = false;
                break;
            }
        }
        
        if ($isEmpty) {
            unset($contents[$i]);
        }
    }
    
    $contents = array_values($contents);
    
    // remove bad characters
    foreach ($contents as &$row) {
        foreach ($row as &$cell) {
            $cell = preg_replace('/[\x00-\x1F]/', '', $cell);
        }
    }
    unset($row, $cell);
    
    if ($contents === array()) {
        return false;
    }
    
    $res = fopen($filename, 'w');
    foreach ($contents as $row) {
        fputcsv($res, $row);
    }
    fclose($res);
    return true;
  }
    
    function checkPost($post,$legitPostNames,$illegalInputs){
      foreach($post as $postKey=>$postItem){
        if($postKey!="stimTableInput" & in_array($postKey,$legitPostNames)){
        // check on what has been inputted
          if(!is_array($postItem)){
            foreach($illegalInputs as $illegalInput){
              if(strpos($postItem,$illegalInput)!==false){
                $_POST[$postKey]=$_SESSION[$postKey];                
                ?>                
                <div class="alert alert-warning fade in">
                  <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                  <strong>Warning!</strong> You tried to use illegal syntax in one of the inputs. Reverting to last accepted version of that input.
                </div>
                <?php
                                
              }
              
              else {
                // the above deals with malicious code. The following tidies up code that is accidentally harmful
                //preg_replace('([^ !#$%&\'()+,\\-.0-9;=@A-Z[\\]^_`a-z{}~])', '', $_POST[$postKey]); //probably too lenient, but leave for now
              }
            }
              

            
          }
        } else {
          unset($post[$postKey]);
        }      
      }
    }

?>

<script>
      Object.size = function(obj) { //solution from http://stackoverflow.com/questions/5223/length-of-a-javascript-object-that-is-associative-array by James Coglan
    var size = 0, key;
    for (key in obj) {
        if (obj.hasOwnProperty(key)) size++;
    }
    return size;
    };

</script>
<?php
    function checkSurveyFilename($surveyFile, $surveyDir) {
        global $_EXPT;
        $errors = array();
        
        if (strtolower(substr($surveyFile, 0, 4)) === 'http') {
            $err = 'For the survey trial type, the "Cue" column must be the name of a local file. '
                 . 'It cannot start with "http". '
                 . '"' . $_EXPT->get('cue') . '" is invalid.';
            
            $errors[] = $err;
        }
        
        if (strtolower(substr($surveyFile, -4)) !== '.csv') {
            $err = 'For the survey trial type, the "Cue" column must be the name of a local file, '
                 . 'ending with the extension ".csv". '
                 . '"' . $_EXPT->get('cue') . '" is invalid.';
            
            $errors[] = $err;
        }
        
        if (strpos($surveyFile, '..') !== false) {
            $err = 'For the survey trial type, the "Cue" column must be the name of a local file, '
                 . 'inside the "Surveys" folder of the "Experiments/Common" folder. '
                 . 'The cue cannot contain "..". '
                 . '"' . $_EXPT->get('cue') . '" is invalid.';
            
            $errors[] = $err;
        }
        
        $surveyCompletePath = $surveyDir . '/' . $surveyFile;
        $surveyActualPath = Collector\Helpers::fileExists($surveyCompletePath, false, 0);
        
        if ($surveyActualPath === false) {
            $err = 'Survey trial type needs a cue for a valid survey file, '
                 . '"' . $surveyFile . '" not found inside "' . $surveyDir . '".';
            
            $errors[] = $err;
        }
        
        return $errors;
    }
    
    function checkSurveyContents($survey, $surveyFile, $trialDir) {
        /* * * * * *
         * Goals
         * 1. check that survey has actual data inside
         * 2. check for required columns ('Question', 'Question Name', 'Type')
         * 3. make sure the question names are unique to each row
         * 4. make sure no question names are empty strings ''
         * 5. if Answers and Values exist, make sure they have the same number of entries
         * 6. make sure all scoring columns have a scoring name
         * * * */
        $errors = array();
        
        # 1. check that survey has actual data inside
        // some error should be moved to Collector\Helpers::getFromFile()
        // it should make sure we find at least 1 row of
        // data underneath the headers
        if (!is_array($survey) || count($survey) < 1) {
            $err = 'The "Survey" trial type failed to read "' . $surveyFile . '" correctly. '
                 . 'Make sure that this is a proper csv file with at least 1 row of data underneath the headers.';
            
            $errors[] = $err;
        }
        
        # 2. check for required columns ('Question', 'Question Name', 'Type')
        // these columns should always be in the file
        // other columns can be requested by specific survey types
        $missingCols = array('Question', 'Question Name', 'Type');
        
        foreach ($missingCols as $i => $reqCol) {
            if (isset($survey[0][$reqCol])) {
                unset($missingCols[$i]);
            }
        }
        
        if ($missingCols !== array()) {
            $err = 'The "Survey" trial type is trying to use the file "' . $surveyFile . '", '
                 . 'but cannot find certain required columns. Make sure that these columns '
                 . 'exist in this file: "' . implode('", "', $missingCols) . '"';
            
            $errors[] = $err;
        }
        
        # 3. make sure the question names are unique to each row
        // make sure question names are unique
        $allSurveyTypes = getSurveyTypes($trialDir);
        $questionNames = array();
        foreach ($survey as $i => $row) {
            $type = cleanSurveyType($row['Type']);
            if ($type === 'instruct' || $type === 'page_break' || $type === 'type_break') {
                continue; // these types dont create an html input element
            }
            if (!isset($allSurveyTypes[$type])) {
                $err = "Inside the survey file \"$surveyFile\", on row " . ($i+2) . ", the type \"{$row['Type']}\" "
                     . "is invalid. This column must be one of the pre-defined types created in the "
                     . "survey trial type folder, in the Type/ subfolder. The list of valid types are: "
                     . "\"" . implode('", "', array_keys($allSurveyTypes)) . "\"";
                $errors[] = $err;
            }
            $questionNames[$row['Question Name']][] = $i;
        }
        
        foreach ($questionNames as $name => $indices) {
            if (count($indices) > 1) {
                $rowNumbers = array();
                
                foreach ($indices as $i) {
                    $rowNumbers[] = $i+2; // padding for human readability
                }
                
                $err = 'The following rows inside "' . $surveyFile . '" use the same '
                     . 'value for the "Question Name" column, but these need to be unique. '
                     . 'Question Name: "' . $name . '", Rows: ' . implode(', ', $rowNumbers);
                
                $errors[] = $err;
            }
            
            if (preg_match('/[^a-zA-Z0-9[\\] _]/', $name) == true) {
                $rowNumbers = array();
                
                foreach ($indices as $i) {
                    $rowNumbers[] = $i+2; // padding for human readability
                }
                
                $err = 'The following rows inside "' . $surveyFile . '" use an invalid '
                     . 'value for the "Question Name" column. The cells in '
                     . 'this column should only contain numbers, letters, underscores, and '
                     . 'square brackets []. '
                     . 'Question Name: "' . $name . '", Rows: ' . implode(', ', $rowNumbers);
                
                $errors[] = $err;
            }
        }
        
        # 4. make sure no question names are empty strings ''
        if (isset($questionNames[''])) {
            $rowNumbers = array();
            
            foreach ($questionNames[''] as $i) {
                $rowNumbers[] = $i+2;
            }
            
            $err = 'The following rows inside "' . $surveyFile . '" have a blank '
                 . 'value for the "Question Name" column, but this column needs '
                 . 'to be filled with names for these questions. '
                 . 'Rows: ' . implode(', ', $rowNumbers);
            
            $errors[] = $err;
        }
        
        # 5. if Answers and Values exist, make sure they have the same number of entries
        if (isset($survey[0]['Answers'], $survey[0]['Values'])) {
            $problemRows = array();
            
            foreach ($survey as $i => $row) {
                if ($row['Values'] === '') continue;
                // delimit cell contents with "|" rather than ",", since answers can be text descriptions
                $rowAnswers = Collector\Helpers::rangeToArray($row['Answers'], '|');
                $rowValues  = Collector\Helpers::rangeToArray($row['Values'],  '|');
                
                if (count($rowAnswers) !== count($rowValues)) {
                    $problemRows[] = $i;
                }
            }
            
            if ($problemRows !== array()) {
                $err = 'In the survey file "' . $surveyFile . '", in at least 1 row, '
                     . 'the "Answers" column and the "Values" column do not have the '
                     . 'same number of entries. These entries are defined by having a range '
                     . 'defined with 2 end points connected by "::", and discrete entries '
                     . 'separated by "|". For example, if the "Answers" column contained "1::3|5::7", '
                     . 'then the program would understand that the answers are "1, 2, 3, 5, 6, 7". '
                     . 'The "Answers" column and the "Values" column must have the same number '
                     . 'of entries defined this way, so that they can be matched up properly during '
                     . 'the data recording. Please correct the following rows: ';
                
                foreach ($problemRows as $rowIndex) {
                    $err .= ($rowIndex + 2) . ', ';
                }
                
                $err = substr($err, 0, -2); // cut off that trailing ", "
                
                $errors[] = $err;
            }
        }
        
        # 6. make sure all scoring columns have a scoring name
        foreach (array_keys($survey[0]) as $header) {
            if (strtolower(substr($header,0, 5)) === 'score') {
                $headerData = explode(':', $header);
                
                if (!isset($headerData[1])) {
                    $err = 'In the survey file "' . $surveyFile . '", the scoring column '
                         . '"' . $header . '" is defined incorrectly. All scoring columns, '
                         . 'identified by starting with the word "score", must have a score '
                         . 'name defined, like so: "Score: Optimism". If this is not intended '
                         . 'to be a scoring column, please rename this column to start with'
                         . 'something other than "score".';
                    
                    $errors[] = $err;
                }
            }
        }
        
        return $errors;
    }
    
    function readSurveyFile($surveyFile, $surveyDir, $trialDir) {
        // check for filename errors, before even loading the file
        $filenameErrors = checkSurveyFilename($surveyFile, $surveyDir);
        
        if ($filenameErrors !== array()) {
            foreach ($filenameErrors as $err) {
                trigger_error($err, E_USER_WARNING);
            }
            
            exit('Errors found with the survey name "' . $surveyFile . '", and it could not be read. '
               . 'Please make sure the "Cue" for this trial defines a survey file, according to the format '
               . 'described in the notes.txt file in this trial type\'s folder.');
        }
        
        // load the survey
        $surveyCompletePath = $surveyDir . '/' . $surveyFile;
        $surveyActualPath = Collector\Helpers::fileExists($surveyCompletePath, false, 0);
        $survey = Collector\Helpers::getFromFile($surveyActualPath, false);
        
        // check the contents of the survey file
        $surveyErrors = checkSurveyContents($survey, $surveyFile, $trialDir);
        
        if ($surveyErrors !== array()) {
            foreach ($surveyErrors as $err) {
                trigger_error($err, E_USER_WARNING);
            }
            
            exit('Errors found with the survey "' . $surveyFile . '", and it cannot be used. '
               . 'Please make sure the data inside this survey file follows the rules laid out '
               . 'in the notes.txt file in this trial type\'s folder.');
        }
        
        // looks like we made it. shuffle and return
        $survey = multiLevelShuffle($survey);
        $survey = shuffle2dArray($survey);
        return $survey;
    }
    
    function cleanSurveyType($type) {
        $type = strtolower($type);
        $type = str_replace(' ', '_', $type);
        return $type;
    }
    
    function getSurveyTypes($trialDir) {
        $typesDir = "$trialDir/Types";
        $scan = scandir($typesDir);
        $types = array();
        $fileTypes = array('display', 'scoring', 'validator');
        foreach ($scan as $entry) {
            if ($entry === '.' || $entry === '..') continue;
            if (!is_dir("$typesDir/$entry")) continue;
            $type = cleanSurveyType($entry);
            foreach ($fileTypes as $filename) {
                $filePath = Collector\Helpers::fileExists("$typesDir/$entry/$filename.php", false, 0);
                if ($filePath !== false) $types[$type][$filename] = $filePath;
            }
            $filePath = fileExists("$typesDir/$entry/getResponses.php", false, 0);
            if ($filePath !== false) $types[$type]['getResponses'] = require $filePath;
        }
        return $types;
    }
    
    function isRespRequired($row) {
        if (isset($row['Required'])) {
            $key = strtolower($row['Required']);
        } else {
            $key = '';
        }
        
        if ($key === 'no' || $key === 'off' || $key === '0') {
            return false;
        } else {
            return true;
        }
    }
    
    function surveyRangeToArray($range) {
        return rangeToArray($range, '|');
    }

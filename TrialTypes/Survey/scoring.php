<?php
    $data = array();
    
    $commonColumns = array('RT', 'RTfirst', 'RTlast', 'Focus');
    $commonData = array();
    foreach ($commonColumns as $col) {
        $commonData[$col] = $_POST[$col];
    }
    $_EXPT->record($commonData);
    
    # SAVE ALL DATA
    foreach ($_POST as $inpName => $resp) {
        if (!is_array($resp)) {
            $data[$inpName] = $resp;
        } else {
            $data[$inpName] = implode('|', $resp);
            foreach ($resp as $i => $iResp) {
                $cleanName = $inpName . '_' . ($i+1);
                if (!isset($data[$cleanName])) $data[$cleanName] = $iResp;
            }
        }
    }
    
    # FIND SURVEY, make sure we have the shuffled version that was actually used
    if (!isset($_SESSION['CurrentSurvey'])) {
        $err = "Error: current survey not saved into SESSION";
        trigger_error($err, E_USER_ERROR);
    }
    $survey = $_SESSION['CurrentSurvey'];

    //Survey doesn't include information from the procedure file.
    
    # CUSTOM TYPE SCORING, let each survey type have access to its data
    $item = $_EXPT->get('item');
    
    if (is_array($item)) {
        $surveyFile = $_EXPT->get('cue');
    } else {
        $surveyFile = $item;
    }
    
    $trialTypeDir = dirname($_TRIAL->getRelatedFile('display'));
    $surveyDir    = $_PATH->get('Common') . '/Surveys';
    require "$trialTypeDir/SurveyFunctions.php";
    
    $allSurveyTypes = getSurveyTypes($trialTypeDir);
    $customData     = array();
    
    $surveyIndex = 0;
    while (isset($survey[$surveyIndex])) {
        $type = $survey[$surveyIndex]['Type'];
        $type = cleanSurveyType($type);
        $surveyRows = array($survey[$surveyIndex]);
        ++$surveyIndex;
        
        while (
            isset($survey[$surveyIndex])
         && cleanSurveyType($survey[$surveyIndex]['Type']) === $type
        ) {
            $surveyRows[] = $survey[$surveyIndex];
            ++$surveyIndex;
        }
        
        if ($type === 'page_break') {
            continue;
        } elseif ($type === 'type_break') {
            continue;
        } elseif (isset($allSurveyTypes[$type]['scoring'])) {
            require $allSurveyTypes[$type]['scoring'];
        }
    }
    
    $scores = array();
    
    # SURVEY SCORING
    // required columns: Answers
    //if survey[0] or ['answers is set']
    if (isset($survey[0]['Answers'])) {
        $scoreCols = array(); //create scoring columns
        foreach (array_keys($survey[0]) as $surveyCol) {
            if (strtolower(substr($surveyCol, 0, 5)) === 'score') {
                $scoreColumnParts = explode(':', $surveyCol);
                if (!isset($scoreColumnParts[1])) continue; // abandon ship
                $scoreName = trim($scoreColumnParts[1]);
                $scoreType = substr($scoreColumnParts[0], 5);
                $scoreType = strtolower(trim($scoreType));
                if ($scoreType !== 'average') $scoreType = 'sum';
                $scoreCols[$surveyCol] = array (
                    'Name' => $scoreName,
                    'Type' => $scoreType
                );
            }
        }

        foreach ($scoreCols as $col => $score) {
            $scoreName  = $score['Name'];
            $scoreType  = $score['Type'];
            $respValues = array();
            
            foreach ($survey as $surveyRow) {
                if ($surveyRow[$col] === '') continue; // this row not used for this scale
                $type = cleanSurveyType($surveyRow['Type']);
                
                $qName = $surveyRow['Question Name'];
                
                if (isset($allSurveyTypes[$type]['getResponses'])) {
                    $rowResponses = $allSurveyTypes[$type]['getResponses']($surveyRow);
                } elseif (isset($data[$qName])) {
                    $rowResponses = array($data[$qName]);
                } else {
                    continue; // somehow, this question isn't in the data
                }
                
                $numericResponses = array();
                
                if ($surveyRow['Answers'] === '') {
                    foreach ($rowResponses as $resp) {
                        if (is_numeric($resp)) $numericResponses[] = $resp;
                    }
                    $respFactor = $surveyRow[$col];
                } else {
                    $answers = surveyRangeToArray($surveyRow['Answers']);
                    if ($surveyRow['Values'] === '') {
                        $values = $answers;
                    } else {
                        $values = surveyRangeToArray($surveyRow['Values']);
                    }
                    
                    foreach ($values as $val) {
                        if (!is_numeric($val)) continue 2; // cant use this row, values arent numeric
                    }
                    if (count($answers) !== count($values)) continue; // cant convert answer to value directly
                    
                    if ($surveyRow[$col][0] === 'r' || $surveyRow[$col][0] === 'R') {
                        $answerValues = array_combine($answers, array_reverse($values));
                        $respFactor   = substr($surveyRow[$col], 1);
                    } else {
                        $answerValues = array_combine($answers, $values);
                        $respFactor   = $surveyRow[$col];
                    }
                    
                    $answerValues = array_combine($answers, $values);
                    
                    foreach ($rowResponses as $resp) {
                        if (isset($answerValues[$resp])) $numericResponses[] = $answerValues[$resp];
                    }
                }
                    
                if (!is_numeric($respFactor)) continue; // a factor of 'string' means nothing
                
                foreach ($numericResponses as $resp) {
                    $respValues[] = $resp * $respFactor;
                }
            }
            
            if (count($respValues) === 0) {
                $computedScore = 'no data'; // no data found for this scale
            } else {
                // if you want to add more scoring types, put the code here, as an elseif
                if ($score['Type'] === 'average') {
                    $computedScore = array_sum($respValues) / count($respValues);
                } else {
                    $computedScore = array_sum($respValues);
                }
            }
            
            $scores["Score_$scoreName"] = $computedScore;
            
            $data["Score_$scoreName"] = $computedScore;
        }
    }
    
    // record survey data into side data
    $sideData = $data;
    
    foreach ($commonColumns as $col) {
        unset($sideData[$col]);
    }
    
    if (empty($sidedata_label)) {
        $sidedata_label = pathinfo($surveyFile, PATHINFO_FILENAME);
    }
    

    /*
        This following section allows for writing of survey on multiple lines. It does this by 
        feeding new columns to the recordTrial function.
    */

    //check the settings column, if vertical, then output that way
    if ($_TRIAL->settings->output === "vertical"){
        //run through each item in survey
        foreach ($survey as $surveyRowIndex => $surveyRow) {
            //grab the type of survey
            $type = cleanSurveyType($surveyRow['Type']);
            //grab the question name
            $qName = $surveyRow['Question Name'];
            
            if (isset($allSurveyTypes[$type]['getResponses'])) {
                $rowResponses = $allSurveyTypes[$type]['getResponses']($surveyRow);
            } elseif (isset($data[$qName])) {
                $rowResponses = array($data[$qName]);
            } else {
                continue; // somehow, this question isn't in the data
            }
            //for reach row response
            foreach ($rowResponses as $resp) {
                //declare extradata array
                $extraData = array();
                $extraData['Resp_Response'] = $resp; //the response column
                $extraData['Resp_Survey_Index'] = $surveyRowIndex+1; //index of survey
                
                //add extra data columns
                foreach ($surveyRow as $col => $val) {
                    $extraData['Survey_' . $col] = $val; 
                }
                
                //add response columns
                foreach ($scores as $col => $score) {
                    $extraData['Resp_' . $col] = $score;
                }
                
                //record the trials
                recordTrial($extraData);
            }
        }
        
        $data = $commonData; 
    }
    
    unset($_SESSION['CurrentSurvey']);

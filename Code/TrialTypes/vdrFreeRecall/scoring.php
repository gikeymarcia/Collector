<?php
/*  Collector
    A program for running experiments on the web
    Copyright 2012-2015 Mikey Garcia & Nate Kornell
 */
    
    // to make sure that everything is recorded, just throw all of POST into
    // $data, which will eventually be recorded into the output file.
    // To create new columns, simply assign the new data you want to record
    // to a new key in $data.
    // For example,    $data[ 'New Column' ] = "Hello";    would make a new
    // column titled "New Column" in the output, and every row for this trial
    // type would have the value "Hello".
    $data = $_POST;
    
    function DamLevLimit( $str1, $str2, $limit ) {
        $str1 = trim(strtolower($str1));
        $str2 = trim(strtolower($str2));
        if( $str1 === $str2 ) {
            return 0;
        }
        if( abs( strlen($str1) - strlen($str2) ) > $limit ) { return false; }
        if( $str1 === '' ) {
            if( strlen($str2) > $limit ) { return false; }
            else {
                return strlen( $str2 );     //if first is empty, length of second
            }
        }
        elseif( $str2 === '' ) {
            if( strlen($str1) > $limit ) { return false; }
            else {
                return strlen( $str1 );         //and if only the second is empty, length of the first
            }
        }
        //after this, i understand nothing
        $score = array();
        
        $inf = strlen($str1) + strlen($str2);
        $score[0][0] = $inf;
        for( $i=0; $i<=strlen($str1); $i++ ){
            $score[$i+1][1] = $i;
            $score[$i+1][0] = $inf;
        }
        for( $i=0; $i<=strlen($str2); $i++ ){
            $score[1][$i+1] = $i;
            $score[0][$i+1] = $inf;
        }
        
        $sd = array();
        $strComb = $str1.$str2;
        for( $i=0; $i<strlen($strComb); $i++ ){
            $sd[ $strComb[$i] ] = 0;
        }
        
        for( $i=1; $i<=strlen($str1); $i++ ) {
            $db = 0;
            for( $j=1; $j<=strlen($str2); $j++ ) {
                $i1 = $sd[$str2[$j-1]];
                $j1 = $db;
                
                if( $str1[$i-1] === $str2[$j-1] ) {
                    $score[$i+1][$j+1] = $score[$i][$j];
                    $db = $j;
                }
                else {
                    $score[$i+1][$j+1] = min( $score[$i][$j], $score[$i+1][$j], $score[$i][$j+1] )+1;
                }
                
                $score[$i+1][$j+1] = min( $score[$i+1][$j+1], ($score[$i1][$j1] + $i - $i1 + $j - $j1 -1) );
            }
            $sd[$str1[$i-1]] = $i;
            if( $score[$i][max(1,$j+$i-strlen($str1)-1)] > $limit ) {
                return false; 
            }
        }
        if( $score[strlen($str1)+1][strlen($str2)+1] > $limit ) {
            return false;
        }
        return $score[strlen($str1)+1][strlen($str2)+1];
    }
    
    $data = $_POST;
    
    $answers = explode( '|', $answer );
    
    $unacceptable = array();
    if( isset( $currentTrial['Stimuli']['Unacceptable Answers'] ) ) {
        $unaccAns = explode( '|', $currentTrial['Stimuli']['Unacceptable Answers'] );
        foreach( $unaccAns as $unacc ) {
            $temp = explode( ',', $unacc );
            foreach( $temp as &$t ) {
                $t = trim($t);
            }
            unset($t);
            $unacceptable[] = $temp;
        }
    } else {
        foreach( $answers as $ans ) {
            $unacceptable[] = array();
        }
    }
    
    $leniency = array();
    if( isset( $currentTrial['Stimuli']['Leniency'] ) ) {
        $leniency = explode( '|', $currentTrial['Stimuli']['Leniency'] );
        foreach( $leniency as &$len ) {
            $len = (int) $len;
        }
        unset($len);
    } else {
        foreach( $answers as $ans ) {
            $leniency[] = 1;
        }
    }
    
    $value = array();
    if( isset( $currentTrial['Stimuli']['Value'] ) ) {
        $value = explode( '|', $currentTrial['Stimuli']['Value'] );
        foreach( $value as &$val ) {
            $val = (float) $val;
        }
        unset($val);
    } else {
        foreach( $answers as $ans ) {
            $value[] = 1;
        }
    }
    
    $answerCount = count($answers);
    
    
    
    $input = 'one';
    $expandOutput = true;
    
    $settings = explode('|', $settings);
    foreach ($settings as $setting) {
        if ($test = removeLabel($setting, 'input')) {
            $test = strtolower($test);
            if (($test === 'one') OR ($test === 'many') OR (is_numeric($test))) {
                $input = $test;
            } else {
                exit('Error: invalid "input" setting for trial type "'.$trialType.'", on trial '.$currentPos);
            }
        } elseif ($test = removeLabel($setting, 'expandOutput')) {
            $test = strtolower($test);
            if (($test === 'no') OR ($test === 'false')) {
                $expandOutput = false;
            }
        }
    }
    
    
    $respExp = array();
    
    if ($input === 'one') {
        $responseFormatted = strtolower(preg_replace("/[^a-zA-Z0-9'\- ]+/", " ", $_POST['Response'] )); //replace most symbols with spaces, so that if they entered like word,word,word, we get separate words
        $responseFormatted = trim(preg_replace( "/\s+/", " ", $responseFormatted ));            //then, set all spaces and newlines to a single space.  this assumes that the answers dont have non-alphanumerical characters in them
        $respExp = explode( ' ', $responseFormatted );
    } else {
        $i = 1;
        while (isset($_POST['Response'.$i])) {
            $respExp[] = $_POST['Response'.$i];
            ++$i;
        }
    }
    
    $damLevByAns = array();
    $damLevByRes = array();
    foreach( $answers as $i => $ans ) {
        foreach( $respExp as $j => $res ) {
            if( in_array( $res, $unacceptable[$i] ) ) { continue; }
            if( (substr($res,-1)==='y' AND substr($ans,-3)==='ies') OR (substr($ans,-1)==='y' AND substr($res,-3)==='ies') ) { $plural = 2; } else { $plural = 0; }     //no penalties for knowing how to spell
            $dist = DamLevLimit( $ans, $res, $leniency[$i]+$plural );
            if( $dist === false ) { continue; }
            $damLevByRes[ $j ][ $i ] = $dist;
            $damLevByAns[ $i ][ $j ] = $dist;
        }
    }
    $match = array();
    while( count($damLevByAns, true) !== count($damLevByAns) ) {            //keep going until all of our Answer rows are empty, meaning they have had all possible matches removed
        foreach( $damLevByAns as $i => $resArray ) {
            foreach( $resArray as $j => $diff ) {
                if( $diff === min($resArray) AND $diff === min($damLevByRes[$j]) ) {
                    $match[$i]['word']         = $respExp[$j];
                    $match[$i]['diff']         = $diff;
                    $match[$i]['output_order'] = $j+1;
                    foreach( $damLevByRes[$j] as $i2 => $diff2 ) {          //remove all references to this match in both arrays, along their columns and rows
                        unset( $damLevByAns[$i2][$j] );
                    }
                    foreach( $damLevByAns[$i] as $j2 => $diff2 ) {
                        unset( $damLevByRes[$j2][$i] );
                    }
                    unset( $damLevByAns[$i] );
                    unset( $damLevByRes[$j] );
                    break 2;
                }
            }
        }
    }
    
    $matchedAnswers = array();
    $differences = array();
    $data['possibleVal'] = array_sum($value);
    $data['possibleAcc'] = $answerCount;
    $data['lenientVal']  = 0;
    $data['strictVal']   = 0;
    $data['lenientAcc']  = 0;
    $data['strictAcc']   = 0;
    foreach( $answers as $i => $ans ) {
        if( isset( $match[$i] ) ) {
            $matchedAnswers[$i]     = $match[$i]['word'];
            $unmatchedAnswers[$i]   = '_';
            $differences[$i]        = $match[$i]['diff'];
            $outputOrders[$i]       = $match[$i]['output_order'];
            $data['lenientAcc']++;
            $data['lenientVal'] += $value[$i];
            if( $match[$i]['diff'] === 0 ) {
                $data['strictAcc']++;
                $data['strictVal'] += $value[$i];
            }
        }
        else {
            $matchedAnswers[$i]     = '_';
            $unmatchedAnswers[$i]   = $ans;
            $differences[$i]        = '_';
            $outputOrders[$i]       = '_';
        }
    }
    $data['matchedAns']     = implode( '|', $matchedAnswers     );  //we can see which words were identified
    $data['output_order']   = implode( '|', $outputOrders   );  //we can see when words were identified
    $data['unmatchedAns']   = implode( '|', $unmatchedAnswers   );  //we can see which words were not identified
    $unmatchedResp = array();
    foreach( $respExp as $resp ) {
        $found = false;
        foreach( $matchedAnswers as $i => $ans ) {
            if( $resp === trim(strtolower( $ans )) ) {
                $found = true;
                unset( $matchedAnswers[$i] );
                break;
            }
        }
        if( !$found ) {
            $unmatchedResp[] = $resp;
        }
    }
    
    $data['Selectivity_Index'] = '';
    $data['SI_Best']           = 0;
    $data['SI_Chance']         = 0;
    
    $valueCopy = $value;
    if ($data['lenientAcc'] > 0) {
        $siChance = $data['possibleVal']/$answerCount*$data['lenientAcc'];
        $siBest = 0;
        sort($valueCopy);
        for( $i=0; $i<$data['lenientAcc']; ++$i ) {
            $siBest += array_pop($valueCopy);
        }
        $data['SI_Best'] = $siBest;
        $data['SI_Chance'] = $siChance;
        if ($siBest !== $siChance)
        {
            $data['Selectivity_Index'] = ($data['lenientVal']-$siChance)/($siBest-$siChance);
        }
    }
    
    $data['Accuracy']       = $data['lenientAcc']/$answerCount;
    $data['unmatchedResp']  = implode( '|', $unmatchedResp  );
    $data['Errors']         = implode( '|', $differences    );  //and we can see how far off they were (so if we set leniency = 2, we can still which were 0, 1, or 2 off)
    
    $currentTrial['Response'] = placeData($data, $currentTrial['Response'], $keyMod);
    
    
    
    if ($expandOutput) {
    
        $stimInfo = array();
        foreach ($currentStimuli as $header => $contents) {
            $stimInfo[$header] = explode('|', $contents);
        }
        
        foreach ($answers as $i => $ans) {
            $extraData = array();
            $extraData['Serial_Position'] = $i+1;
            
            foreach ($stimInfo as $header => $contents) {
                $extraData['Serial_' . $header] = $contents[$i];
            }
            
            if( isset( $match[$i] ) ) {
                $extraData['Serial_Matched_Word'] = $match[$i]['word'];
                $extraData['Serial_Matched_Diff'] = $match[$i]['diff'];
                $extraData['Serial_Output_Order'] = $match[$i]['output_order'];
                $extraData['Serial_lenientAcc']   = 1;
                $extraData['Serial_lenientVal']   = $value[$i];
                if( $match[$i]['diff'] === 0 ) {
                    $extraData['Serial_strictAcc'] = 1;
                    $extraData['Serial_strictVal'] = $value[$i];
                } else {
                    $extraData['Serial_strictAcc'] = 0;
                    $extraData['Serial_strictVal'] = 0;
                }
            }
            else {
                $extraData['Serial_Matched_Word'] = '_';
                $extraData['Serial_Matched_Diff'] = '_';
                $extraData['Serial_Output_Order'] = '_';
                $extraData['Serial_lenientAcc']   = 0;
                $extraData['Serial_lenientVal']   = 0;
                $extraData['Serial_strictAcc']    = 0;
                $extraData['Serial_strictVal']    = 0;
            }
            
            recordTrial($extraData, false, false);
        }
    }

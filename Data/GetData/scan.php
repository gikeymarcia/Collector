<?php
    ini_set('auto_detect_line_endings', true);              // fixes problems reading files saved on mac
    require 'getdataFunctions.php';
    
    if( $_CONFIG->password === '' ) exit( 'GetData has not been enabled. Please enter a password in the Settings file in your experiment folder.' );
    
    $_PATH->loadDefault('Current Data', $_CONFIG->experiment_name . '-Data');
    
    // filter user input before using
    $POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
    
    #### Column Prefixes ####
    $expPrefix              = 'Exp_';
    $finalQuestionsPrefix   = 'FiQ_';
    $demographicsPrefix     = 'Dem_';
    $statusBeginPrefix      = 'Sta_';
    $statusEndPrefix        = 'End_';
    $instructionsPrefix     = 'Ins_';
    ####
    
    
    // scan the TrialType folder, and take the name of any file ending in .php as a trial type
    $trialTypes = array_keys(getAllTrialTypeFiles());
    
    
    // the scopes 'Experiment' and 'Condition' will later cause data in these files to be distributed among all related files in that category
    // e.g., even if participants only provide demographics in their first session, the scope 'Experiment' will let that data be merged to
    // their output in later sessions or different conditions
    $extraFileMeta = array(
         'Demographics'     => array(
             'fileName'         => pathinfo($_PATH->demographics_data, PATHINFO_FILENAME)
            ,'Prefix'           => $demographicsPrefix
            ,'Scope'            => 'Experiment' )
        ,'Final_Questions'  => array(
             'fileName'         => pathinfo($_PATH->final_questions_data, PATHINFO_FILENAME)
            ,'Prefix'           => $finalQuestionsPrefix
            ,'Scope'            => 'Condition' )
        ,'Status_Begin'     => array(
             'fileName'         => pathinfo($_PATH->status_begin_data, PATHINFO_FILENAME)
            ,'Prefix'           => $statusBeginPrefix
            ,'Scope'            => 'ID' )
        ,'Status_End'       => array(
             'fileName'         => pathinfo($_PATH->status_end_data, PATHINFO_FILENAME)
            ,'Prefix'           => $statusEndPrefix
            ,'Scope'            => 'ID' )
        ,'Instructions'     => array(
             'fileName'         => pathinfo($_PATH->instructions_data, PATHINFO_FILENAME)
            ,'Prefix'           => $instructionsPrefix
            ,'Scope'            => 'Condition' )
    );
    
    if( !isset($POST['Password']) AND count( $POST ) > 0 ) {
        foreach( $extraFileMeta as $category => $fileMeta ) {
            if( !isset( $POST[$category] ) OR !isset( $POST[$category.'_Columns'] ) ) {
                unset( $extraFileMeta[$category] );
            }
        }
    }
    
    foreach( $extraFileMeta as $metaName => &$extraMeta ) {
        $extraMeta['Columns'] = array();
    }
    unset( $extraMeta );
    
    
    
    /**
     * Scanning the output folder
     *
     * In this block, we open every file in the output folder, and read the first line of data.
     *
     * Firstly, this lets us find every column header that we can select in the menu.
     * Using this line, we can also find every username, ID, condition, and session that we have data for.
     * 
     * Finally, this also lets us start building a map, in an array structured like this:
     * name -> experimentName -> condition -> session -> id
     *
     * Later, when we need to know what this user has done, we can use foreach loops to tie all their data together.
     *
     * We also place this data in $IDs, so that when we find an ID in the extra files like Demographics,
     * We know how to distribute this data among the proper sessions within the same user/experiment/condition.
     */
    
    $testHeader = 'Username';                                                           // make this a header that you are sure will appear in every output file
    $path = $_PATH->output_dir;
    $users = array();
    $IDs = isset($POST['IDs']) ? array_flip($POST['IDs']) : array();
    $outputColumns = array();
    $allOutputFiles = is_dir( $path ) ? scandir( $path ) : array();
    foreach( $allOutputFiles as $fileName ) {
        $firstRow = getFirstLine( "{$path}/{$fileName}", $testHeader );
        if( $firstRow === false ) { continue; }
        if( isset( $POST['IDs'] ) AND !isset($IDs[$firstRow['ID']]) ) { continue; }
        $outputColumns += $firstRow;                                                    // we only care about the keys, so the contents don't matter
        $name       = $firstRow['Username'];
        $exp        = $firstRow['ExperimentName'];
        $condNum    = $firstRow['Condition Number'];
        $condName   = $firstRow['Condition Description'];
        $session    = $firstRow['Session'];
        $id         = $firstRow['ID'];
        $date       = $firstRow['Date'];
        $IDs[$id] = array(
             'Name' => $name
            ,'Exp'  => $exp
            ,'Cond' => $condNum
            ,'CndN' => $condName
            ,'Sess' => $session
            ,'File' => $fileName
            ,'Date' => $date
        );
        $users[ $name ][ $exp ][ $condNum ][ $session ][ $id ] = $fileName;
    }
    unset( $allOutputFiles );                                                           // might as well free up some memory
    
    // find all files in the folder for extra data that have a name matching one of our extra files
    $path = $_PATH->current_data_dir; 
    $allExtraFiles = is_dir( $path ) ? scandir( $path ) : array();
    $extraFiles = array_splice($allExtraFiles, 2); // remove the '.' and '..'
    foreach( $extraFiles as $fileName ) {
        foreach( $extraFileMeta as $category => $fileMeta ) {
            if( strpos( $fileName, $fileMeta['fileName'] ) !== false ) {
                $extraFileMeta[ $category ][ 'files' ][] = $fileName;
            }
        } 
    }
    unset($allExtraFiles, $extraFiles);
    
    // inside each extra file, match each row (or rows, for final questions) with its ID in $IDs
    // while we are here, get the column options for the menu
    foreach( $extraFileMeta as $category => $fileMeta ) {
        if( !isset( $fileMeta['files'] ) ) { continue; }
        foreach( $fileMeta['files'] as $fileName ) {
            $data = GetFromFile( "{$path}/{$fileName}", false );
            $d = getFirstLine( "{$path}/{$fileName}", $testHeader, true );
            if( $d === false ) { continue; }
            $file = fopen( "{$path}/{$fileName}", "r" );
            $keys = fgetcsv( $file, 0, $d );
            if( $category !== 'Final_Questions' ) { $extraFileMeta[ $category ]['Columns'] += array_flip($keys); }
            while( ($line = fgetcsv($file, 0, $d)) !== false ) {
                $row = array_combine_safely( $keys, $line );
                
                if( !isset( $IDs[$row['ID']] ) ) { continue; }      // if we don't have output from this person, we don't use any of their data
                
                if( $category === 'Final_Questions' ) {
                    if( strtolower(trim($row['Type'])) === 'checkbox' ) {
                        if( $row['Response'] === '' ) { continue; }
                        $IDs[ $row['ID'] ][ $category ][ $row['Question'].'_'.$row['Response'] ] = $row['Response'];
                        $extraFileMeta[$category]['Columns'][$row['Question'].'_'.$row['Response']] = true;
                    } else {
                        $IDs[ $row['ID'] ][ $category ][ $row['Question'] ] = $row['Response'];
                        $extraFileMeta[$category]['Columns'][$row['Question']] = true;
                    }
                } else {
                    $IDs[ $row['ID'] ][ $category ] = $row;
                }
            }
            fclose( $file );
        }
    }
    
    // find our meta-files that have a scope beyond their own session, so we can distribute their data
    $scopes = array( 'Experiment' => array(), 'Condition' => array() );
    foreach( $extraFileMeta as $category => $fileMeta ) {
        if( $fileMeta['Scope'] !== 'Experiment' AND $fileMeta['Scope'] !== 'Condition' ) { continue; }      // these are the only two scopes that we do anything with
        $scopes[ $fileMeta['Scope'] ][] = $category;
    }
    
    /**
     * Chaining IDs
     *
     * This next part distributes the data from each ID in $ID to all related IDs for that username
     * Which data is spread is controlled by the 'Scope' of each type of meta-file, in $extraFileMeta
     * 
     * For something like 'Demographics', with the scope 'Experiment',
     * every output file in the array of $users[ *user* ][ *exp* ] will be given this info.
     *
     * With the scope 'Condition', the data will only be spread within that condition
     *
     * Other scopes, like 'ID',  are ignored, since they are contained to only their original ID
     */
    foreach( $IDs as $i => $id ) {
        foreach( $scopes as $scope => $categories ) {
            foreach( $categories as $category ) {
                if( !isset( $id[$category] ) ) { continue; }
                $distrib = array();
                if( $scope === 'Condition' ) {
                    $distrib[] = $users[ $id['Name'] ][ $id['Exp'] ];
                } elseif( $scope === 'Experiment' ) {
                    foreach( $users[ $id['Name'] ] as $conditions ) {
                        $distrib[] = $conditions;
                    }
                }
                foreach( $distrib as $dist ) {
                    foreach( $dist as $sessions ) {
                        foreach( $sessions as $targIds ) {
                            foreach( $targIds as $tid => $fileName ) {
                                if( $tid === $i ) { continue; }
                                if( !isset( $IDs[$tid][$category] ) ) {
                                    $IDs[$tid][$category] = $id[$category];
                                } elseif( getArrayDims( $IDs[$tid][$category] ) !== 2 ) {
                                    $IDs[$tid][$category] = array( $IDs[$tid][$category], $id[$category] );
                                } else {
                                    $IDs[$tid][$category][] = $id[$category];
                                }
                            }
                        }
                    }
                }
            }
        }
    }
?>

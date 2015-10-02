<?php
    // System Map
    // To use this file, map out the directory structure of the program,
    // using keys as directory or file names, and values as either the
    // contents of a directory, or the label for that directory or file
    //
    // Inside the map, certain keys can be a variable
    // using $dirName as the key, you can give a label to the directory,
    //
    // using $wildCard, you can provide a point for the user to provide
    // their own information (such as the specific trial type "instruct")
    //
    // using $default . 'Some String', you can create a different type of
    // wild card, which should only need to be set once for the experiment
    // good uses for this include the default procedure file or the 
    // default output file
    //
    // more information can be found inside the Pathfinder class
    
    
    $systemMap = array (
        $dirName    => 'root',
        
        'index.php' => 'index',
        
        'Experiment' => array (
            $dirName => 'Experiment',
            
            'Conditions.csv' => 'Conditions',
            'Config.ini' => 'Config',
            'FinalQuestions.csv' => 'Final Questions',
            'Task Instructions.php' => 'Instructions',
            
            'Stimuli' => array (
                $dirName => 'Stimuli Dir',
                
                $default . 
                'Stimuli' => 'Stimuli',
            ),
            
            'Procedure' => array (
                $dirName => 'Procedure Dir',
                
                $default . 
                'Procedure' => 'Procedure',
            ),
            
            'TrialTypes' => array (
                $dirName => 'Custom Trial Types',
                
                $wildCard => array (
                    $dirName => 'Custom Trial Type Dir',
                    
                    'display.php' => 'Custom Trial Display',
                    'helper.inc' => 'Custom Trial Helper',
                    'scoring.php' => 'Custom Trial Scoring',
                    'script.js' => 'Custom Trial Script',
                    'style.css' => 'Custom Trial Style',
                ),
            ),
            
            'Ineligible' => array (
                $dirName => 'Ineligibility Dir',
            ),
            
            'Images' => array (
                $dirName => 'Images',
            ),
            
            'Audio' => array (
                $dirName => 'Audio',
            ),
        ),
        
        'Code'      => array (
            $dirName    => 'Code',
            
            'BasicInfo.php' => 'Basic Info',
            'BasicInfoData.php' => 'Basic Info Record',
            
            'check.php' => 'Check',
            
            'customFunctions.php' => 'Custom Functions',
            
            'defaultTrialHelper.php' => 'default helper',
            'defaultTrialScoring.php' => 'default scoring',
            
            'Done.php' => 'Done',
            
            'errorCheck.php' => 'Error Check',
            
            'Experiment.php' => 'Experiment Page',
            
            'FinalQuestions.php' => 'Final Questions Page',
            
            'footer.php' => 'Footer',
            
            'fqData.php' => 'Final Questions Record',
            
            'Header.php' => 'Header',
            
            'icon.png' => 'Icon',
            
            'initiateCollector' => 'Initiate Collector',
            
            'instructions.php' => 'Instructions Page',
            
            'instructionsRecord.php' => 'Instructions Record',
            
            'login.php' => 'Login',
            
            'nojs.php' => 'No JS',
            
            'parse.class.php' => 'Parse',
            
            'pathfinder.class.php' => 'Pathfinder',
            
            'shuffleFunctions.php' => 'Shuffle Functions',
            
            'systemMap.php' => 'system map',
            
            'trialLoader.php' => 'Trial Tester Loader',
            'trialTester.php' => 'Trial Tester Menu',
            
            'css' => array (
                $dirName => 'CSS Dir',
                
                'global.css' => 'Global CSS',
                'jquery-ui.min.css' => 'Jquery UI CSS',
                'jquery-ui-1.10.4.custom.min.css' => 'Jquery UI Custom CSS',
                
                'nojswarning.png' => 'No JS Warning',
                
                'tutorial.css' => 'Tutorial CSS',
                
                'fonts' => array (
                    $dirName => 'Fonts',
                    
                    // wow there is a lot in here. . .
                ),
                
                'images' => array (
                    $dirName => 'CSS Images',
                    
                    // lots of stuff in here
                ),
            ),
            
            'javascript' => array (
                $dirName => 'JS Dir',
                
                'collector_1.0.0.js' => 'Collector JS',
                
                'jquery.js' => 'Jquery',
                'jquery-ui-1.10.4.custom.min.js' => 'Jquery UI Custom',
                'jquery-ui-1.11.4.min.js' => 'Jquery UI',
                
                'loggingIn.js' => 'Login JS',
                'sha256.js' => 'Sha256 JS',
            ),
            
            'phpbrowscap' => array (
                'Browscap.php' => 'Browscap',
                // more stuff in here
            ),
            
            'TrialTypes' => array (
                $dirName => 'Trial Types',
                
                $wildCard => array (
                    $dirName => 'Trial Type Dir',
                    
                    'display.php' => 'Trial Display',
                    'helper.php' => 'Trial Helper',
                    'scoring.php' => 'Trial Scoring',
                    'script.js' => 'Trial Script',
                    'style.css' => 'Trial Style',
                ),
            ),
        ),
        
        'Data' => array (
            $dirName => 'Data',
            
            $default . 
            'Current Data' => array (
                $dirName => 'Current Data Dir',
                
                'DemographicsData.csv' => 'Demographics Data',
                'FinalQuestionsData.csv' => 'Final Questions Data',
                'InstructionsData.csv' => 'Instructions Data',
                'Status_Begin.csv' => 'Status Begin Data',
                'Status_End.csv' => 'Status End Data',
                
                'Counter' => array (
                    $dirName => 'Counter Dir',
                    
                    $wildCard => 'Counter',
                ),
                
                'JSON_session' => array (
                    $dirName => 'JSON Dir',
                    
                    $default .
                    'json' => 'json',
                ),
                
                'Output' => array (
                    $dirName => 'Output Dir',
                    
                    $default .
                    'Output' => 'Experiment Output',
                ),
            ),
        ),
        
        'Tools' => array (
            $dirName => 'Tools',
            
            // lots of stuff in here
        ),
    );

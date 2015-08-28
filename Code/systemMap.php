<?php
    // System Map
    // To use this file, map out the directory structure of the program,
    // using keys as directory or file names, and values as either the
    // contents of a directory, or the label for that directory or file
    //
    // Inside the map, certain keys can be a variable
    // using 'Dir Name' as the key, you can give a label to the directory,
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
        'Dir Name'    => 'root',
        
        'index.php' => 'index',
        
        'Experiments' => array (
            'Dir Name' => 'Experiments',
            
            'Common' => array (
                'Dir Name' => 'Common',
                
                'Common Config.ini' => 'Common Config',
            
                'Ineligible' => array (
                    'Dir Name' => 'Ineligibility Dir',
                ),
                
                'Images' => array (
                    'Dir Name' => 'Images',
                ),
                
                'Audio' => array (
                    'Dir Name' => 'Audio',
                ),
                
                'TrialTypes' => array (
                    'Dir Name' => 'Custom Trial Types',
                    
                    '{var}' => array (
                        'Dir Name' => 'Custom Trial Type Dir',
                        
                        'display.php' => 'Custom Trial Display',
                        'helper.inc' => 'Custom Trial Helper',
                        'scoring.php' => 'Custom Trial Scoring',
                        'script.js' => 'Custom Trial Script',
                        'style.css' => 'Custom Trial Style',
                    ),
                ),
            ),
            
            '{Current Experiment}' => array (
                'Dir Name' => 'Current Experiment',
                
                'Conditions.csv' => 'Conditions',
                'Config.ini' => 'Config',
                'FinalQuestions.csv' => 'Final Questions',
                'Instructions.php' => 'Instructions',
                
                'Config.ini' => 'Experiment Config',
                
                'Stimuli' => array (
                    'Dir Name' => 'Stimuli Dir',
                    
                    '{Stimuli}' => 'Stimuli',
                ),
                
                'Procedure' => array (
                    'Dir Name' => 'Procedure Dir',
                    
                    '{Procedure}' => 'Procedure',
                ),
            ),
        ),
        
        'Code'      => array (
            'Dir Name'    => 'Code',
            
            'Welcome.php' => 'Welcome',
            
            'BasicInfo.php' => 'Basic Info',
            'BasicInfoData.php' => 'Basic Info Record',
            
            'check.php' => 'Check',
            
            'customFunctions.php' => 'Custom Functions',
            
            'defaultTrialHelper.inc' => 'default helper',
            'defaultTrialScoring.php' => 'default scoring',
            
            'done.php' => 'Done',
            
            'errorCheck.php' => 'Error Check',
            
            'experiment.php' => 'Experiment Page',
            
            'FinalQuestions.php' => 'Final Questions Page',
            
            'Footer.php' => 'Footer',
            
            'FQdata.php' => 'Final Questions Record',
            
            'Header.php' => 'Header',
            
            'icon.png' => 'Icon',
            
            'initiateCollector' => 'Initiate Collector',
            
            'instructions.php' => 'Instructions Page',
            
            'instructionsRecord.php' => 'Instructions Record',
            
            'login.php' => 'Login',
            
            'nojs.php' => 'No JS',
            
            'Parse.php' => 'Parse',
            
            'Pathfinder.class.php' => 'Pathfinder',
            
            'shuffleFunctions.php' => 'Shuffle Functions',
            
            'systemMap.php' => 'system map',
            
            'trialLoader.php' => 'Trial Tester Loader',
            'trialTester.php' => 'Trial Tester Menu',
            
            'css' => array (
                'Dir Name' => 'CSS Dir',
                
                'global.css' => 'Global CSS',
                'jquery-ui.min.css' => 'Jquery UI CSS',
                'jquery-ui-1.10.4.custom.min.css' => 'Jquery UI Custom CSS',
                
                'nojswarning.png' => 'No JS Warning',
                
                'tutorial.css' => 'Tutorial CSS',
                
                'fonts' => array (
                    'Dir Name' => 'Fonts',
                    
                    // wow there is a lot in here. . .
                ),
                
                'images' => array (
                    'Dir Name' => 'CSS Images',
                    
                    // lots of stuff in here
                ),
            ),
            
            'javascript' => array (
                'Dir Name' => 'JS Dir',
                
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
                'Dir Name' => 'Trial Types',
                
                '{var}' => array (
                    'Dir Name' => 'Trial Type Dir',
                    
                    'display.php' => 'Trial Display',
                    'helper.inc' => 'Trial Helper',
                    'scoring.php' => 'Trial Scoring',
                    'script.js' => 'Trial Script',
                    'style.css' => 'Trial Style',
                ),
            ),
        ),
        
        'Data' => array (
            'Dir Name' => 'Data',
            
            '{Current Experiment}-Data{Data Sub Dir}' => array (
                'Dir Name' => 'Current Data Dir',
                
                'DemographicsData.csv' => 'Demographics Data',
                'FinalQuestionsData.csv' => 'Final Questions Data',
                'InstructionsData.csv' => 'Instructions Data',
                'Status_Begin.csv' => 'Status Begin Data',
                'Status_End.csv' => 'Status End Data',
                
                'Counter' => array (
                    'Dir Name' => 'Counter Dir',
                    
                    '{var}.txt' => 'Counter',
                ),
                
                'JSON_session' => array (
                    'Dir Name' => 'JSON Dir',
                    
                    '{Username}.json' => 'json',
                ),
                
                'Output' => array (
                    'Dir Name' => 'Output Dir',
                    
                    '{Output}' => 'Experiment Output',
                ),
            ),
        ),
        
        'Tools' => array (
            'Dir Name' => 'Tools',
            
            // lots of stuff in here
        ),
    );

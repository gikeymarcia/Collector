<?php
return array(
'root'                       => array('Dir'  , '.'),
'index'                      => array('File' , 'index.php'),

'Experiments'                => array('Dir'  , 'Experiments/'),
  'Common'                   => array('Dir'  , 'Experiments/Common/'),
  'Common Settings'          => array('File' , 'Experiments/Common/Common Settings.json'),
  'Password'                 => array('File' , 'Experiments/Common/Password.php'),
  'Ineligibility Dir'        => array('Dir'  , 'Experiments/Common/Ineligible/')
  'Media'                    => array('Dir'  , 'Experiments/Common/Media/')
  'Custom Trial Types'       => array('Dir'  , 'Experiments/Common/TrialTypes/')
  'Custom Trial Type Dir'    => array('Dir'  , 'Experiments/Common/TrialTypes/[var]/')
    'Custom Trial Display'   => array('File' , 'Experiments/Common/TrialTypes/[var]/display.php')
    'Custom Trial Helper'    => array('File' , 'Experiments/Common/TrialTypes/[var]/helper.inc')
    'Custom Trial Scoring'   => array('File' , 'Experiments/Common/TrialTypes/[var]/scoring.php')
    'Custom Trial Script'    => array('File' , 'Experiments/Common/TrialTypes/[var]/script.js')
    'Custom Trial Style'     => array('File' , 'Experiments/Common/TrialTypes/[var]/style.css')
    'Custom Trial Validator' => array('File' , 'Experiments/Common/TrialTypes/[var]/validator.php')

  'Current Experiment'       => array('Dir'  , 'Experiments/[Current Experiment]/')
  'Current Index'            => array('File' , 'Experiments/[Current Experiment]/index.php')
  'Current Return'           => array('File' , 'Experiments/[Current Experiment]/Return.php')
  'Conditions'               => array('CSV'  , 'Experiments/[Current Experiment]/Conditions.csv')
  'Experiment Settings'      => array('File' , 'Experiments/[Current Experiment]/Settings.json')
  'Stimuli Dir'              => array('Dir'  , 'Experiments/[Current Experiment]/Stimuli/')
    'Stimuli'                => array('CSV'  , 'Experiments/[Current Experiment]/Stimuli/[Stimuli]')
  'Procedure Dir'            => array('Dir'  , 'Experiments/[Current Experiment]/Procedure/')
    'Procedure'              => array('CSV'  , 'Experiments/[Current Experiment]/Procedure/[Procedure]')

'Code'                       => array('Dir'  , 'Code/'),
  'Welcome'                  => array('File' , 'Code/Welcome.php'),
  'Welcome Back'             => array('File' , 'Code/WelcomeBack.php'),
  'Check'                    => array('File' , 'Code/check.php'),
  'Custom Functions'         => array('File' , 'Code/customFunctions.php'),
  'Done'                     => array('File' , 'Code/Done.php'),
  'Error Check'              => array('File' , 'Code/errorCheck.php'),
  'Experiment Page'          => array('File' , 'Code/Experiment.php'),
  'Experiment Require'       => array('File' , 'Code/experiment.require.php'),
  'Footer'                   => array('File' , 'Code/footer.php'),
  'Header'                   => array('File' , 'Code/Header.php'),
  'Icon'                     => array('File' , 'Code/icon.png'),
  'Initiate Collector'       => array('File' , 'Code/initiateCollector.php'),
  'Trial Validator Require'  => array('File' , 'Code/trialValidator.require.php'),
  'No JS'                    => array('File' , 'Code/nojs.php'),
  'Set Password'             => array('File' , 'Code/setPassword.php'),
  'Shuffle Functions'        => array('File' , 'Code/shuffleFunctions.php'),
  'Custom Functions'         => array('File' , 'Code/customFunctions.php'),

  'CSS Dir'                  => array('Dir'  , 'Code/css/'),
    'Global CSS'             => array('File' , 'Code/css/global.css'),
    'Jquery UI CSS'          => array('File' , 'Code/css/jquery-ui.min.css'),
    'Jquery UI Custom CSS'   => array('File' , 'Code/css/jquery-ui-1.10.4.custom.min.css'),
    'No JS Warning'          => array('File' , 'Code/css/nojswarning.png'),
    'Tools CSS'              => array('File' , 'Code/css/tools.css'),
    'Tutorial CSS'           => array('File' , 'Code/css/tutorial.css'),
    'Fonts'                  => array('Dir'  , 'Code/css/fonts/'),
    'CSS Images'             => array('Dir'  , 'Code/css/images/'),

  'Classes'                  => array('Dir'  , 'Code/classes/'),
    'Conditions Class'       => array('File' , 'Code/classes/ConditionController.php'),
    'Control Files Class'    => array('File' , 'Code/classes/ControlFile.php'),
    'Debug Class'            => array('File' , 'Code/classes/DebugController.php'),
    'Errors Class'           => array('File' , 'Code/classes/ErrorController.php'),
    'Pathfinder'             => array('File' , 'Code/classes/Pathfinder.php'),
    'Procedure Class'        => array('File' , 'Code/classes/Procedure.php'),
    'Return Class'           => array('File' , 'Code/classes/ReturnVisitController.php'),
    'SideData Class'         => array('File' , 'Code/classes/SideData.php'),
    'Status Class'           => array('File' , 'Code/classes/StatusController.php'),
    'Stimuli Class'          => array('File' , 'Code/classes/Stimuli.php'),
    'system map'             => array('File' , 'Code/classes/systemMap.php'),
    'Trial Validator Class'  => array('File' , 'Code/classes/systemMap.php'),
    'Users Class'            => array('File' , 'Code/classes/User.php'),

  'JS Dir'                   => array('Dir'  , 'Code/javascript/'),
    'Collector JS'           => array('File' , 'Code/javascript/Collector.js'),
    'Jquery'                 => array('File' , 'Code/javascript/jquery.js'),
    'Jquery UI Custom'       => array('File' , 'Code/javascript/jquery-ui-1.10.4.custom.min.js'),
    'Jquery UI'              => array('File' , 'Code/javascript/jquery-1.12.4.min.js'),
    'Login JS'               => array('File' , 'Code/javascript/loggingIn.js'),
    'Sha256 JS'              => array('File' , 'Code/javascript/sha256.js'),

'Data'                       => array('Dir'  , 'Data/'),
  'Current Data Dir'         => array('Dir'  , 'Data/[Current Experiment]-Data[Data Sub Dir]/'),
    'Instructions Data'      => array('File' , 'Data/[Current Experiment]-Data[Data Sub Dir]/InstructionsData.csv'),
    'Status Begin Data'      => array('CSV'  , 'Data/[Current Experiment]-Data[Data Sub Dir]/Status_Begin.csv'),
    'Status End Data'        => array('CSV'  , 'Data/[Current Experiment]-Data[Data Sub Dir]/Status_End.csv'),
    'SideData Data'          => array('CSV'  , 'Data/[Current Experiment]-Data[Data Sub Dir]/SideData.csv'),
    'Counter Dir'            => array('Dir'  , 'Data/[Current Experiment]-Data[Data Sub Dir]/Counter/'),
      'Counter'              => array('File' , 'Data/[Current Experiment]-Data[Data Sub Dir]/Counter/Counter.csv'),
    'Session Storage Dir'    => array('Dir'  , 'Data/[Current Experiment]-Data[Data Sub Dir]/session/'),
      'Session Storage'      => array('Sess' , 'Data/[Current Experiment]-Data[Data Sub Dir]/session/[Username].txt'),
    'Output Dir'             => array('Dir'  , 'Data/[Current Experiment]-Data[Data Sub Dir]/Output/'),
      'Experiment Output'    => array('CSV'  , 'Data/[Current Experiment]-Data[Data Sub Dir]/Output/[Output]'),
        'Trial Validations Dir'     => array('Dir', 'Data/[Current Experiment]-Data[Data Sub Dir]/validations/'),
        'Trial Validation Scan Time'=> array('List', 'Data/[Current Experiment]-Data[Data Sub Dir]/validations/'),

'Admin'                      => array('Dir'  , 'Admin/'),
  'Admin Index'              => array('File' , 'Admin/index.php'),
  'Tools'                    => array('Dir'  , 'Admin/Tools/'),

'Trial Types'                => array('Dir'  , 'TrialTypes/'),
  'Trial Type Dir'           => array('Dir'  , 'TrialTypes/[var]/'),
    'Trial Display'          => array('File' , 'TrialTypes/[var]/display.php'),
    'Trial Helper'           => array('File' , 'TrialTypes/[var]/helper.php'),
    'Trial Scoring'          => array('File' , 'TrialTypes/[var]/scoring.php'),
    'Trial Script'           => array('File' , 'TrialTypes/[var]/script.js'),
    'Trial Style'            => array('File' , 'TrialTypes/[var]/style.css'),
    'Trial Validator'        => array('File' , 'TrialTypes/[var]/validator.php'),
  'default helper'           => array('File' , 'TrialTypes/defaultTrialHelper.php'),
  'default scoring'          => array('File' , 'TrialTypes/defaultTrialScoring.php'),
);

<?php
/**
 * Experiment Settings Class
 */
class Config
{
    /* General Settings */
    
    // The name of the experiment
    public $experimentName = 'Collector';
    
    // Change to restart condition cycling
    public $loginCounterName = '1.txt';
    
    // Toggle Demographics and Instructions pages on and off
    public $doDemographics = false;
    public $doInstructions = true;
    
    // Link to another experiment; use format 'www.cogfog.com/Generic/' 
    // note: the www and the trailing '/' must be present
    public $nextExperiment = false;
    
    // Contact Email
    public $experimenterEmail = 'youremail@yourdomain.com';
    
    // Access control: to enable getdata OR Tools enter a string other than ''
    public $password = '';
    
    // scoring settings
    // determines the % match required to count an answer as 1(correct) or 0(incorrect)
    public $lenientCriteria = 75;
    
    
    
    
    
    /* Debugging Settings */
    
    // Toggle checking that all cues in the stimuli files exist 
    // (all files or only current session's file)
    public $checkAllFiles = true;
    public $checkCurrentFiles = false;
    
    // Create a password here to enable the use of the debug name at login
    public $debugName = '';
    
    // Toggle debugMode (experiment will run in debug for all users)
    public $debugMode = false;
    
    // Trial length (in seconds) when in debug mode
    // (set to '' to use procedure timings)
    public $debugTime = 1;                  
    
    // Toggle display of trial diagnostics
    public $trialDiagnostics = false;
    
    // Toggle disply of diagnostic information immediately after login
    public $stopAtLogin = false;
    
    // Toggle stopping experiment progression if errors are found at login
    public $stopForErrors = true;
    
    
    
    
    
    /* mTurk Settings */
    
    // Toggle mTurk mode on/off (displays verification, checks eligibility)
    public $mTurkMode = false;
    
    // Verification code displayed on done.php
    public $verification = 'Shinebox';
    
    // Toggle using files in eligibility/ folder to check past participation
    public $checkElig = false;
    
    // Toggle prevention of the same IP from participating more than once
    public $blacklist = false;
    
    // The IPs in this array will be allowed to participate more than once
    // ::1 is the default IPv6 loopback -- leave it in so that the check will pass when working locally
    public $whitelist = array('::1', 'other-ip');
    
    
    
    
    
    /* index.php (Starting Page) Settings */
    
    public $welcome = 'Welcome to the experiment!';
    public $expDescription = '<p> This experiment will run for about 25 minutes.  Your goal will be to learn some information.</p>';
    
    // Change to edit this phrase: "Please enter your [Participant ID]"
    public $askForLogin = 'Participant ID';
    
    // Toggle display of the condition selector
    public $showConditionSelector = true;
    
    // Toggle which Conditions column is used for condition selector text
    // true: "Column Description", false: "Number"
    public $useConditionNames = true;
    
    // Show the stimuli and procedure when hovering over the condition options
    public $showConditionInfo = true;
    
    // Flag conditions by putting a # character at the beginning of the "Condition Description"
    // When the conditions are auto-selected, flagged conditions will be skipped
    public $hideFlaggedConditions = true;
}

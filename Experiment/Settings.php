<?php
/**
 * Experiment Settings Class
 */
class Settings
{
    /* General Settings */
    
    // The name of the experiment
    public static $experimentName = 'Collector';
    
    // Change to restart condition cycling
    public static $loginCounterName = '1.txt';
    
    // Toggle Demographics and Instructions pages on and off
    public static $doDemographics = false;
    public static $doInstructions = true;
    
    // Link to another experiment; use format 'www.cogfog.com/Generic/' 
    // note: the www and the trailing '/' must be present
    public static $nextExperiment = false;
    
    // Contact Email
    public static $experimenterEmail = 'youremail@yourdomain.com';
    
    // Access control: to enable getdata OR Tools enter a string other than ''
    public static $password = '';
    
    // scoring settings
    // determines the % match required to count an answer as 1(correct) or 0(incorrect)
    public static $lenientCriteria = 75;
    
    
    
    
    
    /* Debugging Settings */
    
    // Toggle checking that all cues in the stimuli files exist 
    // (all files or only current session's file)
    public static $checkAllFiles = true;
    public static $checkCurrentFiles = false;
    
    // Create a password here to enable the use of the debug name at login
    public static $debugName = '';
    
    // Toggle debugMode (experiment will run in debug for all users)
    public static $debugMode = false;
    
    // Trial length (in seconds) when in debug mode
    // (set to '' to use procedure timings)
    public static $debugTime = 1;                  
    
    // Toggle display of trial diagnostics
    public static $trialDiagnostics = false;
    
    // Toggle disply of diagnostic information immediately after login
    public static $stopAtLogin = false;
    
    // Toggle stopping experiment progression if errors are found at login
    public static $stopForErrors = true;
    
    
    
    
    
    /* mTurk Settings */
    
    // Toggle mTurk mode on/off (displays verification, checks eligibility)
    public static $mTurkMode = false;
    
    // Verification code displayed on done.php
    public static $verification = 'Shinebox';
    
    // Toggle using files in eligibility/ folder to check past participation
    public static $checkElig = false;
    
    // Toggle prevention of the same IP from participating more than once
    public static $blacklist = false;
    
    // The IPs in this array will be allowed to participate more than once
    // ::1 is the default IPv6 loopback -- leave it in so that the check will pass when working locally
    public static $whitelist = array('::1', 'other-ip');
    
    
    
    
    
    /* index.php (Starting Page) Settings */
    
    public static $welcome = 'Welcome to the experiment!';
    public static $expDescription = '<p> This experiment will run for about 25 minutes.  Your goal will be to learn some information.</p>';
    
    // Change to edit this phrase: "Please enter your [Participant ID]"
    public static $askForLogin = 'Participant ID';
    
    // Toggle display of the condition selector
    public static $showConditionSelector = true;
    
    // Toggle which Conditions column is used for condition selector text
    // true: "Column Description", false: "Number"
    public static $useConditionNames = true;
    
    // Show the stimuli and procedure when hovering over the condition options
    public static $showConditionInfo = true;
    
    // Flag conditions by putting a # character at the beginning of the "Condition Description"
    // When the conditions are auto-selected, flagged conditions will be skipped
    public static $hideFlaggedConditions = true;
}

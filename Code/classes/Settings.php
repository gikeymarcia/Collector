<?php
/**
 * Controls the getting, setting, and writing of settings.
 */
class Settings
{
    protected $defaultPass = 'weakpassword';
    
    protected $defaultCommon = array(
        'force_experiment' => '',
        'experimenter_email' => 'youremail@yourdomain.com',
        'check_all_files' => true,
        'check_current_files' => true,
        'debug_name' => '',
        'debug_time' => 1,
        'trial_diagnostics' => false,
        'stop_at_login' => false,
        'stop_for_errors' => true,
    );
    
    protected $defaultExp = array(
        'experiment_name' => 'Collector',
        'debug_mode' => false,
        'lenient_criteria' => 75,
        'welcome' => 'Welcome to the experiment!',
        'exp_description' => '<p>This experiment will run for about 25 minutes. Your goal will be to learn some information.</p>',
        'ask_for_login' => 'Participant ID',
        'show_condition_selector' => true,
        'use_condition_names' => true,
        'show_condition_info' => false,
        'hide_flagged_conditions' => true,
        'verification' => '',
        'check_elig' => false,
        'blacklist' => false,
        'whitelist' => array(
            '::1', 'localhost',
        ),
    );

    protected $common = array(
        'mod' => 0,
        'loc' => null,
        'data' => array(),
    );
    
    protected $experiment = array(
        'mod' => 0,
        'loc' => null,
        'data' => array(),
    );
    
    protected $temp = array();
    
    protected $passLoc;
    
    private $jsonOptions;

    public function __construct($commonLoc, $expLoc, $passLoc)
    {
        $this->checkVersion();
        $this->common = $this->load($commonLoc);
        $this->experiment = $this->load($expLoc);
        $this->passLoc = $passLoc;
    }
    
    public function __get($var)
    {
        $var = trim(strtolower($var));
        if ($var == 'password') {
            $pass = $this->getPassword($this->passLoc);

            return $pass;
        }
        // return values depending on their priority
        // If no value is found then trigger error and return null
        if (isset($this->temp[$var])) {                     // temporary set variables
            return $this->temp[$var];
        } elseif (isset($this->experiment['data'][$var])) { // Experiment Settings
            return $this->experiment['data'][$var];
        } elseif (isset($this->common['data'][$var])) {     // Common settings
            return $this->common['data'][$var];
        } elseif (isset($this->defaultExp[$var])) {         // default experiment settings
            return $this->defaultExp[$var];
        } elseif (isset($this->defaultCommon[$var])) {      // default common settings
            return $this->defaultCommon[$var];
        } else {
            $msg = "Error: You have attempted to use the setting value of $var when that value is not specified";
            trigger_error($msg, E_WARNING);

            return;
        }
    }
    
    public function __set($var, $val)
    {
        $this->temp[strtolower($var)][$val];
    }
    
    private function load($file_path)
    {
        $out = array(
            'mod' => 0,
            'loc' => $file_path,
            'data' => array(),
        );
        if (file_exists($file_path)) {
            $json = json_decode(file_get_contents($file_path), true);
            $out['data'] = $json;
            $out['mod'] = date('U', filemtime($file_path));
        }

        return $out;
    }
    
    public function setPassword($input = null)
    {
        if ($input == null) {
            $input = $this->defaultPass;
        }
        $php_string = "<?php return '$input'; ?>";
        file_put_contents($this->passLoc, $php_string);
    }
    
    protected function getPassword($location)
    {
        if (file_exists($location)) {
            $password = (require $location);
            if ($password == $this->defaultPass) {
                return;
            } else {
                return $password;
            }
        } else {
            return;
        }
    }
    
    public function up_to_date(Pathfinder $paths)
    {
        $commonLoc = $paths->get('Common Settings');
        $commonMod = (file_exists($commonLoc)) ? date('U', filemtime($commonLoc)) : 0;

        $expLoc = $paths->get('Experiment Settings');
        $expMod = (file_exists($expLoc)) ? date('U', filemtime($expLoc)) : 0;

        if ($this->common['loc'] != $commonLoc
            or $this->common['mod'] < $commonMod
        ) {
            $this->common = $this->load($commonLoc);
        }
        if ($this->experiment['loc'] != $expLoc
            or $this->experiment['mod'] < $expMod
        ) {
            $this->experiment = $this->load($expLoc);
        }
        $this->temp = array();          // clear out temporary settings
    }
    
    protected function checkVersion()
    {
        if (version_compare(PHP_VERSION, '5.4.0') >= 0) {
            $this->jsonOptions = JSON_PRETTY_PRINT;
        }
    }
    
    public function set($var, $val)
    {
        $key = trim(strtolower($key));
        $location = (isset($this->defaultCommon[$key])) ? 'common' : 'experiment';
        switch ($location) {
            case 'common':
                $source = &$this->common;
                break;
            default:
                $source = &$this->experiment;
                break;
        }
        $current = $this->$key;
        $type = gettype($current);
        if (is_bool($current)) {                // only allow bools to replace bools
            if (is_bool($val)) {
                $source['data'][$key] = $val;
            } else {
                $msg = "Your setting, $key, should be a boolean but it is a $type.";
                trigger_error($msg, E_WARNING);
            }
        } elseif (is_numeric($current)) {       // only numbers to replace numbers
            if (is_numeric($val)) {
                $source['data'][$key] = $val;
            } else {
                $msg = "Your setting, $key, should be a number but it is a $type.";
                trigger_error($msg, E_WARNING);
            }
        } else {
            $source['data'][$key] = $val;       // allow all other types to be juggled
        }
    }
    
    public function writeSettings()
    {
        #### write common settings
        $common = array();
        foreach ($this->defaultCommon as $key => $value) {
            $common[$key] = $value;
        }
        foreach ($this->common['data'] as $key => $value) {
            $common[$key] = $value;
        }
        $json = json_encode($common, $this->jsonOptions);
        file_put_contents($this->common['loc'], $json);

        #### write experiment settings
        if (strpos($this->experiment['loc'], '{') === false) {      // if we actually have a path to write to
            $experiment = array();
            foreach ($this->defaultExp as $key => $value) {
                $experiment[$key] = $value;
            }
            foreach ($this->experiment['data'] as $key => $value) {
                $experiment[$key] = $value;
            }
            $json = json_encode($experiment, $this->jsonOptions);
            file_put_contents($this->experiment['loc'], $json);
        }
    }
}

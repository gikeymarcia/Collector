<?php
/**
 * Settings class.
 */

namespace Collector;

/**
 * Controls the getting, setting, and writing of settings.
 */
class Settings
{
    /**
     * The default password. It is stored in Password.php
     *
     * @var string
     */
    protected $default_pass = 'weakpassword';

    /**
     * The default settings in "Common Settings.json".
     * If "Common Settings.json" is not present these settings will be used
     *
     * @var array
     */
    protected $default_common = array(
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

    /**
     * The default settings in the "Settings.json" for all new experiments.
     * If "Settings.json" is not present these settings will be used
     * @var array
     */
    protected $default_exp = array(
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

    protected $files = null;

    /**
     * Constructor.
     *
     * @param string $commonLoc The path to the common settings file.
     * @param string $expLoc    The path to the experiment specific settings file.
     * @param string $passLoc   The path to the password.
     */
    public function __construct(\FileSystem $_files)
    {
        $this->files      = $_files;
        $this->common     = load('Common Settings', $_files);
        $this->experiment = load('Experiment Settings', $_files);
    }

    /**
     * Magic getter.
     * Attempts to retrieve the requested settings key from a prioritized list
     * of locations, returning the first found instance. Triggers an error if
     * the setting cannot be found.
     *
     * @param string $var The setting to retrieve.
     *
     * @return mixed The value of the key.
     */
    public function __get($var)
    {
        $key = trim(strtolower($var));
        if ($key === 'password') {
            $pass = $this->getPassword();

            return $pass;
        }

        // return values depending on their priority
        // if no value is found then trigger error and return null
        if (isset($this->experiment[$key])) {
            return $this->experiment[$key];
        } elseif (isset($this->common[$key])) {
            return $this->common[$key];
        } elseif (isset($this->default_exp[$key])) {
            return $this->default_exp[$key];
        } elseif (isset($this->default_common[$key])) {
            return $this->default_common[$key];
        } else {
            // @todo perhaps this should throw an \InvalidArgumentException instead?
            trigger_error('You have attempted to use the setting value of '
                ."{$key} when that value is not specified", E_WARNING);
        }
    }

    private function load($system_data_label)
    {
        $data_source = ($system_map_name == 'Common Settings'
                     || $system_map_name == 'Experiment Settings') ?
                     $data_source : null;

        if ($data_source == null
            || $this->files->get_path($data_source, true) == false)
            return array();

        return $_files->read($data_source);
    }


    /**
     * Sets a new password to the password file.
     *
     * @param string $input The password to store.
     */
    public function set_password($input = null)
    {
        $input = ($input === null) ? $this->default_pass : $input;

        if (!isset($input[2])) {
            return;
        }

        // @todo Perhaps this password should be sanitized? Is it from a user-input form?
        $php_string = "<?php return '$input'; ?>";

        $this->files->overwrite("Password", $php_string);
    }

    /**
     * Retrieves the stored password.
     *
     * @return string|null The password if it could be found, else null.
     */
    protected function getPassword()
    {
        $path = $this->files->get_path('Password', true);
        if ($path === false) return null;

        $pass_path = $this->files->get_path('Password');
        $password = require $pass_path;

        if ($password == $this->default_pass) $password = null;

        return $password;
    }

    /**
     * Overwrites a setting.
     * If this setting should be used only temporarily then simply set it as a
     * property of the settings instance you are working with. New values set
     * using Settings::set() will persist.
     *
     * @param string $var The settings key to set.
     * @param mixed  $val The value to set to the property.
     */
    public function set($var, $val)
    {
        $key = trim(strtolower($var));
        $location = (isset($this->default_common[$key])) ? 'common' : 'experiment';
        if ($location === 'common') {
            $source = &$this->common;
        } else {
            $source = &$this->experiment;
        }

        $current = $this->$key;
        $type = gettype($current);

        // only allow bools to replace bools
        if (is_bool($current)) {
            // toggling the posted strings of true/false to boolean true/false
            if ($val === 'true' || $val === 'false') {
                $val = ($val === 'true') ? true : false;
                $source[$key] = $val;
            } elseif ($val === true || $val === false) {
                $source[$key] = $val;
            } else {
                trigger_error("Your setting '{$key}' should be a boolean but it is, {$val}, a ({$type}):", E_WARNING);
            }

        // save number values as numbers
        } elseif (is_numeric($val)) {
            $int = (int)$val;
            $float = (float)$val;
            $val = ($int == $float) ? $int : $float;
            $source[$key] = $val;
        // allow all other types to be juggled
        } else {
            $source[$key] = $val;
        }
    }

    /**
     * Updates the actual settings files.
     * Will add and overwrite with any values set using Settings::set().
     * Temporary settings set as properties (using the magic __set()) will not
     * be written by Settings::writeSettings().
     */
    public function writeSettings()
    {
        // write common settings
        $common = array_merge($this->default_common, $this->common);
        $this->files->overwrite('Common Settings', $common);

        // write experiment settings if we actually have a path to write to
        if ($this->experiment !== array()) {
            $experiemnt = array_merge($this->default_exp, $this->experiment);
            $this->files->overwrite('Experiment Settings', $experiemnt);
        }
    }
}

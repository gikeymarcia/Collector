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
    protected $defaultPass = 'weakpassword';

    /**
     * The default settings in "Common Settings.json".
     * If "Common Settings.json" is not present these settings will be used
     *
     * @var array
     */
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

    /**
     * The default settings in the "Settings.json" for all new experiments.
     * If "Settings.json" is not present these settings will be used
     * @var array
     */
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

    /**
     * Information about the loaded common settings file: last modification time
     * ('mod'), the path to the file ('loc'), and its data ('data').
     *
     * @var array
     */
    protected $common = array(
        'mod' => 0,
        'loc' => null,
        'data' => array(),
    );

    /**
     * Information about the loaded experiment settings file: last modification
     * time ('mod'), the path to the file ('loc'), and its ('data').
     *
     * @var array
     */
    protected $experiment = array(
        'mod' => 0,
        'loc' => null,
        'data' => array(),
    );

    /**
     * Temporary storage for all properties set on the fly.
     *
     * @var array
     */
    protected $temp = array();

    /**
     * The path to the password file.
     *
     * @var string
     */
    protected $passLoc;

    /**
     * JSON encode options.
     *
     * @var null|int
     */
    private $jsonOptions;

    /**
     * Constructor.
     *
     * @param string $commonLoc The path to the common settings file.
     * @param string $expLoc    The path to the experiment specific settings file.
     * @param string $passLoc   The path to the password.
     */
    public function __construct($commonLoc, $expLoc, $passLoc)
    {
        $this->jsonOptions = $this->canPrettyPrint() ? JSON_PRETTY_PRINT : null;
        $this->common = $this->load($commonLoc);
        $this->experiment = $this->load($expLoc);
        $this->passLoc = $passLoc;
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
        if (isset($this->temp[$key])) {
            // Temporarily set variables
            return $this->temp[$key];
        } elseif (isset($this->experiment['data'][$key])) {
            // Experiment Settings
            return $this->experiment['data'][$key];
        } elseif (isset($this->common['data'][$key])) {
            // Common settings
            return $this->common['data'][$key];
        } elseif (isset($this->defaultExp[$key])) {
            // default experiment settings
            return $this->defaultExp[$key];
        } elseif (isset($this->defaultCommon[$key])) {
            // default common settings
            return $this->defaultCommon[$key];
        } else {
            // @todo perhaps this should throw an \InvalidArgumentException instead?
            trigger_error('You have attempted to use the setting value of '
                ."{$key} when that value is not specified", E_WARNING);
        }
    }

    /**
     * Magic setter.
     * All values set on the fly are added to the temp property (which has the
     * highest priority when __get is called).
     *
     * @param string $var The name of the property to set.
     * @param mixed  $val The value to set to the property.
     */
    public function __set($var, $val)
    {
        $this->temp[trim(strtolower($var))][$val];
    }

    /**
     * Loads a settings file.
     * Stores the last time the file was modified ('mod') the location of the
     * file ('loc') and the data it contains ('data').
     *
     * @param string $file_path The path to the file to load.
     *
     * @return array The array of information about the file.
     */
    private function load($file_path)
    {
        $out = array(
            'mod' => 0,
            'loc' => $file_path,
            'data' => array(),
        );

        if (file_exists($file_path)) {
            $out['data'] = json_decode(file_get_contents($file_path), true);
            $out['mod'] = date('U', filemtime($file_path));
        }

        return $out;
    }

    /**
     * Sets a new password to the password file.
     *
     * @param string $input The password to store.
     */
    public function setPassword($input = null)
    {
        if ($input === null) {
            $input = $this->defaultPass;
        }

        if (!isset($input[2])) {
            return;
        }

        // @todo Perhaps this password should be sanitized? Is it from a user-input form?
        $php_string = "<?php return '$input'; ?>";
        file_put_contents($this->passLoc, $php_string);
    }

    /**
     * Retrieves the stored password.
     *
     * @return string|null The password if it could be found, else null.
     */
    protected function getPassword()
    {
        $password = null;
        if (file_exists($this->passLoc)) {
            $password = (require $this->passLoc);
            if ($password === $this->defaultPass) {
                $password = null;
            }
        }

        return $password;
    }

    /**
     * Refreshes the settings to ensure the correct experiment's settings have
     * been loaded.
     * Uses Pathfinder object to determine what settings to use.
     *
     * @param Pathfinder $paths The current experiment's Pathfinder.
     */
    public function upToDate(\Pathfinder $paths)
    {
        $commonLoc = $paths->get('Common Settings');
        $commonMod = (file_exists($commonLoc)) ? date('U', filemtime($commonLoc)) : 0;

        $expLoc = $paths->get('Experiment Settings');
        $expMod = (file_exists($expLoc)) ? date('U', filemtime($expLoc)) : 0;

        // update common settings
        if ($this->common['loc'] !== $commonLoc
            || $this->common['mod'] < $commonMod
        ) {
            $this->common = $this->load($commonLoc);
        }

        // update experiment specific settings
        if ($this->experiment['loc'] !== $expLoc
            || $this->experiment['mod'] < $expMod
        ) {
            $this->experiment = $this->load($expLoc);
        }

        $this->passLoc = $paths->get("Password");

        // clear out temporary settings
        $this->temp = array();
    }

    /**
     * Checks the current PHP version to see if JSON pretty print is possible.
     *
     * @return bool True if pretty print is possible, else false.
     */
    protected function canPrettyPrint()
    {
        return (version_compare(PHP_VERSION, '5.4.0') >= 0) ? true : false;
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
        $location = (isset($this->defaultCommon[$key])) ? 'common' : 'experiment';
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
                $source['data'][$key] = $val;
            } elseif ($val === true || $val === false) {
                $source['data'][$key] = $val;
            } else {
                trigger_error("Your setting '{$key}' should be a boolean but it is, {$val}, a ({$type}):", E_WARNING);
            }

        // save number values as numbers
        } elseif (is_numeric($val)) {
            $int = (int)$val;
            $float = (float)$val;
            $val = ($int == $float) ? $int : $float;
            $source['data'][$key] = $val;
        // allow all other types to be juggled
        } else {
            // $val = htmlspecialchars($val);
            $source['data'][$key] = $val;
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
        $common = array();
        foreach ($this->defaultCommon as $key => $value) {
            $common[$key] = $value;
        }
        foreach ($this->common['data'] as $key => $value) {
            $common[$key] = $value;
        }
        $json = json_encode($common, $this->jsonOptions);
        file_put_contents($this->common['loc'], $json);

        // write experiment settings if we actually have a path to write to
        if (strpos($this->experiment['loc'], '{') === false) {
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

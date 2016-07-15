<?php
/**
 * Validator class.
 */

namespace Collector;

/**
 * Validators are used to validate Trials.
 *
 * When the validate function is called, the Validator runs each check on the
 * Trial passed to the function. The checks are closures that have been added to
 * the checks property via addCheck(). All checks need to accept a Trial, and
 * must return a string on failure, and TRUE or NULL on pass.
 */
class Validator {
    /**
     * The collection of lambda functions to run on validate.
     * @var array
     */
    protected $checks;

    /**
     * Constructor.
     *
     * @param string|array $pathToCheck [Optional] The path(s) to the validator
     *                                  scripts to initialize the object with.
     */
    public function __construct($pathToCheck = null)
    {
        $this->checks = array();
        if (!empty($pathToCheck) && is_array($pathToCheck)) {
            foreach($pathToCheck as $path) {
                $this->addCheck($path);
            }
        } else if (!empty($pathToCheck)) {
            $this->addCheck($pathToCheck);
        }
    }

    /**
     * Validates the given Trial using the registered check functions.
     *
     * @param Trial $trial The Trial to validate.
     *
     * @return array Returns an array of any errors that are caught during
     *               validation.
     */
    public function validate(Trial $trial)
    {
        $errors = array();
        foreach ($this->checks as $path) {
            $check = include($path);
            $result = $check($trial);
            $this->checkReturnType($result);

            if (!in_array($result, array(true, null), true)) {
                $errors[] = array(
                    'message' => $result,
                    'info' => $trial->getDebugInfo(),
                );
            }
        }

        return $errors;
    }

    /**
     * Adds a new check function to be used by the Validator.
     *
     * @param \Closure $pathToCheck The check function to add.
     */
    public function addCheck($pathToCheck)
    {
        $this->checks[] = $pathToCheck;
    }

    /**
     * Gets all the checks registered for this Validator.
     *
     * Useful for merging two Validators by combining their checks.
     *
     * @return array Returns the array of checks registered with this Validator.
     */
    public function getChecks()
    {
        return $this->checks;
    }

    /**
     * Combines the given Validator's check functions with the current's.
     *
     * @param Validator $validator The Validator to merge with the current.
     */
    public function merge(Validator $validator)
    {
        foreach ($validator->getChecks() as $check) {
            $this->addCheck($check);
        }
    }

    /**
     * Checks that a Validator check function's result is a valid type (string,
     * true, null).
     *
     * @param mixed $result The result from a check function.
     *
     * @throws \Exception Throws an Exception if the return type is not a string,
     *                    true, or null.
     */
    protected function checkReturnType($result)
    {
        if (!is_string($result) && $result !== true && $result !== null) {
            throw new \Exception('Validator check functions must return true or'
                . ' null for valid trials, and must return an error message of '
                . 'the type string for invalid trials ');
        }
    }
}

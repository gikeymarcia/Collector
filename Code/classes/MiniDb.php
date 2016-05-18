<?php
/**
 * MiniDb class.
 */

namespace Collector;

/**
 * An extremely lightweight database object.
 *
 * MiniDB has functions for adding unique keys, retrieving values from keys,
 * updating key-value pairs, and exporting the data to different formats
 * (currently supports exporting to array, JSON, or HTML [as a table]).
 */
class MiniDb
{
    /**
     * The associative array of data.
     * @var array
     */
    protected $data;

    /**
     * Constructor.
     * 
     * @param array $data [Optional] The data to initialze the MiniDb with.
     */
    public function __construct(array $data = array())
    {
        // initialize
        $this->data = array();

        // store keys case insensitively
        foreach ($data as $key => $value) {
            $this->data[strtolower($key)] = $value;
        }
    }

    /**
     * Adds a key if the key does not already exist.
     *
     * @param string $name  The key to add.
     * @param mixed  $value The value to assign to the key.
     *
     * @return bool Returns true if the key is added, else false.
     */
    public function add($name, $value = null)
    {
        $key = strtolower($name);
        if (!array_key_exists($key, $this->data)) {
            $this->data[$key] = $value;

            return true;
        }

        return false;
    }

    /**
     * Gets the value at the given key.
     *
     * @param string $name The key to retrieve the value for.
     *
     * @return mixed Returns the stored value if the key exists, else null.
     */
    public function get($name)
    {
        $key = strtolower($name);

        return isset($this->data[$key]) ? $this->data[$key] : null;
    }

    /**
     * Updates the value at the given key, adding it if it does not yet exist.
     *
     * @param string $name  The key to add or update.
     * @param mixed  $value The value to assign to the key.
     *
     * @return bool Returns true if a key was updated, else false if a key was 
     *              added.
     */
    public function update($name, $value)
    {
        $key = strtolower($name);
        $updated = array_key_exists($key, $this->data);
        $this->data[$key] = $value;

        return $updated;
    }

    /**
     * Exports the full array of data.
     *
     * @param string $format The format of the exported data: PHP array or JSON.
     *
     * @return array Returns the formatted array.
     */
    public function export($format = 'array')
    {
        return $this->formatArray($this->data, $format);
    }

    /**
     * Converts an array to the given format.
     *
     * @param array  $array The array to format.
     * @param string $format The format of the exported data: JSON.
     *
     * @return array The formatted array.
     *
     * @todo Implement HTML and readable string export options.
     */
    protected function formatArray(array $array, $format = null)
    {
        switch (strtolower($format)) {
            case 'json':
                return json_encode($array, JSON_PRETTY_PRINT);
            default:
                return $array;
        }
    }
}

<?php
/**
 * Response class.
 */

namespace Collector;

/**
 * An object for working with participants' responses.
 *
 * Response objects extend MiniDB and thus have functions for adding, getting,
 * updating, and exporting data. The key addition to Responses is that they can
 * be sealed once a Trial has completed which then locks the object to a
 * read-only state.
 */
class Response extends MiniDb
{
    /**
     * Indicates whether the Response can be updated or not.
     * @var bool
     */
    protected $readonly;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->readonly = false;
        parent::__construct();
    }

    /* Overrides
     **************************************************************************/
    /**
     * Overrides the MiniDB add function to check for the read-only state.
     *
     * @param string $name  The key to add.
     * @param mixed  $value The value to assign to the key.
     *
     * @return bool Returns true if the key is added, else false.
     */
    public function add($name, $value = null)
    {
        if (!$this->readonly) {
            return parent::add($name, $value);
        }

        return false;
    }

    /**
     * Overrides the MiniDB update function to check for the read-only state.
     *
     * @param string $name  The key to add or update.
     * @param mixed  $value The value to assign to the key.
     *
     * @return bool Returns null if the Response is sealed, true if the key is
     *              updated, or false if it was added.
     */
    public function update($name, $value = null)
    {
        if (!$this->readonly) {
            return parent::update($name, $value);
        }

        return null;
    }

    /* Class specific
     **************************************************************************/
    /**
     * Prevents the Response from being updated any further.
     */
    public function seal()
    {
        $this->readonly = true;
    }

    /**
     * Checks to see if the Response has been sealed or not.
     *
     * @return bool Returns true if the Response is sealed, else false.
     */
    public function isSealed()
    {
        return $this->readonly;
    }
}

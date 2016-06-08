<?php
/**
 * Autoloader class.
 */

namespace Collector;

/**
 * PSR-4 compliant Autoloader.
 * 
 * A fully qualified class name has the following form:
 * \<NamespaceName>(\<SubNamespaceNames>)*\<ClassName>
 * See more information here: http://www.php-fig.org/psr/psr-4/
 * 
 * Example usage:
 * ```php
 * $loader = new Autoloader();
 * $loader->register();
 * $loader->add('Some\Namespace', 'path/to/somenamespace/root');
 * ```
 */
class Autoloader {
    /**
     * Associative array where the key is a namespace and the value is the path
     * to the namespace.
     *
     * @var array
     */
    protected $namespaces;

    /**
     * Register the load method with the spl_autoloader stack.
     */
    public function register()
    {
        spl_autoload_register(array($this, 'load'));
    }

    /**
     * Adds a namespace and its the path to its associated directory.
     *
     * @param string $namespace The namespace.
     * @param string $dir       The base directory for the namespace.
     */
    public function add($namespace, $dir)
    {
        // ensure namespace ends in '\'
        $ns = trim($namespace, '\\') . '\\';

        // normalize slashes in path and ensure it ends in one
        $path = rtrim(str_replace(DIRECTORY_SEPARATOR, '/', $dir), '/') . '/';

        // store the namespace
        $this->namespaces[$ns] = $path;
    }

    /**
     * Loads the class file for the given fully-qualified class name.
     *
     * @param string $fullClass The fully-qualified class name.
     *
     * @return string|bool      The filename or false if it could not be loaded.
     */
    public function load($fullClass)
    {
        $parts = explode('\\', $fullClass);
        while (null !== $next = array_pop($parts)) {
            $class = (isset($class)) ? $next.'/'.$class : $next;
            $namespace = implode('\\', $parts) . '\\';

            // determine if the namespace is set and try to load the file
            if (isset($this->namespaces[$namespace])) {
                return $this->requireFile($this->namespaces[$namespace].$class.'.php');
            } else {
                return $this->requireFile(__DIR__."/{$fullClass}.php");

            }
        }

        return false;
    }

    /**
     * Require the given file if it exists.
     *
     * @param string $file The file to require.
     *
     * @return boolean     True if the file exists and was required, else false.
     */
    protected function requireFile($file)
    {
        if (file_exists($file)) {
            require $file;

            return true;
        }
        
        return false;
    }
}

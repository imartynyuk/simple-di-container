<?php
/**
 * Dependency injection container.
 * 
 * Provides an ability to setting and getting instances
 * of classes with predefined settings.
 * 
 * @author imartynyuk
 * @version 1.0
 */
class DI 
{
    /**
     * Contains settings of all defined aliases.
     * 
     * @static
     * @var array 
     */
    protected static $settings = array();

    /**
     * Contains instances of already created singletons.
     * 
     * @static
     * @var array
     */
    protected static $singletons = array();

    /**
     * Returns defined settings by alias.
     * 
     * @static
     * @param string $alias An alias.
     * @return array Settings.
     * @throws DIException
     */
    protected static function getSettings($alias)
    {
        if (isset(self::$settings[$alias])) {
            return self::$settings[$alias];
        }

        throw new DIException("Alias '$alias' is not defined.");
    }
    
    /**
     * Includes class file.
     * 
     * @static
     * @param string $alias An alias.
     * @throws DIException
     */
    protected static function requireFile($alias)
    {
        $settings = self::getSettings($alias);
        
        $path = $settings['path'];

        
        if (!file_exists($path)) {
            throw new DIException("File '{$path}' not found. (Alias: '$alias')");
        }
        
        require_once($path);
        
        if (!class_exists($settings['className'])) {
            throw new DIException("Class '{$settings['className']}' not found. (Alias: '$alias', path: '$path')");
        }
    }

    /**
     * Creates an returns an instance of new class.
     * Injects arguments into its constructor.
     * 
     * @static
     * @param type $className The name of new class.
     * @param type $args An array of arguments which will be injected into constructor.
     * @return type
     */
    protected static function build($className, $args)
    {
        $refClass = new ReflectionClass( $className );
        
        return $refClass->newInstanceArgs($args);
    }

    /**
     * Verifies of whether all critical parameters was defined.
     * Throws an exception in case of missed one of them.
     * 
     * @static
     * @param type $settings An array of raw settings.
     * @param type $alias An alias.
     * @throws DIException
     */
    protected static function checkCriticalSettings($settings, $alias)
    {
        if (!isset($settings['className'])) {
            throw new DIException("className is required (Alias: '$alias')");
        }

        if (!isset($settings['path'])) {
            throw new DIException("path is required (Alias: '$alias')");
        }
    }

    /**
     * Verifies of whether all parameters was defined. And fills the missed.
     * 
     * @static
     * @param type $settings An array of raw settings.
     * @param type $alias An alias.
     * @return array Processed settings.
     */
    protected static function processSettings($settings, $alias)
    {
        self::checkCriticalSettings($settings, $alias);

        if (!isset($settings['args'])) {
            $settings['args'] = array();
        }

        if (!isset($settings['singletone'])) {
            $settings['singletone'] = false;
        }

        $settings['path'] = str_replace('.php', '', $settings['path']) . '.php';

        return $settings;
    }

    /**
     * Configures some object using different aliases.
     * 
     * Settings array should contains those critical parameters:
     * - className - name of our class,
     * - path - path to file which contains our class.
     * 
     * Another parameters which can be configured:
     * - singleton - true if class is singleton. Default - false.
     * - args - an arguments which will be injected into constructor. 
     * By default it is an empty array.
     * 
     * @static
     * @param string $alias An alias.
     * @param array $settings An array of settings.
     * @return boolean 
     */
    public static function set($alias, $settings) 
    {
        self::$settings[$alias] = self::processSettings($settings, $alias);
        
        return true;
    }
    
    /**
     * Creates and returns an instance of needed class.
     * Which was already configured by using 'set()' method.
     * 
     * @static
     * @param string $alias An alias.
     * @param array $args Arguments which will be injected in to class. 
     *  If not defined, uses arguments from settings.
     * @return object An instance of needed class.
     */
    public static function get($alias, $args = false)
    {
        $settings = self::getSettings($alias);

        self::requireFile($alias);

        if (!$args) {
            $args = $settings['args'];
        }

        if ($settings['singletone']) {
            if (!isset(self::$singletons[$alias])) {
                self::$singletons[$alias] = self::build($settings['className'], $args);
            }
            
            return self::$singletons[$alias];
        }

        return self::build($settings['className'], $args);
    }
}

class DIException extends Exception {}
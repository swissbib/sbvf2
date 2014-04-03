<?php
namespace Swissbib\RecordDriver\Helper;

use Zend\Config\Config;

/**
 * Base class for customizable method calls
 * Call callMethod with a method name. It will try the following things:
 * Example method: myDummyMethod, example key: a100
 * - myDummyMethodA100
 * - myDummyMethodBase
 * - missingMethod
 *
 */
abstract class CustomizedMethods
{
    /** @var    Config */
    protected $config;



    /**
     * Initialize with config
     *
     * @param    Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }



    /**
     * @param       $methodName
     * @param       $key
     * @param array $arguments
     * @return mixed
     */
    protected function callMethod($methodName, $key, array $arguments = array())
    {
        $customMethod    = $methodName . strtoupper($key);
        $baseMethod      = $methodName . 'Base';

        if (method_exists($this, $customMethod)) {
            return call_user_func_array(array($this, $customMethod), $arguments);
        } elseif (method_exists($this, $baseMethod)) {
            return call_user_func_array(array($this, $baseMethod), $arguments);
        } else {
            return $this->missingMethod($methodName, $key, $arguments);
        }
    }



    /**
     * Handle calls to missing methods
     * This means neither the base method nor the custom method was implemented
     *
     * @param    String        $methodName
     * @param    String        $key
     * @param    Array        $arguments
     * @return    Boolean|Mixed
     */
    protected function missingMethod($methodName, $key, $arguments)
    {
        return false;
    }



    /**
     * Parse values from data array into template string
     *
     * @param    String        $string
     * @param    Array        $data
     * @param    Boolean        $addBraces        Wrap array keys with currly braces for template usage
     * @return    String
     */
    protected function templateString($string, array $data, $addBraces = true)
    {
        if ($addBraces) {
            $newData    = array();
            foreach ($data as $key => $value) {
                $newData['{' . $key . '}'] = $value;
            }
            $data = $newData;
        }

        return str_replace(array_keys($data), array_values($data), trim($string));
    }



    /**
     * Check whether config value exits
     *
     * @param    String        $key
     * @return    Boolean
     */
    protected function hasConfigValue($key)
    {
        return $this->config->offsetExists($key);
    }



    /**
     * Get config value
     *
     * @param    String        $key
     * @return    String
     */
    protected function getConfigValue($key)
    {
        return $this->config->get($key);
    }



    /**
     * Check whether value is defined in a comma separated config parameter
     *
     * @param    String $configKey
     * @param    String $value
     * @return    Boolean
     */
    protected function isValueInConfigList($configKey, $value)
    {
        $configValues    = $this->getConfigList($configKey);

        return in_array($value, $configValues);
    }



    /**
     * Get list items from config
     *
     * @param    String        $configKey
     * @param    Boolean        $toLower
     * @param    Boolean        $trim
     * @param    String        $delimiter
     * @return    String[]
     */
    protected function getConfigList($configKey, $trim = true, $delimiter = ',')
    {
        $data = array();

        if ($this->config->offsetExists($configKey)) {
            $configValue = $this->config->get($configKey);
            $data        = explode($delimiter, $configValue);

            if ($trim) {
                $data = array_map('trim', $data);
            }
        }
        return $data;
    }
}

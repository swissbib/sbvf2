<?php
namespace Libadmin;

use Zend\ModuleManager\Feature\AutoloaderProviderInterface as Autoloadable;
use Zend\ModuleManager\Feature\ConfigProviderInterface as Configurable;

class Module implements Autoloadable, Configurable
{

    /**
     * @return    Array|mixed|\Traversable
     */
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }



    /**
     * @return    Array
     */
    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

}
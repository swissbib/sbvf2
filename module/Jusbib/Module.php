<?php
namespace Jusbib;

use Zend\ModuleManager\Feature\AutoloaderProviderInterface as Autoloadable;
use Zend\ModuleManager\Feature\ConfigProviderInterface as Configurable;
use Zend\ModuleManager\Feature\ConsoleUsageProviderInterface as Consolable;
use Zend\ModuleManager\Feature\InitProviderInterface as Initializable;
use Zend\ModuleManager\ModuleManagerInterface;
use Zend\Mvc\MvcEvent;
use Zend\Console\Adapter\AdapterInterface as Console;

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
     * @param    MvcEvent    $event
     */
    public function onBootstrap(MvcEvent $event)
    {
        $b = new Bootstrapper($event);
        $b->bootstrap();
    }



    /**
     * @return    Array
     */
    public function getAutoloaderConfig()
    {

        //we want to use the classmap mechanism if we are not in development mode
        if (strcmp(APPLICATION_ENV,'development') != 0) {
            preg_match('/(.*?)module/',__DIR__,$matches);

            return array(
                'Zend\Loader\ClassMapAutoloader' => array(
                    __NAMESPACE__ => __DIR__ . '/src/autoload_classmap.php',
                    'VuFind' => $matches[0] . '/VuFind/src/autoload_classmap.php',
                    'VuFindSearch' => $matches[0] . '/VuFindSearch/src/autoload_classmap.php',
                    'VuFindTheme' => $matches[0] . '/VuFindTheme/src/autoload_classmap.php',
                    'Zend' => $matches[1] . 'vendor/zendframework/zendframework/library/Zend/autoload_classmap.php'
                ),

                'Zend\Loader\StandardAutoloader' => array(
                    'namespaces' => array(
                        __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                    ),
                ),
            );


        } else {

            return array(
                'Zend\Loader\StandardAutoloader' => array(
                    'namespaces' => array(
                        __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                    ),
                ),
            );

        }

    }


}

<?php
namespace Swissbib;

use Zend\ModuleManager\Feature\AutoloaderProviderInterface as Autoloadable;
use Zend\ModuleManager\Feature\ConfigProviderInterface as Configurable;
use Zend\ModuleManager\Feature\ConsoleUsageProviderInterface as Consolable;
use Zend\ModuleManager\Feature\InitProviderInterface as Initializable;
use Zend\ModuleManager\ModuleManagerInterface;
use Zend\Mvc\MvcEvent;
use Zend\Console\Adapter\AdapterInterface as Console;

class Module implements Autoloadable, Configurable, Initializable, Consolable
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



    /**
     * @inheritDoc
     */
    public function getConsoleUsage(Console $console)
    {
        return array(
            '# Libadmin VuFind Synchronisation',
            '# Import library and group data from libadmin API and save as local files',
            'libadmin sync [--verbose|-v] [--dry|-d] [--result|-r]',
            array('--verbose|-v', 'Print informations about actions on console output'),
            array('--dry|-d', 'Don\'t replace local files with new data (check if new data is available/reachable)'),
            array('--result|-r', 'Print out a single result info at the end. This is included in the verbose flag'),

            '# Tab40 Location Import',
            '# Extract label information from a tab40 file and convert to vufind language format',
            'tab40import <network> <locale> <source>',
            array('network', 'Network key the file contains informatino about. Ex: idsbb'),
            array('locale', 'Locale key: de, en, fr, etc'),
            array('source', 'Path to input file. Ex: ~/myalephdata/tab40.ger')
        );
    }



    /**
     * @param    ModuleManagerInterface    $m
     */
    public function init(ModuleManagerInterface $m)
    {
        //note: only for testing
        //$m->getEventManager()->attach(ModuleEvent::EVENT_LOAD_MODULES_POST,array($this,'postInSwissbib'),10000);
    }
}

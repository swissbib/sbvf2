<?php

namespace Swissbib;

use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\InitProviderInterface;
use Zend\ModuleManager\ModuleManagerInterface;

use Zend\ModuleManager\ModuleManager,
    Zend\Mvc\MvcEvent;

class Module implements AutoloaderProviderInterface,
						ConfigProviderInterface,
						InitProviderInterface
{
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

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

    public function init(ModuleManagerInterface $m)
    {
    }

    public function onBootstrap(MvcEvent $e)
    {
    }
}

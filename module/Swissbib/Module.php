<?php

namespace Swissbib;
use Zend\ModuleManager\ModuleManager,
    Zend\Mvc\MvcEvent,
    Zend\ModuleManager\ModuleEvent;

class Module
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

    public function init(ModuleManager $m)
    {

        //note: only for testing
        $m->getEventManager()->attach(ModuleEvent::EVENT_LOAD_MODULES_POST,array($this,'postInSwissbib'),10000);

    }

    public function onBootstrap(MvcEvent $e)
    {
    }


    public function postInSwissbib(ModuleEvent $e) {

        //note: only for testing
        $mName = $e->getModuleName();

        $params =  $e->getParams();


    }
}

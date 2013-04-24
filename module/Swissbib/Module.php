<?php
namespace Swissbib;

use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\InitProviderInterface;
use Zend\ModuleManager\ModuleManagerInterface;
use Zend\Mvc\MvcEvent;

class Module implements AutoloaderProviderInterface, ConfigProviderInterface, InitProviderInterface
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
		return array(
			'Zend\Loader\StandardAutoloader' => array(
				'namespaces' => array(
					__NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
				),
			),
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

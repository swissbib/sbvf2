<?php
namespace Swissbib;

use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\InitProviderInterface;
use Zend\ModuleManager\ModuleManagerInterface;

use Zend\ModuleManager\ModuleManager,
		Zend\Mvc\MvcEvent,
		Zend\ModuleManager\ModuleEvent;

class Module implements AutoloaderProviderInterface, ConfigProviderInterface, InitProviderInterface {

	public function getConfig() {
		return include __DIR__ . '/config/module.config.php';
	}



	public function getAutoloaderConfig() {
		return array(
			'Zend\Loader\StandardAutoloader' => array(
				'namespaces' => array(
					__NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
				),
			),
		);
	}



	public function init(ModuleManagerInterface $m) {

		//note: only for testing
		//$m->getEventManager()->attach(ModuleEvent::EVENT_LOAD_MODULES_POST,array($this,'postInSwissbib'),10000);

	}



	public function onBootstrap(MvcEvent $e) {

		$b = new SbBootstrapper($e);
		$b->bootstrap();

	}

	//public function postInSwissbib(ModuleEvent $e) {

	//note: only for testing
	//    $mName = $e->getModuleName();

	//    $params =  $e->getParams();

	//}
}

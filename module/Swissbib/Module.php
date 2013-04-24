<?php
namespace Swissbib;

use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\ConsoleUsageProviderInterface;
use Zend\ModuleManager\Feature\InitProviderInterface;
use Zend\ModuleManager\ModuleManagerInterface;
use Zend\Mvc\MvcEvent;
use Zend\Console\Adapter\AdapterInterface as Console;

class Module
	implements AutoloaderProviderInterface, ConfigProviderInterface, InitProviderInterface, ConsoleUsageProviderInterface
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
	 * @inheritDoc
	 */
	public function getConsoleUsage(Console $console)
	{
		return array(
			'Libadmin VuFind Synchronisation',
			'Import library and group data from libadmin API and save as local files',
			'Usage: libadmin sync [--verbose|-v] [--dry|-d] [--result|-r]',
			'--verbose|-v' => 'Print informations about actions on console output',
			'--dry|-d'     => 'Don\'t replace local files with new data (check if new data is available/reachable)',
			'--result|-r'  => 'Print out a single result info at the end. This is included in the verbose flag'
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

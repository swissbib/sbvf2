<?php
namespace Swissbib;

use VuFind\Config\Reader as ConfigReader;
use Zend\Mvc\MvcEvent;
use Zend\Console\Request as ConsoleRequest;
use Swissbib\Filter\TemplateFilenameFilter;


class Bootstrapper
{

    protected $config;
    protected $event;
    protected $events;

	/**
	 * @param MvcEvent $event
	 */
	public function __construct(MvcEvent $event)
	{
        $application	= $this->config = $event->getApplication();

		$this->config = $application->getServiceManager()->get('VuFind\Config')->get('config');
        $this->event = $event;
        $this->events = $application->getEventManager();

    }

	/**
	 * Bootstrap
	 */
	public function bootstrap() {
        $methods = get_class_methods($this);

        foreach ($methods as $method) {
            if (substr($method,0,4) == "init") {
                $this->$method();
            }
        }
    }



	/**
	 * Add template path filter to filter chain
	 *
	 */
	protected function initFilterChain() {
		if( !$this->event->getRequest() instanceof ConsoleRequest ) {
			$sm = $this->event->getApplication()->getServiceManager();

			$widgetFilter = new TemplateFilenameFilter();
			$widgetFilter->setServiceLocator($sm);

			$view = $sm->get('ViewRenderer');

			$view->getFilterChain()->attach($widgetFilter, 50);
		}
	}

	public function initSwissbibDBs(){

    }

    /**
     * Set up plugin managers.
     */
    protected function DISABLEDinitPluginManagers()
    {
        $app = $this->event->getApplication();
        $serviceManager = $app->getServiceManager();
        $config = $app->getConfig();

        // Use naming conventions to set up a bunch of services based on namespace:
        $namespaces = array(
            'Db\Table'
        );
        foreach ($namespaces as $ns) {
            $serviceName = 'Swissbib\\' . str_replace('\\', '', $ns) . 'PluginManager';
            $factory = function ($sm) use ($config, $ns) {
                $className = 'Swissbib\\' . $ns . '\PluginManager';
                $configKey = strtolower(str_replace('\\', '_', $ns));
                return new $className(
                    new \Zend\ServiceManager\Config(
                        $config['swissbib']['plugin_managers'][$configKey]
                    )
                );
            };

            $serviceManager->setFactory($serviceName, $factory);
        }
    }

}

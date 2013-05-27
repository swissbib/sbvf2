<?php
namespace Swissbib;

use VuFind\Config\Reader as ConfigReader;
use Zend\Console\Console;
use Zend\Mvc\MvcEvent;
use Zend\Console\Request as ConsoleRequest;
use Swissbib\Filter\TemplateFilenameFilter;
use Zend\I18n\Translator\Translator;

class Bootstrapper
{

	protected $config;

	protected $event;

	protected $events;


	/**
	 * @var \Zend\Mvc\ApplicationInterface
	 */
	protected $application;

	/**
	 * @var \Zend\ServiceManager\ServiceLocatorInterface
	 */
	protected $serviceManager;


	/**
	 * @param MvcEvent $event
	 */
	public function __construct(MvcEvent $event)
	{
		$this->application = $event->getApplication();
		$this->serviceManager	= $this->application->getServiceManager();

		$this->config = $this->serviceManager->get('VuFind\Config')->get('config');
		$this->event  = $event;
		$this->events = $this->application->getEventManager();
	}



	/**
	 * Bootstrap
	 * Automatically discovers and evokes all class methods with names starting with 'init'
	 */
	public function bootstrap()
	{
		$methods = get_class_methods($this);

		foreach ($methods as $method) {
			if (substr($method, 0, 4) == 'init') {
				$this->$method();
			}
		}
	}



	/**
	 * Add template path filter to filter chain
	 */
	protected function initFilterChain()
	{
		return;
		if (!$this->event->getRequest() instanceof ConsoleRequest) {
			$sm = $this->event->getApplication()->getServiceManager();

			$widgetFilter = new TemplateFilenameFilter();
			$widgetFilter->setServiceLocator($sm);

			$view = $sm->get('ViewRenderer');

			$view->getFilterChain()->attach($widgetFilter, 50);
		}
	}



	/**
	 * Initialize translator for custom label files
	 * DISABLED AT THE MOMENT
	 */
	public function initLanguage()
	{
		// Language not supported in CLI mode:
		if (Console::isConsole()) {
			return;
		}

		$baseDir = LOCAL_OVERRIDE_DIR . '/languages';

		// Custom namespaces for zendTranslate
		$types   = array(
			'bibinfo',
			'group',
			'institution',
			'union'
		);

		$callback = function ($event) use ($baseDir, $types) {
			$sm = $event->getApplication()->getServiceManager();
			/** @var Translator $translator */
			$translator = $sm->get('VuFind\Translator');
			$locale     = $translator->getLocale();
			$fallback	= 'en';
			$translator->setFallbackLocale($fallback);
				// Add file for fallback locale if not already en
			if ($locale !== 'en') {
				$translator->addTranslationFile('ExtendedIni', $baseDir . '/en.ini', 'default', $fallback);
			}

			foreach ($types as $type) {
				$langFile = $baseDir . '/' . $type . '/' . $locale . '.ini';

				// File not available, add empty file to prevent problems
				if (!is_file($langFile)) {
					$langFile = $baseDir . '/empty_fallback.ini';
				}

				$translator->addTranslationFile('ExtendedIni', $langFile, $type, $locale)
						->setLocale($locale);
			}
		};

		// Attach right AFTER base translator, so it is initialized
		$this->events->attach('dispatch', $callback, 8999);
	}



	/**
	 * Set up plugin managers.
	 */
	protected function initPluginManagers()
	{
		$app            = $this->event->getApplication();
		$serviceManager = $app->getServiceManager();
		$config         = $app->getConfig();

		// Use naming conventions to set up a bunch of services based on namespace:
		$namespaces = array(
			'Db\Table','Search\Results','Search\Options', 'Search\Params'
		);
		foreach ($namespaces as $ns) {
			$serviceName = 'Swissbib\\' . str_replace('\\', '', $ns) . 'PluginManager';
			$factory     = function ($sm) use ($config, $ns) {
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

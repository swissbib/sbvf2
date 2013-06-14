<?php
namespace Swissbib;

use Zend\Config\Config;
use Zend\Console\Console;
use Zend\Mvc\MvcEvent;
use Zend\Console\Request as ConsoleRequest;
use Zend\I18n\Translator\Translator;
use Zend\ServiceManager\ServiceManager;

use VuFind\Config\Reader as ConfigReader;

use Swissbib\Filter\TemplateFilenameFilter;

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
		if (APPLICATION_ENV == 'development' && !$this->event->getRequest() instanceof ConsoleRequest) {
			$sm = $this->event->getApplication()->getServiceManager();

			$widgetFilter = new TemplateFilenameFilter();
			$widgetFilter->setServiceLocator($sm);

			$view = $sm->get('ViewRenderer');

			$view->getFilterChain()->attach($widgetFilter, 50);
		}
	}


	/*
	 * Set fallback locale to english
	 */
	public function initTranslationFallback()
	{
		$baseDir = LOCAL_OVERRIDE_DIR . '/languages';

		$callback = function ($event) use ($baseDir) {
			/** @var Translator $translator */
			$translator = $event->getApplication()->getServiceManager()->get('VuFind\Translator');
			$locale     = $translator->getLocale();
			$fallback	= 'en';
			$translator->setFallbackLocale($fallback);
				// Add file for fallback locale if not already en
			if ($locale !== $fallback) {
				$translator->addTranslationFile('ExtendedIni', $baseDir . '/' . $fallback. '.ini', 'default', $fallback);
			}
		};

		$this->events->attach('dispatch', $callback, 8999);
	}



	/**
	 * Initialize translator for custom label files
	 */
	public function initSpecialTranslations()
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
			/** @var Translator $translator */
			$translator = $event->getApplication()->getServiceManager()->get('VuFind\Translator');
			$locale     = $translator->getLocale();

			foreach ($types as $type) {
				$langFile = $baseDir . '/' . $type . '/' . $locale . '.ini';

					// File not available, add empty file to prevent problems
				if (!is_file($langFile)) {
					$langFile = $baseDir . '/empty_fallback.ini';
				}

				$translator->addTranslationFile('ExtendedIni', $langFile, $type, $locale);
			}
		};

		// Attach right AFTER base translator, so it is initialized
		$this->events->attach('dispatch', $callback, 8998);
	}



	/**
	 * Add files for location translation based on tab40 data to the translator
	 */
	public function initTab40LocationTranslation()
	{
		$callback = function ($event) {
			/** @var ServiceManager $serviceLocator */
			$serviceLocator	= $event->getApplication()->getServiceManager();
			/** @var Translator $translator */
			$translator = $serviceLocator->get('VuFind\Translator');
			/** @var Config $tab40Config */
			$tab40Config	= $serviceLocator->get('VuFind\Config')->get('config')->tab40import;
			$basePath		= $tab40Config->path;
			$languageFiles	= glob($basePath . '/*.ini');

				// Add all found files
			foreach ($languageFiles as $languageFile) {
				list($network, $locale) = explode('-', basename($languageFile, '.ini'));

				$translator->addTranslationFile('ExtendedIni', $languageFile, 'location-' . $network, $locale);
			}
		};

			// Attach right AFTER base translator, so it is initialized
		$this->events->attach('dispatch', $callback, 8997);
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

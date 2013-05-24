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
	 * @param MvcEvent $event
	 */
	public function __construct(MvcEvent $event)
	{
		$application = $this->config = $event->getApplication();

		$this->config = $application->getServiceManager()->get('VuFind\Config')->get('config');
		$this->event  = $event;
		$this->events = $application->getEventManager();

	}



	/**
	 * Bootstrap
	 */
	public function bootstrap()
	{
		$methods = get_class_methods($this);

		foreach ($methods as $method) {
			if (substr($method, 0, 4) == "init") {
				$this->$method();
			}
		}
	}



	/**
	 * Add template path filter to filter chain
	 *
	 */
	protected function initFilterChain()
	{
		return; // Disabled because of IE problem
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
		$types   = array(
			'institution',
			'group',
			'bibinfo'
		);

		$callback = function ($event) use ($baseDir, $types) {
			$sm = $event->getApplication()->getServiceManager();
			/** @var Translator $translator */
			$translator = $sm->get('VuFind\Translator');
			$locale     = $translator->getLocale();
			$translator->setFallbackLocale('en');

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



	public function initSwissbibDBs()
	{

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
			'Db\Table'
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

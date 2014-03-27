<?php
namespace Swissbib;

use Zend\Config\Config;
use Zend\Console\Console;
use Zend\EventManager\Event;
use Zend\Mvc\MvcEvent;
use Zend\Console\Request as ConsoleRequest;
use Zend\I18n\Translator\Translator;
use Zend\ServiceManager\ServiceManager;

use VuFind\Config\Reader as ConfigReader;
use VuFind\Auth\Manager;

use Swissbib\Filter\TemplateFilenameFilter;
use Swissbib\Log\Logger;
use Swissbib\VuFind\Search\Solr\Options;

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
        $this->serviceManager    = $this->application->getServiceManager();

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



    /**
     * Initialize locale change
     * Save changed locale in user
     *
     */
    protected function initLocaleChange()
    {
        /** @var ServiceManager $serviceLocator */
        $serviceLocator    = $this->serviceManager;
        /** @var Manager $authManager */
        $authManager    = $serviceLocator->get('VuFind\AuthManager');

        if ($authManager->isLoggedIn()) {
            $user = $authManager->isLoggedIn();

            $callback = function ($event) use ($user) {
                $request = $event->getRequest();

                if (($locale = $request->getPost()->get('mylang', false)) ||
                    ($locale = $request->getQuery()->get('lng', false))) {
                    $user->language = $locale;
                    $user->save();
                }
            };

            $this->events->attach('dispatch', $callback, 1000);
        }
    }



    /**
     * Initialize translation from user settings
     * Executes later than vufind language init (vufind has priority 9000)
     */
    protected function initUserLocale()
    {
        /** @var ServiceManager $serviceLocator */
        $serviceLocator    = $this->serviceManager;
        /** @var Manager $authManager */
        $authManager    = $serviceLocator->get('VuFind\AuthManager');

        if ($authManager->isLoggedIn()) {
            $locale = $authManager->isLoggedIn()->language;

            if ($locale) {
                /** @var Translator $translator */
                $translator = $this->serviceManager->get('VuFind\Translator');
                $viewModel = $serviceLocator->get('viewmanager')->getViewModel();

                $callback = function ($event) use ($locale, $translator, $viewModel) {
                    $translator->setLocale($locale);

                    $viewModel->setVariable('userLang', $locale);
                };

                $this->events->attach('dispatch', $callback, 8000);
            }
        }
    }



    /*
     * Set fallback locale to english
     */
    protected function initTranslationFallback()
    {
        // Language not supported in CLI mode:
        if (Console::isConsole()) {
            return;
        }

        $baseDir = LOCAL_OVERRIDE_DIR . '/languages';

        $callback = function ($event) use ($baseDir) {
            /** @var Translator $translator */
            $translator = $event->getApplication()->getServiceManager()->get('VuFind\Translator');
            $locale     = $translator->getLocale();
            $fallback    = 'en';
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
    protected function initSpecialTranslations()
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
    protected function initTab40LocationTranslation()
    {
        $callback = function ($event) {
            /** @var ServiceManager $serviceLocator */
            $serviceLocator    = $event->getApplication()->getServiceManager();
            /** @var Translator $translator */
            $translator = $serviceLocator->get('VuFind\Translator');
            /** @var Config $tab40Config */
            $tab40Config    = $serviceLocator->get('VuFind\Config')->get('config')->tab40import;

            if ($tab40Config) {
                $basePath        = $tab40Config->path;
                $languageFiles    = glob($basePath . '/*.ini');

                    // Add all found files
                foreach ($languageFiles as $languageFile) {
                    list($network, $locale) = explode('-', basename($languageFile, '.ini'));

                    $translator->addTranslationFile('ExtendedIni', $languageFile, 'location-' . $network, $locale);
                }
            }
        };

            // Attach right AFTER base translator, so it is initialized
        $this->events->attach('dispatch', $callback, 8997);
    }



    /**
     * Add log listener for missing institution translations
     *
     */
    protected function initMissingTranslationObserver()
    {
        if (APPLICATION_ENV != 'development') {
            return;
        }

        /** @var ServiceManager $serviceLocator */
        $serviceLocator    = $this->event->getApplication()->getServiceManager();
        /** @var \Swissbib\Log\Logger $logger */
        $logger    = $serviceLocator->get('Swissbib\Logger');
        /** @var Translator $translator */
        $translator = $serviceLocator->get('VuFind\Translator');

        /**
         * @param    Event $event
         */
        $callback = function ($event) use ($logger) {
            if ($event->getParam('text_domain') === 'institution') {
                $logger->logUntranslatedInstitution($event->getParam('message'));
            }
        };

        $translator->enableEventManager();
        $translator->getEventManager()->attach('missingTranslation', $callback);
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
            'VuFind\Search\Results','VuFind\Search\Options', 'VuFind\Search\Params'
        );

        foreach ($namespaces as $namespace) {
            $plainNamespace    = str_replace('\\', '', $namespace);
            $shortNamespace    = str_replace('VuFind', '', $plainNamespace);
            $configKey        = strtolower(str_replace('\\', '_', $namespace));
            $serviceName    = 'Swissbib\\' . $shortNamespace . 'PluginManager';
            $serviceConfig    = $config['swissbib']['plugin_managers'][$configKey];
            $className        = 'Swissbib\\' . $namespace . '\PluginManager';

            $pluginManagerFactoryService = function ($sm) use ($className, $serviceConfig) {
                return new $className(
                    new \Zend\ServiceManager\Config($serviceConfig)
                );
            };

            $serviceManager->setFactory($serviceName, $pluginManagerFactoryService);
        }
    }



    /**
     * Add user defined default limit for search
     */
    protected function initDefaultSearchLimit()
    {
        /** @var ServiceManager $serviceLocator */
        $serviceLocator    = $this->event->getApplication()->getServiceManager();
        /** @var Manager $authManager */
        $authManager    = $serviceLocator->get('VuFind\AuthManager');

        if ($authManager->isLoggedIn()) {
            $userLimit = $authManager->isLoggedIn()->max_hits;

            if ($userLimit) {
                /** @var Options $searchOptions */
                $searchOptions =  $serviceLocator->get('Swissbib\SearchResultsPluginManager')->get($this->getActiveTab())->getOptions();

                $searchOptions->setDefaultLimit($userLimit);
            }
        }
    }

    protected function getActiveTab() {
        if (strpos($this->application->getRequest()->getRequestUri(), '/Summon/') !== false) {
            return 'Summon';
        } else {
            return 'Solr';
        }
    }
}

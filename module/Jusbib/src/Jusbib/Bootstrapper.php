<?php
namespace Jusbib;

use \Swissbib\Bootstrapper as SwissbibBootstrapper;

class Bootstrapper extends SwissbibBootstrapper
{

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
            $plainNamespace	= str_replace('\\', '', $namespace);
            $shortNamespace	= str_replace('VuFind', '', $plainNamespace);
            $configKey		= strtolower(str_replace('\\', '_', $namespace));
            $serviceName	= 'Jusbib\\' . $shortNamespace . 'PluginManager';
            $serviceConfig	= $config['jusbib']['plugin_managers'][$configKey];
            $className		= 'Jusbib\\' . $namespace . '\PluginManager';

            $pluginManagerFactoryService = function ($sm) use ($className, $serviceConfig) {
                return new $className(
                    new \Zend\ServiceManager\Config($serviceConfig)
                );
            };

            $serviceManager->setFactory($serviceName, $pluginManagerFactoryService);
        }
    }

}

<?php
namespace Swissbib\VuFind\Search\Results;

use Zend\ServiceManager\ServiceLocatorInterface;

use VuFind\Search\Results\PluginFactory as VuFindResultsPluginFactory;

use Swissbib\VuFind\Search\Helper\ExtendedSolrFactoryHelper;

/**
 * Class PluginFactory
 *
 * @package Swissbib\Search\Results
 */
class PluginFactory extends VuFindResultsPluginFactory
{

    /**
     * @inheritDoc
     */
    public function canCreateServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        /** @var ExtendedSolrFactoryHelper $extendedTargetHelper */
        $extendedTargetHelper    = $serviceLocator->getServiceLocator()->get('Swissbib\ExtendedSolrFactoryHelper');
        $this->defaultNamespace    = $extendedTargetHelper->getNamespace($name, $requestedName);

        return parent::canCreateServiceWithName($serviceLocator, $name, $requestedName);
    }



    /**
     * Create a service for the specified name.
     *
     * @param ServiceLocatorInterface $serviceLocator Service locator
     * @param string                  $name           Name of service
     * @param string                  $requestedName  Unfiltered name of service
     *
     * @return object
     */
    public function createServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        $params     = $serviceLocator->getServiceLocator()->get('Swissbib\SearchParamsPluginManager')->get($requestedName);
        $className  = $this->getClassName($name, $requestedName);

        return new $className($params);
    }
}

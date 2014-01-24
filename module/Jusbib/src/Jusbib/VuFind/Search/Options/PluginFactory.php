<?php
namespace Jusbib\VuFind\Search\Options;

use Zend\ServiceManager\ServiceLocatorInterface;

use VuFind\Search\Options\PluginFactory as VuFindOptionsPluginFactory;

use Swissbib\VuFind\Search\Helper\ExtendedSolrFactoryHelper;

/**
 *  VuFind enhancements to extend the VuFind Options type for the Solr target
 *
 * @package Swissbib\VuFind\Search\Options
 */
class PluginFactory extends VuFindOptionsPluginFactory
{

    /**
     * @inheritDoc
     */
    public function canCreateServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        /** @var ExtendedSolrFactoryHelper $extendedTargetHelper */
        $extendedTargetHelper	= $serviceLocator->getServiceLocator()->get('Jusbib\ExtendedSolrFactoryHelper');
        $this->defaultNamespace	= $extendedTargetHelper->getNamespace($name, $requestedName);

        return parent::canCreateServiceWithName($serviceLocator, $name, $requestedName);
    }
}

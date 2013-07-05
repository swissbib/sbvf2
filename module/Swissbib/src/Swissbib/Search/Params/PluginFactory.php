<?php
namespace Swissbib\Search\Params;

use Zend\ServiceManager\ServiceLocatorInterface;

use VuFind\Search\Params\PluginFactory as VuFindParamsPluginFactory;

use Swissbib\Search\Helper\ExtendedSolrFactoryHelper;
use Swissbib\Search\Solr\Params;

class PluginFactory extends VuFindParamsPluginFactory
{

	/**
	 * @inheritDoc
	 */
	public function canCreateServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
	{
		/** @var ExtendedSolrFactoryHelper $extendedTargetHelper */
		$extendedTargetHelper	= $serviceLocator->getServiceLocator()->get('Swissbib\ExtendedSolrFactoryHelper');
		$this->defaultNamespace	= $extendedTargetHelper->getNamespace($name, $requestedName);

		return parent::canCreateServiceWithName($serviceLocator, $name, $requestedName);
	}



	/**
	 * Create a service for the specified name.
	 *
	 * @param	ServiceLocatorInterface $serviceLocator Service locator
	 * @param	String					$name           Name of service
	 * @param	String					$requestedName  Unfiltered name of service	 *
	 * @return	Params
	 */
	public function createServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
	{
		$options   = $serviceLocator->getServiceLocator()->get('Swissbib\SearchOptionsPluginManager')->get($requestedName);
		$className = $this->getClassName($name, $requestedName);
		return new $className(
			$options,
			$serviceLocator->getServiceLocator()->get('VuFind\Config'),
			$serviceLocator->getServiceLocator()->get('Swissbib\FavoriteInstitutions\Manager')
		);
	}
}

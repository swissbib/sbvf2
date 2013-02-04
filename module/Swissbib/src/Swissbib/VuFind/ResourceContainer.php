<?php
namespace Swissbib\VuFind;

use VuFindTheme\ResourceContainer as VfResourceContainer;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Config\Config;

class ResourceContainer extends VfResourceContainer implements ServiceLocatorAwareInterface {

	protected $services;

	/**
	 * @var	Array
	 */
	protected $ignoredFiles;

	public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
	{
		$this->services 	= $serviceLocator;
		$config				= new Config($serviceLocator->get('Config'));
		$this->ignoredFiles	= $config->swissbib->ignore_assets->toArray();
	}

	public function getServiceLocator()
	{
		return $this->services;
	}



	/**
	 * Remove ignored file before they're added to the resources
	 *
	 * @param	Array|String		$css
	 */
	public function addCss($css) {
		if (!is_array($css) && !is_a($css, 'Traversable')) {
			$css = array($css);
		}

		foreach($this->ignoredFiles as $ignorePattern) {
			foreach($css as $index => $file) {
				if( stristr($file, $ignorePattern) !== false ) {
						// File matches ignore pattern
					unset($css[$index]);
				}
			}
		}

		parent::addCss($css);
	}
}
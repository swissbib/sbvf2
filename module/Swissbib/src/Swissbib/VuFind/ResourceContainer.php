<?php
namespace Swissbib\VuFind;

use VuFindTheme\ResourceContainer as VfResourceContainer;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Config\Config;

class ResourceContainer extends VfResourceContainer implements ServiceLocatorAwareInterface
{

	/**
	 * @var    ServiceLocatorInterface
	 */
	protected $serviceLocator;


	/**
	 * @var    String[]        List of ignore patterns
	 */
	protected $ignoredFiles;



	/**
	 * Inject service locator
	 *
	 * @param    ServiceLocatorInterface $serviceLocator
	 */
	public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
	{
		$this->serviceLocator = $serviceLocator;
		$config               = new Config($serviceLocator->get('Config'));
		$this->ignoredFiles   = $config->swissbib->ignore_assets->toArray();
	}



	/**
	 * Get service locator
	 *
	 * @return    ServiceLocatorInterface
	 */
	public function getServiceLocator()
	{
		return $this->serviceLocator;
	}



	/**
	 * Remove ignored file before they're added to the resources
	 *
	 * @param    Array|String        $css
	 */
	public function addCss($css)
	{
		$css = $this->removeIgnoredFiles($css);

		parent::addCss($css);
	}



	/**
	 * Remove ignored files
	 *
	 * @param    Array|String|\Traversable    $css
	 * @return     Array|\Traversable
	 */
	protected function removeIgnoredFiles($css)
	{
		if (!is_array($css) && !is_a($css, 'Traversable')) {
			$css = array($css);
		}

		foreach ($this->ignoredFiles as $ignorePattern) {
			foreach ($css as $index => $file) {
				if (stristr($file, $ignorePattern) !== false) {
					// File matches ignore pattern
					unset($css[$index]);
				}
			}
		}

		return $css;
	}

}
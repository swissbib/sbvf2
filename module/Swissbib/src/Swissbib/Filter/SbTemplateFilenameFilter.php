<?php

/**
 * Prefix rendered output with HTML comment stating filename of the rendered template
 */

namespace Swissbib\Filter;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\Filter\AbstractFilter;

/**
 * Class SbTemplateFilenameFilter
 * @package Swissbib\Filter
 */
class SbTemplateFilenameFilter extends AbstractFilter implements ServiceLocatorAwareInterface
{
	protected $serviceLocator;

	/**
	 * @param	Mixed	$content
	 * @return	Mixed|String
	 */
	public function filter($content)
	{
		$sm		= $this->getServiceLocator();
		/** @var $phpRenderer \Zend\View\Renderer\PhpRenderer */
		$phpRenderer= $sm->get('Zend\View\Renderer\PhpRenderer');

			// Fetch private property PhpRenderer::__file via reflection
		$rendererReflection	= new \ReflectionObject($phpRenderer);

		$fileProperty	= $rendererReflection->getProperty('__file');
		$fileProperty->setAccessible(true);
		$templateFilename= $fileProperty->getValue($phpRenderer);

			// Remove possibly confidential server details from path
		$directoryDelimiter	= 'themes' . DIRECTORY_SEPARATOR;
		$templateFilename= substr($templateFilename, strpos($templateFilename, $directoryDelimiter) );

		return $this->wrapContentWithComment($content, $templateFilename, '');
	}

	/**
	 * @param $content
	 * @param $templateFilename
	 * @param string $type
	 * @return string
	 */
	private function wrapContentWithComment($content, $templateFilename, $type = '') {
		return
			"\n" . '<!-- Begin' . (!empty($type) ? ' ' . $type : '') . ': ' . $templateFilename . ' -->'
		.	"\n" . $content
		.	"\n" . '<!-- End: ' . $templateFilename . ' -->'
		.	"\n";
	}

	/**
	 * @param ServiceLocatorInterface $serviceLocator
	 */
	public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
	{
		$this->serviceLocator = $serviceLocator;
	}

	/**
	 * @return ServiceLocatorInterface
	 */
	public function getServiceLocator()
	{
		return $this->serviceLocator;
	}

}
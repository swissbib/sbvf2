<?php
namespace Swissbib\RecordDriver\Helper;

use Zend\Config\Config;

/**
 * [Description]
 *
 */
class LocationMap
{
	/** @var	Config  */
	protected $config;



	/**
	 * Initialize with config
	 *
	 * @param	Config	$config
	 */
	public function __construct(Config $config)
	{
		$this->config = $config;
	}



	/**
	 *
	 *
	 * @param	Array		$item
	 * @return	String|Boolean
	 */
	public function getLinkForItem(array $item)
	{
		if ($this->isItemValidForLocationMap($item)) {
			return $this->buildLocationMapLink($item);
		}

		return false;
	}



	protected function callInstitutionMethod($function, array $item)
	{
		$itemInstitution= strtolower($item['institution']);
		$customMethod	= $function . ucfirst($itemInstitution);
		$baseMethod		= $function;

		if (method_exists($this, $customMethod)) {
			return $this->$customMethod($item);
		}

		return $this->$baseMethod($item);
	}



	/**
	 * Check whether location map link should be shown
	 *
	 * @param	Array	$item
	 * @return	Boolean
	 */
	protected function isItemValidForLocationMap(array $item)
	{
		return $this->callInstitutionMethod('isItemValidForLocationMap', $item);
	}

	protected function buildLocationMapLink(array $item)
	{
		return $this->callInstitutionMethod('buildLocationMapLink', $item);
	}


	protected function isItemValidForLocationMapBase(array $item)
	{
		$itemInstitution		= strtolower($item['institution']);
		$hasSignature			= isset($item['signature']) && !empty($item['signature']) && $item['signature'] !== '-';
		$isInstitutionSupported	= $this->config->offsetExists($itemInstitution);

		return $isInstitutionSupported && $hasSignature;
	}


	protected function buildLocationMapLinkBase(array $item)
	{
		$itemInstitution= strtolower($item['institution']);
		$mapLinkPattern = $this->config->get($itemInstitution);
		$data           = array(
			'{PARAMS}' => urlencode($item['signature'])
		);

		return str_replace(array_keys($data), array_values($data), $mapLinkPattern);
	}

}

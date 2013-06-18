<?php
namespace Swissbib\RecordDriver\Helper;

use Zend\Config\Config;

use Swissbib\RecordDriver\Helper\Holdings as HoldingsHelper;

/**
 * Generate location map link depending on item data and configuration
 * This class allows you to implement custom behaviour per institution.
 * Add the institution code as postfix to the called methods.
 * Possible method names are:
 * - isItemValidForLocationMap
 * - buildLocationMapLink
 *
 * Example: isItemValidForLocationMapA100
 *
 */
class LocationMap
{

	/** @var    Config	Location map config defined in config_base.ini in section locationMap */
	protected $config;



	/**
	 * Initialize with config
	 *
	 * @param    Config $config
	 */
	public function __construct(Config $config)
	{
		$this->config = $config;
	}



	/**
	 * Get a link for an item
	 *
	 * @param	HoldingsHelper		$holdingsHelper
	 * @param	Array          		$item
	 * @return	String|Boolean
	 */
	public function getLinkForItem(HoldingsHelper $holdingsHelper, array $item)
	{
		if ($this->isItemValidForLocationMap($item, $holdingsHelper)) {
			return $this->buildLocationMapLink($item, $holdingsHelper);
		}

		return false;
	}



	/**
	 * Try to call an institution specific method or fall back to the base version if not implemented
	 *
	 * @param	String		$function		Function name
	 * @param	Array		$item
	 * @param	Holdings	$holdingsHelper
	 * @return	Mixed		The return value of the called method
	 */
	protected function callInstitutionMethod($function, array $item, HoldingsHelper $holdingsHelper)
	{
		$itemInstitution = strtolower($item['institution']);
		$customMethod    = $function . strtoupper($itemInstitution);
		$baseMethod      = $function . 'Base';
		$method          = method_exists($this, $customMethod) ? $customMethod : $baseMethod;

		return call_user_func(array($this, $method), $item, $holdingsHelper);
	}



	/**
	 * Parse values from data array into template string
	 *
	 * @param    String $string
	 * @param    Array  $data
	 * @return    String
	 */
	protected function parseTemplateString($string, array $data)
	{
		return str_replace(array_keys($data), array_values($data), trim($string));
	}



	/**
	 * Check whether value is defined in a comma separated config parameter
	 *
	 * @param    String $configKey
	 * @param    String $value
	 * @return    Boolean
	 */
	protected function isValueInConfigList($configKey, $value)
	{
		$configData = $this->config->get(strtolower($configKey));

		if ($configData) {
			$configValues = array_map('trim', explode(',', $configData));

			return in_array($value, $configValues);
		}

		return false;
	}



	/**
	 * Check whether location map link should be shown
	 *
	 *
	 * @param    Array          $item
	 * @param    HoldingsHelper $holdingsHelper
	 * @return    Boolean
	 */
	protected function isItemValidForLocationMap(array $item, HoldingsHelper $holdingsHelper)
	{
		return $this->callInstitutionMethod('isItemValidForLocationMap', $item, $holdingsHelper);
	}



	/**
	 * Build link for location map
	 *
	 * @param   Array           $item
	 * @param    HoldingsHelper $holdingsHelper
	 * @return    String|Boolean
	 */
	protected function buildLocationMapLink(array $item, HoldingsHelper $holdingsHelper)
	{
		return $this->callInstitutionMethod('buildLocationMapLink', $item, $holdingsHelper);
	}



	/**
	 * Fallback implementation
	 * By default, item is not valid for map link
	 *
	 * @param    Array    $item
	 * @param    Holdings $holdingsHelper
	 * @return    Boolean
	 */
	protected function isItemValidForLocationMapBase(array $item, HoldingsHelper $holdingsHelper)
	{
		return false;
	}



	/**
	 * Base method for map links
	 * Use as fallback if no custom implementation is available
	 *
	 * @param    Array    $item
	 * @param    Holdings $holdingsHelper
	 * @return    Boolean
	 */
	protected function buildLocationMapLinkBase(array $item, HoldingsHelper $holdingsHelper)
	{
		return false;
	}



	/**
	 * Build simple map link form link pattern and a value for PARAMS placeholder
	 * Use this if you don't need a very special behaviour
	 *
	 * @param	String		$mapLinkPattern
	 * @param	String		$paramsValue
	 * @return	String
	 */
	protected function buildSimpleLocationMapLink($mapLinkPattern, $paramsValue)
	{
		$data = array(
			'{PARAMS}' => urlencode($paramsValue)
		);

		return $this->parseTemplateString($mapLinkPattern, $data);
	}



	/**
	 * Check whether item should have a map link
	 * Customized for A100
	 *
	 * @param    Array    $item
	 * @param    Holdings $holdingsHelper
	 * @return    Boolean
	 */
	protected function isItemValidForLocationMapA100(array $item, HoldingsHelper $holdingsHelper)
	{
		$isItemAvailable      = true; // Implement availability check with holdings helper
		$hasSignature         = isset($item['signature']) && !empty($item['signature']) && $item['signature'] !== '-';
		$accessibleConfigKey  = $item['institution'] . '_codes';
		$isAccessible         = $this->isValueInConfigList($accessibleConfigKey, strtolower($item['location_code']));
		$circulatingConfigKey = $item['institution'] . '_status';
		$isCirculating		  = true;

			// Compare holding status if set
		if (isset($item['holding_status'])) {
			$isCirculating = $this->isValueInConfigList($circulatingConfigKey, $item['holding_status']);
		}

		return $isItemAvailable && $hasSignature && $isAccessible && $isCirculating;
	}



	/**
	 * Build location map link for A100
	 *
	 * @param    Array    $item
	 * @param    Holdings $holdingsHelper
	 * @return    String
	 */
	protected function buildLocationMapLinkA100(array $item, HoldingsHelper $holdingsHelper)
	{
		$mapLinkPattern  = $this->config->get('a100');

		return $this->buildSimpleLocationMapLink($mapLinkPattern, $item['signature']);
	}



	/**
	 * Custom validation check for B500
	 *
	 * @param    Array    $item
	 * @param    Holdings $holdingsHelper
	 * @return    Boolean
	 * @todo	Implement checks
	 */
	protected function isItemValidForLocationMapB500(array $item, HoldingsHelper $holdingsHelper)
	{
		return false;
	}



	/**
	 * Build custom link for B500
	 *
	 * @param    Array    $item
	 * @param    Holdings $holdingsHelper
	 * @return    Boolean
	 */
	protected function buildLocationMapLinkB500(array $item, HoldingsHelper $holdingsHelper)
	{
		$mapLinkPattern  = $this->config->get('b500');

		return $this->buildSimpleLocationMapLink($mapLinkPattern, $item['signature']);
	}



	/**
	 * Check if map link is possible
	 * Make sure signature is present
	 *
	 * @param	Array		$item
	 * @param	Holdings	$holdingsHelper
	 * @return	Boolean
	 */
	protected function isItemValidForLocationMapHSG(array $item, HoldingsHelper $holdingsHelper)
	{
		$hasSignature = isset($item['signature']) && !empty($item['signature']) && $item['signature'] !== '-';

		return $hasSignature;
	}



	/**
	 * Build custom link for HSG
	 *
	 * @param    Array    $item
	 * @param    Holdings $holdingsHelper
	 * @return    Boolean
	 */
	protected function buildLocationMapLinkHSG(array $item, HoldingsHelper $holdingsHelper)
	{
		$mapLinkPattern  = $this->config->get('hsg');
        $hsg_param = $item['location_code'] . ' ' . $item['signature'];

        return $this->buildSimpleLocationMapLink($mapLinkPattern, $hsg_param);

	}
}

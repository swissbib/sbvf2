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
class LocationMap extends LocationMapBase
{


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

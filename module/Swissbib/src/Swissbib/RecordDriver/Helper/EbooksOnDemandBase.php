<?php
namespace Swissbib\RecordDriver\Helper;

use Zend\Config\Config;
use Zend\I18n\Translator\Translator;

use Swissbib\RecordDriver\SolrMarc;

/**
 * Contains all general base methods for ebooks on demand handlings
 *
 */
class EbooksOnDemandBase extends CustomizedMethods
{
	/** @var  Translator */
	protected $translator;



	/**
	 * Initialize
	 *
	 * @param	Config		$eBooksOnDemandConfig
	 * @param	Translator	$translator
	 */
	public function __construct(Config $eBooksOnDemandConfig, Translator $translator)
	{
		parent::__construct($eBooksOnDemandConfig);

		$this->translator	= $translator;
	}



	/**
	 * Get an ebooks on demand link for item depending on custom checks and building methods
	 *
	 * @param	Array		$item
	 * @param	SolrMarc	$recordDriver
	 * @param	Holdings	$holdingsHelper
	 * @return	String|Boolean
	 */
	public function getEbooksOnDemandLink(array $item, SolrMarc $recordDriver, Holdings $holdingsHelper)
	{
		if ($this->isValidForLink($item, $recordDriver, $holdingsHelper)) {
			return $this->buildLink($item, $recordDriver, $holdingsHelper);
		}

		return false;
	}



	/**
	 * Base method for link validity check
	 *
	 * @param	Array		$item
	 * @param	SolrMarc	$recordDriver
	 * @param	Holdings	$holdingsHelper
	 * @return	Boolean
	 */
	protected function isValidForLink(array $item, SolrMarc $recordDriver, Holdings $holdingsHelper)
	{
		return $this->callMethod('isValidForLink', $item['institution'], array($item, $recordDriver, $holdingsHelper));
	}



	/**
	 * Base method to call custom link build methods for institutions
	 *
	 * @param	Array		$item
	 * @param	SolrMarc	$recordDriver
	 * @param	Holdings	$holdingsHelper
	 * @return	String
	 */
	protected function buildLink(array $item, SolrMarc $recordDriver, Holdings $holdingsHelper)
	{
		return $this->callMethod('buildLink', $item['institution'], array($item, $recordDriver, $holdingsHelper));
	}



	/**
	 * Check whether link is active for institution without custom implementation
	 *
	 * @param	Array		$item
	 * @param	SolrMarc	$recordDriver
	 * @param	Holdings	$holdingsHelper
	 * @return	Boolean
	 */
	protected function isValidForLinkBase(array $item, SolrMarc $recordDriver, Holdings $holdingsHelper)
	{
		return false;
	}



	/**
	 * Build link for all institutions which don't have a custom implementation
	 *
	 * @param	Array		$item
	 * @param	SolrMarc	$recordDriver
	 * @param	Holdings	$holdingsHelper
	 * @return	Boolean|String
	 */
	protected function buildLinkBase(array $item, SolrMarc $recordDriver, Holdings $holdingsHelper)
	{
		return false;
	}



	/**
	 * Check whether year is in configured range
	 * Support full and half ranges
	 * Ex: 1500-1900, 1500-, -1900
	 *
	 * @param	String		$institutionCode
	 * @param	Integer		$year
	 * @return	Boolean
	 */
	protected function isYearInRange($institutionCode, $year)
	{
		$year			= intval($year);
		$customConfigKey= strtolower($institutionCode) . '_range';

		if ($this->hasConfigValue($customConfigKey)) {
			$range = $this->config->get($customConfigKey);
		} elseif ($this->hasConfigValue('range')) {
			$range = $this->config->get('range');
		} else {
			$range = false;
		}

		if ($range !== false) {
			list($rangeStart, $rangeEnd) = array_map('intval', explode('-', trim($range)));

			if ($rangeStart && $rangeStart > $year) {
				return false;
			}
			if ($rangeEnd && $rangeEnd < $year) {
				return false;
			}

			return true; // No check failed
		}

		return true; // No range at all
	}



	/**
	 * Is institution supported
	 * Check whether a link pattern is defined
	 *
	 * @param	String		$institutionCode
	 * @return	Boolean
	 */
	protected function isSupportedInstitution($institutionCode)
	{
		$configKey	= strtolower($institutionCode) . '_link';

		return $this->hasConfigValue($configKey);
	}



	/**
	 * Check whether formats are supported
	 * The config only needs to contain the starting part of the format
	 *
	 * @param	String		$institutionCode
	 * @param	String[]	$itemFormats
	 * @return	Boolean
	 */
	protected function isSupportedFormat($institutionCode, array $itemFormats)
	{
		$customConfigKey= strtolower($institutionCode) . '_formats';

		if (sizeof($itemFormats) === 0) {
			return false;
		}

		if ($this->hasConfigValue($customConfigKey)) {
			$configFormats  = $this->getConfigList($customConfigKey);
		} elseif ($this->hasConfigValue('formats')) {
			$configFormats = $this->getConfigList('formats');
		} else {
			$configFormats = false;
		}

		if ($configFormats !== false) {
			foreach ($configFormats as $configFormat) {
				foreach ($itemFormats as $itemFormat) {
					if (stripos($itemFormat, $configFormat) === 0) {
						return true; // Starts with
					}
				}
			}

			return false; // None of the formats matched
		}

		return true; // No formats defined
	}



	/**
	 * Get converted language
	 * Language is current selection of user. Converts are defined in config by lang_de = GER
	 *
	 * @return	String
	 */
	protected function getConvertedLanguage()
	{
		$userLanguage	= $this->translator->getLocale();

		return $this->hasConfigValue('lang_' . $userLanguage) ? $this->config->get('lang_' . $userLanguage) : 'GER';
	}



	/**
	 * Check whether configured stop words are part of the compare string
	 *
	 * @param	String		$institutionCode
	 * @param	String		$compareString
	 * @return	Boolean
	 */
	protected function hasStopWords($institutionCode, $compareString)
	{
		$customConfigKey	= $institutionCode . '_stopwords';

		if ($this->hasConfigValue($customConfigKey)) {
			$stopWords  = $this->getConfigList($customConfigKey);
		} elseif ($this->hasConfigValue('formats')) {
			$stopWords = $this->getConfigList('formats');
		} else {
			$stopWords = false;
		}

		if ($stopWords !== false) {
			foreach ($stopWords as $stopWord) {
				if (stripos($compareString, $stopWord) !== false) {
					return true;
				}
			}
		}

		return false;
	}



	/**
	 * Get link pattern
	 *
	 * @param	String		$institutionCode
	 * @return	String
	 */
	protected function getLinkPattern($institutionCode)
	{
		return $this->getConfigValue(strtolower($institutionCode) . '_link');
	}
}

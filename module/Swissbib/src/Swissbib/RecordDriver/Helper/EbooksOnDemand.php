<?php
namespace Swissbib\RecordDriver\Helper;

use Zend\Config\Config;
use Zend\I18n\Translator\Translator;

use Swissbib\RecordDriver\SolrMarc;

/**
 * Build ebook links depending on institution configuration
 * Config in config_base.ini[eBooksOnDemand]
 *
 */
class EbooksOnDemand extends EbooksOnDemandBase
{

	/**
	 * Check whether A100 item is valid for EOD link
	 *
	 * @param	Array		$item
	 * @param	SolrMarc	$recordDriver
	 * @param	Holdings	$holdingsHelper
	 * @return	Boolean
	 */
	protected function isValidForLinkA100(array $item, SolrMarc $recordDriver, Holdings $holdingsHelper)
	{
		$institutionCode	= strtolower($item['institution']);
		list(,$publishYear) = $recordDriver->getPublicationDates();
		$itemFormats		= $recordDriver->getFormatsRaw();

		$isYearInRange			= $this->isYearInRange($institutionCode, $publishYear);
		$isSupportedInstitution	= $this->isSupportedInstitution($institutionCode);
		$isSupportedFormat		= $this->isSupportedFormat($institutionCode, $itemFormats);
		$hasNoStopWords			= $this->hasStopWords($institutionCode, $item['holding_information']) === false;

		return $isYearInRange && $isSupportedInstitution && $isSupportedFormat && $hasNoStopWords;
	}



	/**
	 * Build EOD link for A100 item
	 *
	 * @param	Array		$item
	 * @param	SolrMarc	$recordDriver
	 * @param	Holdings	$holdingsHelper
	 * @return	String
	 */
	protected function buildLinkA100(array $item, SolrMarc $recordDriver, Holdings $holdingsHelper)
	{
		$linkPattern	= $this->getLinkPattern($item['institution']);
		$data	= array(
			'SYSID'			=> $item['bibsysnumber'],
			'INSTITUTION'	=> urlencode($item['institution'] . $item['signature']),
			'LANGUAGE'		=> $this->getConvertedLanguage()
		);

		return $this->templateString($linkPattern, $data);
	}



	/**
	 * Check whether B400 item is valid for EOD link
	 *
	 * @param	Array		$item
	 * @param	SolrMarc	$recordDriver
	 * @param	Holdings	$holdingsHelper
	 * @return	Boolean
	 */
	protected function isValidForLinkB400(array $item, SolrMarc $recordDriver, Holdings $holdingsHelper)
	{
			// Works the same way, just forward to A100. But use B400 as institution code
		return $this->isValidForLinkA100($item, $recordDriver, $holdingsHelper);
	}



	/**
	 * Build EOD link for B400 item
	 *
	 * @param	Array		$item
	 * @param	SolrMarc	$recordDriver
	 * @param	Holdings	$holdingsHelper
	 * @return	String
	 */
	protected function buildLinkB400(array $item, SolrMarc $recordDriver, Holdings $holdingsHelper)
	{
			// Works the same way, just forward to A100. But use B400 as institution code
		return $this->buildLinkA100($item, $recordDriver, $holdingsHelper);
	}



	protected function isValidForLinkZ01(array $item, SolrMarc $recordDriver, Holdings $holdingsHelper)
	{
		return true; // always show the link - maybe change this
	}



	/**
	 * Build EOD link for B400 item
	 *
	 * @param	Array		$item
	 * @param	SolrMarc	$recordDriver
	 * @param	Holdings	$holdingsHelper
	 * @return	String
	 */
	protected function buildLinkZ01(array $item, SolrMarc $recordDriver, Holdings $holdingsHelper)
	{
		$linkPattern	= $this->getLinkPattern($item['institution']);
		$data	= array(
			'SYSID'		=> $item['bibsysnumber'],
			'CALLNUM'	=> urlencode('(' . $item['network'] . ') ' .  $item['signature'])
		);

		return $this->templateString($linkPattern, $data);
	}
}

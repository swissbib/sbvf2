<?php

/**
 * swissbib / VuFind <<full descriptive name of the class>>
 *
 * PHP version 5
 *
 * Copyright (C) project swissbib, University Library Basel, Switzerland
 * http://www.swissbib.org  / http://www.swissbib.ch / http://www.ub.unibas.ch
 *
 * Date: 2/7/13
 * Time: 9:02 PM
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 2,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @category swissbib_VuFind2
 * @package  <<name of package>>
 * @author   Guenter Hipler  <guenter.hipler@unibas.ch>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     << link to further documentation related to this resource type (Wiki, tracker ...)
 */

namespace Swissbib\RecordDriver\Helper;

use Zend\Config\Config;
use Zend\I18n\Translator\Translator;

use VuFind\Crypt\HMAC;
use VuFind\ILS\Connection as IlsConnection;
use VuFind\Auth\Manager as AuthManager;
use VuFind\Config\PluginManager as ConfigManager;

use Swissbib\VuFind\ILS\Driver\Aleph;
use Swissbib\RecordDriver\SolrMarc;

/**
 * probably Holdings should be a subtype of ZF2 AbstractHelper
 *at first I need a better understanding how things are wired up in this case using means of ZF2
 */
class Holdings
{

	/** @var	IlsConnection	Receive more data from server */
	protected $ils;

	/** @var    AuthManager		Check login status and info */
	protected $authManager;

	/** @var	ConfigManager	Load configurations */
	protected $configManager;

	/** @var    \File_MARC_Record */
	protected $holdings;

	/** @var    String        Parent item */
	protected $idItem;

	/** @var    Array    HMAC keys for ILS */
	protected $hmacKeys = array();

	/**
	 * @var    Array    Map of fields to named params
	 */
	protected $fieldMapping = array(
		'0' => 'local_branch_expanded',
		'1' => 'location_expanded',
		'a' => 'holding_information',
		'B' => 'network',
		'b' => 'institution',
		'C' => 'adm_code',
		'c'	=> 'location_code',
		'E' => 'bibsysnumber',
		'j' => 'signature',
		'o' => 'staff_note',
		'p' => 'barcode',
		'q' => 'localid',
		'r' => 'sequencenumber',
		's' => 'signature2',
		'y' => 'opac_note'
	);

	/**
	 * @var    Array[]|Boolean
	 */
	protected $holdingData = false;

	/** @var	Array[]|Boolean		Holding structure without data */
	protected $holdingStructure = false;

	/**
	 * @var    Array[]    List of availabilities per item and barcode
	 */
	protected $availabilities = array();

	/**
	 * @var    Array[]    List of network domains and libs
	 */
	protected $networks = array();

	/**
	 * @var    Config
	 */
	protected $configHoldings;

	/**
	 * @var    HMAC
	 */
	protected $hmac;

	/**
	 * @var    Array		Mapping from institutions to groups
	 */
	protected $institution2group = array();

	/**
	 * @var		Array
	 */
	protected $groupSorting = array();

	/**
	 * @var		Translator
	 */
	protected $translator;



	/**
	 * Initialize helper with dependencies
	 *
	 * @param    IlsConnection         $ilsConnection
	 * @param    HMAC                  $hmac
	 * @param    AuthManager           $authManager
	 * @param    ConfigManager         $configManager
	 * @param    Translator            $translator
	 * @throws    \Exception
	 */
	public function __construct(
					IlsConnection $ilsConnection,
					HMAC $hmac,
					AuthManager $authManager,
					ConfigManager $configManager,
					Translator $translator
	) {
		$this->ils            = $ilsConnection;
		$this->configManager  = $configManager;
		$this->configHoldings = $configManager->get('Holdings');
		$this->hmac           = $hmac;
		$this->authManager    = $authManager;
		$this->translator     = $translator;


		/** @var Config $relationConfig */
		$relationConfig			= $configManager->get('libadmin-groups');

			// Just ignore missing config to prevent a crashing frontend
		if ($relationConfig->count() !== null) {
			$this->institution2group = $relationConfig->institutions->toArray();
			$this->groupSorting      = $relationConfig->groups->toArray();
		} elseif (APPLICATION_ENV == 'development') {
			throw new \Exception('Missing config file libadmin-groups.ini. Run libadmin sync to solve this problem');
		}

		$holdsIlsConfig = $this->ils->checkFunction('Holds');
		$this->hmacKeys = $holdsIlsConfig['HMACKeys'];

		$this->initNetworks();
	}



	/**
	 * Initialize for item
	 *
	 * @param    String        $idItem
	 * @param    String        $holdingsXml
	 */
	public function setData($idItem, $holdingsXml = '')
	{
		$this->idItem = $idItem;

		$this->setHoldingsContent($holdingsXml);
	}



	/**
	 * Get holdings data
	 *
	 * @param		String		$institutionCode
	 * @param		SolrMarc	$recordDriver
	 * @return    Array[]|Boolean            Contains lists for items and holdings {items=>[],holdings=>[]}
	 */
	public function getHoldings(SolrMarc $recordDriver, $institutionCode)
	{
		if ($this->holdingData === false) {
			$this->holdingData = array();

			if ($this->hasItems()) {
				$this->holdingData['items'] = $this->getItemsData($recordDriver, $institutionCode);
			} elseif ($this->hasHoldings()) {
				$this->holdingData['holdings'] = $this->getHoldingData($recordDriver, $institutionCode);
			}
		}

		return $this->holdingData;
	}



	/**
	 * Get holdings structure grouped by group and institution
	 *
	 * @return Array|\Array[]|bool
	 */
	public function getHoldingsStructure()
	{
		if ($this->holdingStructure === false) {
			$holdingsData = $this->getStructuredHoldingsStructure(852);
			$itemsData    = $this->getStructuredHoldingsStructure(949);

			// Merge items and holding into the same network/institution structure
			// (stays separated by items/holdings key at lowest level)
			$merged             	= $this->mergeHoldings($holdingsData, $itemsData);
			$this->holdingStructure	= $this->sortHoldings($merged);

		}

		return $this->holdingStructure;
	}



	/**
	 * Sort holdings by group based on position in $this->groupSorting
	 * Sort institutions based on position in institution2group
	 *
	 * @param	Array	$holdings
	 * @return	Array
	 */
	protected function sortHoldings(array $holdings)
	{
		$sortedHoldings	= array();

			// Add holdings in sorted order
		foreach ($this->groupSorting as $groupCode) {
			if (isset($holdings[$groupCode])) {
				if (sizeof($this->institution2group)) {
					$sortedInstitutions = array();

					foreach ($this->institution2group as $institutionCode => $groupCodeAgain) {
						if (isset($holdings[$groupCode]['institutions'][$institutionCode])) {
							$sortedInstitutions[$institutionCode] = $holdings[$groupCode]['institutions'][$institutionCode];
						}
					}
				} else {
						// No sorting available, just use available data
					$sortedInstitutions = $holdings[$groupCode]['institutions'];
				}


					// Add group to sorted list
				$sortedHoldings[$groupCode] = $holdings[$groupCode];
					// Add sorted institution list
				$sortedHoldings[$groupCode]['institutions'] = $sortedInstitutions;

					// Remove group
				unset($holdings[$groupCode]);
			}
		}

			// Add all the others (missing data because of misconfiguration?)
		foreach ($holdings as $groupCode => $group) {
			$sortedHoldings[$groupCode] = $group;
		}

		return $sortedHoldings;
	}




	/**
	 * Merge two arrays. Extend sub arrays or add missing elements,
	 * but don't extend existing scalar values (as array_merge_recursive() does)
	 *
	 * @param	Array	$resultData
	 * @param	Array	$newData
	 * @return	Array
	 */
	protected function mergeHoldings(array $resultData, array $newData)
	{
		foreach ($newData as $newKey => $newValue) {
			if (!isset($resultData[$newKey])) {
				$resultData[$newKey] = $newValue;
			} elseif (is_array($resultData[$newKey])) {
				$resultData[$newKey] = $this->mergeHoldings($resultData[$newKey], $newValue);
			}
			// else = Already existing scalar value => ignore (keep first items data)
		}

		return $resultData;
	}



	/**
	 * Initialize networks from config
	 *
	 */
	protected function initNetworks()
	{
		$networkNames = array('aleph', 'virtua');

		foreach ($networkNames as $networkName) {
			$configName = ucfirst(strtolower($networkName)) . 'Networks';

			/** @var Config $networkConfigs */
			$networkConfigs = $this->configHoldings->get($configName);

			foreach ($networkConfigs as $networkCode => $networkConfig) {
				list($domain, $library) = explode(',', $networkConfig, 2);
				$networkCode	= strtolower($networkCode);

				$this->networks[$networkCode] = array(
					'domain'  => $domain,
					'library' => $library,
					'type'    => $networkName
				);
			}
		}
	}



	/**
	 * Set holdings structure
	 *
	 * @param    String        $holdingsXml
	 * @throws    \File_MARC_Exception
	 */
	protected function setHoldingsContent($holdingsXml)
	{
		if (is_string($holdingsXml) && strlen($holdingsXml) > 30) {
			$holdingsMarcXml = new \File_MARCXML($holdingsXml, \File_MARCXML::SOURCE_STRING);
			$marcData        = $holdingsMarcXml->next();

			if (!$marcData) {
				throw new \File_MARC_Exception('Cannot Process Holdings Structure');
			}

			$this->holdings = $marcData;
		} else {
			// Invalid input data. Currently just ignore it
			$this->holdings = false;
			$this->holdingData = array();
		}
	}



	/**
	 * Get holding items for an institution
	 *
	 * @param	SolrMarc	$recordDriver
	 * @param    String $institutionCode
	 * @return    Array   Institution Items
	 */
	protected function getItemsData(SolrMarc $recordDriver, $institutionCode)
	{
		$fieldName          = 949; // Field code for item information in holdings xml
		$institutionItems = $this->geHoldingsData($fieldName, $this->fieldMapping, $institutionCode);

		foreach ($institutionItems as $index => $item) {
				// Add extra information for item
			$institutionItems[$index] = $this->extendItem($item, $recordDriver);
		}

		return $institutionItems;
	}



	/**
	 * Check whether network is supported
	 *
	 * @param    String        $networkCode
	 * @return    Boolean
	 */
	protected function isRestfulNetwork($networkCode)
	{
		$networkCode = strtolower($networkCode);

		return isset($this->configHoldings->Restful->{$networkCode});
	}



	/**
	 * Extend item with additional informations
	 *
	 * @param	Array		$item
	 * @param	SolrMarc	$recordDriver
	 * @return	Array
	 */
	protected function extendItem(array $item, SolrMarc $recordDriver = null)
	{
		$item	= $this->extendItemBasic($item, $recordDriver);
		$item	= $this->extendItemIlsActions($item, $recordDriver);

		return $item;
	}



	/**
	 * Extend item with basic infos
	 * - Ebooks on Demand Link
	 * - Location map
	 *
	 * @param	Array		$item
	 * @param	SolrMarc	$recordDriver
	 * @return	Array
	 */
	protected function extendItemBasic(array $item, SolrMarc $recordDriver = null)
	{
			// EOD LINK
		$item['eodlink']	= $this->getEODLink($item, $recordDriver);
			// Location Map Link
		$item['locationMap']= $this->getLocationMapLink($item);
			// Location label
		$item['locationLabel'] = $this->getLocationLabel($item);

		if (!$this->isRestfulNetwork($item['network'])) {
			$item['backlink'] = $this->getBackLink($item['network'], strtoupper($item['institution']), $item);
		}

		return $item;
	}


	/**
	 * Extend item with action links based on ILS
	 *
	 * @param	Array    	$item
	 * @param	SolrMarc	$recordDriver
	 * @return  Array
	 */
	protected function extendItemIlsActions(array $item, SolrMarc $recordDriver = null)
	{
		$networkCode	= isset($item['network']) ? strtolower($item['network']) : '';

			// Only add links for supported networks
		if ($this->isAlephNetwork($networkCode) && $this->isRestfulNetwork($networkCode)) {
			// Add hold link for item
			$item['holdLink'] = $this->getHoldLink($item);

			// Add availability if supported by network
			$item['availability'] = $this->getAvailabilityInfos($item['bibsysnumber'], $item['barcode']);
			$item['isAvailable']  = $this->isAvailable($item['bibsysnumber'], $item['barcode']);

			if ($this->isLoggedIn()) {
				$item['userActions'] = $this->getAllowedUserActions($item);
			}
		}


		return $item;
	}



	/**
	 * Extend holding with additional informations
	 *
	 * @param	Array			$holding
	 * @param	SolrMarc		$recordDriver
	 * @return	Array
	 */
	protected function extendHolding(array $holding, SolrMarc $recordDriver = null)
	{
		$holding	= $this->extendHoldingBasic($holding, $recordDriver);
//		$holding	= $this->extendHoldingIlsActions($holding, $recordDriver); // NOT USED AT THE MOMENT

		return $holding;
	}



	/**
	 *  Extend holding with basic infos
	 * - Location map
	 *
	 * @param	Array    	$holding
	 * @param	SolrMarc	$recordDriver
	 * @return	Array
	 */
	protected function extendHoldingBasic(array $holding, SolrMarc $recordDriver = null)
	{
			// Location Map Link
		$holding['locationMap'] = $this->getLocationMapLink($holding);
			// Location label
		$holding['locationLabel'] = $this->getLocationLabel($holding);

			// Add backlink for not restful networks
		if (!$this->isRestfulNetwork($holding['network'])) {
			$holding['backlink'] = $this->getBackLink($holding['network'], strtoupper($holding['institution']), $holding);
		}

		return $holding;
	}



	/**
	 * Add action links to holding item
	 *
	 * @param	Array		$holding
	 * @param	SolrMarc	$recordDriver
	 * @return	Array
	 */
	protected function extendHoldingIlsActions(array $holding, SolrMarc $recordDriver = null)
	{

		return $holding;
	}



	/**
	 * Build an EOD link if possible
	 * Return false if item does not support EOD links
	 *
	 * @param	Array    	$item
	 * @param	SolrMarc	$recordDriver
	 * @return	String|Boolean
	 */
	protected function getEODLink(array $item, SolrMarc $recordDriver = null)
	{
		$eodLink = false;

		if ($recordDriver instanceof SolrMarc) {
			list(,$publishYear,) = $recordDriver->getPublicationDates();
			$formats			 = $recordDriver->getFormatsRaw();

			if ($this->isValidForEodLink($publishYear, $item['institution'], $formats)) {
				$eodLink = $this->buildEODLink($item['localid'], $item['institution'], $item['signature']);
			}
		}

		return $eodLink;
	}



	/**
	 * Build EOD link string
	 *
	 * @param	String		$sysId
	 * @param	String		$institution
	 * @param	String		$signature
	 * @return	String
	 */
	protected function buildEODLink($sysId, $institution, $signature)
	{
		$bibId			= strtolower($this->configManager->get('Aleph')->Catalog->bib);
		$instId			= urlencode($institution . $signature);
		$userLanguage	= $this->translator->getLocale();
		/** @var Config $eodConfig */
		$eodConfig		= $this->configManager->get('config')->get('eBooksOnDemand');
		$linkPattern	= $eodConfig->link;
		$language		= $eodConfig->offsetExists('lang_' . $userLanguage) ? $eodConfig->get('lang_' . $userLanguage) : 'GER';

		$data	= array(
			'{SID}'			=> $bibId,
			'{SYSID}'		=> $sysId,
			'{INSTITUTION}'	=> $instId,
			'{LANGUAGE}'	=> $language
		);

		return str_replace(array_keys($data), array_values($data), $linkPattern);
	}



	/**
	 * Check whether conditions for EOD link match for item properties
	 *
	 * @param	Integer		$publishYear
	 * @param	String		$institution
	 * @param	String[]	$formats
	 * @return	Boolean
	 */
	protected function isValidForEodLink($publishYear, $institution, array $formats)
	{
		$eodConfig				= $this->configManager->get('config')->get('eBooksOnDemand');
		$maxYear				= $eodConfig->maxYear;
		$supportedInstitutions	= array_map('strtolower', array_map('trim', explode(',', $eodConfig->institutions)));
		$supportedFormats		= array_map('strtolower', array_map('trim', explode(',', $eodConfig->formats)));
		$itemInstitution		= strtolower($institution);
		$itemFormats			= array_map('strtolower', $formats);

		if ($publishYear <= $maxYear) { // Before year?
			if (in_array($itemInstitution, $supportedInstitutions)) { // Supported institution?
				if (sizeof(array_intersect($itemFormats, $supportedFormats)) > 0) { // Supported format?
					return true;
				}
			}
		}

		return false;
	}



	/**
	 * Build location map link
	 * Return false in case institution is not enable for mapping
	 *
	 * @param	Array		$item
	 * @return	String|Boolean
	 */
	protected function getLocationMapLink(array $item)
	{
		if ($this->isItemValidForLocationMapLink($item)) {
			$mapConfig 		= $this->getLocationMapConfig();
			$itemInstitution= strtolower($item['institution']);
			$mapLinkPattern = $mapConfig->get($itemInstitution);
			$data           = array(
				'{PARAMS}' => urlencode($item['signature'])
			);

			return str_replace(array_keys($data), array_values($data), $mapLinkPattern);
		}

		return false;
	}



	/**
	 * Check whether location map link should be shown
	 *
	 * @param	Array	$item
	 * @return	Boolean
	 */
	protected function isItemValidForLocationMapLink(array $item)
	{
		$mapConfig				= $this->getLocationMapConfig();
		$itemInstitution		= strtolower($item['institution']);
		$hasSignature			= isset($item['signature']) && !empty($item['signature']) && $item['signature'] !== '-';
		$isInstitutionSupported	= $mapConfig->offsetExists($itemInstitution);

		return $isInstitutionSupported && $hasSignature;
	}



	/**
	 * @return	Config
	 */
	protected function getLocationMapConfig()
	{
		return $this->configManager->get('config')->locationMap;
	}



	/**
	 * Get location label
	 * Try to translate. Fallback to index data
	 *
	 * @param	Array	$item
	 * @return	String
	 */
	protected function getLocationLabel(array $item)
	{
		$label = '';

			// Has informations with translation?
		if (isset($item['location_code']) && isset($item['institution']) && isset($item['network'])) {
			$labelKey	= strtolower($item['institution'] . '_' . $item['location_code']);
			$textDomain	= 'location-' . strtolower($item['network']);
			$translated	= $this->translator->translate($labelKey, $textDomain);

			if ($translated !== $labelKey) {
				$label = $translated;
			}
		}

			// Use expanded label or code as fallback
		if (empty($label)) {
			if (isset($item['location_expanded'])) {
				$label = trim($item['location_expanded']);
			} elseif (isset($item['location_code'])) {
				$label = trim($item['location_code']);
			}
		}

		return $label;
	}


	/**
	 * Get list of allowed actions for the current user
	 *
	 * @param    Array        $item
	 * @return    Array
	 */
	protected function getAllowedUserActions($item)
	{
		/** @var Aleph $ilsDriver */
		$ilsDriver = $this->ils->getDriver();
		$patron    = $this->getPatron();

		$itemId  = $item['localid'] . $item['sequencenumber'];
		$groupId = $this->buildItemId($item);

		$allowedActions = $ilsDriver->getAllowedActionsForItem($patron['id'], $itemId, $groupId);
		$host           = $ilsDriver->host; // @todo make dev port dynamic

		if ($allowedActions['photocopyRequest']) {
			$allowedActions['photocopyRequestLink'] = $this->getPhotoCopyRequestLink($host, $item);
		}

		return $allowedActions;
	}



	/**
	 * Get link for external photocopy request
	 *
	 * @todo    refactor
	 * @param    String        $host
	 * @param    Array         $item
	 * @return    String
	 */
	protected function getPhotoCopyRequestLink($host, array $item)
	{
		$queryParams = array(
			'func'           => 'item-photo-request',
			'doc_library'    => $item['adm_code'],
			'adm_doc_number' => $item['localid'],
			'item_sequence'  => $item['sequencenumber'],
			'bib_doc_num'    => $item['bibsysnumber'],
			'bib_library'    => 'DSV01'
		);

		return 'http://' . $host . '/F/?' . http_build_query($queryParams);
	}



	/**
	 * Check whether user is logged in
	 *
	 * @return    Boolean
	 */
	protected function isLoggedIn()
	{
		return $this->authManager->isLoggedIn() !== false;
	}



	/**
	 * Get patron (catalog login data)
	 *
	 * @return    Array
	 */
	protected function getPatron()
	{
		return $this->authManager->storedCatalogLogin();
	}



	/**
	 * Check whether network uses aleph system
	 *
	 * @param    String        $network
	 * @return    Boolean
	 */
	protected function isAlephNetwork($network)
	{
		$network	= strtolower($network);

		return isset($this->networks[$network]) ? $this->networks[$network]['type'] === 'aleph' : false;
	}



	/**
	 * Get a back link
	 * Check first if a custom type is defined for this network
	 * Fallback to network default
	 *
	 * @param    String        $networkCode
	 * @param    String        $institutionCode
	 * @param    Array         $item
	 * @return    Boolean
	 */
	protected function getBackLink($networkCode, $institutionCode, array $item)
	{
		$method      = false;
		$data        = array();
		$networkCode = strtolower($networkCode);

		if (isset($this->configHoldings->Backlink->{$networkCode})) { // Has the network its own backlink type
			$method = 'getBackLink' . ucfirst($networkCode);
			$data   = array(
				'pattern' => $this->configHoldings->Backlink->{$networkCode}
			);
		} else { // no custom type
			if (isset($this->networks[$networkCode])) { // is network even configured?
				$networkType= strtolower($this->networks[$networkCode]['type']);
				$method 	= 'getBackLink' . ucfirst($networkType);

				// Has the network type (aleph, virtua, etc) a general link?
				if (isset($this->configHoldings->Backlink->$networkType)) {
					$data = array(
						'pattern' => $this->configHoldings->Backlink->$networkType
					);
				}
			}
		}

		// Merge in network data if available
		if (isset($this->networks[$networkCode])) {
			$data = array_merge($this->networks[$networkCode], $data);
		}

		// Is a matching method available?
		if ($method && method_exists($this, $method)) {
			return $this->{$method}($networkCode, $institutionCode, $item, $data);
		}

		return false;
	}



	/**
	 * Get back link for aleph
	 *
	 * @param    String        $networkCode
	 * @param    String        $institutionCode
	 * @param    Array         $item
	 * @param    Array         $data
	 * @return    String
	 */
	protected function getBackLinkAleph($networkCode, $institutionCode, $item, array $data)
	{
		$values = array(
			'server'                => $data['domain'],
			'bib-library-code'      => $data['library'],
			'bib-system-number'     => $item['bibsysnumber'],
			'aleph-sublibrary-code' => $institutionCode
		);

		return $this->compileString($data['pattern'], $values);
	}



	/**
	 * Get back link for virtua
	 *
	 * @todo    Get user language
	 * @param    String        $networkCode
	 * @param    String        $institutionCode
	 * @param    Array         $item
	 * @param    Array         $data
	 * @return    String
	 */
	protected function getBackLinkVirtua($networkCode, $institutionCode, $item, array $data)
	{
		$values = array(
			'server'            => $data['domain'],
			'language-code'     => 'de', // @todo fetch from user
			'bib-system-number' => $this->getNumericString($item['bibsysnumber']) // remove characters from number string
		);

		return $this->compileString($data['pattern'], $values);
	}



	/**
	 * Get back link for alexandria
	 * Currently only a wrapper for virtua
	 *
	 * @param    String        $networkCode
	 * @param    String        $institutionCode
	 * @param    Array         $item
	 * @param    Array         $data
	 * @return    String
	 */
	protected function getBackLinkAlexandria($networkCode, $institutionCode, array $item, array $data)
	{
		return $this->getBackLinkVirtua($networkCode, $institutionCode, $item, $data);
	}



	/**
	 * Get back link for SNL
	 * Currently only a wrapper for virtua
	 *
	 * @param    String        $networkCode
	 * @param    String        $institutionCode
	 * @param    Array         $item
	 * @param    Array         $data
	 * @return    String
	 */
	protected function getBackLinkSNL($networkCode, $institutionCode, $item, array $data)
	{
		return $this->getBackLinkVirtua($networkCode, $institutionCode, $item, $data);
	}



	/**
	 * Build rero backlink
	 *
	 * @param       $networkCode
	 * @param       $institutionCode
	 * @param       $item
	 * @param array $data
	 * @return mixed
	 */
	protected function getBackLinkRERO($networkCode, $institutionCode, $item, array $data)
	{
		$values = array(
			'server'            => $data['domain'],
			'language-code'     => 'de', // @todo fetch from user,
			'RERO-network-code' => substr($institutionCode, 0, 2), // first two characters should do it. not sure
			'bib-system-number' => $this->getNumericString($item['bibsysnumber']), // remove characters from number string
			'sub-library-code'  => $institutionCode
		);

		return $this->compileString($data['pattern'], $values);
	}



	/**
	 * Compile string. Replace {varName} pattern with names and data from array
	 *
	 * @param    String        $string
	 * @param    Array         $keyValues
	 * @return    String
	 */
	protected function compileString($string, array $keyValues)
	{
		$newKeyValues = array();

		foreach ($keyValues as $key => $value) {
			$newKeyValues['{' . $key . '}'] = $value;
		}

		return str_replace(array_keys($newKeyValues), array_values($newKeyValues), $string);
	}



	/**
	 * Remove all not-numeric parts from string
	 *
	 * @param    String        $string
	 * @return    String
	 */
	protected function getNumericString($string)
	{
		return preg_replace('[\D]', '', $string);
	}



	/**
	 * Get bib info link
	 * Get false if not found
	 * Array contains url and host value
	 *
	 * @param	String	$institutionCode
	 * @return	Array|Boolean
	 */
	protected function getBibInfoLink($institutionCode)
	{
		$bibInfoLink = $this->translator->translate($institutionCode, 'bibinfo');

		if ($bibInfoLink === $institutionCode) {
			$bibInfoLink = false;
		} else {
			$url	= parse_url($bibInfoLink);
			$bibInfoLink = array(
				'url'	=> $bibInfoLink,
				'host'	=> $url['host']
			);
		}

		return $bibInfoLink;
	}



	/**
	 * Get availability infos for item element
	 *
	 * @param    String        $sysNumber
	 * @param    String        $barcode
	 * @return    Array|Boolean
	 */
	protected function getAvailabilityInfos($sysNumber, $barcode)
	{
		if (!isset($this->availabilities[$sysNumber])) {
			$this->availabilities[$sysNumber] = $this->getItemCirculationStatuses($sysNumber);
		}

		if (!isset($this->availabilities[$sysNumber][$barcode])) {
			$this->availabilities[$sysNumber][$barcode] = false;
		}

		return $this->availabilities[$sysNumber][$barcode];
	}



	/**
	 * Check whether item is avilable
	 *
	 * @todo       Improve checks!
	 * @see        getAvailabilityInfos
	 * @param    String        $sysNumber
	 * @param    String        $barcode
	 * @return    Boolean        bool
	 */
	protected function isAvailable($sysNumber, $barcode)
	{
		$infos = $this->getAvailabilityInfos($sysNumber, $barcode);

		if ($infos) {
			return $infos['loan-status'] === 'Loan';
		}

		return false;
	}



	/**
	 * Get circulation statuses for all elements of the item
	 *
	 * @param    String        $sysNumber
	 * @return    Array[]
	 */
	protected function getItemCirculationStatuses($sysNumber)
	{
        $data                = array();
        try {
            $circulationStatuses = $this->ils->getDriver()->getCirculationStatus($sysNumber);


            foreach ($circulationStatuses as $circulationStatus) {
                $data[$circulationStatus['barcode']] = $circulationStatus;
            }

        } catch (\Exception $e) {
            //todo: GH get logging service
        }

		return $data;
	}



	/**
	 * Get structured data for holdings
	 *
	 * @param	SolrMarc	$recordDriver
	 * @param	String		$institutionCode
	 * @return    Array[]
	 */
	protected function getHoldingData(SolrMarc $recordDriver, $institutionCode)
	{
		$fieldName          = 852; // Field code for item information in holdings xml
		$institutionHoldings = $this->geHoldingsData($fieldName, $this->fieldMapping, $institutionCode);

		foreach ($institutionHoldings as $index => $holding) {
			$institutionHoldings[$index] = $this->extendHolding($holding, $recordDriver);
		}

		return $institutionHoldings;
	}



	/**
	 * Check whether holding holdings are available
	 *
	 * @return	Boolean
	 */
	protected function hasHoldings()
	{
		return $this->holdings && $this->holdings->getField(852) !== false;
	}



	/**
	 * Check whether holding items are available
	 *
	 * @return	Boolean
	 */
	protected function hasItems()
	{
		return $this->holdings && $this->holdings->getField(949) !== false;
	}



	/**
	 * Get structured elements (grouped by group and institution)
	 *
	 * @param    String        $fieldName
	 * @param    Array         $mapping
	 * @param    String        $institutionCode
	 * @return    Array		Items or holdings for institution
	 */
	protected function geHoldingsData($fieldName, array $mapping, $institutionCode)
	{
		$data            = array();
		$fields          = $this->holdings ? $this->holdings->getFields($fieldName) : false;
		$institutionCode = strtolower($institutionCode);

		if (is_array($fields)) {
			foreach ($fields as $index => $field) {
				$item        = $this->extractFieldData($field, $mapping);
				$institution = strtolower($item['institution']);

				if ($institution === $institutionCode) {
					$data[] = $item;
				}
			}
		}

		return $data;
	}



	/**
	 * Get holdings structure for holdings
	 *
	 * @param	Integer		$fieldName
	 * @return	Array[]
	 */
	protected function getStructuredHoldingsStructure($fieldName)
	{
		$data    = array();
		$fields  = $this->holdings ? $this->holdings->getFields($fieldName) : false;
		$mapping = array(
			'B' => 'network',
			'b' => 'institution'
		);

		if (is_array($fields)) {
			foreach ($fields as $index => $field) {
				$item        = $this->extractFieldData($field, $mapping);
				$networkCode = strtolower($item['network']);
				$institution = strtolower($item['institution']);
				$groupCode   = $this->getGroup($institution);

				// Make sure group is present
				if (!isset($data[$groupCode])) {
					$data[$groupCode] = array(
						'label'        => strtolower($groupCode),
						'networkCode'  => $networkCode,
						'institutions' => array()
					);
				}

				// Make sure institution is present
				if (!isset($data[$groupCode]['institutions'][$institution])) {
					$data[$groupCode]['institutions'][$institution] = array(
						'label' 		=> strtolower($institution),
						'bibinfolink'	=> $this->getBibInfoLink($institution)
					);
				}
			}
		}

		return $data;
	}



	/**
	 * Get group code for institution based on mapping data
	 *
	 * @param	String		$institutionCode
	 * @return	String
	 */
	protected function getGroup($institutionCode)
	{
		return isset($this->institution2group[$institutionCode]) ? $this->institution2group[$institutionCode] : 'unknown';
	}



	/**
	 * Build itemId from item properties and the id of the item
	 * ItemId is not the id of the item, it's a combination of sub fields
	 *
	 * @param    Array        $holdingItem
	 * @return    String
	 * @todo    How to handle missing information. Throw exception, ignore?
	 */
	protected function buildItemId(array $holdingItem)
	{
		if (isset($holdingItem['adm_code']) && isset($holdingItem['localid']) && isset($holdingItem['sequencenumber'])) {
			return $holdingItem['adm_code'] . $holdingItem['localid'] . $holdingItem['sequencenumber'];
		}

		return 'incompleteItemData';
	}



	/**
	 * Get link for holding action
	 *
	 * @param    Array    $holdingItem
	 * @return    Array
	 */
	protected function getHoldLink(array $holdingItem)
	{
		$linkValues = array(
			'id'      => $holdingItem['localid'], // $this->idItem,
			'item_id' => $this->buildItemId($holdingItem)
		);

		return array(
			'action' => 'Hold',
			'record' => $this->idItem,
			'anchor' => '#tabnav',
			'query'  => http_build_query($linkValues + array(
				'hashKey' => $this->hmac->generate($this->hmacKeys, $linkValues)
			))
		);
	}



	/**
	 * Extract field data
	 *
	 * @param    \File_MARC_Data_Field    $field
	 * @param    Array                    $fieldMapping    Field code=>name mapping
	 * @return    Array
	 */
	protected function extractFieldData(\File_MARC_Data_Field $field, array $fieldMapping)
	{
		$subFields = $field->getSubfields();
		$rawData   = array();
		$data      = array();

		// Fetch data
		foreach ($subFields as $code => $subdata) {
			$rawData[$code] = $subdata->getData();
		}

		foreach ($fieldMapping as $code => $name) {
			$data[$name] = isset($rawData[$code]) ? $rawData[$code] : '';
		}

		return $data;
	}
}

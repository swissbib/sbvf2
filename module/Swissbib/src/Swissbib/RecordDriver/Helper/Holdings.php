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

use VuFind\Crypt\HMAC;
use VuFind\ILS\Connection as IlsConnection;
use VuFind\Auth\Manager as AuthManager;

use Swissbib\VuFind\ILS\Driver\Aleph;

/**
 * probably Holdings should be a subtype of ZF2 AbstractHelper
 *at first I need a better understanding how things are wired up in this case using means of ZF2
 */
class Holdings
{

	/**
	 * @var    IlsConnection
	 */
	protected $ils;

	/**
	 * @var    AuthManager
	 */
	protected $authManager;

	/**
	 * @var    \File_MARC_Record
	 */
	protected $holdings;

	/**
	 * @var    String        Parent item od
	 */
	protected $idItem;

	/**
	 * @var    Array    HMAC keys for ILS
	 */
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
	protected $extractedData = false;

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
	protected $config;

	/**
	 * @var    \VuFind\Crypt\HMAC    $hmac
	 */
	protected $hmac;



	/**
	 * Initialize helper with dependencies
	 *
	 * @param    IlsConnection      $ils
	 * @param    Config             $holdingsConfig
	 * @param    HMAC               $hmac
	 * @param    AuthManager        $authManager
	 */
	public function __construct(IlsConnection $ils, Config $holdingsConfig, HMAC $hmac, AuthManager $authManager)
	{
		$this->ils         = $ils;
		$this->config      = $holdingsConfig;
		$this->hmac        = $hmac;
		$this->authManager = $authManager;

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
	 * @return    Array[]|Boolean            Contains lists for items and holdings {items=>[],holdings=>[]}
	 */
	public function getHoldings()
	{
		if ($this->extractedData === false) {
			$holdingsData = $this->getHoldingData();
			$itemsData    = $this->getItemData();

			// Merge items and holding into the same network/institution structure
			// (stays separated by items/holdings key at lowest level)
			$this->extractedData =  $this->mergeHoldings($holdingsData, $itemsData);
		}

		return $this->extractedData;
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
			$configName = ucfirst($networkName) . 'Networks';

			/** @var Config $networkConfigs */
			$networkConfigs = $this->config->get($configName);

			foreach ($networkConfigs as $network => $networkConfig) {
				list($domain, $library) = explode(',', $networkConfig, 2);

				$this->networks[$network] = array(
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
			$this->extractedData = array();
		}
	}



	/**
	 * Get values of field
	 *
	 * @return    Array    array[networkid][institutioncode] = array() of values for the current item
	 */
	protected function getItemData()
	{
		$fieldName          = 949; // Field code for item information in holdings xml
		$structuredElements = $this->getStructuredHoldingData($fieldName, $this->fieldMapping, 'items');

		// Add hold link and availability for all items
		foreach ($structuredElements as $networkCode => $network) {
			$structuredElements[$networkCode]['link'] = $this->getNetworkLink($networkCode);

			foreach ($network['institutions'] as $institutionCode => $institution) {
				// Add backlink
				$structuredElements[$networkCode]['institutions'][$institutionCode]['backlink']
						= $this->getBackLink($networkCode, $institutionCode, $institution['items'][0]);

				foreach ($institution['items'] as $index => $item) {
					// Add extra information for item
					$structuredElements[$networkCode]['institutions'][$institutionCode]['items'][$index]
							= $this->extendWithActionLinks($item);
				}
			}
		}

		return $structuredElements;
	}



	/**
	 * Check whether network is supported
	 *
	 * @param    String        $network
	 * @return    Boolean
	 */
	protected function isRestfulNetwork($network)
	{
		return isset($this->config->Restful->{$network});
	}



	/**
	 * Add action links for item
	 *
	 * @todo    Handle multi ILS system
	 * @param    Array    $item
	 * @return    Array
	 */
	protected function extendWithActionLinks(array $item)
	{
			// Only add links for supported networks
		if ($this->isAlephNetwork($item['network']) && $this->isRestfulNetwork($item['network'])) {
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
	protected function getBackLink($networkCode, $institutionCode, $item)
	{
		$method = false;
		$data   = array();

		if (isset($this->config->Backlink->{$networkCode})) { // Has the network its own backlink type
			$method = 'getBackLink' . ucfirst($networkCode);
			$data   = array(
				'pattern' => $this->config->Backlink->{$networkCode}
			);
		} else { // no custom type
			if (isset($this->networks[$networkCode])) { // is network even configured?
				$type   = $this->networks[$networkCode]['type'];
				$method = 'getBackLink' . ucfirst($type);

				// Has the network type (aleph, virtua, etc) a general link?
				$typeNetwork = ucfirst($type);
				if (isset($this->config->Backlink->{$typeNetwork})) {
					$data = array(
						'pattern' => $this->config->Backlink->{$typeNetwork}
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
	 * Get aleph domain link for network
	 *
	 * @param    String        $network
	 * @return    String|Boolean
	 */
	protected function getNetworkLink($network)
	{
		return isset($this->networks[$network]['domain']) ? $this->networks[$network]['domain'] : false;
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
		$circulationStatuses = $this->ils->getDriver()->getCirculationStatus($sysNumber);
		$data                = array();

		foreach ($circulationStatuses as $circulationStatus) {
			$data[$circulationStatus['barcode']] = $circulationStatus;
		}

		return $data;
	}



	/**
	 * Get structured data for holdings
	 *
	 * @return    Array[]
	 */
	protected function getHoldingData()
	{
		return $this->getStructuredHoldingData(852, $this->fieldMapping, 'holdings');
	}



	/**
	 * Get structured elements (grouped by network and institution)
	 *
	 * @param    String        $fieldName
	 * @param    Array         $mapping
	 * @param    String        $elementKey
	 * @return    Array
	 */
	protected function getStructuredHoldingData($fieldName, array $mapping, $elementKey)
	{
		$data   = array();
		$fields = $this->holdings->getFields($fieldName);

		if (is_array($fields)) {
			foreach ($fields as $index => $field) {
				$item        = $this->extractFieldData($field, $mapping);
				$network     = $item['network'];
				$institution = $item['institution'];

				// Make sure network is present
				if (!isset($data[$network])) {
					$data[$network] = array(
						'label'        => strtolower($network),
						'institutions' => array()
					);
				}

				// Make sure institution is present
				if (!isset($data[$network]['institutions'][$institution])) {
					$data[$network]['institutions'][$institution] = array(
						'label'     => strtolower($institution),
						$elementKey => array()
					);
				}

				$data[$network]['institutions'][$institution][$elementKey][] = $item;
			}
		}

		return $data;
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

//
//	public function getTestData() {
//
//		return
//				$testholdings = <<<EOT
//        <record>
//            <datafield tag="949" ind1=" " ind2=" ">
//                <subfield code="B">RERO</subfield>
//                <subfield code="E">vtls0034515</subfield>
//                <subfield code="b">A100</subfield>
//                <subfield code="j">-</subfield>
//                <subfield code="p">1889908238</subfield>
//                <subfield code="4">60100</subfield>
//            </datafield>
//            <datafield tag="949" ind1=" " ind2=" ">
//                <subfield code="B">RERO</subfield>
//                <subfield code="E">vtls003451557</subfield>
//                <subfield code="b">610650002</subfield>
//                <subfield code="j">-</subfield>
//                <subfield code="p">1889908238</subfield>
//                <subfield code="4">60100</subfield>
//            </datafield>
//            <datafield tag="949" ind1=" " ind2=" ">
//                <subfield code="B">RERO</subfield>
//                <subfield code="E">vtls003451557</subfield>
//                <subfield code="b">1234</subfield>
//                <subfield code="j">-</subfield>
//                <subfield code="p">1889908238</subfield>
//                <subfield code="4">60100</subfield>
//            </datafield>
//            <datafield tag="852" ind1=" " ind2=" ">
//                <subfield code="B">IDSBB</subfield>
//                <subfield code="E">sysnr</subfield>
//                <subfield code="b">1234</subfield>
//                <subfield code="j">-</subfield>
//                <subfield code="p">recid</subfield>
//                <subfield code="4">60100</subfield>
//            </datafield>
//        </record>
//EOT;
//
//	}
}

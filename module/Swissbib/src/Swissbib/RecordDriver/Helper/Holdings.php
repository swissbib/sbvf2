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

use Swissbib\RecordDriver\SolrMarc;
use VuFind\Crypt\HMAC;
use Zend\Config\Config;



/**
 * probably Holdings should be a subtype of ZF2 AbstractHelper
 *at first I need a better understanding how things are wired up in this case using means of ZF2
 */
class Holdings implements HoldingsAwareInterface {

	/**
	 * @var	\VuFind\ILS\Connection
	 */
	protected $ils;

	/**
	 * @var	\VuFind\Auth\Manager
	 */
	protected $authManager;

	/**
	 * @var	\File_MARC_Record
	 */
	protected $holdings;

	/**
	 * @var	String		Parent item od
	 */
	protected $idItem;

	/**
	 * @var	Array	HMAC keys for ILS
	 */
	protected $hmacKeys = array();

	/**
	 * @var \Zend\ServiceManager\ServiceManager
	 */
	protected $serviceLocator;

	/**
	 * @var	Array	Map of fields to named params
	 */
	protected $fieldMapping	= array(
		'0'		=> 'local_branch_expanded',
		'1'		=> 'location_expanded',
		'a'		=> 'holding_information',
		'B'		=> 'network',
		'b'		=> 'institution',
		'C'		=> 'adm_code',
		'E'		=> 'bibsysnumber',
		'j'		=> 'signature',
		'o'		=> 'staff_note',
		'p'		=> 'barcode',
		'q'		=> 'localid',
		'r'     => 'sequencenumber',
		's'		=> 'signature2',
		'y'		=> 'opac_note'
	);

	/**
	 * @var	Array[]|Boolean
	 */
	protected $extractedData = false;

	/**
	 * @var	Array[]	List of availabilities per item and barcode
	 */
	protected $availabilities = array();

	/**
	 * @var	Array[]	List of network domains and libs
	 */
	protected $networks = array();





	/**
	 * Initialize for item
	 *
	 * @param	Object		$serviceLocator
	 * @param	String		$idItem
	 * @param	Array		$hmacKeys
	 * @param	String		$holdingsXml
	 */
	public function initRecord($serviceLocator, $idItem, array $hmacKeys, $holdingsXml) {
		$this->serviceLocator	= $serviceLocator->getServiceLocator();
		$this->idItem			= $idItem;
		$this->hmacKeys			= $hmacKeys;

		$this->setHoldingsContent($holdingsXml);

		$this->initNetworks();
	}



	/**
	 * Initialize networks from config
	 */
	protected function initNetworks() {
		/** @var Config $networkConfigs  */
		$networkConfigs	= $this->serviceLocator->get('VuFind\Config')->get('Aleph')->Networks;

		foreach($networkConfigs as $network => $networkConfig) {
			list($domain, $library) = explode(',', $networkConfig, 2);

			$this->networks[$network] = array(
				'domain'	=> $domain,
				'library'	=> $library
			);
		}
	}



	/**
	 * Set holdings structure
	 *
	 * @param	String		$holdingsXml
	 * @throws	\File_MARC_Exception
	 */
	public function setHoldingsContent($holdingsXml) {
		if( strlen($holdingsXml) > 30 ) {
			$holdingsMarcXml= new \File_MARCXML($holdingsXml, \File_MARCXML::SOURCE_STRING);
			$marcData		= $holdingsMarcXml->next();

			if( !$marcData ) {
				throw new \File_MARC_Exception('Cannot Process Holdings Structure');
			}

			$this->holdings = $marcData;
		} else {
				// Invalid input data. Currently just ignore it
			$this->extractedData	= array();
		}
	}



	/**
	 * Get holdings data
	 *
	 * @return	Array[]		Contains lists for items and holdings {items=>[],holdings=>[]}
	 */
	public function getHoldings($authManager, $ils) {
		$this->authManager	= $authManager;
		$this->ils			= $ils;

		if( $this->extractedData === false ) {
			$holdingsData	= $this->getHoldingData();
			$itemsData		= $this->getItemData();

				// Merge items and holding into the same network/institution structure
				// (stays separated by items/holdings key at lowest level)
			$this->extractedData = array_merge_recursive($holdingsData, $itemsData);
		}

		return $this->extractedData;
	}



	/**
	 * Get values of field
	 *
	 * @return	Array	array[networkid][institutioncode] = array() of values for the current item
	 */
	protected function getItemData() {
		$fieldName			= 949; // Field code for item information in holdings xml
		$structuredElements	= $this->getStructuredFieldElements($fieldName, $this->fieldMapping, 'items');
		/** @var \Swissbib\VuFind\ILS\Driver\Aleph $alephDriver */

			// Add hold link and availability for all items
		foreach($structuredElements as $networkCode => $network) {
			$structuredElements[$networkCode]['link'] = $this->getAlephNetworkLink($networkCode);

			foreach($network['institutions'] as $institutionCode => $institution) {
					// Add backlink
				if( isset($this->networks[$networkCode]) ) {
					$firstItem	= reset($institution['items']);
					$structuredElements[$networkCode]['institutions'][$institutionCode]['backlink'] = $this->getAlephBackLink($networkCode, $institutionCode, $firstItem['bibsysnumber']);
				}

				foreach($institution['items'] as $index => $item) {
						// Add extra information for item
					$structuredElements[$networkCode]['institutions'][$institutionCode]['items'][$index] = $this->extendWithActionLinks($item);
				}
			}

		}

		return $structuredElements;
	}



	/**
	 * Check whether network is supported
	 *
	 * @todo	Implement real check / move to config file
	 * @param	String		$network
	 * @return	Boolean
	 */
	protected function isSupportedRestNetwork($network) {
		return $network === 'IDSBB';
	}



	/**
	 * Add action links for item
	 *
	 * @param	Array	$item
	 * @return	Array
	 */
	protected function extendWithActionLinks(array $item) {
		if( $this->isSupportedRestNetwork($item['network']) ) { // Only add links for supported networks
				// Add hold link for item
			$item['holdingLink'] = $this->buildHoldActionLink($item);

				// Add availability if supported by network
			$item['available'] = $this->getAvailabilityInfos($item['bibsysnumber'], $item['barcode']);
		}

		return $item;
	}



	/**
	 * Build a deep link to an Aleph system
	 *
	 * @param	String		$network
	 * @param $institution
	 * @param $itemSysNumber
	 * @return mixed
	 */
	protected function getAlephBackLink($network, $institution, $itemSysNumber) {
		$linkPattern= '{aleph-opac-server}/F?func=item-global&doc_library={aleph-bib-library-code}&doc_number={bib-system-number}&sub_library={aleph-sublibrary-code}';
		$data		= array(
			'{aleph-opac-server}'		=> $this->networks[$network]['domain'],
			'{aleph-bib-library-code}'	=> $this->networks[$network]['library'],
			'{bib-system-number}'		=> $itemSysNumber,
			'{aleph-sublibrary-code}'	=> $institution
		);

		return str_replace(array_keys($data), array_values($data), $linkPattern);
	}



	/**
	 * Get aleph domain link for network
	 *
	 * @param	String		$network
	 * @return	String|Boolean
	 */
	protected function getAlephNetworkLink($network) {
		return isset($this->networks[$network]['domain']) ? $this->networks[$network]['domain'] : false;
	}



	/**
	 * Get availability infos for item element
	 *
	 * @param	String		$sysNumber
	 * @param	String		$barcode
	 * @return	Array|Boolean
	 */
	protected function getAvailabilityInfos($sysNumber, $barcode) {
		if( !isset($this->availabilities[$sysNumber]) ) {
			$this->availabilities[$sysNumber] = $this->getItemCirculationStatuses($sysNumber);
		}

		if( !isset($this->availabilities[$sysNumber][$barcode]) ) {
			$this->availabilities[$sysNumber][$barcode] = false;
		}

		return $this->availabilities[$sysNumber][$barcode];
	}



	/**
	 * Get circulation statuses for all elements of the item
	 *
	 * @param	String		$sysNumber
	 * @return	Array[]
	 */
	protected function getItemCirculationStatuses($sysNumber) {
		$circulationStatuses= $this->ils->getDriver()->getCirculationStatus($sysNumber);
		$data				= array();

		foreach($circulationStatuses as $circulationStatus) {
			$data[$circulationStatus['barcode']] = $circulationStatus;
		}

		return $data;
	}



	/**
	 * Get structured data for holdings
	 *
	 * @return	Array[]
	 */
	protected function getHoldingData() {
		return $this->getStructuredFieldElements(852, $this->fieldMapping, 'holdings');
	}



	/**
	 * Get structured elements (grouped by network and institution)
	 *
	 * @param	String		$fieldName
	 * @param	Array		$mapping
	 * @param	String		$elementKey
	 * @return	Array
	 */
	protected function getStructuredFieldElements($fieldName, array $mapping, $elementKey) {
		$data		= array();
		$fields		= $this->holdings->getFields($fieldName);

		if( is_array($fields) ) {
			foreach($fields as $index => $field) {
				$item		= $this->extractFieldData($field, $mapping);
				$network	= $item['network'];
				$institution= $item['institution'];

					// Make sure network is present
				if( !isset($data[$network]) ) {
					$data[$network] = array(
						'label'			=> 'Label: ' . $network,
						'institutions'	=> array()
					);
				}

					// Make sure institution is present
				if( !isset($data[$network]['institutions'][$institution]) ) {
					$data[$network]['institutions'][$institution] = array(
						'label'		=> 'Label: ' . $institution,
						$elementKey	=> array()
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
	 * @param	Array		$holdingItem
	 * @return	String
	 * @todo	How to handle missing information. Throw exception, ignore?
	 */
	protected function buildItemId(array $holdingItem) {
		if( isset($holdingItem['adm_code']) && isset($holdingItem['localid']) && isset($holdingItem['sequencenumber']) ) {
			return $holdingItem['adm_code'] . $holdingItem['localid'] . $holdingItem['sequencenumber'];
		}

		return 'incompleteItemData';
	}



	/**
	 * Get link for holding action
	 *
	 * @param	Array	$holdingItem
	 * @return	Array
	 */
	protected function buildHoldActionLink(array $holdingItem) {
		$linkValues	= array(
			'id'		=> $holdingItem['localid'], // $this->idItem,
			'item_id'	=> $this->buildItemId($holdingItem)
		);

		/**
		 * @var	\VuFind\Crypt\HMAC	$hmac
		 */
		$hmac	= $this->serviceLocator->get('VuFind\HMAC');

		return array(
			'action'	=> 'Hold',
			'record'	=> $this->idItem,
			'anchor'	=> '#tabnav',
			'query'		=> http_build_query($linkValues + array(
				'hashKey'	=> $hmac->generate($this->hmacKeys, $linkValues)
			))
		);
	}



	/**
	 * Extract field data
	 *
	 * @param	\File_MARC_Data_Field	$field
	 * @param	Array		$fieldMapping	Field code=>name mapping
	 * @return	Array
	 */
	protected function extractFieldData(\File_MARC_Data_Field $field, array $fieldMapping) {
		$subFields	= $field->getSubfields();
		$rawData	= array();
		$data		= array();

			// Fetch data
		foreach($subFields as $code => $subdata) {
			$rawData[$code] = $subdata->getData();
		}

		foreach($fieldMapping as $code => $name) {
			$data[$name]	= isset($rawData[$code]) ? $rawData[$code] : '';
		}

		return $data;
	}



	public function getTestData() {

		return
				$testholdings = <<<EOT
        <record>
            <datafield tag="949" ind1=" " ind2=" ">
                <subfield code="B">RERO</subfield>
                <subfield code="E">vtls0034515</subfield>
                <subfield code="b">A100</subfield>
                <subfield code="j">-</subfield>
                <subfield code="p">1889908238</subfield>
                <subfield code="4">60100</subfield>
            </datafield>
            <datafield tag="949" ind1=" " ind2=" ">
                <subfield code="B">RERO</subfield>
                <subfield code="E">vtls003451557</subfield>
                <subfield code="b">610650002</subfield>
                <subfield code="j">-</subfield>
                <subfield code="p">1889908238</subfield>
                <subfield code="4">60100</subfield>
            </datafield>
            <datafield tag="949" ind1=" " ind2=" ">
                <subfield code="B">RERO</subfield>
                <subfield code="E">vtls003451557</subfield>
                <subfield code="b">1234</subfield>
                <subfield code="j">-</subfield>
                <subfield code="p">1889908238</subfield>
                <subfield code="4">60100</subfield>
            </datafield>
            <datafield tag="852" ind1=" " ind2=" ">
                <subfield code="B">IDSBB</subfield>
                <subfield code="E">sysnr</subfield>
                <subfield code="b">1234</subfield>
                <subfield code="j">-</subfield>
                <subfield code="p">recid</subfield>
                <subfield code="4">60100</subfield>
            </datafield>
        </record>
EOT;

	}

}

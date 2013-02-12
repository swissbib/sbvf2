<?php

namespace Swissbib\RecordDriver\Helper;

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

/**
 * probably HoldingsHelper should be a subtype of ZF2 AbstractHelper
 *at first I need a better understanding how things are wired up in this case using means of ZF2
 */
class HoldingsHelper implements HoldingsAwareInterface {

	/**
	 * @var	\File_MARC_Record
	 */
	protected $holdings;

	/**
	 * @var	Array	Map of fields to named params
	 */
	protected $fieldMapping	= array(
		'E'		=> 'bibsysnumber',
		'p'		=> 'barcode',
		'1'		=> 'location_expanded',
		'0'		=> 'local_branch_expanded',
		's'		=> 'signature2',
		'C'		=> 'adm_code',
		'y'		=> 'opac_note',
		'a'		=> 'holding_information',
		'B'		=> 'network',
		'b'		=> 'institution',
		'q'		=> 'localid'
	);

	/**
	 * @var	Array[]|Boolean
	 */
	protected $extracted = false;


	/**
	 * Set holdings structure
	 *
	 * @param    String    $holdingsXml
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
			$this->extracted	= array();
		}
	}




	/**
	 * Get holdings data
	 *
	 * @return	Array[]
	 */
	public function  getHoldings() {
		if( $this->extracted === false ) {
			$t949 	= $this->getFieldValues("949");
			$t852 	= $this->getFieldValues("852");
			$this->extracted = array_merge($t949, $t852);
		}

		return $this->extracted;
	}



	/**
	 * Get values of field
	 *
	 * @param	String	$fieldName		The MARC field number to read
	 * @return	Array	array[networkid][institutioncode] = array() of values for the current item
	 */
	protected function getFieldValues($fieldName) {
		$data	= array();
		$fields	= $this->holdings->getFields($fieldName);

		if( is_array($fields) ) {
			foreach($fields as $field) {
				$item		= $this->extractFieldData($field);
				$network	= $item['network'];
				$institution= $item['institution'];

				if( !isset($data[$network]) ) {
					$data[$network] = array(
						'label'			=> 'Label: ' . $network,
						'institutions'	=> array()
					);
				}

				if( !isset($data[$network]['institutions'][$institution]) ) {
					$data[$network]['institutions'][$institution] = array(
						'label'	=> 'Label: ' . $institution,
						'copies'=> array()
					);
				}

				$data[$network]['institutions'][$institution]['copies'][] = $item;
			}
		}

		return $data;
	}



	/**
	 * Extract field data
	 *
	 * @param	\File_MARC_Data_Field	$field
	 * @return	Array
	 */
	protected function extractFieldData(\File_MARC_Data_Field $field) {
		$subFields	= $field->getSubfields();
		$rawData	= array();
		$data		= array();

			// Fetch data
		foreach($subFields as $code => $subdata) {
			$rawData[$code] = $subdata->getData();
		}

		foreach($this->fieldMapping as $code => $name) {
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

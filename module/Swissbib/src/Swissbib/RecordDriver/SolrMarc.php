<?php

/**
 * swissbib / VuFind swissbib enhancements for MARC records in Solr
 *
 * PHP version 5
 *
 * Copyright (C) project swissbib, University Library Basel, Switzerland
 * http://www.swissbib.org  / http://www.swissbib.ch / http://www.ub.unibas.ch
 *
 * Date: 1/2/13
 * Time: 4:09 PM
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
 * @package  RecordDriver
 * @author   Guenter Hipler  <guenter.hipler@unibas.ch>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link    http://www.swissbib.org
 */

namespace Swissbib\RecordDriver;

use VuFind\RecordDriver\SolrMarc as VFSolrMarc;
use Swissbib\RecordDriver\Helper\Holdings as HoldingsHelper;

/**
 * enhancement for swissbib MARC records in Solr.
 *
 * @category swissbib_VuFind2
 * @package  RecordDrivers
 * @author   Guenter Hipler  <guenter.hipler@unibas.ch>
 * @author   Oliver Schihin <oliver.schihin@unibas.ch>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://www.swissbib.org
 */
class SolrMarc extends VFSolrMarc {


	protected $personFieldMap = array(
		'a' => 'name',
		'b' => 'numeration',
		'c' => 'titles',
		'd' => 'dates',
		'q' => 'fullername',
		'D' => 'forname'
	);

	/**
	 * @var	HoldingsHelper
	 */
	protected $marcHoldings;



	/**
	 * Initialize holdings
	 *
	 * @inheritDoc
	 */
	public function __construct($mainConfig = null, $recordConfig = null, $searchSettings = null) {
		parent::__construct($mainConfig, $recordConfig, $searchSettings);

		// remove parent dependency first
		//todo: I'm looking for a possibility to wire up the Holdings using
		//ServiceLocator and configuration -> tbd later
//		$holdingsHelper = $this->getServiceLocator()->get('Swissbib\RecordDriverHoldingsHelper');

		$this->marcHoldings = new HoldingsHelper();
	}



	/**
	 * Set raw data and initialize holdings helper
	 *
	 */
	public function setRawData($data) {
		parent::setRawData($data);

		 //$holdings = $this->marcHoldings->getTestData();
		if( isset($data['holdings']) ) {
			$holdsIlsConfig = $this->getILS()->checkFunction('Holds');

			$this->marcHoldings->init($this->getUniqueID(), $holdsIlsConfig['HMACKeys'], $data['holdings']);
		}
	}



	/**
	 * Get years and datetype from field 008 for display
     *
	 * @return  Array
	 */
	public function getPublicationDates() {
            // Get field 008 fixed field code
        $code = $this->marcRecord->getField('008')->getData();

            // Get parts
		$dateType   = substr($code, 6, 1);
		$year1      = substr($code, 7, 4);
		$year2      = substr($code, 11, 4);

		return array($dateType, $year1, $year2);
	}



	/**
	 * Get primary author
	 *
	 * @param	Boolean        $asString
	 * @return	Array|String
	 */
	public function getPrimaryAuthor($asString = false) {
		$data = $this->getMarcSubFieldMap(100, $this->personFieldMap);

		if( $asString ) {
			return isset($data['name']) ? trim($data['name'] . ' ' . $data['forname']) : '';
		}

		return $data;
	}



	/**
	 * Get list of secondary authors data
	 *
	 * @note	exclude: if $l == fre|eng
	 * @todo	Implement note comment
	 * @return	Array[]
	 */
	public function getSecondaryAuthors() {
		return $this->getMarcSubFieldMaps(700, $this->personFieldMap);
	}



	/**
	 * Get corporate authors
	 *
	 * @todo	Are there docs with values in field 110?
	 * @note	exclude: if $l == fre|eng
	 * @return	Array[]
	 */
	public function getCorporateAuthor() {
		return $this->getMarcSubFieldMaps(710, array(
			'a'	=> 'name',
			'b'	=> 'unit'
		));
	}



	/**
	 * Get sub title
	 *
	 * @return	String
	 */
	public function getSubtitle() {
		return $this->getFirstFieldValue('245', array('b'));
	}



	/**
	 * Get edition
	 *
	 * @return	String
	 */
	public function getEdition() {
		return $this->getFirstFieldValue('250', array('a'));
	}



	/**
	 * get subject headings from GND subject headings
	 * build an array (multidimensional?) from all GND headings
	 * GND headings
	 * fields: 600, 610, 611, 630, 648, 650, 651, 655
	 * @ind2=7
	 * subfield $2=gnd
	 * subfields vary per field, build array per field with all
	 * content to be able to treat it in a view helper
	 * @return array
	 */
	/**
	 * Get subject headings
	 *
	 * @return	Array[]
	 */
	public function getGNDSubjectHeadings() {
		return $this->getMarcSubFieldMaps(600, array(
			'a'	=> 'name'
		));
	}



	/**
	 * get group-id from solr-field to display FRBR-Button
	 * @return string
	 */
	public function getGroup() {
		return isset($this->fields['group_id']) ? $this->fields['group_id'] : '';
	}



	/*
	* Library / Institution Code as array
	* @return array
	*/
	public function getInstitution() {
		return isset($this->fields['institution']) ? $this->fields['institution'] : array();
	}



	/**
	 * Get local topic term
	 *
	 * @return	String
	 */
	public function getLocalTopicTerm() {
		return $this->getSimpleMarcFieldValue('690');
	}



	/**
	 * Get host item entry
	 *
	 * @return	String
	 */
	public function getHostItemEntry() {
		return $this->getSimpleMarcSubFieldValue(773, 't');
	}



	/**
	 * Get publisher
	 *
	 * @param	Boolean		$asString
	 * @return	Array|String
	 */
	public function getPublisher($asString = false) {
		$data = $this->getMarcSubFieldMap(260, array(
			'a'	=> 'place',
			'b'	=> 'name',
			'c'	=> 'date'
		));

		if( $asString ) {
			return isset($data['name']) ? trim($data['name'] . ', ' . $data['place']) : '';
		}

		return $data;
	}




	/**
	 * Get marc field
	 *
	 * @param    Integer        $index
	 * @return    \File_MARC_Data_Field|Boolean
	 */
	protected function getMarcField($index) {
		$index	= sprintf('%03d', $index);

		return $this->marcRecord->getField($index);
	}



	/**
	 * @param $index
	 * @param array $fieldMap
	 * @return array
	 */
	protected function getMarcSubFieldMap($index, array $fieldMap) {
		$index			= sprintf('%03d', $index);
		$subFieldValues = array();
		$field			= $this->marcRecord->getField($index);

		if( $field ) {
			$subFieldValues = $this->convertSubFieldsToMap($field, $fieldMap);
		}

		return $subFieldValues;
	}



	/**
	 * Get sub field maps for a field which exists multiple times
	 *
	 * @param	Integer		$index
	 * @param	Array		$fieldMap
	 * @return	Array[]
	 */
	protected function getMarcSubFieldMaps($index, array $fieldMap) {
		$subFieldsValues= array();
		$fields			= $this->marcRecord->getFields($index);

		foreach($fields as $field) {
			$subFieldsValues[] = $this->convertSubFieldsToMap($field, $fieldMap);
		}

		return $subFieldsValues;
	}



	/**
	 * Convert sub fields to array map
	 *
	 * @param	\File_MARC_Data_Field	$field
	 * @param	Array					$fieldMap
	 * @return	Array
	 */
	protected function convertSubFieldsToMap($field, array $fieldMap) {
		$subFieldValues	= array();

		foreach($fieldMap as $code => $name) {
			$subField = $field->getSubfield($code);

			if( $subField ) {
				$subFieldValues[$name] = $subField->getData();
			}
		}

		return $subFieldValues;
	}



	/**
	 * Get value of a sub field
	 *
	 * @param	Integer		$index
	 * @param	String		$subFieldCode
	 * @return	String|Boolean
	 */
	protected function getSimpleMarcSubFieldValue($index, $subFieldCode) {
		$field = $this->getMarcField($index);

		if( $field ) {
			$subField = $field->getSubfield($subFieldCode);

			if( $subField ) {
				return $subField->getData();
			}
		}

		return false;
	}



	/**
	 * Get value of a field
	 *
	 * @param	Integer			$index
	 * @return	String|Boolean
	 */
	protected function getSimpleMarcFieldValue($index) {
		$field = $this->getMarcField($index);

		return $field ? $field->getData() : false;
	}



	/**
	 * Get holdings
	 *
	 * @return	Array[]
	 */
	public function getHoldings() {
        return $this->marcHoldings->getHoldings();
    }



	/**
	 * Get short title
	 * Override base method to assure a string and not an array
	 *
	 * @return	String
	 */
	public function getShortTitle() {
		$shortTitle	= parent::getShortTitle();

		return is_array($shortTitle) ? reset($shortTitle) : $shortTitle;
	}


	public function getTitle() {
		$title	= parent::getTitle();

		return is_array($title) ? reset($title) : $title;
	}

}

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

use VuFind\RecordDriver\SolrMarc as VuFindSolrMarc;

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
class SolrMarc extends VuFindSolrMarc {

	/**
	 * @var	HoldingsHelper
	 */
	protected $holdingsHelper;


	/**
	 * @var	Array	Used also for field 100		_ means repeatable
	 */
	protected $personFieldMap = array(
		'a' => 'name',
		'b' => 'numeration',
		'_c'=> 'titles', // R
		'd' => 'dates',
		'_e'=> 'relator', // R
		'f'	=> 'date_of_work',
		'g'	=> 'misc',
		'l'	=> 'language',
		'_n'=> 'number_of_parts', // R
		'q' => 'fullername',
		'D' => 'forname',
		't'	=> 'title_of_work',
		'_8'=> 'extras',
		'9'	=> 'unknownNumber'
	);


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
	 *
	 * @return	String|Number
	 */
	public function getGroup() {
		return isset($this->fields['group_id']) ? $this->fields['group_id'][0] : '';
	}



	/*
	* Library / Institution Code
	 *
	* @return	String
	*/
	public function getInstitution() {
		return isset($this->fields['institution']) ? $this->fields['institution'][0] : array();
	}



	/**
	 * Get local topic term
	 *
	 * @return	Array
	 */
	public function getLocalTopicTerms() {
		return $this->getMarcSubFieldMaps('690', array(
			'a'	=> 'term',
			'q'	=> 'label', // @todo real name?
			't'	=> 'time', // @todo real name?
			'v'	=> 'form_subdivision'
		));
	}



	/**
	 * Get host item entry
	 *
	 * @todo	Add relevant fields if required
	 * @return	Array
	 */
	public function getHostItemEntry() {
		return $this->getMarcSubFieldMaps(773, array(
			'a'	=> 'heading',
			'b'	=> 'edition',
			'd'	=> 'place',
			'g'	=> 'related',
			'h'	=> 'physical_description'
		));
	}



	/**
	 * Get publishers
	 *
	 * @param	Boolean		$asStrings
	 * @return	Array[]|String[]
	 */
	public function getPublishers($asStrings = false) {
		$data = $this->getMarcSubFieldMaps(260, array(
			'a'	=> 'place',
			'b'	=> 'name',
			'c'	=> 'date',
			'd'	=> 'number',
			'e'	=> 'place_manufacture',
			'g'	=> 'date_manufacture'
		));

		if( $asStrings ) {
			$strings = array();

			foreach($data as $publication) {
				$strings[] = trim($data['name'] . ', ' . $data['place']);
			}

			$data = $strings;
		}

		return $data;
	}



    /**
     * Get physical description out of the MARC record
	 *
	 * @return	Array[]
     */
    public function getPhysicalDescriptions() {
		return $this->getMarcSubFieldMaps(300, array(
			'_a'	=> 'extent',
			'b'		=> 'details',
			'_c'	=> 'dimensions',
			'd'		=> 'material_single',
			'_e'	=> 'material_multiple',
			'_f'	=> 'type',
			'_g'	=> 'size',
			'3'		=> 'appliesTo'
		));
    }



	/**
	 * Get short title
	 * Override base method to assure a string and not an array
	 *
	 * @todo	Still required?
	 * @return	String
	 */
	public function getShortTitle() {
		$shortTitle	= parent::getShortTitle();

		return is_array($shortTitle) ? reset($shortTitle) : $shortTitle;
	}



	/**
	 * Get title
	 *
	 * @todo	Still required?
	 * @return	String
	 */
	public function getTitle() {
		$title	= parent::getTitle();

		return is_array($title) ? reset($title) : $title;
	}



	/**
	 * Get holdings data
	 *
	 * @return	Array|Boolean
	 */
	public function getHoldings() {
		return $this->getHoldingsHelper()->getHoldings();
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
			if( substr($code, 0, 1) === '_' ) { // Underscore means repeatable
				$code	= substr($code, 1); // Remove underscore
				$subFields = $field->getSubfields($code);

				if( sizeof($subFields) ) {
					$subFieldValues[$name] = array();

					foreach($subFields as $subField) {
						$subFieldValues[$name][] = $subField->getData();
					}
				}
			} else { // Normal single field
				$subField = $field->getSubfield($code);

				if( $subField ) {
					$subFieldValues[$name] = $subField->getData();
				}
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
	 * Get initialized holdings helper
	 *
	 * @return	HoldingsHelper
	 */
	protected function getHoldingsHelper() {
		if( !$this->holdingsHelper ) {
			/** @var HoldingsHelper $holdingsHelper  */
			$holdingsHelper	= $this->getServiceLocator()->getServiceLocator()->get('Swissbib\HoldingsHelper');
			$holdingsData	= isset($this->fields['holdings']) ? $this->fields['holdings'] : '';

			$holdingsHelper->setData($this->getUniqueID(), $holdingsData);

			$this->holdingsHelper	= $holdingsHelper;
		}

		return $this->holdingsHelper;
	}

}

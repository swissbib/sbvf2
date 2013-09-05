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
 * @link     http://www.swissbib.org
 */

namespace Swissbib\RecordDriver;

use Zend\I18n\Translator\Translator;
use Zend\ServiceManager\ServiceLocatorInterface;

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
class SolrMarc extends VuFindSolrMarc
{

	/**
	 * @var    HoldingsHelper
	 */
	protected $holdingsHelper;

	/**
	 * @var    Boolean        Change behaviour if getFormats() to return openUrl compatible formats
	 */
	protected $useOpenUrlFormats = false;

	/**
	 * @var    Array    Used also for field 100        _ means repeatable
	 */
	protected $personFieldMap = array(
		'a'  => 'name',
		'b'  => 'numeration',
		'_c' => 'titles', // R
		'd'  => 'dates',
		'_e' => 'relator', // R
		'f'  => 'date_of_work',
		'g'  => 'misc',
		'l'  => 'language',
		'_n' => 'number_of_parts', // R
		'q'  => 'fullername',
		'D'  => 'forname',
		't'  => 'title_of_work',
		'_8' => 'extras',
		'9'  => 'unknownNumber'
	);



	/**
	 * Wrapper for getOpenURL()
	 * Set flag to get special values from getFormats()
	 *
	 * @see        getFormats()
	 * @return    String
	 */
	public function getOpenURL()
	{
		$oldValue                = $this->useOpenUrlFormats;
		$this->useOpenUrlFormats = true;
		$openUrl                 = parent::getOpenURL();
		$this->useOpenUrlFormats = $oldValue;

		return $openUrl;
	}



	/**
	 * Get formats. By default, get translated values
	 * If flag useOpenUrlFormats in class is set, get prepared formats for openUrl
	 *
	 * @return    String[]
	 */
	public function getFormats()
	{
		if ($this->useOpenUrlFormats) {
			return $this->getFormatsOpenUrl();
		} else {
			return $this->getFormatsTranslated();
		}
	}



	/**
	 * Get translated formats
	 *
	 * @return    String[]
	 */
	public function getFormatsTranslated()
	{
		$formats    = $this->getFormatsRaw();
		$translator = $this->getTranslator();

		foreach ($formats as $index => $format) {
			$formats[$index] = $translator->translate($format);
		}

		return $formats;
	}



	/**
	 * Get ISMN (International Standard Music Number)
	 *
	 * @return array
	 */
	public function getISMNs()
	{
		return isset($this->fields['ismn_isn_mv']) && is_array($this->fields['ismn_isn_mv']) ?
				$this->fields['ismn_isn_mv'] : array();
	}



	/**
	 * Get DOI (Digital Object Identifier)
	 *
	 * @return array
	 */
	public function getDOIs()
	{
		return isset($this->fields['doi_isn_mv']) && is_array($this->fields['doi_isn_mv']) ?
				$this->fields['doi_isn_mv'] : array();
	}



	/**
	 * Get URN (Uniform Resource Name)
	 *
	 * @return array
	 */
	public function getURNs()
	{
		return isset($this->fields['urn_isn_mv']) && is_array($this->fields['urn_isn_mv']) ?
				$this->fields['urn_isn_mv'] : array();
	}



	/**
	 * Get formats modified to work with openURL
	 * Formats: Book, Journal, Article
	 *
	 * @todo    Currently, all items are marked as "Book", improve detection
	 * @return    String[]
	 */
	public function getFormatsOpenUrl()
	{
		$formats = $this->getFormatsRaw();
		$found   = false;
		$mapping = array(
			'BK0100' => 'Article',
			'BK0700' => 'Article',
			'BK'     => 'Book',
			'CR'     => 'Journal'
		);

		// Check each format for all patterns
		foreach ($formats as $rawFormat) {
			foreach ($mapping as $pattern => $targetFormat) {
				// Test for begin of string
				if (stristr($rawFormat, $pattern) === 0) {
					$formats[] = $targetFormat;
					$found     = true;
					break 2; // Stop both loops
				}
			}
		}

		// Fallback: Book
		if (!$found) {
			$formats[] = 'Book';
		}

		return $formats;
	}



	/**
	 * Get raw formats as provided by the basic driver
	 * Wrap for getFormats() because it's overwritten in this driver
	 *
	 * @return    String[]
	 */
	public function getFormatsRaw()
	{
		return parent::getFormats();
	}



	/**
	 * Get years and datetype from field 008 for display
	 *
	 * @return  Array
	 */
	public function getPublicationDates()
	{
		// Get field 008 fixed field code
		$code = $this->marcRecord->getField('008')->getData();

		// Get parts
		$dateType = substr($code, 6, 1);
		$year1    = substr($code, 7, 4);
		$year2    = substr($code, 11, 4);

		return array($dateType, $year1, $year2);
	}



	/**
	 * Get primary author
	 *
	 * @param    Boolean $asString
	 * @return    Array|String
	 */
	public function getPrimaryAuthor($asString = true)
	{
		$data = $this->getMarcSubFieldMap(100, $this->personFieldMap);

		if ($asString) {
			$name = isset($data['forname']) ? $data['forname'] : '';
			$name .= isset($data['name']) ? ' ' . $data['name'] : '';

			return trim($name);
		}

		return $data;
	}



	/**
	 * Get list of secondary authors data
	 *
	 * @param    Boolean		$asString
	 * @return    Array[]
	 */
	public function getSecondaryAuthors($asString = true)
	{
		$authors = $this->getMarcSubFieldMaps(700, $this->personFieldMap);

		if ($asString) {
			$stringAuthors = array();

			foreach ($authors as $author) {
				$name            = isset($author['name']) ? $author['name'] : '';
				$forename        = isset($author['forname']) ? $author['forname'] : '';
				$stringAuthors[] = trim($forename . ' ' . $name);
			}

			$authors = $stringAuthors;
		}

		return $authors;
	}



	/**
	 * Get corporate name (authors)
	 *
	 * @todo    Implement or remove note
	 * @note    exclude: if $l == fre|eng
	 * @return    Array[]
	 */
	public function getMainCorporateName()
	{
		return $this->getMarcSubFieldMap(110, array(
												   'a'  => 'name',
												   '_b' => 'unit',
												   'c'  => 'meeting_location',
												   '_d' => 'meeting_date',
												   '_e' => 'relator',
												   'f'  => 'date',
												   'g'  => 'misc',
												   'h'  => 'medium',
												   '_k' => 'form_subheading',
												   'l'  => 'language',
												   '_n' => 'parts_number',
												   '_p' => 'parts_name',
												   's'  => 'version',
												   't'  => 'title',
												   'u'  => 'affiliation',
												   '4'  => 'relator_code'
											  ));
	}



	/**
	 * Get added corporate names
	 *
	 * @return    Array[]
	 */
	public function getAddedCorporateNames()
	{
		return $this->getMarcSubFieldMaps(710, array(
													'a'  => 'name',
													'_b' => 'unit',
													'c'  => 'meeting_location',
													'_d' => 'meeting_date',
													'_e' => 'relator',
													'f'  => 'date',
													'g'  => 'misc',
													'h'  => 'medium',
													'i'  => 'relationship',
													'_k' => 'form_subheading',
													'l'  => 'language',
													'_m' => 'music_performance_medium',
													'_n' => 'parts_number',
													'_p' => 'parts_name',
													'r'  => 'music_key',
													's'  => 'version',
													't'  => 'title',
													'u'  => 'affiliation',
													'x'  => 'issn',
													'3'  => 'materials_specified',
													'4'  => 'relator_code',
													'5'  => 'institution',
													'_8' => 'label'
											   ));
	}



	/**
	 * Get sub title
	 *
	 * @param    Boolean $full    Get full field data. Else only field c is fetched
	 * @return    String|String[]
	 */
	public function getTitleStatement($full = false)
	{
		if ($full) {
			return $this->getMarcSubFieldMap(245, array(
													   'a'  => 'title',
													   'b'  => 'title_remainder',
													   'c'  => 'statement_responsibility',
													   'f'  => 'inclusive_dates',
													   'g'  => 'bulk_dates',
													   'h'  => 'medium',
													   '_k' => 'form',
													   '_n' => 'parts_amount',
													   '_p' => 'parts_name',
													   's'  => 'version'
												  ));
		} else {
			return parent::getTitleStatement();
		}
	}



	/**
	 * Get edition
	 *
	 * @return    String
	 */
	public function getEdition()
	{
		return $this->getFirstFieldValue('250', array('a'));
	}



	/**
	 * Get alternative title
	 *
	 * @return array
	 */
	public function getAltTitle()
	{
		return $this->getFieldArray('246', '247');
	}



	/**
	 * Get dissertation notes for the record.
	 *
	 * @return array
	 */
	public function getDissertationNotes()
	{
		return $this->getFieldArray('502');
	}


	/**
	 * get group-id from solr-field to display FRBR-Button
	 *
	 * @return    String|Number
	 */
	public function getGroup()
	{
        return isset($this->fields['groupid_isn_mv']) ? $this->fields['groupid_isn_mv'][0] : '';
	}


	/*
	* Library / Institution Codes
	 *
	* @return	String[]
	*/
	public function getInstitutions()
	{
		$institutions = array();

		if (isset($this->fields['institution']) && is_array($this->fields['institution'])) {
			$institutions = array_map('strtolower', $this->fields['institution']);
		}

		return $institutions;
	}



	/**
	 * Get local topic term
	 *
	 * @return    Array[]
	 */
	public function getLocalTopicalTerms()
	{
		return $this->getMarcSubFieldMaps(690, array(
													'a'  => 'term',
													'q'  => 'label', // @todo real name?
													't'  => 'time', // @todo real name?
													'_v' => 'form_subdivision'
											   ));
	}


	/**
	 * Get structured subject vocabularies from predefined fields
	 * Extended version of getAllSubjectHeadings()
	 *
	 * $fieldIndexes contains keys of fields to check
	 * $vocabConfigs contains checks for vocabulary detection
	 *
	 * $vocabConfigs:
	 * - ind: Value for indicator 2 in tag
	 * - field: sub field 2 in tag
	 * - fieldsOnly: Only check for given field indexes
	 * - detect: The vocabulary key is defined in sub field 2. Don't use the key in the config (only used for local)
	 *
	 * Expected result:
	 * [
	 * 		gnd => [
	 * 			600 => [{},{},{},...]
	 * 			610 => [{},{},{},...]
	 * 			620 => [{},{},{},...]
	 * 		],
	 *  	rero => [
	 * 			600 => [{},{},{},...]
	 * 			610 => [{},{},{},...]
	 * 			620 => [{},{},{},...]
	 * 		]
	 * ]
	 * {} is an assoc array which contains the field data
	 *
	 * @see    getAllSubjectHeadings
	 * @param    Boolean $ignoreControlFields        Ignore control fields 0 and 2
	 * @return    Array[]
	 */
	public function getAllSubjectVocabularies($ignoreControlFields = false)
	{
		$subjectVocabularies = array();
		$fieldIndexes        = array(600, 610, 611, 630, 648, 650, 651, 655, 656, 690, 691);
		$vocabConfigs        = array(
            'lcsh'        => array(
                'ind' => 0
            ),
            'mesh'        => array(
                'ind' => 2
            ),
            'unspecified' => array(
                'ind' => 4
            ),
            'gnd'         => array(
				'ind'   => 7,
				'field' => 'gnd'
			),
			'rero'        => array(
				'ind'   => 7,
				'field' => 'rero'
			),
            'idsbb'       => array(
                'ind'   => 7,
                'field' => 'ids bs/be'
            ),
            'idszbz'      => array(
                'ind'   => 7,
                'field' => 'ids zbz'
            ),
            'idslu'       => array(
                'ind'   => 7,
                'field' => 'ids lu'
            ),
            'bgr'         => array(
                'ind'   => 7,
                'field' => 'bgr'
            ),
            'sbt'         => array(
                'ind'   => 7,
                'field' => 'tessin-TS'
            ),
            'jurivoc'     => array(
                'ind'   => 7,
                'field' => 'jurivoc'
            ),
            /* only works for one indicator (test case)
               implement with new CBS-data (standardised MARC, not IDSMARC)
            */
			'local'       => array(
				'ind'        => I,
				'fieldsOnly' => array(690, 691),
				'detect'     => true // extract vocabulary from sub field 2
			),
		);
		$fieldMapping        = array(
			'a' => 'a',
			'b' => 'b',
			'c' => 'c',
			'd' => 'd',
			'e' => 'e',
			'f' => 'f',
			'g' => 'g',
			'h' => 'h',
			't' => 't',
			'v' => 'v',
			'x' => 'x',
			'y' => 'y',
			'z' => 'z'
		);

		// Add control fields to mapping list
		if (!$ignoreControlFields) {
			$fieldMapping += array(
				'0' => '0',
				'2' => '2'
			);
		}

		// Iterate over all indexes to check the available fields
		foreach ($fieldIndexes as $fieldIndex) {
			$indexFields = $this->getMarcFields($fieldIndex);

			// iterate over all fields found for the current index
			foreach ($indexFields as $indexField) {
				// check all vocabularies for matching
				foreach ($vocabConfigs as $vocabKey => $vocabConfig) {
					$fieldData     = false;
					$useAsVocabKey = $vocabKey;

					// Are limited fields set in config
					if (isset($vocabConfig['fieldsOnly']) && is_array($vocabConfig['fieldsOnly'])) {
						if (!in_array($fieldIndex, $vocabConfig['fieldsOnly'])) {
							continue; // Skip vocabulary if field is not in list
						}
					}

					if (isset($vocabConfig['ind']) && $indexField->getIndicator(2) == (string)$vocabConfig['ind']) {
						if (isset($vocabConfig['field'])) { // is there a field check required?
							$subField2 = $indexField->getSubfield('2');
							if ($subField2 && $subField2->getData() === $vocabConfig['field']) { // Check field
								// sub field 2 matches the config
								$fieldData = $this->getMappedFieldData($indexField, $fieldMapping, false);
							}
						} else { // only indicator required, add data
							$fieldData = $this->getMappedFieldData($indexField, $fieldMapping, false);
						}
					}

					// Found something? Add to list, stop vocab check and proceed with next field
					if ($fieldData) {
						// Is detect option set, replace vocab key with value from sub field 2 if present
						if (isset($vocabConfig['detect']) && $vocabConfig['detect']) {
							$subField2 = $indexField->getSubfield('2');
							if ($subField2) {
								$useAsVocabKey = $subField2->getData();
							}
						}

						$subjectVocabularies[$useAsVocabKey][$fieldIndex][] = $fieldData;
						break; // Found vocabulary, stop search
					}
				}
			}
		}

		return $subjectVocabularies;
	}


	/**
	 * Get host item entry
	 *
	 * @todo    Add relevant fields if required
	 * @return    Array
	 */
	public function getHostItemEntry()
	{
		return $this->getMarcSubFieldMaps(773, array(
													'a' => 'heading',
													'b' => 'edition',
													'd' => 'place',
													'g' => 'related',
													'h' => 'physical_description'
											   ));
	}



	/**
	 * Get publishers
	 *
	 * @param    Boolean $asStrings
	 * @return    Array[]|String[]
	 */
	public function getPublishers($asStrings = true)
	{
		$data = $this->getMarcSubFieldMaps(260, array(
													 'a' => 'place',
													 'b' => 'name',
													 'c' => 'date',
													 'd' => 'number',
													 'e' => 'place_manufacture',
													 'g' => 'date_manufacture'
												));

		if ($asStrings) {
			$strings = array();

			foreach ($data as $publication) {
				$string = '';

				if (isset($publication['place'])) {
					$string = $publication['place'] . '; ';
				}
				if (isset($publication['name'])) {
					$string .= $publication['name'];
				}

				$strings[] = trim($string);
			}

			$data = $strings;
		}

		return $data;
	}



	/**
	 * Get physical description out of the MARC record
	 *
	 * @param    Boolean $asStrings
	 * @return    Array[]|String[]
	 */
	public function getPhysicalDescriptions($asStrings = true)
	{
		$descriptions = $this->getMarcSubFieldMaps(300, array(
															 '_a' => 'extent',
															 'b'  => 'details',
															 '_c' => 'dimensions',
															 'd'  => 'material_single',
															 '_e' => 'material_multiple',
															 '_f' => 'type',
															 '_g' => 'size',
															 '3'  => 'appliesTo'
														));

		if ($asStrings) {
			$strings = array();
			foreach ($descriptions as $description) {
				$strings[] = $description['extent'][0];
			}
			$descriptions = $strings;
		}

		return $descriptions;
	}



	/**
	 * Get unions
	 *
	 * @return    String[]
	 */
	public function getUnions()
	{
		return isset($this->fields['union']) ? $this->fields['union'] : array();
	}


	/**
	 * Get short title
	 * Override base method to assure a string and not an array
	 * as long as title_short is multivalued=true in solr (necessary because of faulty data)
	 *
	 * @return    String
	 */
	public function getShortTitle()
	{
		$shortTitle = parent::getShortTitle();

		return is_array($shortTitle) ? reset($shortTitle) : $shortTitle;
	}



	/**
	 * Get title
	 *
	 * @return    String
	 */
	public function getTitle()
	{
		$title = parent::getTitle();

		return is_array($title) ? reset($title) : $title;
	}



	/**
	 * Get holdings data
	 *
	 * @param    String  $institutionCode
	 * @param    Boolean $extend
	 * @return    Array|Boolean
	 */
	public function getInstitutionHoldings($institutionCode, $extend = true)
	{
		return $this->getHoldingsHelper()->getHoldings($this, $institutionCode, $extend);
	}



	/**
	 * Get holdings structure without item details
	 *
	 * @return Array[]|bool
	 */
	public function getHoldingsStructure()
	{
		return $this->getHoldingsHelper()->getHoldingsStructure();
	}



	/**
	 * Get hierarchy type
	 * Directly use driver config
	 *
	 * @return bool|string
	 */
	public function getHierarchyType()
	{
		$type = parent::getHierarchyType();

		return $type ? $type : $this->mainConfig->Hierarchy->driver;
	}



	/**
	 * Get marc field
	 *
	 * @param    Integer $index
	 * @return    \File_MARC_Data_Field|Boolean
	 */
	protected function getMarcField($index)
	{
		$index = sprintf('%03d', $index);

		return $this->marcRecord->getField($index);
	}



	/**
	 * Get marc fields
	 * Multiple values are possible for the field
	 *
	 * @param    Integer $index
	 * @return    \File_MARC_Data_Field[]|\File_MARC_List
	 */
	protected function getMarcFields($index)
	{
		$index = sprintf('%03d', $index);

		return $this->marcRecord->getFields($index);
	}



	/**
	 * Get items of a field as named map (array)
	 * Use this method if the field is (N)ot(R)epeatable
	 *
	 * @param       $index
	 * @param array $fieldMap
	 * @return array
	 */
	protected function getMarcSubFieldMap($index, array $fieldMap)
	{
		$index          = sprintf('%03d', $index);
		$subFieldValues = array();
		$field          = $this->marcRecord->getField($index);

		if ($field) {
			$subFieldValues = $this->getMappedFieldData($field, $fieldMap);
		}

		return $subFieldValues;
	}



	/**
	 * Get items of a field (which exists multiple times) as named map (array)
	 * Use this method if the field is (R)epeatable
	 *
	 * @param	Integer		$index
	 * @param	Array		$fieldMap
	 * @param	Boolean		$includeIndicators
	 * @return	Array[]
	 */
	protected function getMarcSubFieldMaps($index, array $fieldMap, $includeIndicators = true)
	{
		$subFieldsValues = array();
		$fields          = $this->marcRecord->getFields($index);

		foreach ($fields as $field) {
			$subFieldsValues[] = $this->getMappedFieldData($field, $fieldMap, $includeIndicators);
		}

		return $subFieldsValues;
	}



	/**
	 * Convert sub fields to array map
	 *
	 * @param    \File_MARC_Data_Field $field
	 * @param    Array                 $fieldMap
	 * @param    Boolean               $includeIndicators        Add the two indicators to the field list
	 * @return    Array
	 */
	protected function getMappedFieldData($field, array $fieldMap, $includeIndicators = true)
	{
		$subFieldValues = array();

		if ($includeIndicators) {
			$subFieldValues['@ind1'] = $field->getIndicator(1);
			$subFieldValues['@ind2'] = $field->getIndicator(2);
		}

		foreach ($fieldMap as $code => $name) {
			if (substr($code, 0, 1) === '_') { // Underscore means repeatable
				$code      = substr($code, 1); // Remove underscore
				/** @var \File_MARC_Subfield[] $subFields */
				$subFields = $field->getSubfields((string)$code);

				if (sizeof($subFields)) {
					$subFieldValues[$name] = array();

					foreach ($subFields as $subField) {
						$subFieldValues[$name][] = $subField->getData();
					}
				}
			} else { // Normal single field
				$subField = $field->getSubfield((string)$code);

				if ($subField) {
					$subFieldValues[$name] = $subField->getData();
				}
			}
		}

		return $subFieldValues;
	}



	/**
	 * Get fields data without mapping. Keep original order of subfields
	 *
	 * @param	Integer		$index
	 * @return	Array[]
	 */
	protected function getMarcSubfieldsRaw($index)
	{
		/** @var \File_MARC_Data_Field[] $fields */
		$fields		= $this->marcRecord->getFields($index);
		$fieldsData = array();

		foreach ($fields as $field) {
			$tempFieldData = array();

			/** @var \File_MARC_Subfield[] $subfields */
			$subfields = $field->getSubfields();

			foreach ($subfields as $subfield) {
				$tempFieldData[] = array(
					'tag'	=> $subfield->getCode(),
					'data'	=> $subfield->getData()
				);
			}

			$fieldsData[] = $tempFieldData;
		}

		return $fieldsData;
	}



	/**
	 * Get value of a sub field
	 *
	 * @param    Integer $index
	 * @param    String  $subFieldCode
	 * @return    String|Boolean
	 */
	protected function getSimpleMarcSubFieldValue($index, $subFieldCode)
	{
		$field = $this->getMarcField($index);

		if ($field) {
			$subField = $field->getSubfield($subFieldCode);

			if ($subField) {
				return $subField->getData();
			}
		}

		return false;
	}



	/**
	 * Get value of a field
	 *
	 * @param    Integer $index
	 * @return    String|Boolean
	 */
	protected function getSimpleMarcFieldValue($index)
	{
		/** @var \File_MARC_Control_Field $field */
		$field = $this->getMarcField($index);

		return $field ? $field->getData() : false;
	}



	/**
	 * Get initialized holdings helper
	 *
	 * @return    HoldingsHelper
	 */
	protected function getHoldingsHelper()
	{
		if (!$this->holdingsHelper) {

			//core record driver in itself doesn't support implmentation of ServiceLocaterAwareInterface with latest merge
			//alternative to the current solution:
			//we implement this Interface by ourselve
			//at the moment I don't know what's the role of the hierachyDriverManager and if it's always initialized
			//ToDo: more analysis necessary!
			//$holdingsHelper = $this->getServiceLocator()->getServiceLocator()->get('Swissbib\HoldingsHelper');
			/** @var HoldingsHelper $holdingsHelper */
			$holdingsHelper = $this->getServiceLocator()->get('Swissbib\HoldingsHelper');

			$holdingsData = isset($this->fields['holdings']) ? $this->fields['holdings'] : '';

			$holdingsHelper->setData($this->getUniqueID(), $holdingsData);

			$this->holdingsHelper = $holdingsHelper;
		}

		return $this->holdingsHelper;
	}



	/**
	 * Helper to get service locator
	 *
	 * @return    ServiceLocatorInterface
	 */
	protected function getServiceLocator()
	{
		return $this->hierarchyDriverManager->getServiceLocator();
	}



	/**
	 * Get translator
	 *
	 * @return    Translator
	 */
	protected function getTranslator()
	{
		return $this->getServiceLocator()->get('VuFind/Translator');
	}



	/**
	 * Get stop words from 909 fields
	 *
	 * @return    String[]
	 */
	public function getLocalCodes()
	{
		$localCodes   = array();
		$fieldsValues = $this->getMarcSubFieldMaps(909, array(
															 'a' => 'a',
															 'b' => 'b',
															 'c' => 'c',
															 'd' => 'd',
															 'e' => 'e',
															 'f' => 'f',
															 'g' => 'g',
															 'h' => 'h',
														));

		foreach ($fieldsValues as $fieldValues) {
			foreach ($fieldValues as $fieldName => $fieldValue) {
				if (strpos($fieldName, '@') !== 0) {
					$localCodes[] = $fieldValue;
				}
			}
		}

		return $localCodes;
	}



	/**
	 * Get highlighted fulltext
	 *
	 * @return    String
	 */
	public function getHighlightedFulltext()
	{
		// Don't check for highlighted values if highlighting is disabled:
		if (!$this->highlight) {
			return '';
		}

		return (isset($this->highlightDetails['fulltext'][0])) ? trim($this->highlightDetails['fulltext'][0]) : '';
	}



	/**
	 * Get table of content
	 * This method is also used to check whether data for tab is available and the tab should be displayed
	 *
	 * @return	String[]
	 */
	public function getTOC()
	{
		return $this->getTableOfContent() + $this->getContentSummary();
	}



	/**
	 * Get table of content
	 * From fields 505.g.r.t
	 * The combination of the lines of defined by the order of the fields
	 * Possible combinations:
	 * - $g. $t / $r
	 * - $g. $t
	 * - $g. $r
	 * - $t. $r
	 * - $t
	 * - $r
	 *
	 * Use the content of the $debugLog if something seems wrong
	 *
	 * @return	String[]
	 */
	public function getTableOfContent()
	{
		$lines		= array();
		$fieldsData = $this->getMarcSubfieldsRaw(505);
		$debugLog	= array();

		foreach ($fieldsData as $fieldIndex => $field) {
			$maxIndex = sizeof($field) - 1;
			$index    = 0;

			while ($index <= $maxIndex) {
				$hasNext	= isset($field[$index+1]);
				$hasTwoNext	= isset($field[$index+2]);
				$currentTag = $field[$index]['tag'];
				$currentData= $field[$index]['data'];
				$nextTag	= $hasNext ? $field[$index+1]['tag'] : null;
				$nextData	= $hasNext ? $field[$index+1]['data'] : null;
				$twoNextTag	= $hasTwoNext ? $field[$index+2]['tag'] : null;
				$twoNextData= $hasTwoNext ? $field[$index+2]['data'] : null;

				if ($currentTag === 'g') {
					if ($hasNext) {
						if ($nextTag === 't') {
							if ($hasTwoNext && $twoNextTag === 'r') { // $g. $t / $r
								$lines[] = $currentData . '. ' . $nextData . ' / ' . $twoNextData;
								$debugLog[$fieldIndex][] = $index . ' | $g. $t / $r';
								$index += 3;
							} else { // $g. $t
								$lines[] = $currentData . '. ' . $nextData;
								$debugLog[$fieldIndex][] = $index . ' | $g. $t';
								$index += 2;
							}
						} elseif ($nextTag === 'r') {  // $g. $r
							$lines[] = $currentData . '. ' . $nextData;
							$debugLog[$fieldIndex][] = $index . ' | $g. $r';
							$index += 2;
						} else {
								// unknown order
							$debugLog[$fieldIndex][] = $index . ' | unknown order';
							$index += 1;
						}
					}
				} elseif ($currentTag ===  't') {
					if ($hasNext) {
						if ($nextTag === 'r') { // $t / $r
							$lines[] = $currentData . ' / ' . $nextData;
							$debugLog[$fieldIndex][] = $index . ' | $t / $r';
							$index += 2;
						} else { // $t
							$lines[] = $currentData;
							$debugLog[$fieldIndex][] = $index . ' | $t';
							$index += 1;
						}
					} else { // $t
						$lines[] = $currentData;
						$debugLog[$fieldIndex][] = $index . ' | $t';
						$index += 1;
					}
				} elseif ($currentTag ===  'r') { // $r
					$lines[] = $currentData;
					$debugLog[$fieldIndex][] = $index . ' | $r';
					$index += 1;
				} else {
					// unknown order
					$debugLog[$fieldIndex][] = $index . ' | unknown order';
					$index += 1;
				}
			}
		}

		return $lines;
	}



	/**
	 * Get content summary
	 * From fields 520.a
	 *
	 * @return	String[]
	 */
	public function getContentSummary()
	{
		$lines = array();
		$summary = $this->getMarcSubFieldMaps(520, array(
											'a'	=> 'summary',
//											'b'	=> 'expansion'
											), false);

			// Copy into simple list
		foreach ($summary as $item) {
			$lines[] = $item['summary'];
		}

		return $lines;
	}



	/**
	 * Get last indexed date string for sorting
	 *
	 * @return	String
	 */
	public function getLastIndexed()
	{
		return isset($this->fields['time_indexed']) ? $this->fields['time_indexed'] : '';
	}



	/**
	 *
	 *
	 * @param	\File_MARC_Data_Field	$field
	 * @param	String					$fieldIndex
	 */
	protected function getFieldData($field, $fieldIndex)
	{
		// Make sure that there is a t field to be displayed:
		if ($title = $field->getSubfield('t')) {
			$title = $title->getData();
		} else {
			return false;
		}

		$linkTypeSetting = isset($this->mainConfig->Record->marc_links_link_types)
				? $this->mainConfig->Record->marc_links_link_types : 'id,oclc,dlc,isbn,issn,title';
		$linkTypes       = explode(',', $linkTypeSetting);

		$link = false;

		if (in_array('id', $linkTypes)) { // Search ID in field 9
			$linkSubfield = $field->getSubfield('9');
			if ($linkSubfield && $bibLink = $this->getIdFromLinkingField($linkSubfield)) {
				$link = array('type' => 'bib', 'value' => $bibLink);
			}
		} elseif (in_array('ctrlnum', $linkTypes)) { // Extract ctrlnum from field w, ignore the prefix
			$linkFields = $linkFields = $field->getSubfields('w');
			foreach ($linkFields as $current) {
				if (preg_match('/\(([^)]+)\)(.+)/', $current->getData(), $matches)) {
					$link = array('type' => 'ctrlnum', 'value' => $matches[1] . $matches[2]);
				}
			}
		}

		// Found link based on special conditions, stop here
		if ($link) {
			return array(
				'title' => 'note_' . $fieldIndex,
				'value' => $title,
				'link'  => $link
			);
		}

		// Fallback to base method if no custom field found
		return parent::getFieldData($field, $fieldIndex);
	}



	/**
	 * @inheritDoc
	 * @note    Prevent php error for invalid index data. parent_id and sequence should contain the same amount of values which correspond
	 * @return    Array
	 */
	public function getHierarchyPositionsInParents()
	{
		if (isset($this->fields['hierarchy_parent_id'])
				&& isset($this->fields['hierarchy_sequence'])
		) {
			if (sizeof($this->fields['hierarchy_parent_id']) > sizeof($this->fields['hierarchy_sequence'])) {
				$this->fields['hierarchy_parent_id'] = array_slice($this->fields['hierarchy_parent_id'], 0, sizeof($this->fields['hierarchy_sequence']));
			}
		}

		return parent::getHierarchyPositionsInParents();
	}
}

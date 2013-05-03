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
	 * @var	Boolean		Change behaviour if getFormats() to return openUrl compatible formats
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
	 * Get possible ISBN/ISSN numbers from record
	 *
	 * @return    String[]
	 */
	public function getISBNs()
	{
		$tags     = array('020', '022', '024');
		$isbnList = array();

		foreach ($tags as $tag) {
			$fields = $this->getMarcSubFieldMaps($tag, array(
															'a'  => 'isbn',
															'_b' => 'binding',
															'c'  => 'availability',
															'z'  => 'canceled'
													   ));

			foreach ($fields as $field) {
				if (isset($field['isbn'])) {
					$isbnList[] = $field['isbn'];
				}
			}
		}

		// Add ISBN numbers from solr field
		$baseIsbn = parent::getISBNs();
		$isbnList = array_merge($isbnList, $baseIsbn);

		return $isbnList;
	}



	/**
	 *
	 * @return	String[]
	 */
	public function getISSNs()
	{
		$issns	= $this->getISSNsFull();
		$simple	= array();

		foreach ($issns as $issn) {
			if (isset($issn['issn'])) {
				$simple[] = $issn['issn'];
			}
		}

		return $simple;
	}



	/**
	 * Get full ISSN marc fields data
	 *
	 * @return	Array[]
	 */
	public function getISSNsFull()
	{
		return $this->getMarcSubFieldMaps('022', array(
													'a'	=> 'issn',
													'l'	=> 'issn-l',
													'_m'	=> 'canceled-l',
													'_y'	=> 'incorrect',
													'_z'	=> 'canceled',
													'2'	=> 'source'
												));
	}



	/**
	 * Get possible ISBN/ISSN numbers from record
	 *
	 * @return String[]
	 */
	public function getStandardNumbers()
	{
		$tags   = array('020', '022', '024');
		$idList = array();

		foreach ($tags as $tag) {
			$fields = $this->getMarcSubFieldMaps($tag, array(
															'a' => 'id',
															'z' => 'canceled',
															'2' => 'code',
													   ));

			foreach ($fields as $field) {
				if (isset($field['id'])) {
					$idList[] = $field['id'];
				}
			}
		}
	}



	/**
	 * Wrapper for getOpenURL()
	 * Set flag to get special values from getFormats()
	 *
	 * @see		getFormats()
	 * @return	String
	 */
	public function getOpenURL()
	{
		$oldValue	= $this->useOpenUrlFormats;
		$this->useOpenUrlFormats = true;
		$openUrl	= parent::getOpenURL();
		$this->useOpenUrlFormats = $oldValue;

		return $openUrl;
	}



	/**
	 * Get formats. By default, get translated values
	 * If flag useOpenUrlFormats in class is set, get prepared formats for openUrl
	 *
	 * @return	String[]
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
	 * @return	String[]
	 */
	public function getFormatsTranslated()
	{
		$formats	= $this->getFormatsRaw();
		$translator	= $this->getTranslator();

		foreach ($formats as $index => $format) {
			$formats[$index] = $translator->translate($format);
		}

		return $formats;
	}



	/**
	 * Get formats modified to work with openURL
	 * Formats: Book, Journal, Article
	 *
	 * @todo	Currently, all items are marked as "Book", improve detection
	 * @return	String[]
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
	 * @return	String[]
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
	 * Get standard numbers (ISBN, ISSN, ISMN, DOI, URN) for display
	 *
	 * @todo    May need a refactoring to simplify
	 * @return Array
	 */

	/**
	 * Get primary author
	 *
	 * @param    Boolean        $asString
	 * @return    Array|String
	 */
	public function getPrimaryAuthor($asString = true)
	{
		$data = $this->getMarcSubFieldMap(100, $this->personFieldMap);

		if ($asString) {
			$name = isset($data['name']) ? $data['name'] : '';
			$name .= isset($data['forname']) ? $data['forname'] : '';

			return trim($name);
		}

		return $data;
	}



	/**
	 * Get list of secondary authors data
	 *
	 * @param    String        $asString
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
				$stringAuthors[] = trim($name . ' ' . $forename);
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
	 * @param    Boolean        $full    Get full field data. Else only field c is fetched
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
	 * get subject headings from GND subject headings
	 * build an array (multidimensional?) from all GND headings
	 * GND headings
	 * fields: 600, 610, 611, 630, 648, 650, 651, 655
	 *
	 * @ind2=7
	 * subfield $2=gnd
	 * subfields vary per field, build array per field with all
	 * content to be able to treat it in a view helper
	 * @return array
	 */
	/**
	 * Get subject headings
	 *
	 * @return    Array[]
	 */
	public function getGNDSubjectHeadings()
	{
		return $this->getMarcSubFieldMaps(600, array(
													'a' => 'name',
													'b' => 'numeration',
													'c' => 'title',
													'd' => 'lifespan',
													't' => 'work'
											   ));
	}



	/**
	 * get group-id from solr-field to display FRBR-Button
	 *
	 * @return    String|Number
	 */
	public function getGroup()
	{
		return isset($this->fields['group_id']) ? $this->fields['group_id'][0] : '';
	}



	/*
	* Library / Institution Codes
	 *
	* @return	String[]
	*/
	public function getInstitutions()
	{
		return isset($this->fields['institution']) ? $this->fields['institution'] : array();
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
	 * Get topical terms
	 *
	 * @return Array[]
	 */
	public function getTopicalTerms()
	{
		return $this->getMarcSubFieldMaps(650, array(
													'a'  => 'term',
													'b'  => 'term_geographic',
													'c'  => 'location',
													'd'  => 'active_dates',
													'_e' => 'relator_term',
													'q'  => 'label', // @todo real name?
													't'  => 'time', // @todo real name?
													'_v' => 'form_subdivision',
													'_x' => 'general_subdivision',
													'_y' => 'chronological_subdivision',
													'_z' => 'geographical_subdivision',
													'_0' => 'authority_record_control_numer',
													'2'  => 'source_heading',
													'3'  => 'materials',
													'_4' => 'relator_code'
											   ));
	}



	public function getAllSubjectHeadings()
	{
		$retval = array();
		// These are the fields that may contain (controlled or local) subject headings:
		$fields = array(
			'600', '610', '611', '630', '648', '650', '651', '655', '656', '690', '691',
		);

		// Try each MARC field one at a time:
		foreach ($fields as $field) {
			$results = $this->getMarcSubFieldMaps($field, array(
															   'a' => $field . 'a',
															   'b' => $field . 'b',
															   'c' => $field . 'c',
															   'd' => $field . 'd',
															   'e' => $field . 'e',
															   'f' => $field . 'f',
															   'g' => $field . 'g',
															   'h' => $field . 'h',
															   'v' => $field . 'v',
															   'x' => $field . 'x',
															   '0' => $field . '0',
															   '2' => $field . '2',
														  ));

			foreach ($results as $result) {
				$retval[] = $result;
			}
			if (!empty($field)) {
				continue;
			}
		}
		return $retval;
	}



	/**
	 * Get geographic names
	 * Field 651
	 *
	 * @return    Array[]
	 */
	public function getAddedGeographicNames()
	{
		return $this->getMarcSubFieldMaps(651, array(
													'a'  => 'name',
													'_e' => 'relator',
													'_v' => 'form_subdivision',
													'_x' => 'general_subdivision',
													'_y' => 'chronilogical_subdivision',
													'_z' => 'geographical_subdivision',
													'_0' => 'arcn',
													'2'  => 'source',
													'3'  => 'materials',
													'_4' => 'relator_code'
											   ));
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
	 * @param    Boolean        $asStrings
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
				$strings[] = trim(
					(array_key_exists('name', $publication) ? $publication['name'] . ', ' : '')
							. $publication['place']);
			}

			$data = $strings;
		}

		return $data;
	}



	/**
	 * Get physical description out of the MARC record
	 *
	 * @param    Boolean        $asStrings
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
	 * Get formatted content notes (505)
	 *
	 * @return    Array[]
	 */
	public function getFormattedContentNotes()
	{
		return $this->getMarcSubFieldMaps(505, array(
													'a'  => 'notes',
													'_g' => 'misc',
													'_r' => 'responsibility',
													'_t' => 'title',
													'_u' => 'URI'
											   ));
	}



	/**
	 * Get short title
	 * Override base method to assure a string and not an array
	 *
	 * @todo    Still required?
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
	 * @todo    Still required?
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
	 * @return    Array|Boolean
	 */
	public function getHoldings()
	{
		return $this->getHoldingsHelper()->getHoldings();
	}



	/**
	 * Get marc field
	 *
	 * @param    Integer        $index
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
	 * @param    Integer        $index
	 * @return    \File_MARC_Field[]|\File_MARC_List
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
	 * @param    Integer        $index
	 * @param    Array          $fieldMap
	 * @return    Array[]
	 */
	protected function getMarcSubFieldMaps($index, array $fieldMap)
	{
		$subFieldsValues = array();
		$fields          = $this->marcRecord->getFields($index);

		foreach ($fields as $field) {
			$subFieldsValues[] = $this->getMappedFieldData($field, $fieldMap);
		}

		return $subFieldsValues;
	}



	/**
	 * Convert sub fields to array map
	 *
	 * @param    \File_MARC_Data_Field    $field
	 * @param    Array                    $fieldMap
	 * @return    Array
	 */
	protected function getMappedFieldData($field, array $fieldMap)
	{
		$subFieldValues = array(
			'@ind1' => $field->getIndicator(1),
			'@ind2' => $field->getIndicator(2)
		);

		foreach ($fieldMap as $code => $name) {
			if (substr($code, 0, 1) === '_') { // Underscore means repeatable
				$code      = substr($code, 1); // Remove underscore
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
	 * Get value of a sub field
	 *
	 * @param    Integer        $index
	 * @param    String         $subFieldCode
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
	 * @param    Integer            $index
	 * @return    String|Boolean
	 */
	protected function getSimpleMarcFieldValue($index)
	{
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


			$holdingsData   = isset($this->fields['holdings']) ? $this->fields['holdings'] : '';

			$holdingsHelper->setData($this->getUniqueID(), $holdingsData);

			$this->holdingsHelper = $holdingsHelper;
		}

		return $this->holdingsHelper;
	}



	/**
	 * Helper to get service locator
	 *
	 * @return	ServiceLocatorInterface
	 */
	protected function getServiceLocator()
	{
		return $this->hierarchyDriverManager->getServiceLocator();
	}



	/**
	 * Get translator
	 *
	 * @return	Translator
	 */
	protected function getTranslator()
	{
		return $this->getServiceLocator()->get('VuFind/Translator');
	}
}

<?php

/**
 * swissbib / VuFind swissbib enhancements for Summon records
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

use VuFind\RecordDriver\Summon as VuFindSummon;
use Swissbib\RecordDriver\Helper\Holdings as HoldingsHelper;

/**
 * Enhancement for swissbib Summon records
 *
 * @category swissbib_VuFind2
 * @package  RecordDrivers
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://www.swissbib.org
 */
class Summon extends VuFindSummon
{

	/**
	 * @param	String	$fieldName
	 * @param	String	$fallbackValue
	 * @return	String
	 */
	private function getField($fieldName, $fallbackValue = '') {
		return array_key_exists($fieldName, $this->fields) ? $this->fields[$fieldName] : $fallbackValue;
	}

	/**
	 * @return 	String	Author name(s)
	 */
	public function getAuthor() {
		$author = $this->getField('Author', '-');

		return is_array($author) ? implode(', ', $author) : $author;
	}

	/**
	 * @return	Array
	 */
	private function getLinkModel() {
		return $this->getField('LinkModel');
	}

	/**
	 * @return	Array
	 */
	public function getURI() {
		return $this->getField('URI');
	}

	/**
	 * @return	Boolean
	 */
	public function hasDirectLink() {
		return in_array('DirectLink', $this->getLinkModel());
	}

	/**
	 * @return	Boolean
	 */
	public function hasFulltext() {
		return 1 === intval($this->getField('hasFullText'));
	}

	/**
	 * @todo	implement
	 * @return	array
	 */
	public function getAllSubjectVocabularies() {
		return array();
	}

	/**
	 * @todo	implement
	 * @return string
	 */
	public function getAltTitle() {
		return '';
	}
}

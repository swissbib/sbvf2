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



/**
 * enhancement for swissbib MARC records in Solr.
 *
 * @category swissbib_VuFind2
 * @package  RecordDrivers
 * @author   Guenter Hipler  <guenter.hipler@unibas.ch>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://www.swissbib.org
 */
class SbSolrMarc extends VFSolrMarc
{


    protected $marcHoldings;

    public function setRawData($data)
    {
        //only for test purposes within this type to see if the type is correct instantiated
        //Call the parent's set method...
        parent::setRawData($data);

        //todo: integrate the holdings helper (GH)
        //$holdings = trim($data['holdings']);
        //$this->marcHoldings = new \Swissbib\RecordDriver\Helper\HoldingsHelper($holdings);
        //$t =  $this->marcHoldings->getHoldings949(array("b","B","E","j","p","z","Z"),false);



    }

    public function getSubtitle()
    {
        return $this->getFirstFieldValue('245', array('b'));
    }

    public function getEdition()
    {
        return $this->getFirstFieldValue('250', array('a'));
    }

    /* build from controlfield 008 */
    public function getPublicationDates()
    {
        $datetype = substr($this->marcRecord->getField('008')->getData(), 6, 1);
        $year1 = substr($this->marcRecord->getField('008')->getData(), 7, 4);
        $year2 = substr($this->marcRecord->getField('008')->getData(), 11, 4);

        return array($datetype, $year1, $year2);
    }

    /* FRBR-Link */
    public function getGroup()
    {
        return isset($this->fields['group_id']) ? $this->fields['group_id'] : '';
    }

    /* Library / Institution Code as array */
    public function getInstitution()
    {
        return isset($this->fields['institution']) ? $this->fields['institution'] : array();
    }

}

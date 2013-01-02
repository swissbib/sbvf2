<?php



/**
 * swissbib enhancements for Solr aspect of the Search Multi-class (Results)
 *
 * PHP version 5
 *
 * Copyright (C) project swissbib, University Library Basel, Switzerland
 * http://www.swissbib.org  / http://www.swissbib.ch / http://www.ub.unibas.ch
 *
 * Date: 1/1/13
 * Time: 1:23 PM
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
 * @package  Search_Solr
 * @author   Guenter Hipler  <guenter.hipler@unibas.ch>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://www.swissbib.org
 */



namespace Swissbib\Search\Solr;




use VuFind\Search\Solr\Results as VFResults;

/**
 * Solr Search Parameters
 *
 * @category swissbib_VuFind2
 * @package  Search_Solr
 * @author   Guenter Hipler  <guenter.hipler@unibas.ch>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://www.swissbib.org
 */
class Results extends VFResults
{

    /**
     * Support method for _performSearch(): given an array of Solr response data,
     * construct an appropriate record driver object.
     *
     * @param array $data Solr data
     *
     * @return Swissbib\RecordDriver\SbsolrDefault
     */
    protected function initRecordDriver($data)
    {
        // Remember bad classes to prevent unnecessary file accesses.
        static $badClasses = array();

        // Determine driver path based on record type:
        //todo: analyze more sophisticated which types should be processed by types in the SwissBib namespace
        $driver = 'Swissbib\RecordDriver\Solr' . ucwords($data['recordtype']);

        // If we can't load the driver, fall back to the default, index-based one:
        if (isset($badClasses[$driver]) || !@class_exists($driver)) {
            $badClasses[$driver] = 1;
            $driver = 'Swissbib\RecordDriver\SolrDefault';
        }

        // Build the object:
        if (class_exists($driver)) {
            return new $driver($data);
        }

        throw new \Exception('Cannot find record driver -- ' . $driver);
    }


}
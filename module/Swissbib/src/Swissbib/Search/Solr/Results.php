<?php
namespace Swissbib\Search\Solr;

/**
 * swissbib extended Results type for the Solr target
 *
 * PHP version 5
 *
 * Copyright (C) project swissbib, University Library Basel, Switzerland
 * http://www.swissbib.org  / http://www.swissbib.ch / http://www.ub.unibas.ch
 *
 * Date: 11/05/13
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
 * @package  Swissbib\Search\Solr
 * @author   Guenter Hipler  <guenter.hipler@unibas.ch>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://www.swissbib.org
 */

use VuFind\Search\Solr\Results as VFSolrResults;
use VuFindSearch\Query\AbstractQuery;
use VuFindSearch\Query\QueryGroup;
use VuFindSearch\ParamBag;

/**
 * Class to extend the core VF2 SOLR functionality related to Solr Results
 */
class Results extends VFSolrResults
{

	/**
	 * Create backend parameters
	 * Add facet queries for user institutions
	 *
	 * @param	AbstractQuery	$query
	 * @param	Params        	$params
	 * @return	ParamBag
	 */
	protected function createBackendParameters(AbstractQuery $query, Params $params)
	{
		$backendParams = parent::createBackendParameters($query, $params);

		//with SOLR 4.3 AND is no longer the default parameter
		$backendParams->add("q.op", "AND");

		$backendParams = $this->addUserInstitutions($backendParams);

		return $backendParams;
	}



	/**
	 * Add user institutions as facet queries to backend params
	 *
	 * @param	ParamBag	$backendParams
	 * @return	ParamBag
	 */
	protected function addUserInstitutions(ParamBag $backendParams)
	{
		/** @var String[] $favoriteInstitutions */
		$favoriteInstitutions = $this->getParams()->getUserFavoritesInstitutions();

		if (sizeof($favoriteInstitutions > 0)) {
				//facet parameter has to be true in case it's false
			$backendParams->set("facet", "true");

			foreach ($favoriteInstitutions as $institutionCode) {
				$backendParams->add("facet.query", "institution:" . $institutionCode);
				$backendParams->add("bq", "institution:" . $institutionCode . "^5000");
			}
		}

		return $backendParams;
	}



	/**
	 * Get facet queries from result
	 * Data is extracted
	 * Format: {field, value, count, name}
	 *
	 * @param	Boolean		$onlyNonZero
	 * @return	Array[]
	 */
	protected function getResultQueryFacets($onlyNonZero = false)
	{
		/** @var \ArrayObject $queryFacets */
		$queryFacets = $this->responseFacets->getQueryFacets();
		$facets		= array();

		foreach ($queryFacets as $facetName => $queryCount) {
			list($fieldName,$filterValue) = explode(':', $facetName, 2);

			if (!$onlyNonZero || $queryCount > 0) {
				$facets[] = array(
					'field'	=> $fieldName,
					'value'	=> $filterValue,
					'count'	=> $queryCount,
					'name'	=> $facetName
				);
			}
		}

		return $facets;
	}



	/**
	 * Get special facets
	 * - User favorite institutions
	 *
	 * @return	Array[]
	 */
	public function getSpecialFacets()
	{
		$queryFacets	= $this->getResultQueryFacets(true);
		$facetListItems	= array();

		foreach ($queryFacets as $queryFacet) {
			if ($queryFacet['field'] === 'institution') {
				$sortKey	= $queryFacet['count'] . '_' . $queryFacet['value']; // Sortable but unique key

				$facetListItems[$sortKey] = array(
					'value'			=> $queryFacet['value'],
					'displayText'	=> $queryFacet['value'],
					'count'			=> $queryFacet['count'],
					'isApplied'		=> $this->getParams()->hasFilter($queryFacet['name'])
				);
			}
		}

		if (empty($facetListItems)) {
			return array();
		}

			// Sort by count (which is the key)
		ksort($facetListItems);
		$facetListItems = array_values($facetListItems);

		return array(
			'userInstitutions' => array(
				'label'	=> 'mylibraries',
				'field'	=> 'institution',
				'list'	=> $facetListItems
			)
		);
	}



	/**
	 * Get facet list
	 * Add institution query facets on top of the list
	 *
	 * @param	Array|Null		$filter
	 * @return	Array[]
	 */
	public function getFacetList($filter = null)
	{
		$facetList 				= parent::getFacetList($filter);
		$userInstitutionFacets	= $this->getSpecialFacets();

			// Prepend special facets
		$facetList = $userInstitutionFacets + $facetList;

		return $facetList;
	}
}

<?php
namespace Swissbib\Search\Solr;

use VuFind\Search\Solr\Results as VuFindSolrResults;
use VuFindSearch\Query\AbstractQuery;
use VuFindSearch\Query\QueryGroup;
use VuFindSearch\ParamBag;

use Swissbib\Favorites\Manager;

/**
 * Class to extend the core VF2 SOLR functionality related to Solr Results
 */
class Results extends VuFindSolrResults
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
		/** @var Manager $favoritesManger */
		$favoritesManger		= $this->getServiceLocator()->get('Swissbib\FavoriteInstitutions\Manager');
		/** @var String[] $favoriteInstitutions */
		$favoriteInstitutions	= $favoritesManger->getUserInstitutions();

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
				$sortKey	= sprintf('%09d', $queryFacet['count']) . '_' . $queryFacet['value']; // Sortable but unique key

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
		krsort($facetListItems);
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

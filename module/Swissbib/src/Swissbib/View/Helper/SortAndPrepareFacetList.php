<?php
namespace Swissbib\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Vufind\Search\Base\Results;


/**
 * Improved version of VuFind\View\Helper\Root\SortFacetList
 * Add url and sort, but keep all data
 *
 */
class SortAndPrepareFacetList extends AbstractHelper {

	/**
	 * Sort and extend facet list
	 *
	 * @param	Results		$results
	 * @param	String		$field
	 * @param	Array		$list
	 * @param	String		$searchRoute
	 * return	Array
	 */
	public function __invoke(Results $results, $field, array $list, $searchRoute) {
		$facets = array();
		// avoid limit on URL
		$results->getParams()->setLimit($results->getOptions()->getDefaultLimit());
		$urlHelper	= $this->getView()->plugin('url');
		$baseRout	= $urlHelper($searchRoute);

		foreach($list as $facet) {
			$facet['url']	= $baseRout . $results->getUrlQuery()->addFacet($field, $facet['value']);
			$facets[$facet['displayText']] = $facet;
		}

		ksort($facets, SORT_NATURAL);

		return array_values($facets);
	}

}
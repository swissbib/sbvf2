<?php
namespace Swissbib\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Vufind\Search\Base\Results;

/**
 * Improved version of VuFind\View\Helper\Root\SortFacetList
 * Add url and sort, but keep all data
 */
class SortAndPrepareFacetList extends AbstractHelper
{

    /**
     * Sort and extend facet list
     *
     * @param    Results        $results        VuFind\Search\Solr\Results
     * @param    String         $field          Facet group ID, e.g. 'navSubidsbb'
     * @param    Array          $list           Contained items of the facet group
     * @param    String         $searchRoute    e.g. 'search-results'
     * @param    Array            $routeParams
     * @return  Array
     */
    public function __invoke(Results $results, $field, array $list, $searchRoute, array $routeParams = array())
    {
        $facets = array();
        // Avoid limit on URL
//        $results->getParams()->setLimit($results->getOptions()->getDefaultLimit());
        $urlHelper = $this->getView()->plugin('url');
        $baseRoute = $urlHelper($searchRoute, $routeParams);

        foreach ($list as $facetItem) {
            $facetItem['url']                  = $baseRoute . $results->getUrlQuery()->addFacet($field, $facetItem['value']);
            $facets[$facetItem['displayText']] = $facetItem;
        }

        return array_values($facets);
    }
}

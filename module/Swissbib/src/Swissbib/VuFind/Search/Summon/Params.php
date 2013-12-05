<?php


namespace Swissbib\VuFind\Search\Summon;

use VuFind\Search\Summon\Params as VFSummonParams;
use SerialsSolutions_Summon_Query as SummonQuery,
    VuFind\Solr\Utils as SolrUtils,
    VuFindSearch\ParamBag;


class Params extends VFSummonParams
{


    public function getSearchClassId()
    {



        //$class = explode('\\', get_class($this));
        //return $class[2];
        //we can't use the basic VuFind mechanism return class[2] because our namespace is build as
        //Swissbib/Vufind/Search/[specialized Search target]
        //therefor it has o be $class[3]
        //My guess: the whole Design related to search types will be refactored by VuFind in the upcoming time (More intensive use of EventManager)
        //so return the name of the target makes it more explicit for a type only responsible for Summon results
        return 'Summon';

    }

    /**
     * Set up filters based on VuFind settings.
     *
     * @param ParamBag $params     Parameter collection to update
     *
     * @return void
     */
    public function createBackendFilterParameters(ParamBag $params)
    {
        // flag our non-Standard checkbox filters:
        $foundIncludeNewspapers = false;        # includeNewspapers
        $foundIncludeWithoutFulltext = false;   # includeWithoutFulltext
        $filterList = $this->getFilterList();
        // Which filters should be applied to our query?
        if (!empty($filterList)) {
            // Loop through all filters and add appropriate values to request:
            foreach ($filterList as $filterArray) {
                foreach ($filterArray as $filt) {
                    $safeValue = SummonQuery::escapeParam($filt['value']);
                    if ($filt['field'] == 'holdingsOnly') {
                        // Special case -- "holdings only" is a separate parameter from
                        // other facets.
                        $params->set('holdings', strtolower(trim($safeValue)) == 'true');
                    } else if ($filt['field'] == 'excludeNewspapers') {
                        // support a checkbox for excluding newspapers:
                        // this is now the default behaviour.
                    } else if ($filt['field'] == 'includeNewspapers') {
                        // explicitly include newspaper articles
                        $foundIncludeNewspapers = true;
                    } else if ($range = SolrUtils::parseRange($filt['value'])) {
                        // Special case -- range query (translate [x TO y] syntax):
                        $from = SummonQuery::escapeParam($range['from']);
                        $to = SummonQuery::escapeParam($range['to']);
                        $params->add('rangeFilters', "PublicationDate,{$from}:{$to}");
                    } else if ($filt['field'] == 'includeWithoutFulltext') {
                        $foundIncludeWithoutFulltext = true;
                    } else {
                        // Standard case:
                        $params->add('filters', "{$filt['field']},{$safeValue}");
                    }
                }
            }
        }
        // special cases (apply also when filter list is empty)
        // newspaper articles
        if ( ! $foundIncludeNewspapers ) {
            // this actually means: do *not* show newspaper articles
            $params->add('filters', "ContentType,Newspaper Article,true");
        }
        // combined facet "with holdings/with fulltext"
        if ( !$foundIncludeWithoutFulltext ) {
            $params->set('holdings', true);
            $params->add('filters',  'IsFullText,true');

        } else {
            $params->set('holdings', false);
        }
    }



    /**
     * @return string
     */
    public function getTypeLabel()
    {
        return $this->getServiceLocator()->get('Swissbib\TypeLabelMappingHelper')->getLabel($this);
    }

}
<?php


namespace Swissbib\Vufind\Search\Summon;

use SerialsSolutions_Summon_Query as SummonQuery,
    VuFind\Exception\RecordMissing as RecordMissingException,
    VuFind\Search\Base\Results as BaseResults,
    VuFind\Solr\Utils as SolrUtils,
    VuFindSearch\ParamBag;

use VuFind\Search\Summon\Results as VFSummonResults;


class Results extends VFSummonResults
{
    /**
     * Set up filters based on VuFind settings.
     *
     * @param ParamBag $params     Parameter collection to update
     * @param array    $filterList Filter settings
     *
     * @return void
     */
    public function createBackendFilterParameters(ParamBag $params, $filterList)
    {
        // flag our non-Standard checkbox filters:
        $foundIncludeNewspapers = false;        # includeNewspapers
        $foundIncludeWithoutFulltext = false;   # includeWithoutFulltext

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
     * Turn the list of spelling suggestions into an array of urls
     *   for on-screen use to implement the suggestions.
     *
     * @return array Spelling suggestion data arrays
     */
    public function getSpellingSuggestions()
    {
        $retVal = array();
        foreach ($this->getRawSuggestions() as $term => $details) {
            foreach ($details['suggestions'] as $word) {
                // Strip escaped characters in the search term (for example, "\:")
                $term = stripcslashes($term);
                $word = stripcslashes($word);
                // strip enclosing parentheses
                $from = array( '/^\(/', '/\)$/');
                $to = array('','');
                $term = preg_replace($from,$to,$term);
                $word = preg_replace($from,$to,$word);
                $retVal[$term]['suggestions'][$word] = array('new_term' => $word);
            }
        }
        return $retVal;
    }

}

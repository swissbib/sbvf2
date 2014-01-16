<?php
namespace Jusbib\VuFind\Search\Solr;

use Swissbib\VuFind\Search\Solr\Options as SwissbibSolrOptions;

/*
 * Class to extend the core VF2 SOLR functionality related to Options
 */
class Options extends SwissbibSolrOptions
{

    /**
     * Return the route name of the action used for performing advanced searches.
     * Returns false if the feature is not supported.
     *
     * @return string|bool
     */
    public function getAdvancedSearchClassificationAction()
    {
        return 'search-advancedClassification';
    }

}

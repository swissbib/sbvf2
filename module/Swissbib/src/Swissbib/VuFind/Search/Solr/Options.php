<?php
namespace Swissbib\VuFind\Search\Solr;

use VuFind\Search\Solr\Options as VuFindSolrOptions;

/*
 * Class to extend the core VF2 SOLR functionality related to Options
 */
class Options extends VuFindSolrOptions
{

    /**
     * Set default limit
     *
     * @param    Integer        $limit
     */
    public function setDefaultLimit($limit)
    {
        $this->defaultLimit = intval($limit);
    }
}

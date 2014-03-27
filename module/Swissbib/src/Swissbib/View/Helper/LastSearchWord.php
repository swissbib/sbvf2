<?php
namespace Swissbib\View\Helper;

use Zend\View\Helper\AbstractHelper;
use VuFind\Search\Memory;

/**
 * Get last search word from search memory
 * the search word (lookfor) is part of the search URL
 *
 */
class LastSearchWord extends AbstractHelper
{

    /**
     * Get last search word
     *
     * @return    String
     */
    public function __invoke()
    {
        $lookFor       = '';

        $lastSearchUrl = $this->getView()->plugin('getextendedlastsearchlink')->getLinkOnly();
        $lastSearch    = parse_url($lastSearchUrl);

        if (isset($lastSearch['query'])) {
            parse_str($lastSearch['query'], $queryParts);

            if (isset($queryParts['lookfor'])) {
                $lookFor = trim($queryParts['lookfor']);
            }
        }

        return $lookFor;
    }
}

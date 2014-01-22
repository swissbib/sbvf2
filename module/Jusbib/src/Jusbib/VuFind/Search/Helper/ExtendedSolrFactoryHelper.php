<?php
namespace Jusbib\VuFind\Search\Helper;

use Swissbib\VuFind\Search\Helper\ExtendedSolrFactoryHelper as SwissbibExtendedSolrFactoryHelper;

class ExtendedSolrFactoryHelper extends SwissbibExtendedSolrFactoryHelper
{

    /**
     * Get namespace
     * swissbib namespace for extensible targets, else default namespace
     *
     * @param	String			$name
     * @param	String			$requestedName
     * @return	String
     */
    public function getNamespace($name, $requestedName)
    {
        if ($this->isExtendedTarget($name, $requestedName)) {
            return 'Jusbib\VuFind\Search';
        } else {
            return 'VuFind\Search';
        }
    }

}

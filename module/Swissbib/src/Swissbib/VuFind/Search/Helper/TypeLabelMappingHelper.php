<?php
namespace Swissbib\VuFind\Search\Helper;

use VuFind\Search\Base\Params;
use VuFindSearch\Query\Query;

class TypeLabelMappingHelper
{

    /**
     * @param   Params $params
     * @return  string
     */
    public function getLabel(Params $params)
    {
        if ($params->getQuery() instanceof Query) {
            $type = strtolower($params->getQuery()->getHandler());
            if ($type !== 'allfields') return 'adv_search_' . $type;
        }

        return 'All_Fields';
    }

} 
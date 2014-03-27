<?php
namespace Swissbib\View\Helper;

use Zend\View\Helper\AbstractHelper;

/**
 * Render facet item
 */
class FacetItem extends AbstractHelper
{

    /**
     * Render facet item
     *
     * @param    Array        $facetData
     * @param    String        $facetType
     * @return    String
     */
    public function __invoke(array $facetData, $facetType)
    {
        $facetData = array(
            'facet' => $facetData,
            'type'    => $facetType
        );

        return $this->getView()->render('search/sidebar/facet-item', $facetData);
    }
}
